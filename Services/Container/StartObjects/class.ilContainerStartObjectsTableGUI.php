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

/**
 * ilContainerStartObjectsTableGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContainerStartObjectsTableGUI extends ilTable2GUI
{
    protected ilContainerStartObjects $start_obj; // [ilContainerStartObjects]

    public function __construct(
        ilContainerStartObjectsGUI $a_parent_obj,
        string $a_parent_cmd,
        ilContainerStartObjects $a_start_objects
    ) {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->lng->loadLanguageModule('crs');

        $this->start_obj = $a_start_objects;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn('', '', '1');

        if ($a_parent_cmd === 'listStructure') {
            $this->addColumn($this->lng->txt('cntr_ordering'), 'pos', '5%');
        }

        $this->addColumn($this->lng->txt('type'), 'type', '1');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');

        // add
        if ($a_parent_cmd !== 'listStructure') {
            $this->setTitle($this->lng->txt('crs_select_starter'));
            $this->addMultiCommand('addStarter', $this->lng->txt('crs_add_starter'));
            $this->addCommandButton('listStructure', $this->lng->txt('cancel'));

            $this->setDefaultOrderField('title');
        }
        // list
        else {
            $this->setTitle($this->lng->txt('crs_start_objects'));
            $this->addMultiCommand('askDeleteStarter', $this->lng->txt('remove'));
            $this->addCommandButton('saveSorting', $this->lng->txt('sorting_save'));

            $this->setDefaultOrderField('pos');
        }
        $this->setDefaultOrderDirection('asc');

        $this->setRowTemplate("tpl.start_objects_row.html", "Services/Container");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox('starter');

        $data = [];

        // add
        if ($a_parent_cmd !== 'listStructure') {
            $data = $this->getPossibleObjects();
        }
        // list
        else {
            $data = $this->getStartObjects();
        }

        $this->setData($data);
    }

    protected function getPossibleObjects(): array
    {
        $data = [];
        foreach ($this->start_obj->getPossibleStarters() as $item_ref_id) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item_ref_id);

            $data[$item_ref_id]['id'] = $item_ref_id;
            $data[$item_ref_id]['title'] = $tmp_obj->getTitle();
            $data[$item_ref_id]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item_ref_id]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            if ($tmp_obj->getDescription() !== '') {
                $data[$item_ref_id]['description'] = $tmp_obj->getDescription();
            }
        }

        return $data;
    }

    protected function getStartObjects(): array
    {
        $data = [];
        $counter = 0;
        foreach ($this->start_obj->getStartObjects() as $start_id => $item) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_ref_id']);

            $data[$item['item_ref_id']]['id'] = $start_id;
            $data[$item['item_ref_id']]['title'] = $tmp_obj->getTitle();
            $data[$item['item_ref_id']]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item['item_ref_id']]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            $counter += 10;
            $data[$item['item_ref_id']]['pos'] = $counter;

            if ($tmp_obj->getDescription() !== '') {
                $data[$item['item_ref_id']]['description'] = $tmp_obj->getDescription();
            }
        }

        return $data;
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->getParentCmd() === 'listStructure') {
            $this->tpl->setCurrentBlock('pos_bl');
            $this->tpl->setVariable("POS_ID", $a_set["id"]);
            $this->tpl->setVariable("POS", $a_set["pos"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"] ?? '');
        $this->tpl->setVariable("ICON_SRC", $a_set["icon"]);
        $this->tpl->setVariable("ICON_ALT", $a_set["type"]);
    }
}
