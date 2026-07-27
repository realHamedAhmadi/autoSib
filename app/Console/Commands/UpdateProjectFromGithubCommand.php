<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class UpdateProjectFromGithubCommand extends Command
{
    /**
     * Command signature.
     *
     * Example:
     * php artisan app:update-from-github realHamedAhmadi autoSib --branch=main
     */
    protected $signature = 'app:update-from-github
                            {owner : GitHub repository owner}
                            {repo : GitHub repository name}
                            {--branch=main : Branch name}
                            {--token= : GitHub token for private repositories}';

    /**
     * Command description.
     */
    protected $description = 'Download a project zip from GitHub, extract it, and replace current project files safely';

    /**
     * Paths or names to keep untouched during update.
     */
    private array $protectedItems = [
        '.env',
        '.git',
        'storage',
        'node_modules',
        '.idea',
        '.vscode',
    ];

    public function handle(): int
    {
        $owner = $this->argument('owner');
        $repo = $this->argument('repo');
        $branch = $this->option('branch');
        $token = $this->option('token');

        $basePath = base_path();
        $workDir = storage_path('app/updater');
        $zipPath = $workDir . '/release.zip';
        $extractPath = $workDir . '/extracted';

        $this->info('Starting update process...');

        try {
            $this->prepareWorkingDirectories($workDir, $extractPath);

            $downloadUrl = "https://codeload.github.com/{$owner}/{$repo}/zip/refs/heads/{$branch}";
            $this->info("Downloading from: {$downloadUrl}");

            $this->downloadZip($downloadUrl, $zipPath, $token);
            $this->extractZip($zipPath, $extractPath);

            $sourceRoot = $this->detectExtractedRoot($extractPath);

            if (!$sourceRoot) {
                $this->error('Could not detect extracted repository root directory.');
                return self::FAILURE;
            }

            $this->info("Detected extracted root: {$sourceRoot}");

            $this->replaceProjectFiles($sourceRoot, $basePath);

            $this->callSilent('optimize:clear');

            $this->info('Project updated successfully.');
            $this->warn('If needed, run migrations manually: php artisan migrate --force');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Update failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Prepare temporary working directories.
     */
    private function prepareWorkingDirectories(string $workDir, string $extractPath): void
    {
        if (File::exists($workDir)) {
            File::deleteDirectory($workDir);
        }

        File::makeDirectory($workDir, 0755, true);
        File::makeDirectory($extractPath, 0755, true);
    }

    /**
     * Download the repository zip file from GitHub.
     */
    private function downloadZip(string $url, string $zipPath, ?string $token = null): void
    {
        $request = Http::timeout(120)->withOptions([
            'stream' => true,
        ]);

        if (!empty($token)) {
            $request = $request->withToken($token);
        }

        $response = $request->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub download failed with status {$response->status()}.");
        }

        File::put($zipPath, $response->body());

        if (!File::exists($zipPath) || File::size($zipPath) === 0) {
            throw new \RuntimeException('Downloaded zip file is empty or missing.');
        }
    }

    /**
     * Extract zip archive to destination.
     */
    private function extractZip(string $zipPath, string $extractPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Failed to open zip archive.');
        }

        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            throw new \RuntimeException('Failed to extract zip archive.');
        }

        $zip->close();
    }

    /**
     * Detect the root directory inside extracted GitHub archive.
     */
    private function detectExtractedRoot(string $extractPath): ?string
    {
        $items = File::directories($extractPath);

        if (count($items) !== 1) {
            return null;
        }

        return $items[0];
    }

    /**
     * Replace current project files with downloaded files.
     */
    private function replaceProjectFiles(string $sourceRoot, string $targetRoot): void
    {
        $this->info('Replacing project files...');

        $items = scandir($sourceRoot);

        if ($items === false) {
            throw new \RuntimeException('Failed to read extracted source directory.');
        }

        foreach ($items as $item) {
            if (in_array($item, ['.', '..'], true)) {
                continue;
            }

            if ($this->isProtected($item)) {
                $this->line("Skipping protected item: {$item}");
                continue;
            }

            $sourcePath = $sourceRoot . DIRECTORY_SEPARATOR . $item;
            $targetPath = $targetRoot . DIRECTORY_SEPARATOR . $item;

            if (is_dir($sourcePath)) {
                if (File::exists($targetPath)) {
                    File::deleteDirectory($targetPath);
                }

                File::copyDirectory($sourcePath, $targetPath);
                $this->line("Updated directory: {$item}");
            } else {
                if (File::exists($targetPath)) {
                    File::delete($targetPath);
                }

                File::copy($sourcePath, $targetPath);
                $this->line("Updated file: {$item}");
            }
        }
    }

    /**
     * Check if an item should not be replaced.
     */
    private function isProtected(string $item): bool
    {
        return in_array($item, $this->protectedItems, true);
    }
}
