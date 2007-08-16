<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilObjCourseGroupingGUI
*
* @author your name <your email> 
* @version $Id$
* 
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjCourseGroupingGUI
{
	var $content_obj;
	var $tpl;
	var $ctrl;
	var $lng;
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGroupingGUI(&$content_obj,$a_obj_id = 0)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;

		$this->type = "crsg";
		$this->content_obj =& $content_obj;
		$this->content_type = $ilObjDataCache->lookupType($this->content_obj->getId());

		$this->id = $a_obj_id;
		$this->ctrl->saveParameter($this,'obj_id');

		$this->__initGroupingObject();

	}

	function getContentType()
	{
		return $this->content_type;
	}

	function listGroupings()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.groupings.html",'Modules/Course');
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_UNAMBIGUOUSNESS",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("ASSIGNED_ITEMS",$this->lng->txt('groupings_assigned_obj_'.$this->getContentType()));

		$items = ilObjCourseGrouping::_getVisibleGroupings($this->content_obj->getId());

		// Fill table
		$counter = 0;
		$has_access = false;
		foreach($items as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			// Description
			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_GRP",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}

			// Assigned items
			$assigned_items = $tmp_obj->getAssignedItems();
			if($num_items = count($assigned_items))
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_assigned_courses_info'));
			}
			else
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_no_courses_assigned'));
			}
			
			foreach($assigned_items as $condition)
			{
				$this->tpl->setCurrentBlock("item");
				$this->tpl->setVariable("ITEM_TITLE",$ilObjDataCache->lookupTitle($condition['target_obj_id']));
				$this->tpl->parseCurrentBlock();
			}


			$this->tpl->setCurrentBlock("grouping_row");
			$this->tpl->setVariable("GRP_TITLE",$tmp_obj->getTitle());

			if(ilObjCourseGrouping::_checkAccess($grouping_id))
			{
				$has_access = true;
				$this->tpl->setVariable("CHECK_GRP",ilUtil::formCheckbox(0,'grouping[]',$grouping_id));
			}
			$this->tpl->setVariable("AMB_GRP",$this->lng->txt($tmp_obj->getUniqueField()));
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));

			$this->ctrl->setParameterByClass('ilobjcoursegroupinggui','obj_id',$grouping_id);
			$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','edit'));

			$this->tpl->parseCurrentBlock();
		}

		if(count($items) and $has_access)
		{
			$this->tpl->setCurrentBlock("has_items");
			$this->tpl->setVariable("ARR_DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
			$this->tpl->setVariable("EDIT",$this->lng->txt('edit'));
			$this->tpl->setVariable("DELETE",$this->lng->txt('delete'));
			$this->tpl->setVariable("ACTIONS",$this->lng->txt('actions'));
			$this->tpl->parseCurrentBlock();
		}
		elseif(!count($items))
		{
			// no items
			$this->tpl->setCurrentBlock("no_items");
			$this->tpl->setVariable("TXT_NO_ITEMS",$this->lng->txt('no_datasets'));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("ADD",$this->lng->txt('crs_add_grouping'));

	}


	function askDeleteGrouping()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		if(!count($_POST['grouping']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_grouping_select_one'));
			$this->listGroupings();
			
			return false;
		}

		ilUtil::sendInfo($this->lng->txt('crs_grouping_delete_sure'));
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_ask_delete_goupings.html",'Modules/Course');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		
		
		$counter = 0;
		foreach($_POST['grouping'] as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_GRP",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("grouping_row");
			$this->tpl->setVariable("GRP_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
		$_SESSION['crs_grouping_del'] = $_POST['grouping'];
	}

	function deleteGrouping()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		if(!count($_SESSION['crs_grouping_del']))
		{
			ilUtil::sendInfo('No grouping selected');
			$this->listGroupings();

			return false;
		}
		foreach($_SESSION['crs_grouping_del'] as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping((int) $grouping_id);
			$tmp_obj->delete();
		}
		ilUtil::sendInfo($this->lng->txt('crs_grouping_deleted'));
		$this->listGroupings();
		
		unset($_SESSION['crs_grouping_del']);
		return true;
	}

	function create()
	{
		$options = array('login' => 'login',
						 'email' => 'email',
						 'matriculation' => 'matriculation');


		$title = ilUtil::prepareFormOutput($_POST["title"],true);
		$desc  = ilUtil::stripSlashes($_POST["description"]);
		$unique = $_POST['unique'] ? $_POST['unique'] : 'login';

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_add.html",'Modules/Course');

		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("DESC",$desc);
		$this->tpl->setVariable("UNAM_SELECT",ilUtil::formSelect($unique,'unique',$options));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_add_grouping'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_UNAM",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required_field'));
		$this->tpl->setVariable("CMD_SUBMIT",'add');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('add'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
	}

	function cancel()
	{
		// unset session variables
		unset($_SESSION['crs_grouping_del']);

		$this->listGroupings();
		return true;
	}

	function add()
	{
		if(!$_POST['title'])
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_enter_title'));
			$this->create();
			
			return false;
		}

		$this->grp_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->grp_obj->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->grp_obj->setUniqueField($_POST['unique']);
		if($this->grp_obj->create($this->content_obj->getRefId(),$this->content_obj->getId()))
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_added_grouping'));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_err_adding_grouping'));
		}

		$this->listGroupings();
		return false;
	}
	
	function edit($a_grouping_id = 0)
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		if($a_grouping_id)
		{
			$grouping_id = $a_grouping_id;
		}
		elseif(count($_POST['grouping']) != 1)
		{
			ilUtil::sendInfo($this->lng->txt('grouping_select_exactly_one'));
			$this->listGroupings();
			return false;
		}
		else
		{
			$grouping_id = (int) $_POST['grouping'][0];
		}

		$options = array('login' => 'login',
						 'email' => 'email',
						 'matriculation' => 'matriculation');

		$tmp_grouping = new ilObjCourseGrouping($grouping_id);

		$title = isset($_POST['title']) ? ilUtil::stripSlashes($_POST['title']) : $tmp_grouping->getTitle();
		$description  = isset($_POST["description"]) ? ilUtil::stripSlashes($_POST['description']) : $tmp_grouping->getDescription();
		$unique = $_POST['unique'] ? $_POST['unique'] : $tmp_grouping->getUniqueField();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_edit.html",'Modules/Course');

		$this->ctrl->setParameter($this,'obj_id',$grouping_id);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("EDIT_GROUPING",$this->lng->txt('edit_grouping'));
		$this->tpl->setVariable("BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('grouping_change_assignment'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('description'));

		$this->tpl->setVariable("TXT_UNAM",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("UNAM_SELECT",ilUtil::formSelect($unique,'unique',$options));

		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("DESCRIPTION",$description);


		$items = $tmp_grouping->getAssignedItems();
		foreach($items as $cond_data)
		{
			$this->tpl->setCurrentBlock("list_courses");
			$this->tpl->setVariable("LIST_CRS_TITLE",$ilObjDataCache->lookupTitle($cond_data['target_obj_id']));
			$this->tpl->parseCurrentBlock();
		}
		if(count($items))
		{
			$this->tpl->setCurrentBlock("assigned");
			$this->tpl->setVariable("ASS_COURSES",$this->lng->txt('crs_grp_table_assigned_courses'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("no_assigned");
			$this->tpl->setVariable("MESSAGE_NO_COURSES",$this->lng->txt('crs_grp_no_courses_assigned'));
			$this->tpl->parseCurrentBlock();
		}
	}


	function update()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		if(!$_GET['obj_id'])
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->listGroupings();
			return false;
		}
		if(!$_POST['title'])
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_enter_title'));
			$this->edit((int) $_GET['obj_id']);
			return false;
		}
		
		$tmp_grouping = new ilObjCourseGrouping($_GET['obj_id']);
		$tmp_grouping->setTitle(ilUtil::stripSlashes($_POST['title']));
		$tmp_grouping->setDescription(ilUtil::stripSlashes($_POST['description']));
		$tmp_grouping->setUniqueField($_POST['unique']);
		$tmp_grouping->update();

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->listGroupings();

		return true;
	}

	function selectCourse()
	{
		global $ilErr,$ilAccess,$ilObjDataCache,$tree,$ilUser;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		if(!$_GET['obj_id'])
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->listGroupings();
			return false;
		}

		$tmp_grouping = new ilObjCourseGrouping((int) $_GET['obj_id']);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_select_crs.html",'Modules/Course');
		
		$this->ctrl->setParameter($this,'obj_id',(int) $_GET['obj_id']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_grp_assign_crs').' ('.$this->grp_obj->getTitle().')');
		$this->tpl->setVariable("BTN_ASSIGN",$this->lng->txt('grouping_change_assignment'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));

		$counter = 0;
		$items = ilUtil::_getObjectsByOperations($this->getContentType(),
												 'write',
												 $ilUser->getId(),-1);
												 
		$items_obj_id = array();
		$items_ids = array();
		foreach($items as $ref_id)
		{
			$obj_id =  $ilObjDataCache->lookupObjId($ref_id);
			$items_ids[$obj_id] = $ref_id;
			$items_obj_id[] = $obj_id;
		}
		$items_obj_id = ilUtil::_sortIds($items_obj_id,'object_data','title','obj_id');
		foreach($items_obj_id as $obj_id)
		{
			$item_id = $items_ids[$obj_id];
			if($tree->checkForParentType($item_id,'adm'))
			{
				continue;
			}
			$obj_id = $ilObjDataCache->lookupObjId($item_id);
			$title = $ilObjDataCache->lookupTitle($obj_id);
			$description = $ilObjDataCache->lookupDescription($obj_id);

			$assigned = $tmp_grouping->isAssigned($obj_id) ? 1 : 0;

			if(strlen($description))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_CRS",$description);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("crs_row");
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_CRS",ilUtil::formCheckbox($assigned,'crs_ids[]',$item_id));
			$this->tpl->setVariable("CRS_TITLE",$title);

			$path = $this->__formatPath($tree->getPathFull($item_id));
			$this->tpl->setVariable("CRS_PATH",$this->lng->txt('path').": ".$path);
			$this->tpl->parseCurrentBlock();
		}

		return true;
	}

	function assignCourse()
	{
		global $ilErr,$ilAccess,$ilObjDataCache,$tree,$ilUser;

		if(!$ilAccess->checkAccess('write','',$this->content_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		if(!$_GET['obj_id'])
		{
			ilUtil::sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->listGroupings();
			return false;
		}

		$container_ids = is_array($_POST['crs_ids']) ? $_POST['crs_ids'] : array();

		$tmp_grouping = new ilObjCourseGrouping((int) $_GET['obj_id']);

		// delete all existing conditions
		include_once './classes/class.ilConditionHandler.php';
		
		$condh = new ilConditionHandler();
		$condh->deleteByObjId((int) $_GET['obj_id']);

		$added = 0;
		foreach($container_ids as $course_ref_id)
		{
			$tmp_crs =& ilObjectFactory::getInstanceByRefId($course_ref_id);
			$tmp_condh =& new ilConditionHandler();
			$tmp_condh->enableAutomaticValidation(false);

			$tmp_condh->setTargetRefId($course_ref_id);
			$tmp_condh->setTargetObjId($tmp_crs->getId());
			$tmp_condh->setTargetType($this->getContentType());
			$tmp_condh->setTriggerRefId(0);
			$tmp_condh->setTriggerObjId($this->id);
			$tmp_condh->setTriggerType('crsg');
			$tmp_condh->setOperator('not_member');
			$tmp_condh->setValue($this->grp_obj->getUniqueField());

			if(!$tmp_condh->checkExists())
			{
				$tmp_condh->storeCondition();
				++$added;
			}
		}
		$this->edit((int) $_GET['obj_id']);
	}	
		

	function &executeCommand()
	{
		global $ilTabs;

		$ilTabs->setTabActive('crs_groupings');

		$cmd = $this->ctrl->getCmd();
		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "edit";
		}
		$this->$cmd();
	}

	// PRIVATE
	function __initGroupingObject()
	{
		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';

		$this->grp_obj =& new ilObjCourseGrouping($this->id);
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

} // END class.ilObjCourseGrouping
?>
