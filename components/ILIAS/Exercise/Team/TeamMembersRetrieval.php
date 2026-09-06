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

namespace ILIAS\Exercise\Team;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class TeamMembersRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignmentTeam $team,
        protected int $parent_ref_id,
        protected bool $edit_permission
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
        $order ??= new Order('name', Order::ASC);
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
        return $field === 'id';
    }

    protected function collectData(): array
    {
        $access = $this->domain->access();
        $data = [];

        foreach ($this->team->getMembers() as $id) {
            $access_warning = '';
            if (!$access->checkAccessOfUser($id, 'read', '', $this->parent_ref_id) &&
                is_array($info = $access->getInfo())) {
                $access_warning = $info[0]['text'] ?? '';
            }

            $data[] = [
                'id' => $id,
                'name' => \ilUserUtil::getNamePresentation(
                    $id,
                    false,
                    false,
                    '',
                    $this->edit_permission
                ),
                'access_warning' => $access_warning
            ];
        }

        return $data;
    }
}
