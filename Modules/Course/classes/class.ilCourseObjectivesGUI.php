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
		include_once './Modules/Course/classes/class.ilCourseObjective.php';

		global $ilCtrl,$lng,$ilErr,$ilias,$tpl,$tree,$ilTabs;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id"));

		$this->ilErr =& $ilErr;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->tabs_gui =& $ilTabs;

		$this->course_id = $a_course_id;
		$this->__initCourseObject();
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilTabs;

		$ilTabs->setTabActive('crs_objectives');
		
		$cmd = $this->ctrl->getCmd();


		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "list";
		}
		
		$this->setSubTabs();
		$this->$cmd();
	}
	
	/**
	 * List question assignent 
	 *
	 * @access public
	 * 
	 */
	public function listQuestionAssignment()
	{
	 	global $ilAccess;
	 	
	 	if(!$ilAccess->checkAccess('write','',$this->course_obj->getRefId()))
	 	{
	 		$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write',$this->ilErr->MESSAGE));
	 	}
		if(!isset($_GET['objective_id']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			return false;
		}
		include_once('Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
		if(!$assignable = ilCourseObjectiveQuestion::_getAssignableTests($this->course_obj->getRefId()))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_tests_inside_crs'));
			$this->listObjectives();
			return false;
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_objective_list_questions.html','Modules/Course');
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_lobj.gif'));
		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
		// Back button
		$this->__showButton('listObjectives',$this->lng->txt('back'));
		
		// Title
		$this->__initObjectivesObject((int) $_GET['objective_id']);
		$this->__initQuestionObject((int) $_GET['objective_id']);
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('crs_objectives_lm_assignment'));
		$this->tpl->setVariable('OBJECTIVE_TITLE',$this->objectives_obj->getTitle());
		
		// Footer
		$this->tpl->setVariable('DOWNRIGHT',ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable('BTN_ASSIGN',$this->lng->txt('crs_objective_assign_lm'));
		$this->tpl->setVariable('BTN_CANCEL',$this->lng->txt('cancel'));
		
		$counter = 0;
		foreach($assignable as $node)
		{
			if(!$tmp_tst =& ilObjectFactory::getInstanceByRefId((int) $node['ref_id'],false))
			{
				continue;
			}		

			$assignable = false;
			foreach($qst = $this->__sortQuestions($tmp_tst->getAllQuestions()) as $question_data)
			{
				$tmp_question =& ilObjTest::_instanciateQuestion($question_data['question_id']);

				$this->tpl->setCurrentBlock('chapter');
				$this->tpl->setVariable('CHAPTER_TITLE',$tmp_question->getTitle());
				$id = ilCourseObjectiveQuestion::_isAssigned((int) $_GET['objective_id'],
					$tmp_tst->getRefId(),
					$question_data['question_id']);
				$this->tpl->setVariable('CHECK_CHAPTER',ilUtil::formCheckbox(
						$id ? 1 : 0,
						'questions[]',$node['ref_id'].'_'.$question_data['question_id']));
				$this->tpl->parseCurrentBlock();
	
			}
			if(count($qst))
			{
				$this->tpl->setCurrentBlock('chapters');
				$this->tpl->setVariable('TXT_CHAPTER',$this->lng->txt('objs_qst'));
				$this->tpl->parseCurrentBlock();
			}			
			
			
			if(strlen($node['description']))
			{
				$this->tpl->setCurrentBlock('row_desc');
				$this->tpl->setVariable('DESCRIPTION',$node['description']);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock('table_content');
			$this->tpl->setVariable('ROWCOL',ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'.gif'));
			$this->tpl->setVariable('TYPE_ALT',$this->lng->txt('obj_'.$node['type']));
			$this->tpl->setVariable('MAT_TITLE',$node['title']);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Assign materials
	 *
	 * @access public
	 */
	public function assignQuestions()
	{
		global $ilAccess,$ilObjDataCache;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			return false;
		}

		$this->__initQuestionObject((int) $_GET['objective_id']);
		
		if(!is_array($_POST['questions']))
		{
			$this->objectives_qst_obj->deleteAll();
		}
		else
		{
			// Delete unchecked
			foreach($this->objectives_qst_obj->getQuestions() as $question)
			{
				$id = $question['ref_id'].'_'.$question['question_id'];
				if(!in_array($id,$_POST['questions']))
				{
					$this->objectives_qst_obj->delete($question['qst_ass_id']);
				}
			}
			// Add checked
			foreach($_POST['questions'] as $question_id)
			{
				list($test_ref_id,$qst_id) = explode('_',$question_id);
				$test_obj_id = $ilObjDataCache->lookupObjId($test_ref_id);

				if(ilCourseObjectiveQuestion::_isAssigned((int) $_GET['objective_id'],$test_ref_id,$qst_id))
				{
					continue;
				}
				
				$this->objectives_qst_obj->setTestRefId($test_ref_id);
				$this->objectives_qst_obj->setTestObjId($test_obj_id);
				$this->objectives_qst_obj->setQuestionId($qst_id);
				$this->objectives_qst_obj->add();
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('crs_objectives_assigned_lm'));
		$this->listObjectives();
	}
	

	/**
	 * Show assignment of course materials
	 *
	 * @access public
	 * 
	 */
	public function listMaterialAssignment()
	{
	 	global $ilAccess, $objDefinition;
	 	
	 	if(!$ilAccess->checkAccess('write','',$this->course_obj->getRefId()))
	 	{
	 		$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write',$this->ilErr->MESSAGE));
	 	}
		if(!isset($_GET['objective_id']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			return false;
		}
		include_once('Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
		if(!$assignable = ilCourseObjectiveMaterials::_getAssignableMaterials($this->course_obj->getRefId()))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_lms_found'));
			$this->listObjectives();
			return false;
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_objective_list_materials.html','Modules/Course');
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_lobj.gif'));
		
		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
		// Back button
		$this->__showButton('listObjectives',$this->lng->txt('back'));
		
		// Title
		$this->__initObjectivesObject((int) $_GET['objective_id']);
		$this->__initLMObject((int) $_GET['objective_id']);
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('crs_objectives_lm_assignment'));
		$this->tpl->setVariable('OBJECTIVE_TITLE',$this->objectives_obj->getTitle());
		
		// Footer
		$this->tpl->setVariable('DOWNRIGHT',ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable('BTN_ASSIGN',$this->lng->txt('crs_objective_assign_lm'));
		$this->tpl->setVariable('BTN_CANCEL',$this->lng->txt('cancel'));
		
		$counter = 0;
		foreach($assignable as $node)
		{
			// no side blocks here
			if($objDefinition->isSideBlock($node['type']))
			{
				continue;
			}
			
			if($node['type'] == 'lm')
			{
				include_once('Modules/LearningModule/classes/class.ilLMObject.php');
				foreach($chapters = $this->__getAllChapters($node['child']) as $chapter)
				{
					$this->tpl->setCurrentBlock('chapter');
					$this->tpl->setVariable('CHAPTER_TITLE',ilLMObject::_lookupTitle($chapter));
					$this->tpl->setVariable('CHECK_CHAPTER',ilUtil::formCheckbox(
						$this->objectives_lm_obj->isChapterAssigned($node['ref_id'],$chapter) ? 1 : 0,
						'chapters[]',$node['child'].'_'.$chapter));
					$this->tpl->parseCurrentBlock();
				}
				if(count($chapters))
				{
					$this->tpl->setCurrentBlock('chapters');
					$this->tpl->setVariable('TXT_CHAPTER',$this->lng->txt('objs_st'));
					$this->tpl->parseCurrentBlock();
				}			
			}
			if(strlen($node['description']))
			{
				$this->tpl->setCurrentBlock('row_desc');
				$this->tpl->setVariable('DESCRIPTION',$node['description']);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock('table_content');
			$this->tpl->setVariable('ROWCOL',ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('CHECK_MAT',ilUtil::formCheckbox($this->objectives_lm_obj->isAssigned($node['child']) ? 1 : 0,
				'materials[]',$node['child']));
			$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'.gif'));
			$this->tpl->setVariable('TYPE_ALT',$this->lng->txt('obj_'.$node['type']));
			$this->tpl->setVariable('MAT_TITLE',$node['title']);
			$this->tpl->parseCurrentBlock();
		}
		

		
	}
	
	/**
	 * Assign materials
	 *
	 * @access public
	 */
	public function assignMaterials()
	{
		global $ilAccess,$ilObjDataCache;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			return false;
		}

		$this->__initLMObject((int) $_GET['objective_id']);
		$this->objectives_lm_obj->deleteAll();
		
		if(is_array($_POST['materials']))
		{
			foreach($_POST['materials'] as $node_id)
			{
				$obj_id = $ilObjDataCache->lookupObjId($node_id);
				$type = $ilObjDataCache->lookupType($obj_id);
				
				$this->objectives_lm_obj->setLMRefId($node_id);
				$this->objectives_lm_obj->setLMObjId($obj_id);
				$this->objectives_lm_obj->setType($type);
				$this->objectives_lm_obj->add();
			}
		}
		if(is_array($_POST['chapters']))
		{
			foreach($_POST['chapters'] as $chapter)
			{
				list($ref_id,$chapter_id) = explode('_',$chapter);
				
				$this->objectives_lm_obj->setLMRefId($ref_id);
				$this->objectives_lm_obj->setLMObjId($chapter_id);
				$this->objectives_lm_obj->setType('st');
				$this->objectives_lm_obj->add();
			}
		}
		ilUtil::sendInfo($this->lng->txt('crs_objectives_assigned_lm'));
		$this->listObjectives();
	}

	
	/**
	 * Show objectives
	 *
	 * @access public
	 * 
	 */
	public function listObjectives()
	{
	 	global $ilAccess,$ilErr,$ilObjDataCache;
	 	
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_show_objectives.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_lobj.gif'));
		$this->tpl->setVariable('TBL_TITLE_IMG_ALT',$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable('HEAD_TITLE',$this->lng->txt('title'));
		$this->tpl->setVariable('HEAD_MATERIALS',$this->lng->txt('crs_objective_assigned_materials'));
		$this->tpl->setVariable('HEAD_QUESTIONS',$this->lng->txt('crs_objective_assigned_qst'));
		$this->tpl->setVariable('DOWNRIGHT',ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable('BTN_DELETE',$this->lng->txt('delete'));
		$this->tpl->setVariable('BTN_ADD',$this->lng->txt('crs_add_objective'));
		
		if(!count($objectives = ilCourseObjective::_getObjectiveIds($this->course_obj->getId())))
		{
			$this->tpl->setCurrentBlock('table_empty');
			$this->tpl->setVariable('EMPTY_TXT',$this->lng->txt('crs_no_objectives_created'));
			$this->tpl->parseCurrentBlock();
			return true;
		}
		
		$counter = 0;
		foreach($objectives as $objective)
		{
			$objective_obj = $this->__initObjectivesObject($objective);
	
			// Up down links
			++$counter;
			if(count($objectives) > 1)
			{
				if($counter == 1)
				{
					$this->tpl->setVariable("NO_IMG_PRE_TYPE",ilUtil::getImagePath('empty.gif'));
				}					
				if($counter > 1) 
				{
					$this->tpl->setCurrentBlock("img");
					$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
					$this->tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'moveObjectiveUp'));
					$this->tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('a_up.gif'));
					$this->tpl->setVariable("IMG_ALT",$this->lng->txt('crs_move_up'));
					$this->tpl->parseCurrentBlock();
				}
				if($counter < count($objectives))
				{
					$this->tpl->setCurrentBlock("img");
					$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
					$this->tpl->setVariable("IMG_LINK",$this->ctrl->getLinkTarget($this,'moveObjectiveDown'));
					$this->tpl->setVariable("IMG_TYPE",ilUtil::getImagePath('a_down.gif'));
					$this->tpl->setVariable("IMG_ALT",$this->lng->txt('crs_move_down'));
					$this->tpl->parseCurrentBlock();
				}
				if($counter == count($objectives))
				{
					$this->tpl->setCurrentBlock("no_img_post");
					$this->tpl->setVariable("NO_IMG_POST_TYPE",ilUtil::getImagePath('empty.gif'));
					$this->tpl->parseCurrentBlock();
				}					
			}
			
			// Assigned Tests
			$this->__initQuestionObject($objective_obj->getObjectiveId());
			foreach($this->objectives_qst_obj->getTests() as $tst)
			{
				foreach($this->objectives_qst_obj->getQuestionsOfTest($tst['obj_id']) as $qst)
				{
					$this->tpl->setCurrentBlock('qst_row');
					$this->tpl->setVariable('QST_TITLE',$qst['title']);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock('test_row');
				$this->tpl->setVariable('TST_IMG',ilUtil::getImagePath('icon_tst_s.gif'));
				$this->tpl->setVariable('TST_ALT',$this->lng->txt('obj_tst'));
				$this->tpl->setVariable('TST_TITLE',$tst['title']);
				$this->tpl->parseCurrentBlock();
			}

			// Assigned Materials
			$this->__initLMObject($objective_obj->getObjectiveId());
			foreach($this->objectives_lm_obj->getMaterials() as $material)
			{
				$this->tpl->setCurrentBlock('material_row');
		
				$container_obj_id = $ilObjDataCache->lookupObjId($material['ref_id']);
				$title = $ilObjDataCache->lookupTitle($container_obj_id);
				switch($material['type'])
				{
					case 'st':
						include_once('Modules/LearningModule/classes/class.ilLMObject.php');
						$img = ilUtil::getImagePath('icon_lm_s.gif');
						$alt = $this->lng->txt('obj_'.$material['type']);
						$chapter_title = 
						$title .= (' -> '.ilLMObject::_lookupTitle($material['obj_id'])); 
						break;
					default: 
						$img = ilUtil::getImagePath('icon_'.$material['type'].'_s.gif');
						$alt = $this->lng->txt('obj_'.$material['type']);
						break;
				}
				$this->tpl->setVariable('MAT_IMG',$img);
				$this->tpl->setVariable('MAT_ALT',$alt);
				$this->tpl->setVariable('MAT_TITLE',$title);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("table_content");
			$this->tpl->setVariable('LABEL_ID',$objective_obj->getObjectiveId());
			$this->tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$this->tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'objective[]',$objective_obj->getObjectiveId()));
			$this->tpl->setVariable("TITLE",$objective_obj->getTitle());
			$this->tpl->setVariable("DESCRIPTION",$objective_obj->getDescription());
			
			$this->ctrl->setParameter($this,'objective_id',$objective_obj->getObjectiveId());
			$this->tpl->setVariable('LINK_MAT',$this->ctrl->getLinkTarget($this,'listMaterialAssignment'));
			$this->tpl->setVariable('ADD_MAT',$this->lng->txt('crs_objective_add_mat'));
			$this->tpl->setVariable('LINK_QST',$this->ctrl->getLinkTarget($this,'listQuestionAssignment'));
			$this->tpl->setVariable('ADD_QST',$this->lng->txt('crs_objective_add_qst'));
			$this->tpl->setVariable('LINK_EDIT',$this->ctrl->getLinkTarget($this,'editObjective'));
			$this->tpl->setVariable('EDIT',$this->lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}
	 	
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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->moveUp((int) $_GET['objective_id']);
		ilUtil::sendInfo($this->lng->txt('crs_moved_objective'));

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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}
		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->moveDown((int) $_GET['objective_id']);
		ilUtil::sendInfo($this->lng->txt('crs_moved_objective'));

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
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_add_objective.html",'Modules/Course');
		
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_lobj.gif'));
		$this->tpl->setVariable('TBL_TITLE_IMG_ALT',$this->lng->txt('crs_objectives'));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_add_objective'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required_field'));
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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_add_objective.html",'Modules/Course');

		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('crs_update_objective'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_lobj.gif'));
		$this->tpl->setVariable('TBL_TITLE_IMG_ALT',$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required_field'));
		$this->tpl->setVariable("CMD_SUBMIT",'updateObjective');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('save'));
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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}
		if(!$_POST['objective']['title'])
		{		
			ilUtil::sendInfo($this->lng->txt('crs_objective_no_title_given'));
			$this->editObjective();
			
			return false;
		}


		$objective_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);

		$objective_obj->setObjectiveId((int) $_GET['objective_id']);
		$objective_obj->setTitle(ilUtil::stripSlashes($_POST['objective']['title']));
		$objective_obj->setDescription(ilUtil::stripSlashes($_POST['objective']['description']));

		$objective_obj->update();
		
		ilUtil::sendInfo($this->lng->txt('crs_objective_modified'));
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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives.html",'Modules/Course');

		ilUtil::sendInfo($this->lng->txt('crs_delete_objectve_sure'));

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_delete_row.html",'Modules/Course');

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
		$tpl->setVariable("BTN_NAME",'deleteObjectives');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('delete'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'listObjectives');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('cancel'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",1);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();


		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$tbl->setTitle($this->lng->txt("crs_objectives"),"icon_lobj.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("50%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($_POST['objective']));

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
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();
			
			return true;
		}

		foreach($_SESSION['crs_delete_objectives'] as $objective_id)
		{
			$objective_obj =& $this->__initObjectivesObject($objective_id);
			$objective_obj->delete();
		}

		ilUtil::sendInfo($this->lng->txt('crs_objectives_deleted'));
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
			ilUtil::sendInfo('crs_no_title_given',true);

			$this->addObjective();
			return false;
		}

		$objective_obj =& $this->__initObjectivesObject();

		$objective_obj->setTitle(ilUtil::stripSlashes($_POST['objective']['title']));
		$objective_obj->setDescription(ilUtil::stripSlashes($_POST['objective']['description']));
		$objective_obj->add();
		
		ilUtil::sendInfo($this->lng->txt('crs_added_objective'));
		$this->listObjectives();

		return true;
	}

	// Question assignment
	function listAssignedQuestions()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!isset($_GET['objective_id']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_objective_selected'));
			$this->listObjectives();

			return false;
		}

		$this->ctrl->setParameter($this,'objective_id',(int) $_GET['objective_id']);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_list_qst.html",'Modules/Course');

		if(!count($this->__getAllTests()))
		{
			#$this->__showButton('listObjectives',$this->lng->txt('crs_objective_overview_objectives'));
			ilUtil::sendInfo($this->lng->txt('crs_no_tests_inside_crs'));
			
			return true;
		}

		$this->__initQuestionObject((int) $_GET['objective_id']);
		if(!count($questions = $this->objectives_qst_obj->getQuestions()))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_questions_assigned'));
			#$this->__showButton('listObjectives',$this->lng->txt('crs_objective_overview_objectives'));
			$this->__showButton('assignTestSelect',$this->lng->txt('crs_objective_assign_question'));

			return true;
		}

		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_list_qst_row.html",'Modules/Course');

		#$this->__showButton('listObjectives',$this->lng->txt('crs_objective_overview_objectives'));

		$counter = 0;
		foreach($this->__sortQuestions($questions) as $question)
		{
			++$counter;

			include_once './Modules/Test/classes/class.ilObjTest.php';

			$tmp_question =& ilObjTest::_instanciateQuestion($question['question_id']);

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
			$tpl->setVariable("CHECK_OBJECTIVE",ilUtil::formCheckbox(0,'question[]',$question['qst_ass_id']));
			$tpl->setVariable("TITLE",$tmp_question->getTitle());
			$tpl->setVariable("DESCRIPTION",$tmp_question->getComment());
			$tpl->parseCurrentBlock();

			unset($tmp_question);
		}

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		// Show action row

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'askDeassignQuestion');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('crs_objective_deassign_question'));
		$tpl->parseCurrentBlock();

		// Show add button
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'assignTestSelect');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('crs_objective_assign_question'));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",2);
		$tpl->setVariable("WIDTH","width=\"50%\"");
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();

		// create table
		$tbl = new ilTableGUI();
		$tbl->setStyle('table','std');

		// title & header columns
		$objectives_obj =& $this->__initObjectivesObject((int) $_GET['objective_id']);
		$header_title = $this->lng->txt("crs_objectives_assigned_questions").' ('.$objectives_obj->getTitle().')';

		$tbl->setTitle($header_title,"icon_lobj.gif",$this->lng->txt("crs_objectives"));

		$tbl->setHeaderNames(array('',$this->lng->txt("title")));
		$tbl->setHeaderVars(array("","title"), 
							array("ref_id" => $this->course_obj->getRefId(),
								  "objective_id" => (int) $_GET['objective_id'],
								  "cmdClass" => "ilcourseobjectivesgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1%","99%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(0);

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());

		return true;
	}


	function __sortQuestions($a_qst_ids)
	{
		return ilUtil::sortArray($a_qst_ids,'title','asc');
	}


	function editQuestionAssignment()
	{
		global $rbacsystem;

		$this->tabs_gui->setSubTabActive('crs_objective_overview_question_assignment');

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.crs_objectives_edit_question_assignments.html','Modules/Course');

		#$this->__showButton('listObjectives',$this->lng->txt('crs_objective_overview_objectives'));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CSS_TABLE",'fullwidth');
		$this->tpl->setVariable("WIDTH",'80%');
		$this->tpl->setVariable("COLUMN_COUNT",5);
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_lobj.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_objectives'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_objectives_edit_question_assignments'));
		
		$head_titles = array(array($this->lng->txt('title'),"35%"),
							 array($this->lng->txt('crs_objectives_nr_questions'),"10%"),
							 array($this->lng->txt('crs_objectives_max_points'),"10%"),
							 array($this->lng->txt('options'),"35%"));

		$counter = 0;
		foreach($head_titles as $title)
		{
			$this->tpl->setCurrentBlock("tbl_header_no_link");

			if(!$counter)
			{
				$this->tpl->setVariable("TBL_HEADER_COLSPAN",' colspan="2"');
				++$counter;
			}
			$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",$title[0]);
			$this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK",$title[1]);
			$this->tpl->parseCurrentBlock();
		}

		foreach(ilCourseObjective::_getObjectiveIds($this->course_obj->getId()) as $objective_id)
		{
			$tmp_objective_obj =& $this->__initObjectivesObject($objective_id);
			
			$this->__initQuestionObject($objective_id);

			$counter = 1;
			foreach($this->objectives_qst_obj->getTests() as $test_data)
			{
				$show_buttons = true;

				$tmp_test =& ilObjectFactory::getInstanceByRefId($test_data['ref_id']);

				$this->tpl->setCurrentBlock("test_row");
				$this->tpl->setVariable("TEST_TITLE",$tmp_test->getTitle());
				$this->tpl->setVariable("TEST_QST",$this->objectives_qst_obj->getNumberOfQuestionsByTest($test_data['ref_id']));
				$this->tpl->setVariable("TEST_POINTS",$this->objectives_qst_obj->getMaxPointsByTest($test_data['ref_id']));

				// Options
				$this->tpl->setVariable("TXT_CHANGE_STATUS",$this->lng->txt('crs_change_status'));
				$this->tpl->setVariable("CHECK_CHANGE_STATUS",ilUtil::formCheckbox((int) $test_data['tst_status'],
																				   'test['.$test_data['test_objective_id'].'][status]'
																				   ,1));
				$this->tpl->setVariable("TXT_SUGGEST",$this->lng->txt('crs_suggest_lm'));
				$this->tpl->setVariable("SUGGEST_NAME",'test['.$test_data['test_objective_id'].'][limit]');
				$this->tpl->setVariable("SUGGEST_VALUE",(int) $test_data['tst_limit']);

				$this->tpl->parseCurrentBlock();



				++$counter;
			}
			$this->tpl->setCurrentBlock("objective_row");
			$this->tpl->setVariable("OBJ_TITLE",$tmp_objective_obj->getTitle());
			$this->tpl->setVariable("OBJ_DESCRIPTION",$tmp_objective_obj->getDescription());
			$this->tpl->setVariable("OBJ_QST",count($this->objectives_qst_obj->getQuestions()));
			$this->tpl->setVariable("OBJ_POINTS",$this->objectives_qst_obj->getMaxPointsByObjective());
			$this->tpl->setVariable("ROWSPAN",$counter);
			$this->tpl->parseCurrentBlock();
			
			// Options
			unset($tmp_objective_obj);
		}
		// Buttons
		if($show_buttons)
		{
			$this->tpl->setCurrentBlock("edit_footer");
			$this->tpl->setVariable("TXT_RESET",$this->lng->txt('reset'));
			$this->tpl->setVariable("TXT_UPDATE",$this->lng->txt('save'));
			$this->tpl->setVariable("CMD_UPDATE",'updateQuestionAssignment');
			$this->tpl->parseCurrentBlock();
		}
	}

	function updateQuestionAssignment()
	{
		global $rbacsystem;

		$this->tabs_gui->setSubTabActive('crs_objective_overview_question_assignment');


		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->course_obj->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}
		if(!is_array($_POST['test']))
		{
			ilUtil::sendInfo('Internal error: CRSM learning objectives');
			$this->editQuestionAssignment();

			return false;
		}
		// Validate
		foreach($_POST['test'] as $test_obj_id => $data)
		{
			if(!preg_match('/1?[0-9][0-9]?/',$data['limit']) or 
			   $data['limit'] < 0 or 
			   $data['limit'] > 100)
			{
				ilUtil::sendInfo($this->lng->txt('crs_objective_insert_percent'));
				$this->editQuestionAssignment();

				return false;
			}
		}
		
		foreach($_POST['test'] as $test_obj_id => $data)
		{
			include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';

			$test_data = ilCourseObjectiveQuestion::_getTest($test_obj_id);

			$this->__initQuestionObject($test_data['objective_id']);
			$this->objectives_qst_obj->setTestStatus($data['status'] ? 1 : 0);
			$this->objectives_qst_obj->setTestSuggestedLimit($data['limit']);
			$this->objectives_qst_obj->updateTest($test_obj_id);
		}
		ilUtil::sendInfo($this->lng->txt('crs_objective_updated_test'));
		$this->editQuestionAssignment();

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
		return $this->objectives_obj = new ilCourseObjective($this->course_obj,$a_id);
	}

	function __initLMObject($a_objective_id = 0)
	{
		include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
		$this->objectives_lm_obj =& new ilCourseObjectiveMaterials($a_objective_id);

		return true;
	}

	function __initQuestionObject($a_objective_id = 0)
	{
		include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
		$this->objectives_qst_obj =& new ilCourseObjectiveQuestion($a_objective_id);

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

	/**
	* set sub tabs
	*/
	function setSubTabs()
	{
		global $ilTabs;

		$ilTabs->addSubTabTarget("crs_objective_overview_objectives",
								 $this->ctrl->getLinkTarget($this, "listObjectives"),
								 array("listObjectives", "moveObjectiveUp", "moveObjectiveDown", "listAssignedLM"),
								 array(),
								 '',
								 true);
			
		$ilTabs->addSubTabTarget("crs_objective_overview_question_assignment",
								 $this->ctrl->getLinkTarget($this, "editQuestionAssignment"),
								 "editQuestionAssignment",
								 array(),
								 '',
								 false);

	}
}
?>