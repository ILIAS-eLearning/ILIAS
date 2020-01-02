<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesAdvancedMetaData
*/

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDFieldTableGUI extends ilTable2GUI
{
    protected $permissions; // [ilAdvancedMDPermissionHelper]
    protected $may_edit_pos; // [bool]
    
    /**
     * Constructor
     *
     * @access public
     * @param object calling gui class
     * @param string parent command
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', ilAdvancedMDPermissionHelper $a_permissions, $a_may_edit_pos)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->permissions = $a_permissions;
        $this->may_edit_pos = (bool) $a_may_edit_pos;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'f', 1);
        $this->addColumn($this->lng->txt('position'), 'position', "5%");
        $this->addColumn($this->lng->txt('title'), 'title', "30%");
        $this->addColumn($this->lng->txt('md_adv_field_fields'), 'fields', "35%");
        $this->addColumn($this->lng->txt('options'), 'obj_types', "30%");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.edit_fields_row.html", "Services/AdvancedMetaData");
        $this->setDefaultOrderField("position");
        #$this->setDefaultOrderDirection("desc");
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param
     *
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('TXT_SEARCHABLE', $this->lng->txt('md_adv_searchable'));
        $this->tpl->setVariable('ASS_ID', $a_set['id']);
        if ($a_set['searchable']) {
            $this->tpl->setVariable('ASS_CHECKED', 'checked="checked"');
        }
        if (!$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE] ||
            !(bool) $a_set['supports_search']) {
            $this->tpl->setVariable('ASS_DISABLED', ' disabled="disabled"');
        }
        
        $this->tpl->setVariable('VAL_POS', $a_set['position']);
        if (!$this->may_edit_pos) {
            $this->tpl->setVariable('POS_DISABLED', ' disabled="disabled"');
        }
        
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);
        }
        
        $this->tpl->setVariable('FIELD_TYPE', $a_set['type']);
        
        foreach ((array) $a_set['properties'] as $key => $value) {
            $this->tpl->setCurrentBlock('field_value');
            $this->tpl->setVariable('FIELD_KEY', $key);
            $this->tpl->setVariable('FIELD_VAL', $value);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT]) {
            $this->ctrl->setParameter($this->parent_obj, 'field_id', $a_set['id']);
            $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'editField'));
            $this->tpl->setVariable('TXT_EDIT_RECORD', $this->lng->txt('edit'));
        }
    }
    
    
    /**
     * parese field data
     *
     * @access public
     * @param
     *
     */
    public function parseDefinitions($a_definitions)
    {
        $counter = 0;
        foreach ($a_definitions as $definition) {
            $tmp_arr['position'] = ++$counter*10;
            $tmp_arr['id'] = $definition->getFieldId();
            $tmp_arr['title'] = $definition->getTitle();
            $tmp_arr['description'] = $definition->getDescription();
            $tmp_arr['fields'] = array();
            $tmp_arr['searchable'] = $definition->isSearchable();
            $tmp_arr['type'] = $this->lng->txt($definition->getTypeTitle());
            $tmp_arr['properties'] = $definition->getFieldDefinitionForTableGUI();
            $tmp_arr['supports_search'] = $definition->isSearchSupported();
            
            $tmp_arr['perm'] = $this->permissions->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                $definition->getFieldId(),
                array(
                    ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT
                    ,array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE)
                )
            );
            
            $defs_arr[] = $tmp_arr;
        }
        $this->setData($defs_arr ? $defs_arr : array());
    }
}
