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

use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

class ilObjectOwnershipManagementTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;

    protected int $user_id;

    public function __construct(?object $parent_obj, string $parent_cmd, int $user_id)
    {
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->access = $DIC['ilAccess'];
        $this->tree = $DIC['tree'];
        $this->obj_definition = $DIC['objDefinition'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        $this->user_id = $user_id;
        $this->setId('objownmgmt'); // #16373

        parent::__construct($parent_obj, $parent_cmd);

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('path'), 'path');
        $this->addColumn($this->lng->txt('action'));

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));
        $this->setRowTemplate('tpl.obj_ownership_row.html', 'Services/Object');
        $this->setDisableFilterHiding();

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');
    }

    public function initItems(array $data): void
    {
        $process_arr = [];
        $is_admin = false;
        $a_type = '';
        if ($data === []) {
            return;
        }

        if (!$this->user_id) {
            $is_admin = $this->access->checkAccess('visible', '', SYSTEM_FOLDER_ID);
        }

        foreach ($data as $id => $item) {
            // workspace objects won't have references
            $refs = ilObject::_getAllReferences($id);
            if ($refs) {
                foreach ($refs as $ref_id) {
                    // objects in trash are hidden
                    if (!$this->tree->isDeleted($ref_id)) {
                        if ($this->user_id) {
                            $readable = $this->access->checkAccessOfUser(
                                $this->user_id,
                                'read',
                                '',
                                $ref_id,
                                $a_type
                            );
                        } else {
                            $readable = $is_admin;
                        }

                        $process_arr[$ref_id] = [
                            'obj_id' => $id,
                            'ref_id' => $ref_id,
                            'type' => ilObject::_lookupType($id),
                            'title' => $item,
                            'path' => $this->buildPath($ref_id),
                            'readable' => $readable
                        ];
                    }
                }
            }
        }

        $this->setData($process_arr);
    }

    protected function fillRow(array $set): void
    {
        $icon = $this->ui_factory->symbol()->icon()->standard($set['type'], $set['title'], Standard::MEDIUM);
        $this->tpl->setVariable('ICON', $this->ui_renderer->render($icon));

        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('PATH', $set['path']);

        if ($set['readable'] && !$this->isParentReadOnly()) {
            $this->tpl->setCurrentBlock('actions');
            $this->tpl->setVariable('ACTIONS', $this->buildActions($set['ref_id'], $set['type']));
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function buildActions(int $ref_id, string $type): string
    {
        $this->ctrl->setParameter($this->parent_obj, 'ownid', $ref_id);

        $actions = [];
        $actions[] = $this->ui_factory->link()->standard(
            $this->lng->txt('show'),
            ilLink::_getLink($ref_id, $type)
        )->withOpenInNewViewport(true);

        $actions[] = $this->ui_factory->link()->standard(
            $this->lng->txt('move'),
            $this->ctrl->getLinkTargetByClass(get_class($this->parent_obj), 'move')
        );

        $actions[] = $this->ui_factory->link()->standard(
            $this->lng->txt('change_owner'),
            $this->ctrl->getLinkTargetByClass(get_class($this->parent_obj), 'changeOwner')
        );

        if (!in_array($type, ['crsr', 'catr', 'grpr']) && $this->obj_definition->allowExport($type)) {
            $actions[] = $this->ui_factory->link()->standard(
                $this->lng->txt('export'),
                $this->ctrl->getLinkTargetByClass(get_class($this->parent_obj), 'export')
            );
        }

        $actions[] = $this->ui_factory->link()->standard(
            $this->lng->txt('delete'),
            $this->ctrl->getLinkTargetByClass(get_class($this->parent_obj), 'delete')
        );

        $actions_dropdown = $this->ui_factory->dropdown()->standard($actions)
            ->withLabel($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->parent_obj, 'ownid', '');

        return $this->ui_renderer->render($actions_dropdown);
    }

    protected function buildPath(int $ref_id): string
    {
        $path = '...';
        $counter = 0;
        $path_full = $this->tree->getPathFull($ref_id);
        foreach ($path_full as $data) {
            if (++$counter < (count($path_full) - 2)) {
                continue;
            }
            if ($ref_id != $data['ref_id']) {
                $path .= ' &raquo; ' . $data['title'];
            }
        }

        return $path;
    }

    protected function isParentReadOnly(): bool
    {
        if (!method_exists($this->parent_obj, 'isReadOnly')) {
            return false;
        }
        return $this->parent_obj->isReadOnly();
    }
}
