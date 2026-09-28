<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning/ILIAS
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Taxonomy\Nodes;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected array $data;

    public function __construct(
        \ilTaxonomyTree $tree,
        int $parent_node_id,
        bool $manual_sorting
    ) {
        $this->data = $tree->getChildsByTypeFilter($parent_node_id, ["taxn"]);
        $this->data = \ilArrayUtil::sortArray(
            $this->data,
            $manual_sorting ? "order_nr" : "title",
            "asc",
            false
        );
        foreach ($this->data as &$row) {
            $row["id"] = (int) $row["child"];
        }
        unset($row);
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->applyOrder($this->data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->data);
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "order_nr" || $field === "child";
    }
}
