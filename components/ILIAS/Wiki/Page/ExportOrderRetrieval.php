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

namespace ILIAS\Wiki\Page;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class ExportOrderRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected array $all_pages,
        protected array $page_ids
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->page_ids as $page_id) {
            yield [
                "id" => $page_id,
                "title" => $this->all_pages[$page_id]["title"]
            ];
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->page_ids);
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }
}
