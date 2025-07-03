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

namespace ILIAS\Catgory;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Category\InternalDomainService;
use ILIAS\Category\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Symbol\Icon\Standard;

class AssignRoleTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $ref_id,
        protected int $managed_user_id,
        protected int $managing_user_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'ilcatluaar';
    }

    protected function getTitle(): string
    {
        $lng = $this->domain->lng();
        return $lng->txt('role_assignment') . ' (' .
            $this->domain->profile()->getNamePresentation($this->managed_user_id, true) . ')';
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->assignedRoledRetrieval(
            $this->ref_id,
            $this->managed_user_id,
            $this->managing_user_id
        );
    }

    protected function transformRow(array $data_row): array
    {
        $f = $this->gui->ui()->factory();

        $icon = $data_row["assigned"] ?? false
            ? $f->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small')
            : $f->symbol()->icon()->custom('assets/images/standard/icon_unchecked.svg', '', 'small');

        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'desc' => $data_row['desc'],
            'type' => $data_row['type'],
            'icon' => $icon
        ];
    }


    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->textColumn('title', $lng->txt('title'), true)
            ->textColumn('desc', $lng->txt('description'))
            ->textColumn('type', $lng->txt('type'))
            ->iconColumn('icon', $lng->txt('info_assigned'), false);

        $table = $table->multiAction(
            'assignSave',
            $lng->txt("change_assignment")
        );

        return $table;
    }
}
