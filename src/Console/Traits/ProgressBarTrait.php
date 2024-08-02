<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Traits;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Trait ProgressBarTrait
 *
 * @package App\Console\Traits
 */
trait ProgressBarTrait
{
    /** @var ProgressBar|null */
    protected ?ProgressBar $bar = null;

    /**
     * @param int|array|Collection|null $max
     * @return ProgressBar
     */
    protected function startBar(int | array | Collection $max = null): ProgressBar
    {
        $max = is_countable($max) ? count($max) : $max;
        $this->setMaxSteps($max);
        return $this->getBar();
    }

    /**
     * @return ProgressBar
     */
    protected function getBar(): ProgressBar
    {
        if (!$this->bar) {
            $this->setBar();
        }
        return $this->bar;
    }

    /**
     * @param int $max
     * @return ProgressBar
     */
    protected function setBar(int $max = 0): ProgressBar
    {
        $this->bar = $this->output->createProgressBar($max);
        $this->bar->setOverwrite(!0);
        $this->bar->setProgressCharacter('');
        $this->bar->setBarCharacter('▓');
        $this->bar->setEmptyBarCharacter('░');
        return $this->bar;
    }

    /**
     * @return ProgressBar
     */
    protected function finishBar(): ProgressBar
    {
        $this->getBar()->finish();
        $this->components->info('Finish');
        return $this->getBar();
    }

    /**
     * @param int $step
     *
     * @return ProgressBar
     */
    protected function advanceBar(int $step = 1): ProgressBar
    {
        $this->getBar()->advance($step);
        // sleep(1);
        return $this->getBar();
    }

    /**
     * @param int $max
     * @return ProgressBar
     */
    protected function setMaxSteps(int $max): ProgressBar
    {
        $this->getBar()->setMaxSteps($max + $this->getBar()->getProgress());
        return $this->getBar();
    }
}
