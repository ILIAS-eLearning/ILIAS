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

namespace ILIAS\LearningModule\Editing\ShortTitles;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ilLMObject;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected int $lm_id,
        protected string $lang
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getShortTitles();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getShortTitles());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function getShortTitles(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $data = [];
        foreach (ilLMObject::getShortTitles($this->lm_id, $this->lang) as $short_title) {
            $data[] = [
                "id" => (int) $short_title["obj_id"],
                "title" => $short_title["title"],
                "short_title" => $short_title["short_title"],
                "default_title" => $short_title["default_title"] ?? "",
                "default_short_title" => $short_title["default_short_title"] ?? ""
            ];
        }

        return $this->data = $data;
    }
}
