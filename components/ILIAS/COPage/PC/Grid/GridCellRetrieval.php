<?php

/* Copyright (c) 1998-2024 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\Grid;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GridCellRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilPCGrid $grid
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->grid->getCellData();

        foreach ($data as $v) {
            $row = $v;
            // Add mandatory 'id' field for TableAdapterGUI
            $row["id"] = $v["hier_id"] . ":" . $v["pc_id"];
            yield $row;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->grid->getCellData());
    }

    public function isFieldNumeric(
        string $field
    ): bool {
        return false;
    }
}
