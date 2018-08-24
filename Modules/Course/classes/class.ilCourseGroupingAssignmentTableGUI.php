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
class ilCourseGroupingAssignmentTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj, $a_parent_cmd, $a_content_obj, $a_group_obj)
	{
	 	global $lng, $ilCtrl;
		
	 	$this->lng = $lng;	
	 	$this->ctrl = $ilCtrl;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);		
		
		$this->type = ilObject::_lookupType($a_content_obj->getId());
		$this->lng->loadLanguageModule($this->type);		
		
		// #9017
		$this->setLimit(9999);
					
		$this->addColumn('','', 1);
		$this->addColumn($this->lng->txt('title'), 'title');
		$this->addColumn($this->lng->txt('path'), 'path');
			
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
	 		
		$this->setTitle($this->lng->txt('crs_grp_assign_crs').' ('.$a_group_obj->getTitle().')');		
		
		$this->setRowTemplate("tpl.crs_grp_select_crs.html","Modules/Course");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		
		$this->addMultiCommand('assignCourse', $this->lng->txt('grouping_change_assignment'));		 			
		$this->addCommandButton('edit', $this->lng->txt('cancel'));		 			
		
		$this->getItems($a_content_obj, $a_group_obj);
	}
	
	protected function getItems($a_content_obj, $a_group_obj)
	{
		global $ilUser, $tree;
		
		$counter = 0;
		$items = ilUtil::_getObjectsByOperations($this->type,
												 'write', $ilUser->getId(), -1);												 
		$items_obj_id = array();
		$items_ids = array();
		foreach($items as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$items_ids[$obj_id] = $ref_id;
			$items_obj_id[] = $obj_id;
		}		
		$items_obj_id = ilUtil::_sortIds($items_obj_id,'object_data','title','obj_id');
		
		$assigned_ids = array();
		$assigned = $a_group_obj->getAssignedItems();
		if($assigned)
		{
			foreach($assigned as $item)
			{
				$assigned_ids[] = $item['target_ref_id'];
			}
		}
		
		$data = array();
		foreach($items_obj_id as $obj_id)
		{
			$item_id = $items_ids[$obj_id];
			if($tree->checkForParentType($item_id,'adm'))
			{
				continue;
			}
			
			$obj_id = ilObject::_lookupObjId($item_id);
			
			$data[] = array('id' => $item_id,
				'title' => ilObject::_lookupTitle($obj_id),
				'description' => ilObject::_lookupDescription($obj_id),
				'path' => $this->__formatPath($tree->getPathFull($item_id)),
				'assigned' => in_array($item_id, $assigned_ids));
		}
		
		$this->setData($data);
	}
		
	function __formatPath($a_path_arr)
	{
		$counter = 0;
		foreach($a_path_arr as $data)
		{
			if(!$counter++)
			{
				continue;
			}
			if($counter++ > 2)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}

		return $path;
	}

	public function fillRow($a_set)
	{						
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_PATH", $a_set["path"]);
		
		if($a_set["assigned"])
		{
			$this->tpl->setVariable("STATUS_CHECKED", " checked=\"checked\"");
		}
	}
}

?>