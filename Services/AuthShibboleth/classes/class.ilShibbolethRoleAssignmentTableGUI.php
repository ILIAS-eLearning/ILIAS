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
* @ingroup ServicesAuthShibboleth 
*/
class ilShibbolethRoleAssignmentTableGUI extends ilTable2GUI
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
	public function __construct($a_parent_obj,$a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->lng->txt('shib_rule_type'),'type',"30%");
	 	$this->addColumn($this->lng->txt('shib_ilias_role'),'role',"20%");
	 	$this->addColumn($this->lng->txt('shib_rule_condition'),'condition',"50%");
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_role_assignment_row.html","Services/AuthShibboleth");
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
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_TYPE',$a_set['type']);
		$this->tpl->setVariable('VAL_CONDITION',$a_set['condition']);
		$this->tpl->setVariable('VAL_ROLE',$a_set['role']);
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
		
		$this->ctrl->setParameter($this->getParentObject(),'rule_id',$a_set['id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'roleAssignment'));
	}
	
	/**
	 * Parse
	 *
	 * @access public
	 * @param array array of ShibRoleAssignmentRule
	 * 
	 */
	public function parse($rule_objs)
	{
	 	foreach($rule_objs as $rule)
	 	{
			$tmp_arr['id'] = $rule->getRuleId();
			$tmp_arr['type'] = $rule->isPluginActive() ?
				$this->lng->txt('shib_role_by_plugin') :
				$this->lng->txt('shib_role_by_attribute');
			if(!$rule->isPluginActive())
			{
				$tmp_arr['condition'] = $rule->conditionToString();	
			}
			else
			{
				"None";
			}
			
			
			$tmp_arr['role'] = ilObject::_lookupTitle($rule->getRoleId());
			
			$records_arr[] = $tmp_arr;
	 	}
	 	
	 	$this->setData($records_arr ? $records_arr : array());
	}
}


?>