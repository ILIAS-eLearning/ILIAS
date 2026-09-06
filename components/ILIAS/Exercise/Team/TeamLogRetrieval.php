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

class TeamLogRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignmentTeam $team
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
        $order ??= new Order('tstamp', Order::DESC);
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
        return in_array($field, ['id', 'tstamp'], true);
    }

    protected function collectData(): array
    {
        $data = [];

        foreach ($this->team->getLog() as $item) {
            $message = match ((int) $item['action']) {
                \ilExAssignmentTeam::TEAM_LOG_CREATE_TEAM => 'create_team',
                \ilExAssignmentTeam::TEAM_LOG_ADD_MEMBER => 'add_member',
                \ilExAssignmentTeam::TEAM_LOG_REMOVE_MEMBER => 'remove_member',
                \ilExAssignmentTeam::TEAM_LOG_ADD_FILE => 'add_file',
                \ilExAssignmentTeam::TEAM_LOG_REMOVE_FILE => 'remove_file',
                default => ''
            };

            $details = $this->domain->lng()->txt('exc_team_log_' . $message);
            if ($item['details']) {
                $details = sprintf($details, $item['details']);
            }

            $data[] = [
                'id' => (int) $item['log_id'],
                'tstamp' => (int) $item['tstamp'],
                'user' => \ilObjUser::_lookupFullname((int) $item['user_id']),
                'details' => $details
            ];
        }

        return $data;
    }
}
