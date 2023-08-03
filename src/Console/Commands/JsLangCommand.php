<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands;

use Myth\LaravelTools\Console\BaseCommand;

class JsLangCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:js-lang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export language files to JS';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->applyCustomStyle();
        $this->info("Start deploy");
        $locales = config('4myth-tools.locales');
        $disk = $this->disk();
        $dir = 'deploy';
        $files = config('4myth-tools.js_lang_command_files', []);
        foreach ($locales as $locale) {
            foreach ($files as $file) {
                $lang = __($file, [], $locale);
                if (!is_array($lang)) {
                    $this->error(sprintf(
                        'File %s/%s is empty',
                        $locale,
                        $lang,
                    ));
                    continue;
                }
                $data = collect($lang);
                if ($file == 'choice' && $locale == 'ar') {
                    $data = $data->map(function ($v) {
                        $res = explode('|', $v);
                        if (count($res) == 2) {
                            return implode('|', [$res[1], $res[0]]);
                        }

                        return $v;
                    });
                }
                $path = "{$dir}/{$locale}/$file.js";
                $disk->put($path, $data->toJson(JSON_UNESCAPED_UNICODE));
                $this->info($disk->path($path));
            }
        }
    }
}
