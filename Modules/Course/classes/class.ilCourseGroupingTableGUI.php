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
class ilCourseGroupingTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj, $a_parent_cmd, $a_content_obj)
	{
	 	global $lng, $ilCtrl;
		
	 	$this->lng = $lng;	
	 	$this->ctrl = $ilCtrl;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);		
		
		$type = ilObject::_lookupType($a_content_obj->getId());
		$this->lng->loadLanguageModule($type);		
		
		$this->addColumn('','', 1);
		$this->addColumn($this->lng->txt('title'), 'title');
		$this->addColumn($this->lng->txt('description'), 'description');
		$this->addColumn($this->lng->txt('unambiguousness'), 'unique');
		$this->addColumn($this->lng->txt('groupings_assigned_obj_'.$type), 'assigned');
		$this->addColumn('','');
		
		
		$this->setTitle($this->lng->txt('groupings'));		

		$this->addMultiCommand('askDeleteGrouping', $this->lng->txt('delete'));		 			
		$this->setSelectAllCheckbox('grouping');
		
		$this->setRowTemplate("tpl.groupings.html","Modules/Course");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
	 	
		$this->getItems($a_content_obj);
	}
	
	protected function getItems($a_content_obj)
	{
		$items = ilObjCourseGrouping::_getVisibleGroupings($a_content_obj->getId());

		$data = array();
		foreach($items as $grouping_id)
		{
			$tmp_obj = new ilObjCourseGrouping($grouping_id);
			
			$data[$grouping_id]['id'] = $grouping_id;
			$data[$grouping_id]['title'] = $tmp_obj->getTitle();
			$data[$grouping_id]['unique'] = $this->lng->txt($tmp_obj->getUniqueField());

			// Description
			if(strlen($tmp_obj->getDescription()))
			{
				$data[$grouping_id]['description'] = $tmp_obj->getDescription();
			}

			// Assigned items
			$assigned_items = $tmp_obj->getAssignedItems();
			foreach($assigned_items as $condition)
			{
				$data[$grouping_id]['assigned'][] = ilObject::_lookupTitle($condition['target_obj_id']);
			}
		}
		
		$this->setData($data);
	}

	public function fillRow($a_set)
	{						
		if(count($a_set["assigned"]))
		{			
			foreach($a_set["assigned"] as $item)
			{
				$this->tpl->setCurrentBlock("assigned");
				$this->tpl->setVariable("ITEM_TITLE", $item);
				$this->tpl->parseCurrentBlock();
			}	
		}
		else
		{
			$this->tpl->setCurrentBlock("assigned");
			$this->tpl->setVariable("ITEM_TITLE", $this->lng->txt('crs_grp_no_courses_assigned'));
			$this->tpl->parseCurrentBlock();			
		}		 
		
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
		$this->tpl->setVariable("TXT_UNIQUE", $a_set["unique"]);
				
		$this->ctrl->setParameter($this->parent_obj, 'obj_id', $a_set["id"]);
		$this->tpl->setVariable("EDIT_LINK",
			$this->ctrl->getLinkTarget($this->parent_obj, 'edit'));		
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
	}
}

?>