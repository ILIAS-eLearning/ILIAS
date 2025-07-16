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

namespace ILIAS\Repository\Ownership;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Symbol\Icon\Standard;

class OwnershipManagementTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $user_id,
        protected string $title,
        protected array $objects,
        protected string $selected_type,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'objownmgmt';
    }

    protected function getTitle(): string
    {
        return $this->title;
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new OwnershipManagementRetrieval(
            $this->domain,
            $this->user_id,
            $this->objects,
            $this->selected_type
        );
    }

    protected function transformRow(array $data_row): array
    {
        $f = $this->gui->ui()->factory();
        $refinery = $this->domain->refinery();

        $icon = $f->symbol()->icon()->standard(
            $data_row['type'],
            $data_row['title'],
            Standard::MEDIUM
        );

        return [
            'id' => $data_row['ref_id'],
            'icon' => $icon,
            'title' => $refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $data_row['title']
            ),
            'path' => $data_row['path'],
            'type' => $data_row['type'],
            'readable' => $data_row['readable']
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if (!$data_row['readable']) {
            return false;
        }

        if ($action === 'export') {
            $obj_definition = $this->domain->objectDefinition();
            $type = $data_row['type'];
            if (in_array($type, ['crsr', 'catr', 'grpr']) || !$obj_definition->allowExport($type)) {
                return false;
            }
        }

        if ($action === 'show') {
            return true; // Show action should always be available for readable items
        }

        if (!method_exists($this->parent_gui, 'isReadOnly')) {
            return true;
        }

        return !$this->parent_gui->isReadOnly();
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->iconColumn('icon', $lng->txt('type'), false)
            ->textColumn('title', $lng->txt('title'), true)
            ->textColumn('path', $lng->txt('path'));

        $table = $table->singleAction(
            'show',
            $lng->txt('show')
        );

        $table = $table->singleAction(
            'move',
            $lng->txt('move')
        );

        $table = $table->singleAction(
            'changeOwner',
            $lng->txt('change_owner')
        );

        $table = $table->singleAction(
            'export',
            $lng->txt('export')
        );

        $table = $table->singleAction(
            'delete',
            $lng->txt('delete')
        );

        return $table;
    }
}
