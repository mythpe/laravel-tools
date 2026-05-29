<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
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

    /** @var string | array */
    public string | array $content;

    /** @var string */
    public string $fileName;
    /**
     * @var bool
     */
    public bool $json = true;

    /**
     * @var int Json Flags
     */
    public int $flags;

    /**
     * @param string|array|null $content
     * @param string|null $fileName
     * @param bool $json
     * @param int|null $flags
     */
    public function __construct(string | array | null $content, ?string $fileName = null, bool $json = true, ?int $flags = null)
    {
        $this->disk = static::getDisk();
        $this->json = $json;
        $this->flags = $flags ?? ($json ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : 0);
        $this->content = $content ?? '';
        $fileName = $fileName ?? Carbon::now()->format(config('4myth-tools.date_format.log'));
        $this->fileName = Str::finish($fileName, '.'.($json ? 'json' : 'log'));
    }

    public static function getDisk(): Filesystem
    {
        return Storage::disk('logs');
    }

    /**
     * @param string|array|null $content
     * @param string|null $fileName
     * @param bool $json
     * @param int|null $flags
     * @return static
     */
    public static function log(string | array | null $content, ?string $fileName = null, bool $json = true, ?int $flags = null): static
    {
        $static = new static(...func_get_args());
        $static->create();
        return $static;
    }

    public function create(): void
    {
        $at = "[At ".Carbon::now()->format('Y-m-d-H:i')."]:";
        if ($this->json) {
            $content = $this->content;
            $data = [
                [
                    'at'  => $at,
                    'log' => $content,
                ],
                ...($this->disk->json($this->fileName) ?? []),
            ];
            $this->disk->put($this->fileName, json_encode($data, $this->flags));
            return;
        }
        $at .= PHP_EOL;
        $content = is_array($this->content) ? json_encode($this->content, $this->flags) : $this->content;
        $this->disk->prepend($this->fileName, $at.$content);
    }
}
