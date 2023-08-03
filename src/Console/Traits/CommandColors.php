<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Traits;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Trait CommandColors
 *
 * @package App\Console\Traits
 */
trait CommandColors
{
    /**
     * @var string[]
     */
    protected $colors = [
        'black'   => 'black',
        'red'     => 'red',
        'green'   => 'green',
        'yellow'  => 'yellow',
        'blue'    => 'blue',
        'magenta' => 'magenta',
        'cyan'    => 'cyan',
        'white'   => 'white',
        'default' => 'default',
    ];

    protected $themes = [
        'r' => ['red', 'white', []],
        'a' => ['black', 'white', []],
        'b' => ['blue', 'white', []],
        'w' => ['white', 'blue', []],
        'g' => ['green'],
    ];

    /**
     * @return $this
     */
    protected function applyCustomStyle(): self
    {
        foreach ($this->themes as $t => $options) {
            $this->output->getFormatter()->setStyle($t, new OutputFormatterStyle(...$options));
        }
        return $this;
    }

    /**
     * @param string $str
     * @param string $fg
     * @param string $bg
     *
     * @return $this
     */
    protected function text($str = '', $fg = 'default', $bg = 'default'): self
    {
        $this->line("<fg={$fg};bg=${bg}>{$str}</>");
        return $this;
    }

    /**
     * @param string $string
     */
    protected function lineGreen($string = '')
    {
        $this->l($string, 'g');
    }

    /**
     * @param string $str
     * @param string $tag
     *
     * @return $this
     */
    protected function l(string $str, string $tag = 'a'): self
    {
        $this->line("<{$tag}>{$str}</{$tag}>");
        return $this;
    }
}
