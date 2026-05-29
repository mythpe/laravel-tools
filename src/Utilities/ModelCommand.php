<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class ModelCommand
{
    /**
     * Original string
     * @var Stringable
     */
    public Stringable $string;
    public Stringable $name;
    public ?Stringable $namespace = null;
    public Stringable $singular;
    public Stringable $plural;
    public Stringable $studlySingular;
    public Stringable $studlyPlural;
    public Stringable $snakeSingular;
    public Stringable $snakePlural;
    public Stringable $camelSingular;
    public Stringable $camelPlural;
    public Stringable $kebabSingular;
    public Stringable $kebabPlural;
    public Stringable $titleSingular;
    public Stringable $titlePlural;

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {
        $this->string = Str::of($string);
        $this->name = $this->string->classBasename();

        $this->namespace = $this->string->contains('\\') ? Str::of('\\'.$this->string->beforeLast('\\')) : null;

        $this->singular = $this->name->singular();
        $this->plural = $this->name->plural();

        $this->studlySingular = $this->singular->studly();
        $this->studlyPlural = $this->plural->pluralStudly();

        $this->snakeSingular = $this->singular->snake();
        $this->snakePlural = $this->plural->snake();

        $this->camelSingular = $this->singular->camel();
        $this->camelPlural = $this->plural->camel();

        $this->kebabSingular = $this->singular->kebab();
        $this->kebabPlural = $this->plural->kebab();

        $this->titleSingular = $this->singular->snake(' ')->singular()->title();
        $this->titlePlural = $this->plural->snake(' ')->plural()->title();
    }
}
