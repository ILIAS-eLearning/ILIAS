<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\HelpTooltip;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Help\Tooltips\TooltipsManager;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected TooltipsManager $tooltips,
        protected string $component
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getTooltips();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->getTooltips());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function getTooltips(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $this->data = [];
        foreach ($this->tooltips->getAllTooltips($this->component) as $tooltip) {
            $this->data[] = [
                "id" => (int) $tooltip["id"],
                "tt_id" => (string) $tooltip["tt_id"],
                "text" => (string) $tooltip["text"]
            ];
        }
        usort(
            $this->data,
            static fn(array $left, array $right): int => strcmp($left["tt_id"], $right["tt_id"])
        );

        return $this->data;
    }
}
