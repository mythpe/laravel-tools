<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands\Export;

use Illuminate\Support\Facades\Storage;
use Myth\LaravelTools\Console\BaseCommand;

class ExportLanguageFilesCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:export-lang
{--f|flip : File choice files}
{--o|output=deploy : Output path}
{--d|disk=setup : Output Disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export language files to json';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->applyCustomStyle();
        $this->info('Start Export');
        $langDisk = Storage::disk('lang');
        $this->diskName = $this->option('disk');
        $outputDisk = $this->disk();
        $locales = $langDisk->allDirectories();
        $configFiles = config('4myth-tools.js_lang_command_files', '*');
        // $configFiles = '*';
        $dir = $this->option('output');
        $flipChoiceFiles = $this->option('flip');
        foreach ($locales as $locale) {
            if ($configFiles == '*') {
                $files = $langDisk->allFiles($locale);
            }
            else {
                $files = collect($langDisk->allFiles($locale))->filter(fn($e) => in_array(pathinfo($e, PATHINFO_FILENAME), $configFiles))->values()->toArray();
            }
            foreach ($files as $file) {
                $fileName = pathinfo($file, PATHINFO_FILENAME);
                $data = collect(require $langDisk->path($file));
                if ($fileName == 'choice' && $locale == 'ar') {
                    $data = $data->map(function ($v) {
                        $res = explode('|', $v);
                        if (count($res) == 2) {
                            return implode('|', [$res[1], $res[0]]);
                        }

                        return $v;
                    });
                }
                $path = "$dir/$locale/$fileName.json";
                $outputDisk->put($path, $data->toJson(JSON_UNESCAPED_UNICODE));
                $o = str_ireplace(base_path(), '', $outputDisk->path($path));
                $o = str_ireplace('/', '\\', $o);
                $o = trim($o, '/\\');
                $this->components->info("JSON: [$o]");
            }
        }
    }
}
