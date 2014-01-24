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

include_once './Services/Table/classes/class.ilTable2GUI.php';

/** 
* Show active rules
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesWebServicesECS
*/
class ilECSCategoryMappingTableGUI extends ilTable2GUI
{
	/**
	 * Constructor 
	 * @return
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','f','1px');
	 	$this->addColumn($this->lng->txt('obj_cat'),'category','40%');
	 	$this->addColumn($this->lng->txt('ecs_cat_mapping_type'),'kind','50%');
	 	$this->addColumn('','edit','10%');
		$this->setRowTemplate('tpl.rule_row.html','Services/WebServices/ECS');
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setSelectAllCheckbox('rules');
		$this->setTitle($this->lng->txt('ecs_tbl_active_rules'));
		$this->addMultiCommand('deleteCategoryMappings',$this->lng->txt('delete'));
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('TXT_ID',$this->lng->txt('ecs_import_id'));
		$this->tpl->setVariable('VAL_CAT_ID',$a_set['category_id']);
		$this->tpl->setVariable('TXT_TITLE',$this->lng->txt('title'));
		$this->tpl->setVariable('VAL_CAT_TITLE',$a_set['category']);
		$this->tpl->setVariable('VAL_CONDITION',$a_set['kind']);
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('PATH',$this->buildPath($a_set['category_id']));
		
		$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'rule_id',$a_set['id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'editCategoryMapping'));
		$this->ctrl->clearParametersByClass(get_class($this->getParentObject()));
	}

	/**
	 * Parse 
	 * @param	array	$a_rules	Array of mapping rules
	 * @return
	 */
	public function parse($a_rules)
	{
		foreach($a_rules as $rule)
		{
			$tmp_arr['id'] = $rule->getMappingId();
			$tmp_arr['category_id'] = $rule->getContainerId();
			$tmp_arr['category'] = ilObject::_lookupTitle(ilObject::_lookupObjId($rule->getContainerId()));
			$tmp_arr['kind'] = $rule->conditionToString();
			
			$content[] = $tmp_arr;
		}
		$this->setData($content ? $content : array());
	}
	
	private function buildPath($a_ref_id)
	{
		$loc = new ilLocatorGUI();
		$loc->setTextOnly(false);
		$loc->addContextItems($a_ref_id);
		
		return $loc->getHTML();
	}
	
}
?>
