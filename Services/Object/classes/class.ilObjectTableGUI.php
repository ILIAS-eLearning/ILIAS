<?php

declare(strict_types=1);

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

/**
 * Settings for LO courses
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectTableGUI extends ilTable2GUI
{
    protected array $objects = [];
    protected bool $show_path = false;
    protected bool $row_selection_input = false;

    public function __construct(?object $parent_obj, string $parent_cmd, string $id)
    {
        $this->setId('obj_table_' . $id);
        parent::__construct($parent_obj, $parent_cmd);
    }

    public function enableObjectPath(bool $status): void
    {
        $this->show_path = $status;
    }

    public function enabledObjectPath(): bool
    {
        return $this->show_path;
    }

    public function customizePath(ilPathGUI $path): ilPathGUI
    {
        return $path;
    }

    public function enableRowSelectionInput(bool $stat): void
    {
        $this->row_selection_input = $stat;
    }

    public function enabledRowSelectionInput(): bool
    {
        return $this->row_selection_input;
    }

    public function fillRowSelectionInput(array $set): void
    {
        $this->tpl->setCurrentBlock('row_selection_input');
        $this->tpl->setVariable('OBJ_INPUT_TYPE', 'checkbox');
        $this->tpl->setVariable('OBJ_INPUT_NAME', 'id[]');
        $this->tpl->setVariable('OBJ_INPUT_VALUE', $set['ref_id']);
    }

    /**
     * set table content objects
     */
    public function setObjects(array $ref_ids): void
    {
        $this->objects = $ref_ids;
    }

    /**
     * get object ref_ids
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    public function init(): void
    {
        if ($this->enabledRowSelectionInput()) {
            $this->addColumn('', 'id', '5px');
        }

        $this->addColumn($this->lng->txt('type'), 'type', '30px');
        $this->addColumn($this->lng->txt('title'), 'title');

        $this->setOrderColumn('title');
        $this->setRowTemplate('tpl.object_table_row.html', 'Services/Object');
    }

    protected function fillRow(array $set): void
    {
        if ($this->enabledRowSelectionInput()) {
            $this->fillRowSelectionInput($set);
        }

        $this->tpl->setVariable('OBJ_LINK', ilLink::_getLink($set['ref_id'], $set['type']));
        $this->tpl->setVariable('OBJ_LINKED_TITLE', $set['title']);
        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($set['obj_id'], "small", $set['type']));
        $this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $set['type']));

        if ($this->enabledObjectPath()) {
            $path_gui = new ilPathGUI();
            $path_gui = $this->customizePath($path_gui);

            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('OBJ_PATH', $path_gui->getPath(ROOT_FOLDER_ID, (int) $set['ref_id']));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function parse(): void
    {
        $counter = 0;
        $set = array();
        foreach ($this->getObjects() as $ref_id) {
            $type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            if ($type == 'rolf') {
                continue;
            }

            $set[$counter]['ref_id'] = $ref_id;
            $set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
            $set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            $set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            $counter++;
        }
        $this->setData($set);
    }
}
