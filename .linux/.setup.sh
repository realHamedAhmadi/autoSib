#!/usr/bin/env bash
set -e

# ---------------------------
# Helpers
# ---------------------------

log() { printf ">>> %s\n" "$*"; }
warn() { printf "WARNING: %s\n" "$*" >&2; }
die() { printf "ERROR: %s\n" "$*" >&2; exit 1; }

# Update or append .env variables
update_env_var() {
    key="$1"
    value="$2"
    file="$3"

    if grep -q "^#\?[[:space:]]*${key}=" "$file"; then
        sed -i "s|^#\?[[:space:]]*${key}=.*|${key}=${value}|" "$file"
    else
        printf "%s=%s\n" "$key" "$value" >> "$file"
    fi
}

# Verify GD is enabled for current PHP CLI
assert_php_gd() {
    if ! php -m 2>/dev/null | grep -qi '^gd$'; then
        printf "ERROR: PHP GD extension is not enabled for PHP CLI.\n" >&2
        printf "PHP version: %s\n" "$(php -r 'echo PHP_VERSION;')" >&2
        printf "%s\n" "$(php --ini 2>/dev/null)" >&2
        return 1
    fi
    return 0
}

# ---------------------------
# Inputs
# ---------------------------

printf "=== PostgreSQL Configuration Setup ===\n"

printf "Enter Database Name [laravel_db]: "
read -r input_db_name
DB_NAME="${input_db_name:-laravel_db}"

printf "Enter Database Username [laravel_user]: "
read -r input_db_user
DB_USER="${input_db_user:-laravel_user}"

printf "Enter Database Password [secret]: "
stty -echo
read -r input_db_pass
stty echo
printf "\n"
DB_PASS="${input_db_pass:-secret}"

DB_HOST="127.0.0.1"
DB_PORT="5432"

# ---------------------------
# Detect environment
# ---------------------------

log "Detecting environment..."

IS_TERMUX=0
if [ -n "${TERMUX_VERSION:-}" ] || [ -d "/data/data/com.termux" ]; then
    IS_TERMUX=1
fi

# Escalation tool for Debian/Ubuntu
SUDO=""
if [ "$IS_TERMUX" -eq 0 ]; then
    if [ "$(id -u)" -ne 0 ]; then
        SUDO="sudo"
    fi
fi

# ---------------------------
# Install packages + PostgreSQL startup
# ---------------------------

if [ "$IS_TERMUX" -eq 1 ]; then
    log "Environment: Termux detected."

    pkg update -y

    # Install PHP, required extensions, Composer, and PostgreSQL
    pkg install -y \
        php \
        php-pgsql \
        php-gd \
        php-intl \
        php-bcmath \
        postgresql \
        git \
        curl \
        unzip

    # Initialize DB if needed
    if [ ! -d "$PREFIX/var/lib/postgresql" ]; then
        mkdir -p "$PREFIX/var/lib/postgresql"
        initdb "$PREFIX/var/lib/postgresql"
    fi

    # Start PostgreSQL if not ready
    if ! pg_isready -q 2>/dev/null; then
        mkdir -p "$PREFIX/var/log" || true
        pg_ctl -D "$PREFIX/var/lib/postgresql" -l "$PREFIX/var/log/pgsql.log" start || true
        sleep 2
    fi

    # Run psql in Termux
    run_psql() {
        psql -d postgres -tAc "$1"
    }

else
    log "Environment: Standard Linux (Ubuntu/Debian) detected."

    $SUDO apt-get update -y

    # Base tools
    $SUDO apt-get install -y curl git unzip software-properties-common

    # PHP + extensions required by Laravel and maatwebsite/excel/phpoffice/phpspreadsheet
    $SUDO apt-get install -y \
        php-cli \
        php-curl \
        php-mbstring \
        php-xml \
        php-zip \
        php-pgsql \
        php-gd \
        php-bcmath \
        php-intl \
        postgresql \
        postgresql-contrib

    # Install Composer if missing
    if ! command -v composer >/dev/null 2>&1; then
        log "Installing Composer..."
        curl -sS https://getcomposer.org/installer | php
        $SUDO mv composer.phar /usr/local/bin/composer
        $SUDO chmod +x /usr/local/bin/composer
    fi

    # Start PostgreSQL
    if command -v systemctl >/dev/null 2>&1; then
        $SUDO systemctl enable postgresql
        $SUDO systemctl start postgresql
    else
        $SUDO service postgresql start
    fi

    # Run psql as postgres (works both as root and non-root)
    run_psql() {
        if [ "$(id -u)" -eq 0 ]; then
            su - postgres -c "psql -tAc \"$1\""
        else
            sudo -u postgres psql -tAc "$1"
        fi
    }
