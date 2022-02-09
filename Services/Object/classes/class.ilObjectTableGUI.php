<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Settings for LO courses
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectTableGUI extends ilTable2GUI
{
    protected $objects = array();
    protected $show_path = false;
    protected $row_selection_input = false;
    
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     * @param type $a_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
    {
        $this->setId('obj_table_' . $a_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, '');
    }
    
    /**
     *
     * @param type $a_status
     */
    public function enableObjectPath($a_status)
    {
        $this->show_path = $a_status;
    }
    
    /**
     *
     * @return type
     */
    public function enabledObjectPath()
    {
        return $this->show_path;
    }
    
    /**
     * Customize path instance
     * @param ilPathGUI $path
     * @return \ilPathGUI
     */
    public function customizePath(ilPathGUI $path)
    {
        return $path;
    }
    
    public function enableRowSelectionInput($a_stat)
    {
        $this->row_selection_input = $a_stat;
    }
    
    /**
     * @return type
     */
    public function enabledRowSelectionInput()
    {
        return $this->row_selection_input;
    }
    
    /**
     * Fill row selection input
     * @param type $set
     */
    public function fillRowSelectionInput($set)
    {
        $this->tpl->setCurrentBlock('row_selection_input');
        $this->tpl->setVariable('OBJ_INPUT_TYPE', 'checkbox');
        $this->tpl->setVariable('OBJ_INPUT_NAME', 'id[]');
        $this->tpl->setVariable('OBJ_INPUT_VALUE', $set['ref_id']);
    }


    
    /**
     * set table content objects
     * @param array $a_ref_ids
     */
    public function setObjects($a_ref_ids)
    {
        $this->objects = $a_ref_ids;
    }
    
    /**
     * get object ref_ids
     * @return type
     */
    public function getObjects()
    {
        return $this->objects;
    }
    
    /**
     * init table
     */
    public function init()
    {
        if ($this->enabledRowSelectionInput()) {
            $this->addColumn('', 'id', '5px');
        }
        
        $this->addColumn($this->lng->txt('type'), 'type', '30px');
        $this->addColumn($this->lng->txt('title'), 'title');
        
        $this->setOrderColumn('title');
        $this->setRowTemplate('tpl.object_table_row.html', 'Services/Object');
    }
    
    /**
     * fill table rows
     * @param array $a_set
     */
    public function fillRow(array $a_set) : void
    {
        if ($this->enabledRowSelectionInput()) {
            $this->fillRowSelectionInput($a_set);
        }
        
        $this->tpl->setVariable('OBJ_LINK', ilLink::_getLink($a_set['ref_id'], $a_set['type']));
        $this->tpl->setVariable('OBJ_LINKED_TITLE', $a_set['title']);
        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($a_set['obj_id'], "small", $a_set['type']));
        $this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $a_set['type']));
        
        
        if ($this->enabledObjectPath()) {
            $path_gui = new ilPathGUI();
            $path_gui = $this->customizePath($path_gui);
            
            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('OBJ_PATH', $path_gui->getPath(ROOT_FOLDER_ID, (int) $a_set['ref_id']));
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
     * Parse objects
     */
    public function parse()
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
