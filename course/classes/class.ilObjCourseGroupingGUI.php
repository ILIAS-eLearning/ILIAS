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
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjCourseGroupingGUI
{
	var $crs_obj;
	var $tpl;
	var $ctrl;
	var $lng;

	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGroupingGUI(&$crs_obj,$a_obj_id = 0)
	{
		global $tpl,$ilCtrl,$lng;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;

		$this->type = "crsg";
		$this->crs_obj =& $crs_obj;

		$this->id = $a_obj_id;
		$this->ctrl->saveParameter($this,'obj_id');

		$this->__initGroupingObject();

	}

	function create()
	{
		$options = array('login' => 'login',
						 'email' => 'email',
						 'matriculation' => 'matriculation');


		$title = ilUtil::prepareFormOutput($_POST["title"],true);
		$desc  = ilUtil::stripSlashes($_POST["description"]);
		$unique = $_POST['unique'] ? $_POST['unique'] : 'login';

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_add.html","course");

		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("DESC",$desc);
		$this->tpl->setVariable("UNAM_SELECT",ilUtil::formSelect($unique,'unique',$options));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_add_grouping'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_UNAM",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required'));
		$this->tpl->setVariable("CMD_SUBMIT",'add');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('add'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
	}

	function cancel()
	{
		$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
	}

	function add()
	{
		if(!$_POST['title'])
		{
			sendInfo($this->lng->txt('crs_grp_enter_title'));
			$this->create();
			
			return false;
		}

		$this->grp_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->grp_obj->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->grp_obj->setUniqueField($_POST['unique']);
		if($this->grp_obj->create($this->crs_obj->getRefId(),$this->crs_obj->getId()))
		{
			sendInfo($this->lng->txt('crs_grp_added_grouping'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return true;
		}
		sendInfo($this->lng->txt('crs_grp_err_adding_grouping'));
		$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
		
		return false;
	}
	
	function edit()
	{
		$options = array('login' => 'login',
						 'email' => 'email',
						 'matriculation' => 'matriculation');

		$title = isset($_POST['title']) ? ilUtil::stripSlashes($_POST['title']) : $this->grp_obj->getTitle();
		$description  = isset($_POST["description"]) ? ilUtil::stripSlashes($_POST['description']) : $this->grp_obj->getDescription();
		$unique = $_POST['unique'] ? $_POST['unique'] : $this->grp_obj->getUniqueField();

		if(!$this->id)
		{
			sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return false;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_edit.html","course");

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilobjcoursegui','listGroupings'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_grouping').' ('.$this->grp_obj->getTitle().')');
		$this->tpl->setVariable("BTN_UPDATE",$this->lng->txt('update'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('crs_add_grp_assignment'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('description'));

		$this->tpl->setVariable("TXT_UNAM",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("UNAM_SELECT",ilUtil::formSelect($unique,'unique',$options));

		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("DESCRIPTION",$description);

		if($this->grp_obj->getCountAssignedCourses())
		{
			foreach($this->grp_obj->getAssignedCourses() as $cond_data)
			{
				if($cond_data['target_ref_id'] == $this->crs_obj->getRefId())
				{
					continue;
				}
				$tmp_obj =& ilObjectFactory::getInstanceByRefId($cond_data['target_ref_id']);
				
				$this->tpl->setCurrentBlock("list_courses");
				
				$this->ctrl->setParameter($this,'cond_id',$cond_data['id']);
				$this->tpl->setVariable("DELETE_LINK",$this->ctrl->getLinkTarget($this,'deleteAssignment'));
				$this->tpl->setVariable("LIST_CRS_TITLE",$tmp_obj->getTitle());
				$this->tpl->setVariable("TXT_DELETE",$this->lng->txt('delete'));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("assigned");
			$this->tpl->setVariable("ASS_ROWSPAN",$this->grp_obj->getCountAssignedCourses());
			$this->tpl->setVariable("ASS_COURSES",$this->lng->txt('crs_grp_table_assigned_courses'));
			$this->tpl->setVariable("CRS_TITLE",$this->crs_obj->getTitle());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("no_assigned");
			$this->tpl->setVariable("MESSAGE_NO_COURSES",$this->lng->txt('crs_grp_no_courses_assigned'));
			$this->tpl->parseCurrentBlock();
		}
	}

	function deleteAssignment()
	{
		include_once './classes/class.ilConditionHandler.php';

		if(!$this->id)
		{
			sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return false;
		}
		$condh =& new ilConditionHandler();
		$condh->deleteCondition((int) $_GET['cond_id']);

		// DELETE crs_obj condition if it is the last
		if($this->grp_obj->getCountAssignedCourses() == 1)
		{
			$condh->deleteByObjId($this->id);
		}
		sendInfo($this->lng->txt('crs_grp_deassigned_courses'));
		$this->edit();
	}
		

	function selectCourse()
	{
		global $tree;

		if(!$this->id)
		{
			sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return false;
		}
		if(count($courses = ilUtil::_getObjectsByOperations('crs','write')) == 1)
		{
			sendInfo($this->lng->txt('crs_grp_no_course_found'));
			$this->edit();

			return false;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_grp_select_crs.html","course");

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'edit'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_grp_assign_crs').' ('.$this->grp_obj->getTitle().')');
		$this->tpl->setVariable("BTN_ASSIGN",$this->lng->txt('crs_grp_assign_crs'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));

		$counter = 0;
		foreach($courses as $course_id)
		{
			if($course_id == $this->crs_obj->getRefId())
			{
				continue;
			}
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($course_id);

			#if(ilObjCourseGrouping::_isInGrouping($tmp_obj->getId()))
			#{
			#	continue;
			#}
			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_CRS",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("crs_row");
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_CRS",ilUtil::formCheckbox(0,'crs_ids[]',$course_id));
			$this->tpl->setVariable("CRS_TITLE",$tmp_obj->getTitle());

			$path = $this->__formatPath($tree->getPathFull($course_id));
			$this->tpl->setVariable("CRS_PATH",$this->lng->txt('path').": ".$path);

			
			$this->tpl->parseCurrentBlock();

		}

		return true;
	}

	function assignCourse()
	{
		include_once './classes/class.ilConditionHandler.php';

		if(!$this->id)
		{
			sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return false;
		}
		if(!count($_POST['crs_ids']))
		{
			sendInfo($this->lng->txt('crs_grp_no_course_selected'));
			$this->selectCourse();

			return true;
		}

		$added = 0;
		foreach($_POST['crs_ids'] as $course_ref_id)
		{
			$tmp_crs =& ilObjectFactory::getInstanceByRefId($course_ref_id);
			$tmp_condh =& new ilConditionHandler();
			$tmp_condh->enableAutomaticValidation(false);

			$tmp_condh->setTargetRefId($course_ref_id);
			$tmp_condh->setTargetObjId($tmp_crs->getId());
			$tmp_condh->setTargetType('crs');
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
		if($added)
		{
			// NOW add the course itself in condition table if it does not exist
			
			$tmp_condh =& new ilConditionHandler();
			$tmp_condh->enableAutomaticValidation(false);

			$tmp_condh->setTargetRefId($this->crs_obj->getRefId());
			$tmp_condh->setTargetObjId($this->crs_obj->getId());
			$tmp_condh->setTargetType('crs');
			$tmp_condh->setTriggerRefId(0);
			$tmp_condh->setTriggerObjId($this->id);
			$tmp_condh->setTriggerType('crsg');
			$tmp_condh->setOperator('not_member');
			$tmp_condh->setValue($this->grp_obj->getUniqueField());

			if(!$tmp_condh->checkExists())
			{
				$tmp_condh->storeCondition();
			}
			sendInfo($this->lng->txt('crs_grp_assigned_courses'));
			$this->edit();

			return true;
		}
		else
		{
			sendInfo($this->lng->txt('crs_grp_courses_already_assigned'));
			$this->edit();

			return true;
		}			
	}	
		

	function update()
	{
		if(!$this->id)
		{
			sendInfo($this->lng->txt('crs_grp_no_grouping_id_given'));
			$this->ctrl->redirectByClass('ilObjCourseGUI','listGroupings');
			
			return false;
		}
		if(!$_POST['title'])
		{
			sendInfo($this->lng->txt('crs_grp_enter_title'));
			$this->edit();

			return false;
		}
		$this->grp_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->grp_obj->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->grp_obj->setUniqueField($_POST['unique']);

		$this->grp_obj->update();

		sendInfo($this->lng->txt('crs_grp_modified_grouping'));
		$this->edit();

		return true;
	}

	function &executeCommand()
	{
		global $ilMainTabs;

		$ilMainTabs->activate('crs_groupings');

		$cmd = $this->ctrl->getCmd();
		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "edit";
		}
		$this->$cmd();
	}

	function otherSelectAssign()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem,$tree;

		if(!$rbacsystem->checkAccess("write", $this->crs_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_other_assign.html","course");
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'edit'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		if(!count($groupings = ilObjCourseGrouping::_getAllGroupings($this->crs_obj->getRefId(),false)))
		{
			sendInfo($this->lng->txt('crs_no_groupings_crs_can_be_assigned_to'));
		
			return true;
		}
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_UNAMBIGUOUSNESS",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_ASSIGN",$this->lng->txt('crs_grouping_assign'));
		
		
		$counter = 0;
		foreach($groupings as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			$tmp_crs = ilObjectFactory::getInstanceByRefId($tmp_obj->getCourseRefId());

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_GRP",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}
			foreach($tmp_obj->getAssignedCourses() as $condition)
			{
				$this->tpl->setCurrentBlock("path");
				$this->tpl->setVariable("ASS_PATH",'&nbsp;&nbsp;'.
										$this->__formatPath($tree->getPathFull($condition['target_ref_id'])));
				$this->tpl->parseCurrentBlock();
			}

			$disabled = !$rbacsystem->checkAccess('write',$tmp_obj->getCourseRefId());

			$this->tpl->setCurrentBlock("grouping_row");
			$this->tpl->setVariable("GRP_TITLE",$tmp_obj->getTitle().' ('.$tmp_crs->getTitle().')');
			$this->tpl->setVariable("CHECK_GRP",ilUtil::formCheckbox((int) $tmp_obj->isAssigned($this->crs_obj->getId()),
																	 'grouping[]',
																	 $grouping_id,
																	 $disabled));
										
			$this->tpl->setVariable("AMB_GRP",$this->lng->txt($tmp_obj->getUniqueField()));
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));


			if($num_courses = $tmp_obj->getCountAssignedCourses())
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_assigned_courses_info')." <b>$num_courses</b> ");
			}
			else
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_no_courses_assigned'));
			}
			$this->tpl->parseCurrentBlock();
		}	
	}

	function otherAssign()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem,$tree;

		if(!$rbacsystem->checkAccess("write", $this->crs_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$_POST['grouping'] = $_POST['grouping'] ? $_POST['grouping'] : array();
		foreach(ilObjCourseGrouping::_getAllGroupings($this->crs_obj->getRefId()) as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			if($tmp_obj->isAssigned($this->crs_obj->getId()) and !in_array($grouping_id,$_POST['grouping']))
			{
				$tmp_obj->deassign($this->crs_obj->getRefId(),$this->crs_obj->getId());
				continue;
			}
			if(!$tmp_obj->isAssigned($this->crs_obj->getId()) and in_array($grouping_id,$_POST['grouping']))
			{
				$tmp_obj->assign($this->crs_obj->getRefId(),$this->crs_obj->getId());
				continue;
			}
		}
		sendInfo($this->lng->txt('crs_grouping_modified_assignment'));
		$this->otherSelectAssign();
		
		return true;
	}
	// PRIVATE
	function __initGroupingObject()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		$this->grp_obj =& new ilObjCourseGrouping($this->id);
	}

	function __formatPath($a_path_arr)
	{
		$counter = 0;
		foreach($a_path_arr as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}

		if(strlen($path) > 40)
		{
			return '...'.substr($path,-40);
		}
		return $path;
	}

} // END class.ilObjCourseGrouping
?>
