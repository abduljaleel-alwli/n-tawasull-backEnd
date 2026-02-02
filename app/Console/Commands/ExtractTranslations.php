<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class ExtractTranslations extends Command
{
    protected $signature = 'lang:extract {lang=ar}';
    protected $description = 'Extract __() translation strings into a JSON language file';

    public function handle(): int
    {
        $lang = $this->argument('lang');
        $outputPath = resource_path("lang/{$lang}.json");

        $strings = [];

        $finder = new Finder();

        $finder
            ->files()
            ->in([
                app_path(),
                resource_path(),
                base_path('routes'),
            ])
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs();

        foreach ($finder as $file) {
            $contents = $file->getContents();

            preg_match_all(
                "/__\(\s*[\"']([^\"']+)[\"']\s*\)/u",
                $contents,
                $matches
            );

            foreach ($matches[1] as $text) {
                $strings[$text] = $strings[$text] ?? $text;
            }
        }

        // دمج مع الملف القديم إن وجد
        if (File::exists($outputPath)) {
            $existing = json_decode(File::get($outputPath), true) ?? [];
            $strings = array_merge($strings, $existing);
        }

        File::ensureDirectoryExists(dirname($outputPath));

        File::put(
            $outputPath,
            json_encode(
                $strings,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );

        $this->info('✔ Extracted ' . count($strings) . ' translation strings.');
        $this->info("✔ Saved to: {$outputPath}");

        return self::SUCCESS;
    }
}
