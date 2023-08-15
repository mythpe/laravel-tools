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
use Myth\LaravelTools\Utilities\Postman;

class PostmanCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:postman
{--domain= : Domain name}
{--name= : Name of Postman collection}
{--id= : ID of Postman collection}
{--eid= : ID of Postman exporter}
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
    public function handle(): void
    {
        $this->components->task("Start documentation", function () {
            $postman = new Postman();
            $name = $this->option('name') ?: $postman->getCollectionName();
            $id = $this->option('id');
            $exporter = $this->option('eid');
            $domain = $this->option('domain') ?: config('4myth-tools.postman.domain', config('app.url'));
            $locale = $this->option('locale');

            if ($id) {
                $postman->setCollectionId($id);
            }
            if ($domain) {
                $postman->setDomain($domain);
            }

            if ($this->option('generate')) {
                $name .= '-'.time();
            }
            if ($name) {
                $postman->setCollectionName($name);
            }
            if ($exporter) {
                $postman->setExporterId($exporter);
            }
            if ($locale) {
                $postman->setLocale($locale);
            }
            // $postman->command = $this;
            $postman->documentation();
            if (count($postman->withCommand) > 0) {
                foreach ($postman->withCommand as $c) {
                    $this->getComponents()->error(json_encode(array_unique($c($this))));
                }
            }
            $this->components->info("File created: <fg=green>{$postman->getFilePath()}</>");
        });
    }
}
