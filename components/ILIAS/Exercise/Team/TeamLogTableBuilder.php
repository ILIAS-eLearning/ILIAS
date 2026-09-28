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

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TeamLogTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilExAssignmentTeam $team,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'exercise_team_log';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_team_log');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->teamLogRetrieval($this->team);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            'id' => $data_row['id'],
            'tstamp' => (new \DateTimeImmutable('@' . $data_row['tstamp']))
                ->setTimezone(new \DateTimeZone($this->domain->user()->getTimeZone())),
            'user' => $data_row['user'],
            'details' => htmlspecialchars($data_row['details'])
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->dateColumn('tstamp', $lng->txt('date'), true)
            ->textColumn('user', $lng->txt('user'), true)
            ->textColumn('details', $lng->txt('details'));
    }
}
