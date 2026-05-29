<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands;

use Myth\LaravelTools\Console\BaseCommand;

/**
 *
 */
class UtilitiesCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:utilities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run utilities commands';

    /**
     * @var string
     */
    protected string $diskName = 'setup';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $disk = $this->disk();
        $path = $disk->path('utilities');
        $files = glob($path.'/*.{php,json}', GLOB_BRACE);
        $list = array_map('basename', $files);

        $step = $this->choice("Select Step", $list);
        $key = array_search($step, $list);
        $name = $list[$key];

        $message = "Do you want to continue? [$name]";
        if (!$this->confirm($message)) {
            return;
        }
        $this->call('setup:utilities', ['--file' => $name]);
    }
}
