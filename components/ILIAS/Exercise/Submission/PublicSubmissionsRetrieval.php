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

namespace ILIAS\Exercise\Submission;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class PublicSubmissionsRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignment $assignment
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
        return in_array($field, ['id', 'submission_count'], true);
    }

    protected function collectData(): array
    {
        $data = [];
        $submission_manager = $this->domain->submission($this->assignment->getId());

        foreach ($this->assignment->getMemberListData() as $member) {
            $member_id = (int) $member['usr_id'];
            $data[] = [
                'id' => $member_id,
                'name' => $member['name'],
                'login' => $member['login'],
                'submission_count' => $submission_manager->countSubmissionsOfUser($member_id)
            ];
        }

        return $data;
    }
}
