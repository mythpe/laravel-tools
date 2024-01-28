<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Logger
{
    /** @var Filesystem|FilesystemAdapter */
    public FilesystemAdapter | Filesystem $disk;

    /** @var string */
    public string $content;

    /** @var string */
    public string $fileName;

    /**
     * @param string|array|null $content
     * @param string|null $fileName
     */
    public function __construct(string | array | null $content, ?string $fileName = null)
    {
        $this->disk = static::getDisk();
        $this->content = is_null($content) ? '' : (is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : $content);
        $fileName = $fileName ?? Carbon::now()->format(config('4myth-tools.date_format.log'));
        $this->fileName = Str::finish($fileName, '.log');
    }

    public static function getDisk(): Filesystem
    {
        return Storage::disk('logs');
    }

    /**
     * @param string|array $content
     * @param string|null $fileName
     * @return self
     */
    public static function log(string | array $content, ?string $fileName = null): self
    {
        $static = new static(...func_get_args());
        $static->create();
        return $static;
    }

    public function create(): void
    {
        $content = "[At ".Carbon::now()->format('Y-m-d-H:i')."]:".PHP_EOL;
        $content .= $this->content;
        $this->disk->prepend($this->fileName, $content, PHP_EOL.'======End======'.PHP_EOL);
    }
}
