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
* class ilobjcourseobjectivesgui
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseObjectivesGUI
{
	var $ctrl;
	var $ilias;
	var $ilErr;
	var $lng;
	var $tpl;

	var $course_obj;
	var $course_id;
	
	function ilCourseObjectivesGUI($a_course_id)
	{
		include_once './course/classes/class.ilCourseObjective.php';

		global $ilCtrl,$lng,$ilErr,$ilias,$tpl,$tree;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id"));

		$this->ilErr =& $ilErr;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tree =& $tree;

		$this->course_id = $a_course_id;
		$this->__initCourseObject();
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "list";
		}
		$this->$cmd();
	}

	function listAssignedLM()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}

		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_list_lm.html","course");


		$this->__initLMObject((int) $_GET['objective_id']);
		if(!count($lms = $this->objectives_lm_obj->getLMs()))
		{
			sendInfo($this->lng->txt('crs_no_lms_assigned'));
			$this->__showButton('assignLMSelect',$this->lng->txt('crs_objective_assign_lm'));

			return true;
		}

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_lm_list_row.html","course");

		$counter = 0;
		foreach($lms as $item)
		{
			++$counter;

			$tmp_lm =& ilObjectFactory::getInstanceByRefId($item['ref_id']);

			$title = $tmp_lm->getTitle();
			if($item['type'] == 'st')
			{
				include_once './content/classes/class.ilLMObjectFactory.php';

				$st_obj = ilLMObjectFactory::getInstance($tmp_lm,$item['obj_id']);
				$title .= (" -> ".$st_obj->getTitle());
			}
				
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'lm[]',$item['lm_ass_id']));

			$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('icon_'.$tmp_lm->getType().'.gif'));
			$tpl->setVariable("IMG_ALT",$this->lng->txt('obj_'.$tmp_lm->getType()));
			$tpl->setVariable("TITLE",$title);
			$tpl->setVariable("DESCRIPTION",$tmp_lm->getDescription());
			$tpl->parseCurrentBlock();

			unset($tmp_lm);
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'askDeleteLM');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_objective_delete_lm_assignment'));
		$tpl->parseCurrentBlock();

		// Show add button
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'assignLMSelect');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('crs_objective_assign_lm'));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",3);
		$tpl->setVariable("WIDTH","width=\"50%\"");
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$header_title = $this->lng->txt("crs_objectives_assigned_lms")." (".$objective_obj->getTitle().")";

		$tbl->setTitle($header_title,"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array('',$this->lng->txt('type'),$this->lng->txt("title")));
		$tbl->setHeaderVars(array("","type","title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "objective_id" => (int) $_GET['objective_id'],
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1%","1%",'98%'));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());

		return true;


	}

	function askDeleteLM()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!count($_POST['lm']))
		{
			sendInfo($this->lng->txt('crs_lm_no_assignments_selected'));
			$this->listAssignedLM();

			return false;
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_delete_lm.html","course");
		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);

		sendInfo($this->lng->txt('crs_deassign_lm_sure'));

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_delete_lm_row.html","course");

		$this->__initLMObject((int) $_GET['objective_id']);

		$counter = 0;
		foreach($_POST['lm'] as $lm_ass_id)
		{
			$lm_ass_data = $this->objectives_lm_obj->getLM($lm_ass_id);

			$tmp_lm =& ilObjectFactory::getInstanceByRefId($lm_ass_data['ref_id']);
			$title = $tmp_lm->getTitle();
			if($lm_ass_data['type'] == 'st')
			{
				include_once './content/classes/class.ilLMObjectFactory.php';

				$st_obj = ilLMObjectFactory::getInstance($tmp_lm,$lm_ass_data['obj_id']);
				$title .= (" -> ".$st_obj->getTitle());
			}

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor(++$counter,"tblrow2","tblrow1"));
			$tpl->setVariable("TITLE",$title);
			$tpl->setVariable("DESCRIPTION",$tmp_lm->getDescription());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'listAssignedLM');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('cancel'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'deleteLM');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_lm_deassign'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",1);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();

		$tpl->setVariable("WIDTH","width=\"50%\"");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');
		
			
		// title & header columns
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);
		$header_title = $this->lng->txt("crs_objective")." (".$objective_obj->getTitle().')';

		$tbl->setTitle($header_title,"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("100%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));


		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());

		// Save marked objectives
		$_SESSION['crs_delete_lm'] = $_POST['lm'];

		return true;


	}

	function deleteLM()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!count($_SESSION['crs_delete_lm']))
		{
			sendInfo('No lm selected');
			$this->listAssignedLM();

			return false;
		}

		$this->__initLMObject((int) $_GET['objective_id']);

		foreach($_SESSION['crs_delete_lm'] as $lm_ass_id)
		{
			$this->objectives_lm_obj->delete($lm_ass_id);
		}
		sendInfo($this->lng->txt('crs_lm_assignment_deleted'));
		$this->listAssignedLM();

		return true;
	}



	function assignLMSelect()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!count($all_lms = $this->__getAllLMs()))
		{
			sendInfo($this->lng->txt('crs_no_objective_lms_found'));
			$this->listAssignedLM();

			return false;
		}
		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_lm_select.html","course");
		$this->__showButton('listAssignedLM',$this->lng->txt('back'));


		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_lm_select_row.html","course");

		$counter = 0;
		foreach($all_lms as $item)
		{
			++$counter;

			$tmp_lm =& ilObjectFactory::getInstanceByRefId($item);

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'lm[]',$item));

			$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('icon_'.$tmp_lm->getType().'.gif'));
			$tpl->setVariable("IMG_ALT",$this->lng->txt('obj_'.$tmp_lm->getType()));
			$tpl->setVariable("TITLE",$tmp_lm->getTitle());
			$tpl->setVariable("DESCRIPTION",$tmp_lm->getDescription());
			$tpl->parseCurrentBlock();

			unset($tmp_lm);
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'assignLM');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_objective_assign_lm'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'assignChapterSelect');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_objective_assign_chapter'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",3);
		$tpl->setVariable("WIDTH","width=\"50%\"");
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$objectives_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);
		$header_title = $this->lng->txt("crs_objectives_lm_assignment").' ('.$objectives_obj->getTitle().')';

		$tbl->setTitle($header_title,"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array('',$this->lng->txt('type'),$this->lng->txt("title")));
		$tbl->setHeaderVars(array("","type","title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "objective_id" => (int) $_GET['objective_id'],
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1%","1%",'98%'));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());

		return true;
	}

	function assignChapterSelect()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(count($_POST['lm']) !== 1)
		{
			sendInfo($this->lng->txt('crs_select_exactly_one_lm'));
			$this->assignLMSelect();

			return false;
		}
		foreach($_POST['lm'] as $lm_id)
		{
			$tmp_lm =& ilObjectFactory::getInstanceByRefId($lm_id);
			if($tmp_lm->getType() != 'lm')
			{
				sendInfo($this->lng->txt('crs_select_native_lm'));
				$this->assignLMSelect();
				
				return false;
			}
		}
		$lm_id = (int) $_POST['lm'][0];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_chapter_select.html","course");
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_chapter_select_row.html","course");

		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->ctrl->setParameter($this,'lm_id',(int) $lm_id);
		$this->__showButton('assignLMSelect',$this->lng->txt('back'));
		
		$lm_obj =& ilObjectFactory::getInstanceByRefId($lm_id);

		$counter = 0;
		foreach($this->__getAllChapters($lm_id) as $chapter)
		{
			++$counter;
			include_once './content/classes/class.ilLMObjectFactory.php';

			$st_obj = ilLMObjectFactory::getInstance($lm_obj,$chapter);
			
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$tpl->setVariable("TITLE",$st_obj->getTitle());
			$tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'chapter[]',$st_obj->getId()));
			$tpl->parseCurrentBlock();
		}


		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'assignLMChapter');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_objectives_assign_chapter'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("WIDTH","width=\"50%\"");
		$tpl->setVariable("COLUMN_COUNTS",2);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');
		

		// title & header columns
		$objectives_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);
		$header_title = $this->lng->txt("crs_objectives_chapter_assignment").' ('.$objectives_obj->getTitle().')';

		$tbl->setTitle($header_title,"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array('',$this->lng->txt("title")));
		$tbl->setHeaderVars(array("type","title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1%","99%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());
		

		return true;


	}

	function assignLMChapter()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!count($_POST['chapter']))
		{
			$_POST['lm'] = array((int) $_GET['lm_id']);
			sendInfo($this->lng->txt('crs_no_chapter_selected'));
			$this->assignChapterSelect();

			return false;
		}

		$this->__initLMObject((int) $_GET['objective_id']);

		$counter = 0;
		foreach($_POST['chapter'] as $chapter_id)
		{
			$tmp_lm =& ilObjectFactory::getInstanceByRefId((int) $_GET['lm_id']);

			$this->objectives_lm_obj->setType('st');
			$this->objectives_lm_obj->setLMRefId($tmp_lm->getRefId());
			$this->objectives_lm_obj->setLMObjId($chapter_id);
			
			if($this->objectives_lm_obj->checkExists())
			{
				continue;
			}
			
			$this->objectives_lm_obj->add();
			++$counter;
		}

		if($counter)
		{
			sendInfo($this->lng->txt('crs_objectives_assigned_lm'));
			$this->listAssignedLM();
		}
		else
		{
			sendInfo($this->lng->txt('crs_chapter_already_assigned'));
			$this->assignLMSelect();
		}

		return true;
	}

	function assignLM()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!count($_POST['lm']))
		{
			sendInfo($this->lng->txt('crs_no_lm_selected'));
			$this->assignLMSelect();

			return false;
		}

		$this->__initLMObject((int) $_GET['objective_id']);

		$counter = 0;
		foreach($_POST['lm'] as $lm_id)
		{
			$tmp_lm =& ilObjectFactory::getInstanceByRefId($lm_id);

			$this->objectives_lm_obj->setType($tmp_lm->getType());
			$this->objectives_lm_obj->setLMRefId($tmp_lm->getRefId());
			$this->objectives_lm_obj->setLMObjId($tmp_lm->getId());
			
			if($this->objectives_lm_obj->checkExists())
			{
				continue;
			}
			
			$this->objectives_lm_obj->add();
			++$counter;
		}

		if($counter)
		{
			sendInfo($this->lng->txt('crs_objectives_assigned_lm'));
			$this->listAssignedLM();
		}
		else
		{
			sendInfo($this->lng->txt('crs_lms_already_assigned'));
			$this->assignLMSelect();
		}

		return true;
	}

	function listObjectives()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives.html","course");
		if(!count($objectives = ilCourseObjective::_getObjectiveIds($this->course_obj->getId())))
		{
			$this->__showButton('addObjective','crs_add_objective');
			sendInfo($this->lng->txt('crs_no_objectives_created'));
			
			return true;
		}

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_row.html","course");

		$counter = 0;
		foreach($objectives as $objective)
		{
			++$counter;
			$objective_obj =& $this->__initObjectivesObject($objective);

			// Up down links
			if(count($objectives) > 1)
			{
				if($counter < count($objectives))
				{
					$tpl->setCurrentBlock("img");
					$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
					$tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'moveObjectiveDown'));
					$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('a_down.gif'));
					$tpl->setVariable("IMG_ALT",$this->lng->txt('crs_move_down'));
					$tpl->parseCurrentBlock();
				}
				if($counter > 1) 
				{
					$tpl->setCurrentBlock("img");
					$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
					$tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'moveObjectiveUp'));
					$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('a_up.gif'));
					$tpl->setVariable("IMG_ALT",$this->lng->txt('crs_move_up'));
					$tpl->parseCurrentBlock();
				}
			}
			// Edit link
			$tpl->setCurrentBlock("img");
			$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
			$tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'editObjective'));
			$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('edit.gif'));
			$tpl->setVariable("IMG_ALT",$this->lng->txt('edit'));
			$tpl->parseCurrentBlock();
			
			// Assign lm
			$tpl->setCurrentBlock("img");
			$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
			$tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'listAssignedLM'));
			$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('icon_lm.gif'));
			$tpl->setVariable("IMG_ALT",$this->lng->txt('crs_lm_assignment'));
			$tpl->parseCurrentBlock();

			// Assign questions
			$tpl->setCurrentBlock("img");
			$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
			$tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'listQuestions'));
			$tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('icon_tst.gif'));
			$tpl->setVariable("IMG_ALT",$this->lng->txt('crs_question_assignment'));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("options");
			$tpl->setVariable("OPT_ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));


			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'objective[]',$objective_obj->getObjectiveId()));
			$tpl->setVariable("TITLE",$objective_obj->getTitle());
			$tpl->setVariable("DESCRIPTION",$objective_obj->getDescription());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'askDeleteObjective');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('delete'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'addObjective');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('crs_add_objective'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",3);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$tbl->setTitle($this->lng->txt("crs_objectives"),"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array('',$this->lng->txt("title"),$this->lng->txt('options')));
		$tbl->setHeaderVars(array("type","title",'options'), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1%","80%",'20%'));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());
		

		return true;
	}
	function moveObjectiveUp()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!$_GET['objective_id'])
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->moveUp((int) $_GET['objective_id']);
		sendInfo($this->lng->txt('crs_moved_objective'));

		$this->listObjectives();

		return true;
	}
	function moveObjectiveDown()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!$_GET['objective_id'])
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->moveDown((int) $_GET['objective_id']);
		sendInfo($this->lng->txt('crs_moved_objective'));

		$this->listObjectives();

		return true;
	}


	function addObjective()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_add_objective.html","course");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_add_objective'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required'));
		$this->tpl->setVariable("CMD_SUBMIT",'saveObjective');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('add'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));

		return true;
	}

	function editObjective()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_add_objective.html","course");

		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_update_objective'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required'));
		$this->tpl->setVariable("CMD_SUBMIT",'updateObjective');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('update'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));

		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$this->tpl->setVariable("TITLE",$objective_obj->getTitle());
		$this->tpl->setVariable("DESC",$objective_obj->getDescription());

		return true;
	}

	function updateObjective()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{		
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!$_POST['objective']['title'])
		{		
			sendInfo($this->lng->txt('crs_objective_no_title_given'));
			$this->editObjective();
			
			return false;
		}


		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->setObjectiveId((int) $_GET['objective_id']);
		$objective_obj->setTitle(ilUtil::stripSlashes($_POST['objective']['title']));
		$objective_obj->setDescription(ilUtil::stripSlashes($_POST['objective']['description']));

		$objective_obj->update();
		
		sendInfo($this->lng->txt('crs_objective_modified'));
		$this->listObjectives();

		return true;
	}


	function askDeleteObjective()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!count($_POST['objective']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives.html","course");

		sendInfo($this->lng->txt('crs_delete_objectve_sure'));

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_delete_row.html","course");

		$counter = 0;
		foreach($_POST['objective'] as $objective_id)
		{
			$objective_obj =& $this->__initObjectivesObject($objective_id);

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor(++$counter,"tblrow2","tblrow1"));
			$tpl->setVariable("TITLE",$objective_obj->getTitle());
			$tpl->setVariable("DESCRIPTION",$objective_obj->getDescription());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Show action row
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'listObjectives');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('cancel'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'deleteObjectives');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('delete'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",1);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$tbl->setTitle($this->lng->txt("crs_objectives"),"icon_crs.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("50%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($objectives));

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());
		

		// Save marked objectives
		$_SESSION['crs_delete_objectives'] = $_POST['objective'];

		return true;
	}

	function deleteObjectives()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!count($_SESSION['crs_delete_objectives']))
		{
			sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}

		foreach($_SESSION['crs_delete_objectives'] as $objective_id)
		{
			$objective_obj =& $this->__initObjectivesObject($objective_id);

			$objective_obj->delete();
		}

		sendInfo($this->lng->txt('crs_objectives_deleted'));
		$this->listObjectives();

		return true;
	}


	function saveObjective()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!$_POST['objective']['title'])
		{
			sendInfo('crs_no_title_given',true);

			$this->addObjective();
			return false;
		}

		$objective_obj =& $this->__initObjectivesObject();

		$objective_obj->setTitle(ilUtil::stripSlashes($_POST['objective']['title']));
		$objective_obj->setDescription(ilUtil::stripSlashes($_POST['objective']['description']));
		$objective_obj->add();
		
		sendInfo($this->lng->txt('crs_added_objective'));
		$this->listObjectives();

		return true;
	}
		


	// PRIVATE
	function __initCourseObject()
	{
		if(!$this->course_obj =& ilObjectFactory::getInstanceByRefId($this->course_id,false))
		{
			$this->ilErr->raiseError("ilCourseObjectivesGUI: cannot create course object",$this->ilErr->MESSAGE);
			exit;
		}
		// do i need members?
		$this->course_obj->initCourseMemberObject();

		return true;
	}

	function &__initObjectivesObject($a_id = 0)
	{
		return new ilCourseObjective($this->course_obj,$a_id);
	}

	function __initLMObject($a_objective_id = 0)
	{
		include_once './course/classes/class.ilCourseObjectiveLM.php';

		$this->objectives_lm_obj =& new ilCourseObjectiveLM($a_objective_id);

		return true;
	}

	function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);

		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}

	function __getAllLMs()
	{
		global $tree;
		
		foreach($tree->getSubTree($tree->getNodeData($this->course_obj->getRefId())) as $node)
		{
			switch($node['type'])
			{
				case 'lm':
				case 'htlm':
				case 'sahs':
					$all_lms[] = $node['ref_id'];
					break;
			}
		}
		return $all_lms ? $all_lms : array();
	}

	function __getAllChapters($a_ref_id)
	{
		$tmp_lm =& ilObjectFactory::getInstanceByRefId($a_ref_id);

		$tree = new ilTree($tmp_lm->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		foreach($tree->getSubTree($tree->getNodeData($tree->getRootId())) as $node)
		{
			if($node['type'] == 'st')
			{
				$chapter[] = $node['child'];
			}
		}

		return $chapter ? $chapter : array();
	}


}
?>