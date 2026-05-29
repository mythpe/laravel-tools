<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands\Export;

use Myth\LaravelTools\Console\BaseCommand;
use Myth\LaravelTools\Utilities\Helpers;

class ExportLanguageCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:lang
{--f|flip : File choice files}
{--t|type=json : Export type like json | undot | dot}
{--e|ext=json : Export Extension}
{--o|output=deploy : Output path}
{--d|disk=setup : Output Disk}
{--F|files=* : Files with export}';

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
        $langDisk = Helpers::langDisk();
        $ext = $this->option('ext');
        $this->diskName = $this->option('disk');
        $outputDisk = $this->disk();
        $localeDirectories = $langDisk->allDirectories();
        $configFiles = $this->option('files');
        if (empty($configFiles)) {
            $configFiles = ['attributes', 'choice', 'const', 'global', 'labels', 'replace'];
        }
        if (!is_array($configFiles)) {
            $configFiles = [$configFiles];
        }
        $dir = $this->option('output');
        $flipChoiceFiles = $this->option('flip');
        $empty = [];
        $array = [];
        foreach ($localeDirectories as $localeDirectory) {
            $array[$localeDirectory] ??= [];
            if ($configFiles == '*' || (count($configFiles) == 1 && $configFiles[0] == '*')) {
                $files = $langDisk->allFiles($localeDirectory);
            }
            else {
                $files = collect($langDisk->allFiles($localeDirectory))
                    ->filter(
                        fn($e) => in_array(pathinfo($e, PATHINFO_FILENAME), $configFiles)
                    )->values()->toArray();
            }
            //dd($files);
            foreach ($files as $file) {
                $info = pathinfo($file);
                $fileName = $info['filename'];
                $extension = $info['extension'];
                $data = collect();
                if ($extension == 'php') {
                    $data = collect(require $langDisk->path($file));
                }
                if ($extension == 'json') {
                    $data = collect(json_decode(trim($langDisk->get($file)), !0));
                }
                if ($fileName == 'choice' && $localeDirectory == 'ar') {
                    $data = $data->map(function ($v) {
                        $res = explode('|', $v);
                        if (count($res) == 2) {
                            return implode('|', [$res[1], $res[0]]);
                        }
                        return $v;
                    });
                }
                //$data = $data->map(fn($t) => preg_replace('/:(\w+)/', '{$1}', $t));
                $data = $data->map(fn($t) => preg_replace_callback('/:(\w+)/', fn($m) => '{'.strtolower($m[1]).'}', $t));
                $array[$localeDirectory][$fileName] ??= collect();
                $array[$localeDirectory][$fileName] = $array[$localeDirectory][$fileName]->merge($data);
            }
        }
        foreach ($array as $locale => $files) {
            foreach ($files as $fileName => $data) {
                $outDir = "$dir/$locale";
                if (!in_array($locale, $empty)) {
                    $outputDisk->deleteDirectory($outDir);
                    $empty[] = $locale;
                }
                $path = "$outDir/$fileName.$ext";
                $type = $this->option('type');
                if (method_exists($data, $type)) {
                    $data = $data->{$type}();
                }
                $outputDisk->put($path, $data->toJson(JSON_UNESCAPED_UNICODE));
                $o = str_ireplace(base_path(), '', $outputDisk->path($path));
                $o = str_ireplace('/', '\\', $o);
                $o = trim($o, '/\\');
                $this->components->info("JSON: [$o]");
            }
        }
    }
}
