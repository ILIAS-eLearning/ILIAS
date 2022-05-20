<?php
declare(strict_types=1);

namespace ILIAS\Services\MyStaff\Utils;

final class ListFetcherResult
{
    private int $totalDatasetCount;
    private array $dataset;

    public function __construct(array $dataset, int $totalDatasetCount)
    {
        $this->dataset = $dataset;
        $this->totalDatasetCount = $totalDatasetCount;
    }

    /**
     * @return int
     */
    public function getTotalDatasetCount() : int
    {
        return $this->totalDatasetCount;
    }

    /**
     * @return array
     */
    public function getDataset() : array
    {
        return $this->dataset;
    }
}
