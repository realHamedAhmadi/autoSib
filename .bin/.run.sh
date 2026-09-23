#!/usr/bin/env bash

# Resolve project directory dynamically (one directory above this script)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Detect OS environment
IS_TERMUX=false
if [ -n "$TERMUX_VERSION" ]; then
    IS_TERMUX=true
fi

# Configuration
LOG_DIR="$HOME/.service_logs"
HOST="0.0.0.0"
PORT="8000"
APP_URL="http://127.0.0.1:${PORT}"
BROWSER_FLAG_FILE="$HOME/.enable_browser"

if [ "$IS_TERMUX" = true ]; then
    PG_DATA_DIR="$PREFIX/var/lib/postgresql"
fi

# Ensure hidden log directory and log files exist
mkdir -p "$LOG_DIR"
touch "$LOG_DIR/postgres.log" "$LOG_DIR/laravel_server.log" "$LOG_DIR/laravel_queue.log"

# ----------------------------------------------------
# 1. PostgreSQL Service
# ----------------------------------------------------
if [ "$IS_TERMUX" = true ]; then
    if pg_isready -q; then
        echo "[INFO] PostgreSQL is already running (Termux)."
    else
        echo "[INFO] Starting PostgreSQL (Termux)..."
        pg_ctl -D "$PG_DATA_DIR" -l "$LOG_DIR/postgres.log" start
    fi
else
    # Ubuntu environment: use systemctl or service
    if pg_isready -q; then
        echo "[INFO] PostgreSQL is already running (Ubuntu)."
    else
        echo "[INFO] Starting PostgreSQL (Ubuntu)..."
        if command -v systemctl > /dev/null 2>&1; then
            sudo systemctl start postgresql
        else
            sudo service postgresql start
        fi
    fi
fi

# ----------------------------------------------------
# 2. Check Project Directory
# ----------------------------------------------------
if [ ! -d "$PROJECT_DIR" ]; then
    echo "[ERROR] Project directory not found: $PROJECT_DIR"
    exit 1
fi

echo "[INFO] Target project directory: $PROJECT_DIR"
cd "$PROJECT_DIR" || exit 1

# ----------------------------------------------------
# 3. Git Pull (Fetch latest code)
# ----------------------------------------------------
if [ -d ".git" ]; then
    echo "[INFO] Pulling latest changes from Git repository..."
    git pull || echo "[WARN] Git pull failed (network offline or merge conflicts). Continuing..."
else
    echo "[WARN] Not a git repository. Skipping git pull."
fi

# ----------------------------------------------------
# 4. Laravel Cache & Optimization
# ----------------------------------------------------
echo "[INFO] Clearing cached configuration and routes..."
php artisan optimize:clear

echo "[INFO] Re-optimizing Laravel caches..."
php artisan optimize

# ----------------------------------------------------
# 5. Laravel Migration
# ----------------------------------------------------
echo "[INFO] Running artisan migrate..."
php artisan migrate --force || echo "Migration skipped or encountered an issue."

# ----------------------------------------------------
# 6. Laravel Web Server (artisan serve)
# ----------------------------------------------------
if pgrep -f "artisan serve" > /dev/null; then
    echo "[INFO] Laravel server is already running."
else
    echo "[INFO] Starting Laravel server on ${HOST}:${PORT}..."
    nohup php artisan serve --host="$HOST" --port="$PORT" >> "$LOG_DIR/laravel_server.log" 2>&1 &
fi

# ----------------------------------------------------
# 7. Laravel Queue Worker (artisan queue:work)
# ----------------------------------------------------
if pgrep -f "artisan queue:work" > /dev/null; then
    echo "[INFO] Laravel queue worker is already running."
else
    echo "[INFO] Starting Laravel queue worker..."
    nohup php artisan queue:work --sleep=3 --tries=3 >> "$LOG_DIR/laravel_queue.log" 2>&1 &
fi

echo "[SUCCESS] All services verified."

# ----------------------------------------------------
# 8. Open Default Browser (Conditional)
# ----------------------------------------------------
if [ -f "$BROWSER_FLAG_FILE" ]; then
    echo "[INFO] Auto-open browser is enabled. Launching..."
    sleep 2
    if [ "$IS_TERMUX" = true ]; then
        termux-open-url "$APP_URL"
    else
        # Ubuntu environment: check for GUI session and xdg-open
        if [ -n "$DISPLAY" ] || [ -n "$WAYLAND_DISPLAY" ]; then
            if command -v xdg-open > /dev/null 2>&1; then
                xdg-open "$APP_URL" > /dev/null 2>&1 &
            elif command -v sensible-browser > /dev/null 2>&1; then
                sensible-browser "$APP_URL" > /dev/null 2>&1 &
            fi
        else
            echo "[WARN] No graphical display detected. Skipping browser launch."
        fi
    fi
else
    echo "[INFO] Auto-open browser is disabled. Skipping."
fi
