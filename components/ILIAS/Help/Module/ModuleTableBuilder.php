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

namespace ILIAS\Help\Module;

use ILIAS\Help\InternalDomainService;
use ILIAS\Help\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class ModuleTableBuilder extends CommonTableBuilder
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
        return 'help_modules';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('help_modules');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->moduleRetrieval();
    }

    protected function getOrderingCommand(): string
    {
        return $this->has_write_permission ? 'saveOrdering' : '';
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === 'activateModule') {
            return !$data_row['active'];
        }
        if ($action === 'deactivateModule') {
            return (bool) $data_row['active'];
        }

        return true;
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();

        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'create_date' => (new \DateTimeImmutable('@' . $data_row['create_date']))
                ->setTimezone(new \DateTimeZone($this->domain->user()->getTimeZone())),
            'active' => $data_row['active'] ? $lng->txt('yes') : $lng->txt('no')
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('title', $lng->txt('title'), true)
            ->dateColumn('create_date', $lng->txt('help_imported_on'), true)
            ->textColumn('active', $lng->txt('active'), true);

        if ($this->has_write_permission) {
            $table = $table
                ->singleAction('activateModule', $lng->txt('activate'))
                ->singleAction('deactivateModule', $lng->txt('deactivate'))
                ->singleAction('confirmHelpModuleDeletion', $lng->txt('delete'), true);
        }

        return $table;
    }
}
