<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Str;

class ModelCommand
{
    public string $string;

    public string $name;

    public ?string $namespace = null;

    public string $studly;

    public string $snake;
    public string $snakePlural;

    public function __construct(string $str)
    {
        $this->string = Str::of($str);
        $this->name = $this->string->afterLast('\\')->studly();
        $this->studly = $this->name->studly();
        $this->snake = $this->name->snake();
        $this->snakePlural = $this->snake->plural();
    }
}