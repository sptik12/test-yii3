<?php

declare(strict_types=1);

namespace App\Backend\Component\CarData;

interface CarDataInterface
{
    public function getCarDataByVinCode(
        string $vinCode
    ): CarData;

    public function getAllMakes(): array;

    public function getModels(string $make): array;
}
