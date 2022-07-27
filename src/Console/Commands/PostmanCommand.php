<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Console\Commands;

use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;
use Myth\LaravelTools\Utilities\Deploy;

class PostmanCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:postman
{--domain=http://192.168.1.34:8034 : Domain name}
{--name= : Name of Postman collection}
{--id= : ID of Postman collection}
{--locale=ar : Locale of Postman collection}
{--g|generate : New IDs of Postman collection}
';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create application documentation';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->applyCustomStyle();
        $this->alert("Start documentation");
        $name = $this->option('name') ?: config('app.name');
        $id = $this->option('id') ?: Str::random(20);
        $locale = $this->option('locale') ?: config('app.locale');
        if($this->option('generate')){
            $name = Str::random(4).'-'.time().'-'.Str::random(4);
        }
        // d($this->options(),$name);
        $postman = new Deploy($this->option('domain'), $name, $id, $locale);
        $postman->documentation();
        $this->lineGreen("Created in: [{$postman->getFilePath()}]");
    }
}
