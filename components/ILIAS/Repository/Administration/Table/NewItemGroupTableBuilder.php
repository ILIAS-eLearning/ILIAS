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

namespace ILIAS\Repository\Administration\Table;

use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class NewItemGroupTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected bool $has_write_permission,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'repnwitgrptbl';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('rep_new_item_groups');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        $retrieval = $this->domain->newItemGroupRetrieval();
        $unassigned_subitems = $retrieval->getUnassignedSubItemCount();

        if ($unassigned_subitems > 0) {
            $this->gui->mainTemplate()->setOnScreenMessage('info', sprintf(
                $this->domain->lng()->txt('rep_new_item_group_unassigned_subitems'),
                $unassigned_subitems
            ));
        }

        return $retrieval;
    }

    protected function getOrderingCommand(): string
    {
        return $this->has_write_permission ? 'saveNewItemGroupOrder' : '';
    }

    protected function transformRow(array $data_row): array
    {
        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'subitems' => $data_row['subitems']
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('title', $lng->txt('title'))
            ->textColumn('subitems', $lng->txt('rep_new_item_group_nr_subitems'));

        if ($this->has_write_permission) {
            $table = $table
                ->singleRedirectAction(
                    'editNewItemGroup',
                    $lng->txt('edit'),
                    [\ilObjRepositorySettingsGUI::class],
                    'editNewItemGroup',
                    'grp_id'
                )
                ->singleAction('confirmDeleteNewItemGroup', $lng->txt('delete'), true);
        }

        return $table;
    }

}
