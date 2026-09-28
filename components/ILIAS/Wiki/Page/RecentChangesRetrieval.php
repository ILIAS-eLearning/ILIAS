<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
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
use ILIAS\Wiki\InternalDomainService;

class RecentChangesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();
        $order ??= new Order("date", Order::DESC);
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function collectData(): array
    {
        $data = [];
        $page_manager = $this->domain->page()->page($this->ref_id);

        foreach ($page_manager->getRecentChanges() as $page) {
            $data[] = [
                "date" => $page->getLastChange(),
                "user" => $page->getLastChangedUser(),
                "id" => $page->getId(),
                "title" => $page->getTitle(),
                "lang" => $page->getLanguage(),
                "nr" => $page->getOldNr(),
                "user_sort" => \ilUserUtil::getNamePresentation(
                    $page->getLastChangedUser(),
                    false,
                    false
                )
            ];
        }

        return $data;
    }
}
