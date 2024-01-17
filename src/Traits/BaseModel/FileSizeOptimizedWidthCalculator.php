<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseModel;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Support\ImageFactory;

class FileSizeOptimizedWidthCalculator
{
    public function calculateWidthsFromFile(string $imagePath): Collection
    {
        $image = ImageFactory::load($imagePath);

        $width = $image->getWidth();
        $height = $image->getHeight();
        $fileSize = filesize($imagePath);

        return $this->calculateWidths($fileSize, $width, $height);
    }

    public function calculateWidths(int $fileSize, int $width, int $height): Collection
    {
        $targetWidths = collect();

        $targetWidths->push($width);

        $ratio = $height / $width;
        $area = $height * $width;

        $predictedFileSize = $fileSize;
        $pixelPrice = $predictedFileSize / $area;

        while (true) {
            $predictedFileSize *= 0.7;

            $newWidth = (int) floor(sqrt(($predictedFileSize / $pixelPrice) / $ratio));

            if ($this->finishedCalculating((int) $predictedFileSize, $newWidth)) {
                return $targetWidths->filter(fn($width) => $width <= 1920)->values();
            }

            $targetWidths->push($newWidth);
        }
    }

    protected function finishedCalculating(int $predictedFileSize, int $newWidth): bool
    {
        if ($newWidth < 20) {
            return true;
        }

        if ($predictedFileSize < (1024 * 10)) {
            return true;
        }

        return false;
    }

}