<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Logger
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter */
    public $disk;

    /** @var string */
    public $content;

    /** @var string */
    public $fileName;

    public function __construct(string $content, $fileName = null)
    {
        $this->disk = static::getDisk();
        $this->content = $content;
        $fileName = $fileName ?? Carbon::now()->format(config('4myth-tools.date_format.log'));
        $this->fileName = Str::finish($fileName, '.log');
    }

    public static function getDisk()
    {
        return Storage::disk('logs');
    }

    public static function log(): self
    {
        $static = new static(...func_get_args());
        $static->create();
        return $static;
    }

    public function create()
    {
        $content = "[At ".Carbon::now()->format('H:i')."]:".PHP_EOL;
        $content .= $this->content;
        $this->disk->prepend($this->fileName, $content, PHP_EOL.'======End======'.PHP_EOL);
    }
}
