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

include_once('Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesLDAP
*/
class ilLDAPRoleAssignmentTableGUI extends ilTable2GUI
{
    protected $lng;
    protected $ctrl;
    
    /**
     * constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', '', 1);
        $this->addColumn($this->lng->txt('ldap_rule_type'), 'type', "20%");
        $this->addColumn($this->lng->txt('ldap_ilias_role'), 'role', "30%");
        $this->addColumn($this->lng->txt('ldap_rule_condition'), 'condition', "20%");
        $this->addColumn($this->lng->txt('ldap_add_remove'), '', '30%');
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_role_assignment_row.html", "Services/LDAP");
        $this->setDefaultOrderField('type');
        $this->setDefaultOrderDirection("desc");
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param array row data
     *
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('VAL_CONDITION', $a_set['condition']);
        $this->tpl->setVariable('VAL_ROLE', $a_set['role']);
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        
        if ($a_set['add']) {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('no'));
        }
        if ($a_set['remove']) {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('no'));
        }
        
        
        $this->ctrl->setParameter($this->getParentObject(), 'rule_id', $a_set['id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editRoleAssignment'));
    }
    
    /**
     * Parse
     *
     * @access public
     * @param array array of LDAPRoleAssignmentRule
     *
     */
    public function parse($rule_objs)
    {
        foreach ($rule_objs as $rule) {
            $tmp_arr['id'] = $rule->getRuleId();
            
            switch ($rule->getType()) {
                case ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_attribute');
                    break;
                case ilLDAPRoleAssignmentRule::TYPE_GROUP:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_group');
                    break;
                case ilLDAPRoleAssignmentRule::TYPE_PLUGIN:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_plugin');
                    break;
                
            }
            
            $tmp_arr['condition'] = $rule->conditionToString();
            $tmp_arr['add'] = $rule->isAddOnUpdateEnabled();
            $tmp_arr['remove'] = $rule->isRemoveOnUpdateEnabled();
            
            $tmp_arr['role'] = ilObject::_lookupTitle($rule->getRoleId());
            
            $records_arr[] = $tmp_arr;
        }
        
        $this->setData($records_arr ? $records_arr : array());
    }
}
