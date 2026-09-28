<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed under the GPL-3.0,
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

class ModulesTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $group_id,
        protected bool $has_write_permission,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return 'repmodtbl';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('cmps_repository_object_types');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->modulesRetrieval($this->group_id);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            'id' => $data_row['id'],
            'icon' => $this->gui->ui()->factory()->symbol()->icon()->standard(
                $data_row['id'],
                '',
                'small'
            ),
            'type' => $data_row['caption'],
            'module' => $data_row['subdir'],
            'creation' => $data_row['creation']
                ? $this->domain->lng()->txt('yes')
                : $this->domain->lng()->txt('no')
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        return match ($action) {
            'enableModuleCreation' => !$data_row['creation'],
            'disableModuleCreation' => $data_row['creation'],
            default => true
        };
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->iconColumn('icon', '')
            ->textColumn('type', $lng->txt('cmps_rep_object'))
            ->textColumn('module', $lng->txt('cmps_module'))
            ->textColumn('creation', $lng->txt('cmps_enable_creation'));

        if ($this->has_write_permission) {
            $table = $table
                ->singleAction('enableModuleCreation', $lng->txt('cmps_enable_creation'))
                ->singleAction('disableModuleCreation', $lng->txt('cmps_disable_creation', 'cmps'))
                ->singleAction('moveModuleToGroup', $lng->txt('move'), true);
        }

        return $table;
    }

    protected function getOrderingCommand(): string
    {
        return $this->has_write_permission ? 'saveModules' : '';
    }
}
