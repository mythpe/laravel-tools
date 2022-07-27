<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Console\Traits;

use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Trait ProgressBarTrait
 *
 * @package App\Console\Traits
 */
trait ProgressBarTrait
{
    /**
     * @var
     */
    protected $bar;

    /**
     * @param int|null $max
     *
     * @return $this
     */
    protected function startBar(int $max = null): self
    {
        $this->getBar()->start($max);
        $this->line('');
        return $this;
    }

    /**
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getBar(): ?ProgressBar
    {
        return $this->bar;
    }

    /**
     * @param int $max
     *
     * @return $this
     */
    protected function setBar(int $max = 0): self
    {
        $this->bar = $this->output->createProgressBar($max);
        return $this;
    }

    /**
     * @return $this
     */
    protected function finishBar(): self
    {
        $this->getBar()->finish();
        $this->line(' ');
        $this->alert('Finish');
        return $this;
    }

    /**
     * @param int $step
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function advanceBar(int $step = 1): ProgressBar
    {
        $this->getBar()->advance($step);
        $this->line('');
        return $this->getBar();
    }
}
