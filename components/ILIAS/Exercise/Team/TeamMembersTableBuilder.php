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

class TeamMembersTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilExAssignmentTeam $team,
        protected int $parent_ref_id,
        protected bool $read_only,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'exercise_team_members';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_team_members');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        $edit_permission = $this->domain->access()->checkAccessOfUser(
            $this->domain->user()->getId(),
            'edit',
            '',
            $this->parent_ref_id
        );

        return $this->domain->teamMembersRetrieval(
            $this->team,
            $this->parent_ref_id,
            $edit_permission
        );
    }

    protected function transformRow(array $data_row): array
    {
        $name = $data_row['name'];
        if ($data_row['access_warning'] !== '') {
            $name .= '<br>' . htmlspecialchars($data_row['access_warning']);
        }

        return [
            'id' => $data_row['id'],
            'name' => $name
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $table = $table->textColumn(
            'name',
            $this->domain->lng()->txt('name'),
            true
        );

        if (!$this->read_only) {
            $table = $table->singleAction(
                'confirmRemoveTeamMember',
                $this->domain->lng()->txt('remove')
            );
        }

        return $table;
    }
}
