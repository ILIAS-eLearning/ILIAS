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

namespace ILIAS\LearningModule\Question\BlockedUsers;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
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
        $data = $this->getBlockedUsers();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getBlockedUsers());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function getBlockedUsers(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $track = \ilLMTracker::getInstance($this->ref_id);
        $data = [];
        foreach ($track->getBlockedUsersInformation() as $row) {
            $data[] = [
                "id" => $row["qst_id"] . ":" . $row["user_id"],
                "user" => $row["user_name"],
                "question" => $row["question_text"],
                "page" => $row["page_title"],
                "last_try" => $row["last_try"] ?? "",
                "unlocked" => $row["unlocked"] ?? false
            ];
        }

        return $this->data = $data;
    }
}
