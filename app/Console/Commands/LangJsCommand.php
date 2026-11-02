<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LangJsCommand extends Command
{
    protected $signature = 'lang:js {path=public/js/lang/messages.js}';

    protected $description = 'Generate JS localization file from Laravel lang files';

    public function handle()
    {
        $langPath = base_path('lang');

        if (!File::exists($langPath)) {
            $this->error("Lang folder not found at: " . $langPath);
            return;
        }

        $outputPath = base_path($this->argument('path'));

        $locales = File::directories($langPath);

        $messages = [];

        foreach ($locales as $localePath) {
            $locale = basename($localePath);
            $files = File::files($localePath);

            foreach ($files as $file) {
                $fileName = pathinfo($file, PATHINFO_FILENAME);
                $messages[$locale][$fileName] = require $file;
            }
        }

        $jsContent = "window.Lang = " . json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";";

        File::ensureDirectoryExists(dirname($outputPath));

        File::put($outputPath, $jsContent);

        $this->info("Lang.js generated successfully at: " . $outputPath);
    }
}
