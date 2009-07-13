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
* Class ilCourseObjectivePresentationGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* 
* @ilCtrl_Calls ilCourseObjectivePresentationGUI: 
* 
*/

include_once 'Modules/Course/classes/class.ilCourseObjectiveResult.php';

class ilCourseObjectivePresentationGUI
{
	var $container_obj;
	var $course_obj;

	var $tpl;
	var $ctrl;
	var $lng;
	var $tabs_gui;

	/**
	* Constructor
	* @access public
	*/
	function ilCourseObjectivePresentationGUI(&$container_gui)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs,$ilUser;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tabs_gui =& $ilTabs;

		$this->container_gui =& $container_gui;
		$this->container_obj =& $this->container_gui->object;

		$this->objective_result_obj = new ilCourseObjectiveResult($ilUser->getId());

		$this->__initCourseObject();
	}

		

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd();
				if (!$cmd = $this->ctrl->getCmd())
				{
					$cmd = "view";
				}
				$this->$cmd();
				break;
		}
	}

	/**
	* Also called from ilCtrl (no command is performed here,
	* just to get standard html)
	*/
	function &getHTML()
	{
		$this->view();
	}

	function view()
	{
		global $rbacsystem,$ilUser,$ilBench;

		$ilBench->start('Objectives','Objectives_view');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objective_view.html",'Modules/Course');
		$this->__showButton('askReset',$this->lng->txt('crs_reset_results'));

		$this->__readObjectivesStatus();

		$ilBench->stop('Objectives','Objectives_read');

		// (1) show infos
		$this->__showInfo();

		// (2) show objectives
		$ilBench->start('Objectives','Objectives_objectives');
		$this->__showObjectives();
		$ilBench->stop('Objectives','Objectives_objectives');

		// (3) show lm's
		$ilBench->start('Objectives','Objectives_lms');
		$this->__showLearningMaterials();
		$ilBench->stop('Objectives','Objectives_lms');

		// (4) show tests
		$ilBench->start('Objectives','Objectives_tests');
		$this->__showTests();
		$ilBench->stop('Objectives','Objectives_tests');


		// (5) show other resources
		$ilBench->start('Objectives','Objectives_or');
		$this->__showOtherResources();
		$ilBench->stop('Objectives','Objectives_or');

		$ilBench->stop('Objectives','Objectives_view');

		$ilBench->save();

		return true;
	}


	function askReset()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_ask_reset.html",'Modules/Course');
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_reset_sure'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_RESET",$this->lng->txt('reset'));
		
		return true;
	}
	
	function reset()
	{
		global $ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
		
		// Debug
		ilObjUser::_writePref($ilUser->getId(),'crs_objectives_force_details_'.$this->course_obj->getId(),0);
		
		
		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->course_obj->getId());
		
		ilUtil::sendSuccess($this->lng->txt('crs_objectives_reseted'));
		$this->view();
	}
		


	// PRIVATE
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
	
	function __readObjectivesStatus()
	{
		$this->objective_result_obj->readStatus($this->course_obj->getId());
		$this->accomplished = $this->objective_result_obj->getAccomplished($this->course_obj->getId());
		$this->status = $this->objective_result_obj->getStatus($this->course_obj->getId());
		$this->suggested = $this->objective_result_obj->getSuggested($this->course_obj->getId(),$this->status);
	}

	function __showInfo()
	{
		include_once './Modules/Course/classes/class.ilCourseObjective.php';

		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->course_obj->getId())))
		{
			return true;
		}

		$this->tpl->addBlockfile('INFO_BLOCK','info_block','tpl.crs_objectives_view_info_table.html','Modules/Course');
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_info_'.$this->status));
//		ilUtil::sendInfo($this->lng->txt('crs_objectives_info_'.$this->status));
		return true;
	}

	function __showObjectives()
	{
		include_once './Modules/Course/classes/class.ilCourseObjective.php';

		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->course_obj->getId())))
		{
			return false;
		}
		if($_GET['details'])
		{
			$_SESSION['crs_details_id'] = $_GET['details'];
		}
		$this->details_id = $_SESSION['crs_details_id'] ? $_SESSION['crs_details_id'] : $objective_ids[0];

		// TODO get status for table header
		switch($this->status)
		{
			case 'none':
				$status = $this->lng->txt('crs_objective_accomplished');
				break;

			case 'pretest':
			case 'pretest_non_suggest':
				$status = $this->lng->txt('crs_objective_pretest');
				break;

			default:
				$status = $this->lng->txt('crs_objective_result');
		}

		// show table
		$this->tpl->addBlockfile('OBJECTIVE_BLOCK','objective_block','tpl.crs_objectives_view_table.html','Modules/Course');

		$this->tpl->setVariable("TBL_TITLE_OBJECTIVES",$this->lng->txt('crs_objectives'));

		$this->__showHideLinks('objectives');

		if(isset($_SESSION['crs_hide_objectives']))
		{
			return true;
		}

		$this->tpl->setVariable('TBL_HEADER_NAME_OBJECTIVES_A',$this->lng->txt('type'));
		
		$this->tpl->setVariable('TBL_HEADER_NAME_OBJECTIVES_B',$this->lng->txt('description'));

		$this->tpl->setVariable('TBL_HEADER_NAME_OBJECTIVES_C',$status);


		//$max = count($objective_ids) % 2 ? count($objective_ids) + 1 : count($objective_ids); 
		$max = count($objective_ids); 
		for($i = 0; $i < $max; ++$i)
		{
			$tmp_objective =& new ilCourseObjective($this->course_obj,$objective_ids[$i]);

			$this->tpl->setCurrentBlock("objective_row");

			if($this->details_id == $objective_ids[$i])
			{
				$this->tpl->setVariable("OBJ_CLASS_1_OBJECTIVES",'option_value_details');
				$this->tpl->setVariable("OBJ_CLASS_1_CENTER_OBJECTIVES",'option_value_center_details');
			}
			else
			{
				$this->tpl->setVariable("OBJ_CLASS_1_OBJECTIVES",'option_value');
				$this->tpl->setVariable("OBJ_CLASS_1_CENTER_OBJECTIVES",'option_value_center');
			}				
			$this->tpl->setVariable("OBJ_NR_1_OBJECTIVES",($i + 1).'.');

			$this->ctrl->setParameter($this,'details',$objective_ids[$i]);
			$this->tpl->setVariable("OBJ_LINK_1_OBJECTIVES",$this->ctrl->getLinkTarget($this,'view'));
			$this->tpl->setVariable("OBJ_TITLE_1_OBJECTIVES",$tmp_objective->getTitle());

			$img = !in_array($objective_ids[$i],$this->suggested) ?
				ilUtil::getImagePath('icon_ok.gif') :
				ilUtil::getImagePath('icon_not_ok.gif');

			$txt = !in_array($objective_ids[$i],$this->suggested) ?
				$this->lng->txt('crs_objective_accomplished') :
				$this->lng->txt('crs_objective_not_accomplished');

			$this->tpl->setVariable("OBJ_STATUS_IMG_1_OBJECTIVES",$img);
			$this->tpl->setVariable("OBJ_STATUS_ALT_1_OBJECTIVES",$txt);


			if(isset($objective_ids[$i + $max / 2]))
			{
				$tmp_objective =& new ilCourseObjective($this->course_obj,$objective_ids[$i + $max / 2]);

				$this->tpl->setCurrentBlock("objective_row");
				if($this->details_id == $objective_ids[$i + $max / 2])
				{
					$this->tpl->setVariable("OBJ_CLASS_2_OBJECTIVES",'option_value_details');
					$this->tpl->setVariable("OBJ_CLASS_2_CENTER_OBJECTIVES",'option_value_center_details');
				}
				else
				{
					$this->tpl->setVariable("OBJ_CLASS_2_OBJECTIVES",'option_value');
					$this->tpl->setVariable("OBJ_CLASS_2_CENTER_OBJECTIVES",'option_value_center');
				}				
				$this->tpl->setVariable("OBJ_NR_2_OBJECTIVES",($i + $max / 2 + 1).'.');
				$this->ctrl->setParameter($this,'details',$objective_ids[$i + $max / 2]);
				$this->tpl->setVariable("OBJ_LINK_2_OBJECTIVES",$this->ctrl->getLinkTarget($this,'view'));
				$this->tpl->setVariable("OBJ_TITLE_2_OBJECTIVES",$tmp_objective->getTitle());


				$objective_id = $objective_ids[$i + $max / 2];

				$img = !in_array($objective_id,$this->suggested) ?
					ilUtil::getImagePath('icon_ok.gif') :
					ilUtil::getImagePath('icon_not_ok.gif');

				$txt = !in_array($objective_id,$this->suggested) ?
					$this->lng->txt('crs_objective_accomplished') :
					$this->lng->txt('crs_objective_not_accomplished');

				$this->tpl->setVariable("OBJ_STATUS_IMG_2_OBJECTIVES",$img);
				$this->tpl->setVariable("OBJ_STATUS_ALT_2_OBJECTIVES",$txt);
			}
	
			$this->tpl->parseCurrentBlock();
			unset($tmp_objective);
		}
		$this->ctrl->setParameter($this,'details','');
	}

	function __showLearningMaterials()
	{
		global $rbacsystem,$ilias,$ilUser,$ilObjDataCache;

		include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
		include_once './Services/Repository/classes/class.ilRepositoryExplorer.php';
		include_once './Modules/Course/classes/class.ilCourseLMHistory.php';

		if(!count($lms = ilCourseObjectiveMaterials::_getAllAssignedMaterials($this->course_obj->getId())))
		{
			return false;
		}
		if($this->details_id)
		{
			$objectives_lm_obj =& new ilCourseObjectiveMaterials($this->details_id);
		}

		$lm_continue =& new ilCourseLMHistory($this->course_obj->getRefId(),$ilUser->getId());
		$continue_data = $lm_continue->getLMHistory();

		$this->tpl->addBlockfile('LM_BLOCK','lm_block','tpl.crs_objectives_view_lm_table.html','Modules/Course');
		$this->tpl->setVariable("TBL_TITLE_LMS",$this->lng->txt('crs_learning_materials'));


		$this->__showHideLinks('lms');

		if(isset($_SESSION['crs_hide_lms']))
		{
			return true;
		}

		$this->tpl->setVariable("TBL_HEADER_LMS_TYPE",$this->lng->txt('type'));
		$this->tpl->setVariable("TBL_HEADER_NAME_LMS",$this->lng->txt('description'));

		$counter = 1;
		foreach($lms as $lm_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($lm_id);
			$obj_type = $ilObjDataCache->lookupType($obj_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($lm_id,$obj_id);
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($lm_id,$ilObjDataCache->lookupType($obj_id));
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($ilObjDataCache->lookupType($obj_id),$lm_id,$obj_id);
			$obj_frame = $obj_frame ? $obj_frame : '';
			$contentObj = false;

			if(ilRepositoryExplorer::isClickable($obj_type,$lm_id,$obj_id))
			{
				$this->tpl->setCurrentBlock("lm_read");
				$this->tpl->setVariable("READ_TITLE_LMS",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->setVariable("READ_TARGET_LMS",$obj_frame);
				$this->tpl->setVariable("READ_LINK_LMS", $obj_link);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("lm_visible");
				$this->tpl->setVariable("VISIBLE_LINK_LMS",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->parseCurrentBlock();
			}
			// add to desktop link
			if(!$ilUser->isDesktopItem($lm_id,$obj_type) and 
			   ($this->course_obj->getAboStatus() == $this->course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$lm_id))
				{
					$this->tpl->setCurrentBlock("lm_desklink");
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_ref_id',$lm_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_id',$lm_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'type',$obj_type);
					
					$this->tpl->setVariable("DESK_LINK_LMS",$this->ctrl->getLinkTarget($this->container_gui,'addToDesk'));
					$this->tpl->setVariable("TXT_DESK_LMS", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}

			// CONTINUE LINK
			if(isset($continue_data[$lm_id]))
			{
				$this->tpl->setCurrentBlock("lm_continuelink");
				$this->tpl->setVariable("CONTINUE_LINK_LMS",'ilias.php?baseClass=ilLMPresentationGUI&ref_id='.$lm_id.'&obj_id='.
										$continue_data[$lm_id]['lm_page_id']);

				$target = '';
				$this->tpl->setVariable("CONTINUE_LINK_TARGET",$obj_frame);
				$this->tpl->setVariable("TXT_CONTINUE_LMS",$this->lng->txt('continue_work'));
				$this->tpl->parseCurrentBlock();
			}

			// Description
			if(strlen($ilObjDataCache->lookupDescription($obj_id)))
			{
				$this->tpl->setCurrentBlock("lms_description");
				$this->tpl->setVariable("DESCRIPTION_LMS",$ilObjDataCache->lookupDescription($obj_id));
				$this->tpl->parseCurrentBlock();
			}
			// LAST ACCESS
			if(isset($continue_data["$lm_id"]))
			{
				$this->tpl->setVariable("TEXT_INFO_LMS",$this->lng->txt('last_access'));
				$this->tpl->setVariable('INFO_LMS',ilDatePresentation::formatDate(new ilDateTime($continue_data[$lm_id]['last_access'],IL_CAL_UNIX)));
				
			}
			elseif($obj_type == 'lm')
			{
				$this->tpl->setVariable("INFO_LMS",$this->lng->txt('not_accessed'));
			}
			
			if($this->details_id)
			{
				$objectives_lm_obj->setLMRefId($lm_id);
				if($objectives_lm_obj->checkExists())
				{
					$objectives_lm_obj =& new ilCourseObjectiveMaterials($this->details_id);
					
					if($conditions_ok)
					{
						foreach($objectives_lm_obj->getChapters() as $lm_obj_data)
						{
							if($lm_obj_data['ref_id'] != $lm_id)
							{
								continue;
							}

							include_once './Modules/LearningModule/classes/class.ilLMObject.php';
							
						
							$this->tpl->setCurrentBlock("chapters");
							
							if($lm_obj_data['type'] == 'st')
							{
								$this->tpl->setVariable("TXT_CHAPTER",$this->lng->txt('chapter'));
							}
							else
							{
								$this->tpl->setVariable("TXT_CHAPTER",$this->lng->txt('page'));																
							}
							$this->tpl->setVariable("CHAPTER_LINK_LMS","ilias.php?baseClass=ilLMPresentationGUI&ref_id=".
													$lm_obj_data['ref_id'].
													'&obj_id='.$lm_obj_data['obj_id']);
							$this->tpl->setVariable("CHAPTER_LINK_TARGET_LMS",$obj_frame);
							$this->tpl->setVariable("CHAPTER_TITLE",ilLMObject::_lookupTitle($lm_obj_data['obj_id']));
							$this->tpl->parseCurrentBlock();
						}
					}
					$this->tpl->setVariable("OBJ_CLASS_CENTER_LMS",'option_value_center_details');
					$this->tpl->setVariable("OBJ_CLASS_LMS",'option_value_details');
				}
				else
				{
					$this->tpl->setVariable("OBJ_CLASS_CENTER_LMS",'option_value_center');
					$this->tpl->setVariable("OBJ_CLASS_LMS",'option_value');
				}
			}
			else
			{
				$this->tpl->setVariable("OBJ_CLASS_CENTER_LMS",'option_value_center');
				$this->tpl->setVariable("OBJ_CLASS_LMS",'option_value');
			}
			$this->tpl->setCurrentBlock("lm_row");
			$this->tpl->setVariable('IMG_TYPE_MAT',ilUtil::getImagePath('icon_'.$obj_type.'.gif'));
			$this->tpl->setVariable('TXT_IMG_MAT',$this->lng->txt('obj_'.$obj_type));
			$this->tpl->setVariable("OBJ_NR_LMS",$counter.'.');
			$this->tpl->parseCurrentBlock();

			++$counter;
		}
	}

	function __showTests()
	{
		global $ilias,$rbacsystem,$ilObjDataCache,$ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';

		if(!count($tests = $this->__getAllTests()))
		{
			return false;
		}

		$this->tpl->addBlockfile('TEST_BLOCK','test_block','tpl.crs_objectives_view_tst_table.html','Modules/Course');
		$this->tpl->setVariable("TBL_TITLE_TST",$this->lng->txt('tests'));


		$this->__showHideLinks('tst');

		if(isset($_SESSION['crs_hide_tst']))
		{
			return true;
		}

		$this->tpl->setVariable("TBL_HEADER_WIDTH_TST_1","75%");
		$this->tpl->setVariable("TBL_HEADER_NAME_TST_1",$this->lng->txt('description'));

		$this->tpl->setVariable("TBL_HEADER_WIDTH_TST","5%");
		$this->tpl->setVariable("TBL_HEADER_NAME_TST",$this->lng->txt('crs_nr'));

		$this->tpl->setVariable("TBL_HEADER_WIDTH_TST_2","20%");
		$this->tpl->setVariable("TBL_HEADER_NAME_TST_2",'');
		$counter = 1;
		foreach($tests as $tst_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($tst_id);
			$obj_type = $ilObjDataCache->lookupType($obj_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($tst_id,$obj_id);
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($tst_id,$obj_type);
			$obj_link = "ilias.php?baseClass=ilObjTestGUI&ref_id=".$tst_id."&cmd=infoScreen";
			$obj_frame = '';

			if(ilRepositoryExplorer::isClickable($obj_type,$tst_id,$obj_id))
			{
				$this->tpl->setCurrentBlock("tst_read");
				$this->tpl->setVariable("READ_TITLE_TST",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->setVariable("READ_TARGET_TST",$obj_frame);
				$this->tpl->setVariable("READ_LINK_TST", $obj_link.'&crs_show_result='.$this->course_obj->getRefId());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("tst_visible");
				$this->tpl->setVariable("VISIBLE_LINK_TST",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->parseCurrentBlock();
			}
				// add to desktop link
			if(!$ilUser->isDesktopItem($tst_id,$obj_type) and 
			   ($this->course_obj->getAboStatus() == $this->course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$tst_id))
				{
					$this->tpl->setCurrentBlock("tst_desklink");
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_ref_id',$tst_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_id',$tst_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'type',$obj_type);
					$this->tpl->setVariable("DESK_LINK_TST",$this->ctrl->getLinkTarget($this->container_gui,'addToDesk'));

					$this->tpl->setVariable("TXT_DESK_TST", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setCurrentBlock("tst_row");
			$this->tpl->setVariable("OBJ_TITLE_TST",$ilObjDataCache->lookupTitle($obj_id));
			$this->tpl->setVariable("OBJ_NR_TST",$counter.'.');

			// Check if test is assigned to objective
			include_once('Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
			if($this->details_id and ilCourseObjectiveQuestion::_isTestAssignedToObjective($tst_id,$this->details_id))
			{
				$this->tpl->setVariable("OBJ_CLASS_CENTER_TST",'option_value_center_details');
				$this->tpl->setVariable("OBJ_CLASS_TST",'option_value_details');
			}
			else
			{
				$this->tpl->setVariable("OBJ_CLASS_CENTER_TST",'option_value_center');
				$this->tpl->setVariable("OBJ_CLASS_TST",'option_value');
			}
			$this->tpl->parseCurrentBlock();
			++$counter;
		}
	}
	
	function __showOtherResources()
	{
		global $ilias,$rbacsystem,$ilObjDataCache,$objDefinition;

		if(!count($ors = $this->__getOtherResources()))
		{
			return false;
		}

		$this->tpl->addBlockfile('RESOURCES_BLOCK','resources_block','tpl.crs_objectives_view_or_table.html','Modules/Course');
		$this->tpl->setVariable("TBL_TITLE_OR",$this->lng->txt('crs_other_resources'));


		$this->__showHideLinks('or');

		if(isset($_SESSION['crs_hide_or']))
		{
			return true;
		}

		$this->tpl->setCurrentBlock("tbl_header_columns_or");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_OR","5%");
		$this->tpl->setVariable("TBL_HEADER_NAME_OR",$this->lng->txt('type'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_header_columns_or");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_OR","75%");
		$this->tpl->setVariable("TBL_HEADER_NAME_OR",$this->lng->txt('description'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_header_columns_or");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_OR","20%");
		$this->tpl->setVariable("TBL_HEADER_NAME_OR",'');
		$this->tpl->parseCurrentBlock();

		$counter = 1;
		foreach($ors as $or_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($or_id);
			$obj_type = $ilObjDataCache->lookupType($obj_id);

			// do not show side block items
			if ($objDefinition->isSideBlock($obj_type))
			{
				continue;
			}
			
			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($or_id,$obj_id);
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($or_id,$obj_type);
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($obj_type,$or_id,$obj_id);
			$obj_frame = $obj_frame ? $obj_frame : '';

			if(ilRepositoryExplorer::isClickable($obj_type,$or_id,$obj_id))
			{
				$this->tpl->setCurrentBlock("or_read");
				$this->tpl->setVariable("READ_TITLE_OR",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->setVariable("READ_TARGET_OR",$obj_frame);
				$this->tpl->setVariable("READ_LINK_OR", $obj_link);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("or_visible");
				$this->tpl->setVariable("VISIBLE_LINK_OR",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->parseCurrentBlock();
			}
			// add to desktop link
			if(!$ilias->account->isDesktopItem($or_id,$obj_type) and 
			   ($this->course_obj->getAboStatus() == $this->course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$or_id))
				{
					$this->tpl->setCurrentBlock("or_desklink");
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_ref_id',$or_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'item_id',$or_id);
					$this->ctrl->setParameterByClass(get_class($this->container_gui),'type',$obj_type);
					
					$this->tpl->setVariable("DESK_LINK_OR",$this->ctrl->getLinkTarget($this->container_gui,'addToDesk'));

					$this->tpl->setVariable("TXT_DESK_OR", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setCurrentBlock("or_row");
			$this->tpl->setVariable("OBJ_TITLE_OR",$ilObjDataCache->lookupTitle($obj_id));
			$this->tpl->setVariable('IMG_TYPE_OR',ilUtil::getTypeIconPath($obj_type,$obj_id,'small'));
			$this->tpl->setVariable("TXT_IMG_OR",$this->lng->txt('obj_'.$obj_type));
			$this->tpl->setVariable("OBJ_CLASS_CENTER_OR",'option_value_center');
			$this->tpl->setVariable("OBJ_CLASS_OR",'option_value');
			$this->tpl->parseCurrentBlock();

			++$counter;
		}
	}


	function __getAllTests()
	{
		foreach($items = $this->course_obj->items_obj->getItems() as $node)
		{
			switch($node['type'])
			{
				case 'tst':
					$tests[] = $node['ref_id'];
					break;
			}
		}
		return $tests ? $tests : array();
	}

	function __getOtherResources()
	{
		include_once('Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
		$assigned = ilCourseObjectiveMaterials::_getAllAssignedMaterials($this->course_obj->getId());
		
		foreach($items = $this->course_obj->items_obj->getItems() as $node)
		{
			if(in_array($node['ref_id'],$assigned))
			{
				continue;
			}
			if($node['type'] == 'tst')
			{
				continue;
			}			
			$all_lms[] = $node['ref_id'];
		}
		return $all_lms ? $all_lms : array();
	}


	function __showHideLinks($a_part)
	{
		if($_GET['show_hide_'.$a_part] == 1)
		{
			unset($_SESSION['crs_hide_'.$a_part]);
		}
		if($_GET['show_hide_'.$a_part] == 2)
		{
			$_SESSION['crs_hide_'.$a_part] = true;
		}

		$this->ctrl->setParameter($this,'show_hide_'.$a_part,$_SESSION['crs_hide_'.$a_part] ? 1 : 2);
		$this->tpl->setVariable("LINK_HIDE_SHOW_".strtoupper($a_part),$this->ctrl->getLinkTarget($this,'view'));
		$this->tpl->setVariable("TXT_HIDE_SHOW_".strtoupper($a_part),$_SESSION['crs_hide_'.$a_part] ? 
								$this->lng->txt('crs_show_link_'.$a_part) :
								$this->lng->txt('crs_hide_link_'.$a_part));

		$this->ctrl->setParameter($this,'show_hide_'.$a_part,'');

		$this->tpl->setVariable("HIDE_SHOW_IMG_".strtoupper($a_part),$_SESSION['crs_hide_'.$a_part] ? 
								ilUtil::getImagePath('a_down.gif') :
								ilUtil::getImagePath('a_up.gif'));

		return true;
	}



	function __initCourseObject()
	{
		global $tree;

		if($this->container_obj->getType() == 'crs')
		{
			// Container is course
			$this->course_obj =& $this->container_obj;
		}
		else
		{
			$course_ref_id = $tree->checkForParentType($this->container_obj->getRefId(),'crs');
			$this->course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id);
		}
		$this->course_obj->initCourseItemObject();
		return true;
	}
	
} // END class.ilCourseObjectivePresentationGUI
?>
