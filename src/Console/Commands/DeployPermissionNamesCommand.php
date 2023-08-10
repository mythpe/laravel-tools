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
use function getRouterPermissions;

class DeployPermissionNamesCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:check-permission
{--l|locale=ar : app locale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check permission names';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->withProgressBar(getRouterPermissions(!1), function ($per) {
            app()->setLocale($this->option('locale'));
            $p = app(config('4myth-tools.permission_class','\\App\\Models\\Permission'))->make($per);
            $this->newLine();
            if ($per['name'] == $p->name_to_string || !$p->name_to_string) {
                $this->error($per);
            }
            $this->line($p->name_to_string);
        });
    }
}
