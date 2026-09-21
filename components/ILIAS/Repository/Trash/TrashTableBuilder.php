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

namespace ILIAS\Repository\Trash;

use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Symbol\Icon\Standard;

class TrashTableBuilder extends CommonTableBuilder
{
    protected ?FilterAdapterGUI $filter = null;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'adm_trash_table';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('rep_trash_table_title') . ' "' .
            \ilObject::_lookupTitle(\ilObject::_lookupObjId($this->ref_id)) . '"';
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new TrashRetrieval($this->domain, $this->ref_id);
    }

    protected function transformRow(array $data_row): array
    {
        $breadcrumbs = [];
        foreach ($data_row['path'] as $path_item) {
            $path_ref_id = (int) $path_item['child'];
            $path_title = $path_ref_id === ROOT_FOLDER_ID
                ? $this->domain->lng()->txt('repository')
                : $path_item['title'];
            $breadcrumbs[] = $this->gui->ui()->factory()->link()->standard(
                $path_title,
                \ilLink::_getLink($path_ref_id, $path_item['type'])
            );
        }

        return [
            'id' => $data_row['id'],
            'icon' => $this->gui->ui()->factory()->symbol()->icon()->standard(
                $data_row['type'],
                $data_row['title'],
                Standard::SMALL
            ),
            'title' => $data_row['title'],
            'path' => $this->gui->ui()->factory()->breadcrumbs($breadcrumbs),
            'deleted_by' => $data_row['deleted_by'],
            'deleted' => new \DateTimeImmutable($data_row['deleted']),
            'num_subs' => $data_row['num_subs']
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->filterData($this->getFilter()->getData() ?? [])
            ->iconColumn('icon', $lng->txt('type'))
            ->textColumn('title', $lng->txt('title'), true)
            ->column('path', $this->gui->ui()->factory()->table()->column()->breadcrumb($lng->txt('path')))
            ->textColumn('deleted_by', $lng->txt('rep_trash_table_col_deleted_by'), true)
            ->dateColumn('deleted', $lng->txt('rep_trash_table_col_deleted_on'), true)
            ->textColumn('num_subs', $lng->txt('rep_trash_table_col_num_subs'), true)
            ->multiAction('undelete', $lng->txt('btn_undelete_origin_location'))
            ->multiAction('restoreToNewLocation', $lng->txt('btn_undelete_new_location'))
            ->multiAction('confirmRemoveFromSystem', $lng->txt('btn_remove_system'))
            ->singleAction('undeleteObject', $lng->txt('btn_undelete_origin_location'))
            ->singleAction('restoreToNewLocationObject', $lng->txt('btn_undelete_new_location'))
            ->singleAction('confirmRemoveFromSystemObject', $lng->txt('btn_remove_system'));
    }

    public function getFilter(): FilterAdapterGUI
    {
        if ($this->filter === null) {
            $lng = $this->domain->lng();
            $lng->loadLanguageModule('rep');
            $this->filter = $this->gui->filter(
                'adm_trash_filter_' . $this->ref_id,
                [get_class($this->parent_gui)],
                $this->parent_cmd
            );
            $this->filter
                ->select('type', $lng->txt('type'), $this->prepareTypeFilterTypes())
                ->text('title', $lng->txt('title'))
                ->text('deleted_by', $lng->txt('rep_trash_table_col_deleted_by'))
                ->duration('deleted', $lng->txt('rep_trash_table_col_deleted_on'), false);
        }

        return $this->filter;
    }

    protected function prepareTypeFilterTypes(): array
    {
        $lng = $this->domain->lng();
        $obj_definition = $this->domain->objectDefinition();
        $options = [];

        foreach ((new \ilTreeTrashQueries())->getTrashedNodeTypesForContainer($this->ref_id) as $type) {
            if (in_array($type, ['rolf', 'root'], true) || !$obj_definition->isRBACObject($type)) {
                continue;
            }

            if ($obj_definition->isPlugin($type)) {
                $lng->loadLanguageModule('rep_robj_' . $type);
                $options[$type] = $lng->txt('rep_robj_' . $type . '_objs_' . $type);
            } else {
                $options[$type] = $lng->txt('objs_' . $type);
            }
        }

        asort($options, SORT_LOCALE_STRING);
        return $options;
    }
}
