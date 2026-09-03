<?php

/* Copyright (c) 1998-2023 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\Layout;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalBase;
use ilPageLayout;

class PageLayoutRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();

        // Apply ordering if specified
        $data = $this->applyOrder($data, $order);

        // Apply range (pagination) if specified
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->collectData());
    }

    protected function collectData(): array
    {
        $data = ilPageLayout::getLayoutsAsArray();
        foreach ($data as $k => $v) {
            $data[$k]["id"] = $v["layout_id"];
        }
        return $data;
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "layout_id" || $field === "id";
    }
}
