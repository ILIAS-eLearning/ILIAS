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

namespace ILIAS\Portfolio\Administration;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class PortfolioRoleAssignmentRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected PortfolioRoleAssignmentManager $manager
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
        return in_array($field, ["role_id", "template_ref_id"], true);
    }

    protected function collectData(): array
    {
        return array_map(
            static fn(array $row): array => [
                "id" => (string) $row["role_id"] . "_" . (string) $row["template_ref_id"],
                "role_title" => (string) $row["role_title"],
                "template_title" => (string) $row["template_title"],
                "role_id" => (int) $row["role_id"],
                "template_ref_id" => (int) $row["template_ref_id"]
            ],
            $this->manager->getAllAssignmentData()
        );
    }
}