fi

# ---------------------------
# Configure PostgreSQL (user/db)
# ---------------------------

log "Configuring database..."

USER_EXISTS="$(run_psql "SELECT 1 FROM pg_roles WHERE rolname = '$DB_USER'")" || USER_EXISTS=""
if [ "$USER_EXISTS" != "1" ]; then
    run_psql "CREATE USER \"$DB_USER\" WITH PASSWORD '$DB_PASS';"
else
    run_psql "ALTER USER \"$DB_USER\" WITH PASSWORD '$DB_PASS';"
fi

DB_EXISTS="$(run_psql "SELECT 1 FROM pg_database WHERE datname = '$DB_NAME'")" || DB_EXISTS=""
if [ "$DB_EXISTS" != "1" ]; then
    run_psql "CREATE DATABASE \"$DB_NAME\" OWNER \"$DB_USER\";"
fi

run_psql "GRANT ALL PRIVILEGES ON DATABASE \"$DB_NAME\" TO \"$DB_USER\";"

log "PostgreSQL setup completed successfully."

# ---------------------------
# Laravel project in parent directory
# ---------------------------

LARAVEL_DIR=".."
ENV_PATH="$LARAVEL_DIR/.env"
ENV_EXAMPLE_PATH="$LARAVEL_DIR/.env.example"
ARTISAN_PATH="$LARAVEL_DIR/artisan"
AUTOLOAD_PATH="$LARAVEL_DIR/vendor/autoload.php"
COMPOSER_JSON="$LARAVEL_DIR/composer.json"

# Ensure .env exists
if [ ! -f "$ENV_PATH" ]; then
    if [ -f "$ENV_EXAMPLE_PATH" ]; then
        log ".env not found. Copying from .env.example..."
        cp "$ENV_EXAMPLE_PATH" "$ENV_PATH"
    else
        log ".env not found. Creating blank .env..."
        touch "$ENV_PATH"
    fi
fi

# Update DB env vars
log "Updating .env database variables..."
update_env_var "DB_CONNECTION" "pgsql" "$ENV_PATH"
update_env_var "DB_HOST" "$DB_HOST" "$ENV_PATH"
update_env_var "DB_PORT" "$DB_PORT" "$ENV_PATH"
update_env_var "DB_DATABASE" "$DB_NAME" "$ENV_PATH"
update_env_var "DB_USERNAME" "$DB_USER" "$ENV_PATH"
update_env_var "DB_PASSWORD" "$DB_PASS" "$ENV_PATH"

# ---------------------------
# Composer install (reliable vendor check)
# ---------------------------

[ -f "$COMPOSER_JSON" ] || die "composer.json not found in $LARAVEL_DIR"

log "Checking PHP GD extension (required by PhpSpreadsheet)..."
assert_php_gd || die "Enable/install php-gd for the PHP CLI that runs composer."

log "Installing Composer dependencies (this will create vendor/)..."
(
    cd "$LARAVEL_DIR"
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-interaction --prefer-dist --optimize-autoloader
)

[ -f "$AUTOLOAD_PATH" ] || die "Composer did not create vendor/autoload.php. Check composer output."

# ---------------------------
# Artisan tasks
# ---------------------------

if [ -f "$ARTISAN_PATH" ]; then
    log "Running artisan key:generate..."
    php "$ARTISAN_PATH" key:generate --force

    log "Running artisan migrate..."
    php "$ARTISAN_PATH" migrate --force || warn "Migration skipped or encountered an issue."
else
    warn "artisan not found at $ARTISAN_PATH; skipping artisan commands."
fi

log "Setup finished successfully."
