<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseStartObjectsTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_obj_course)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->lng->loadLanguageModule('crs');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn('', '', 1);
        $this->addColumn($this->lng->txt('type'), 'type', 1);
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');
        
        // add
        if ($a_parent_cmd != 'listStructure') {
            $this->setTitle($this->lng->txt('crs_select_starter'));
                                     
            $this->addMultiCommand('addStarter', $this->lng->txt('crs_add_starter'));
        }
        // list
        else {
            $this->setTitle($this->lng->txt('crs_start_objects'));
            
            $this->addMultiCommand('askDeleteStarter', $this->lng->txt('delete'));
        }
             
        $this->setRowTemplate("tpl.crs_add_starter.html", "Modules/Course");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox('starter');
        
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');
        
        
        $data = array();
                
        include_once './Modules/Course/classes/class.ilCourseStart.php';
        $crs_start = new ilCourseStart($a_obj_course->getRefId(), $a_obj_course->getId());
        
        // add
        if ($a_parent_cmd != 'listStructure') {
            $data = $this->getPossibleObjects($a_obj_course, $crs_start);
        }
        // list
        else {
            $data = $this->getStartObjects($a_obj_course, $crs_start);
        }
    
        $this->setData($data);
    }
    
    protected function getPossibleObjects($a_obj_course, $crs_start)
    {
        $data = array();
        foreach ($crs_start->getPossibleStarters() as $item_ref_id) {
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
    
    protected function getStartObjects($a_obj_course, $crs_start)
    {
        $starters = $crs_start->getStartObjects();
        
        /*
        if(!count($starters))
        {
            ilUtil::sendInfo($this->lng->txt('crs_no_starter_created'));
        }
        */
        
        $data = array();
        foreach ($starters as $start_id => $item) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_ref_id']);

            $data[$item['item_ref_id']]['id'] = $start_id;
            $data[$item['item_ref_id']]['title'] = $tmp_obj->getTitle();
            $data[$item['item_ref_id']]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item['item_ref_id']]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            if (strlen($tmp_obj->getDescription())) {
                $data[$item['item_ref_id']]['description'] = $tmp_obj->getDescription();
            }
        }
        
        return $data;
    }

    public function fillRow($a_set)
    {
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("ICON_SRC", $a_set["icon"]);
        $this->tpl->setVariable("ICON_ALT", $a_set["type"]);
    }
}
