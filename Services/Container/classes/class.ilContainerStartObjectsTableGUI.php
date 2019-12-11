<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * ilContainerStartObjectsTableGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesContainer
 */
class ilContainerStartObjectsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $start_obj; // [ilContainerStartObjects]
    
    public function __construct($a_parent_obj, $a_parent_cmd, ilContainerStartObjects $a_start_objects)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->lng->loadLanguageModule('crs');
        
        $this->start_obj = $a_start_objects;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn('', '', 1);
        
        if ($a_parent_cmd == 'listStructure') {
            $this->addColumn($this->lng->txt('cntr_ordering'), 'pos', '5%');
        }
        
        $this->addColumn($this->lng->txt('type'), 'type', 1);
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');
        
        // add
        if ($a_parent_cmd != 'listStructure') {
            $this->setTitle($this->lng->txt('crs_select_starter'));
            $this->addMultiCommand('addStarter', $this->lng->txt('crs_add_starter'));
            $this->addCommandButton('listStructure', $this->lng->txt('cancel'));
            
            $this->setDefaultOrderField('title');
            $this->setDefaultOrderDirection('asc');
        }
        // list
        else {
            $this->setTitle($this->lng->txt('crs_start_objects'));
            $this->addMultiCommand('askDeleteStarter', $this->lng->txt('remove'));
            $this->addCommandButton('saveSorting', $this->lng->txt('sorting_save'));
            
            $this->setDefaultOrderField('pos');
            $this->setDefaultOrderDirection('asc');
        }
             
        $this->setRowTemplate("tpl.start_objects_row.html", "Services/Container");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox('starter');
                            
        $data = array();
        
        // add
        if ($a_parent_cmd != 'listStructure') {
            $data = $this->getPossibleObjects();
        }
        // list
        else {
            $data = $this->getStartObjects();
        }
    
        $this->setData($data);
    }
    
    protected function getPossibleObjects()
    {
        $data = array();
        foreach ($this->start_obj->getPossibleStarters() as $item_ref_id) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item_ref_id);

            $data[$item_ref_id]['id'] = $item_ref_id;
            $data[$item_ref_id]['title'] = $tmp_obj->getTitle();
            $data[$item_ref_id]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item_ref_id]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            if (strlen($tmp_obj->getDescription())) {
                $data[$item_ref_id]['description'] = $tmp_obj->getDescription();
            }
        }
        
        return $data;
    }
    
    protected function getStartObjects()
    {
        $data = array();
        $counter = 0;
        foreach ($this->start_obj->getStartObjects() as $start_id => $item) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_ref_id']);

            $data[$item['item_ref_id']]['id'] = $start_id;
            $data[$item['item_ref_id']]['title'] = $tmp_obj->getTitle();
            $data[$item['item_ref_id']]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item['item_ref_id']]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');
            
            $counter += 10;
            $data[$item['item_ref_id']]['pos'] = $counter;

            if (strlen($tmp_obj->getDescription())) {
                $data[$item['item_ref_id']]['description'] = $tmp_obj->getDescription();
            }
        }
        
        return $data;
    }

    public function fillRow($a_set)
    {
        if ($this->getParentCmd() == 'listStructure') {
            $this->tpl->setCurrentBlock('pos_bl');
            $this->tpl->setVariable("POS_ID", $a_set["id"]);
            $this->tpl->setVariable("POS", $a_set["pos"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("ICON_SRC", $a_set["icon"]);
        $this->tpl->setVariable("ICON_ALT", $a_set["type"]);
    }
}
