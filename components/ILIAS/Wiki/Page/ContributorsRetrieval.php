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

class ContributorsRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected int $wiki_id
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
        $order ??= new Order("name", Order::ASC);
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
        return $field === "id";
    }

    protected function collectData(): array
    {
        $data = [];

        foreach (\ilWikiPage::getWikiContributors($this->wiki_id) as $contributor) {
            $user_id = (int) $contributor["user_id"];
            if (!\ilObject::_exists($user_id)) {
                continue;
            }
            $name = $contributor["lastname"] . ", " . $contributor["firstname"];

            $data[] = [
                "id" => $user_id,
                "name" => $name,
                "pages" => $contributor["pages"],
                "status" => (int) (\ilWikiContributor::_lookupStatus(
                    $this->wiki_id,
                    $user_id
                ) ?? \ilWikiContributor::STATUS_NOT_GRADED),
                "status_date" => \ilWikiContributor::_lookupStatusTime(
                    $this->wiki_id,
                    $user_id
                ),
                "mark" => \ilLPMarks::_lookupMark($user_id, $this->wiki_id)
            ];
        }

        return $data;
    }
}
