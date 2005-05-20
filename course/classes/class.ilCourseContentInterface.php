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
* class ilobjcourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* This class is aggregated in folders, groups which have a parent course object
* Since it is something like an interface, all varirables, methods have there own name space (names start with cci) to avoid collisions
*
* @ilCtrl_Calls ilCourseContentInterface: ilConditionHandlerInterface

* @extends Object
* @package ilias-core
*/

class ilCourseContentInterface
{
	var $cci_course_obj;
	var $cci_course_id;
	var $cci_ref_id;
	var $cci_client_class;

	var $chi_obj;
	

	function ilCourseContentInterface(&$client_class,$a_ref_id)
	{
		global $lng,$tpl,$ilCtrl,$tree,$ilUser;

		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->tree =& $tree;
		$this->ilUser =& $ilUser;

		$this->cci_ref_id = $a_ref_id;
		$this->cci_read();
		$this->cci_client_class = strtolower(get_class($client_class));

		$this->cci_client_obj =& $client_class;
		$this->cci_course_obj =& ilObjectFactory::getInstanceByRefId($this->cci_course_id);
		$this->cci_course_obj->initCourseItemObject($this->cci_ref_id);

		$this->lng->loadLanguageModule('crs');
		
		return true;
	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();


		switch($next_class)
		{

			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				$new_gui =& new ilConditionHandlerInterface($this,$_GET['item_id']);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
				$new_gui->setBackButtons(array('edit' => $this->ctrl->getLinkTarget($this,'cciEdit'),
											   'preconditions' => $this->ctrl->getLinkTargetByClass('ilconditionhandlerinterface',
																									'listConditions')));
				$this->ctrl->forwardCommand($new_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$this->$cmd();
					
				break;
		}
		return true;
	}

	function cci_init(&$client_class,$a_ref_id)
	{
		$this->cci_ref_id = $a_ref_id;
		$this->cci_read();
		$this->cci_client_class = strtolower(get_class($client_class));

		$this->cci_course_obj =& ilObjectFactory::getInstanceByRefId($this->cci_course_id);
		$this->cci_course_obj->initCourseItemObject($this->cci_ref_id);

		$this->lng->loadLanguageModule('crs');
		
		return true;
	}
	
	function cci_objectives_ask_reset()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_ask_reset.html","course");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this->cci_client_obj));
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_reset_sure'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_RESET",$this->lng->txt('reset'));

		return true;
	}

	function cci_view()
	{
		include_once "./classes/class.ilRepositoryExplorer.php";
		include_once "./payment/classes/class.ilPaymentObject.php";
		include_once './course/classes/class.ilCourseStart.php';

		global $rbacsystem;
		global $ilias;
		global $ilUser;

		$write_perm = $rbacsystem->checkAccess("write",$this->cci_ref_id);
		$enabled_objectives = $this->cci_course_obj->enabledObjectiveView();
		$view_objectives = ($enabled_objectives and ($this->cci_ref_id == $this->cci_course_obj->getRefId()));

		// Jump to start objects if there is one
		$start_obj =& new ilCourseStart($this->cci_course_obj->getRefId(),$this->cci_course_obj->getId());
		if(count($this->starter = $start_obj->getStartObjects()) and 
		   !$start_obj->isFullfilled($ilUser->getId()) and 
		   !$write_perm)
		{
			$this->cci_start_objects();

			return true;
		}


		// Jump to objective view if selected or user is only member
		if(($view_objectives and !$write_perm) or ($_SESSION['crs_viewmode'] == 'objectives' and $write_perm))
		{
			$this->cci_objectives();

			return true;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_view.html","course");


		$write_perm = $rbacsystem->checkAccess("write",$this->cci_ref_id);
		if($write_perm)
		{
			$items = $this->cci_course_obj->items_obj->getAllItems();
		}
		else
		{
			$items = $this->cci_course_obj->items_obj->getItems();
		}
		
		// NO ITEMS FOUND
		if(!count($items))
		{	
			sendInfo($this->lng->txt("crs_no_items_found"));

			return true;
		}

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$maxcount = count($items);

		#$cont_arr = array_slice($items, $_GET["offset"], $_GET["limit"]);
		// no limit
		$cont_arr = $items;

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_content_row.html","course");
		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($cont_data['obj_id']);
				
				#if ($rbacsystem->checkAccess('read',$cont_data["ref_id"]) and 
				#	($conditions_ok or $rbacsystem->checkAccess('write',$cont_data['ref_id'])))
				$obj_link = ilRepositoryExplorer::buildLinkTarget($cont_data["child"],$cont_data["type"]);
				$obj_frame = ilRepositoryExplorer::buildFrameTarget($cont_data["type"],$cont_data["child"],$cont_data["obj_id"]);
				$contentObj = false;
				if (strcmp($cont_data["type"], "lm") == 0)
				{
					require_once("./content/classes/class.ilObjContentObject.php");
					$contentObj = new ilObjContentObject($cont_data["ref_id"]);
					$contentObj->readProperties();
				}
				if(ilRepositoryExplorer::isClickable($cont_data['type'],$cont_data['ref_id'],$cont_data['obj_id'])
					&& $obj_link != "")	
				{
					$tpl->setCurrentBlock("crs_read");
					$tpl->setVariable("READ_TITLE", $cont_data["title"]);
					$tpl->setVariable("READ_LINK", $obj_link);
					if (strcmp($cont_data["type"], "lm") == 0)
					{
						if ($rbacsystem->checkAccess('write',$cont_data["ref_id"]) && !$contentObj->getOnline())
						{
							$tpl->setVariable("R_CLASS", " class=\"offline\"");
						}
					}
					if ($obj_frame == "")
					{
						$tpl->setVariable("READ_TARGET", "");
					}
					else
					{
						$tpl->setVariable("READ_TARGET", $obj_frame);
					}
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("crs_visible");
					$tpl->setVariable("VIEW_TITLE", $cont_data["title"]);
					$tpl->parseCurrentBlock();
				}
				if($cont_data["type"] == "file" and $rbacsystem->checkAccess('read',$cont_data['ref_id']))
				{
					$this->cci_client_obj->ctrl->setParameterByClass('ilObjFileGUI','cmd','sendFile');
					$this->cci_client_obj->ctrl->setParameterByClass('ilObjFileGUI','ref_id',$cont_data['ref_id']);
				}

				if(!$conditions_ok)
				{
					foreach(ilConditionHandler::_getConditionsOfTarget($cont_data['obj_id']) as $condition)
					{
						if(ilConditionHandler::_checkCondition($condition['id']))
						{
							continue;
						}
						$trigger_obj =& ilObjectFactory::getInstanceByRefId($condition['trigger_ref_id']);

						if(ilRepositoryExplorer::isClickable($trigger_obj->getType(),$trigger_obj->getRefId(),$trigger_obj->getId()))
						{
							$tpl->setCurrentBlock("link");
							$tpl->setVariable("PRECONDITION_LINK",
											  ilRepositoryExplorer::buildLinkTarget($trigger_obj->getRefId(),$trigger_obj->getType()));
							$tpl->setVariable("PRECONDITION_NAME",$trigger_obj->getTitle());
							$tpl->parseCurrentBlock();
						}
						else
						{
							$tpl->setCurrentBlock("no_link");
							$tpl->setVariable("PRECONDITION_NO_TITLE",$trigger_obj->getTitle());
							$tpl->parseCurrentBlock();
						}
					}
					$tpl->setCurrentBlock("crs_preconditions");
					$tpl->setVariable("TXT_PRECONDITIONS",$this->lng->txt('condition_precondition'));
					$tpl->parseCurrentBlock();
				}

				if($rbacsystem->checkAccess('write',$cont_data['ref_id']))
				{
					if($obj_link = ilRepositoryExplorer::buildEditLinkTarget($cont_data["child"],$cont_data["type"]))
					{
						$tpl->setCurrentBlock("crs_edit");
						$tpl->setVariable("EDIT_LINK", $obj_link);
						$tpl->setVariable("TXT_EDIT",$this->lng->txt('edit'));
						$tpl->parseCurrentBlock();
					}
				}
				if($rbacsystem->checkAccess('delete',$cont_data['ref_id']))
				{
					$tpl->setCurrentBlock("crs_delete");

					$this->cci_client_obj->ctrl->setParameterByClass("ilRepositoryGUI","ref_id",$cont_data["ref_id"]);
						
					$tpl->setVariable("DELETE_LINK",$this->cci_client_obj->ctrl->getLinkTargetByClass("ilRepositoryGUI","delete"));
					$tpl->setVariable("TXT_DELETE",$this->lng->txt('delete'));
					$tpl->parseCurrentBlock();
				}

				// add evaluation tool link
				if (strcmp($cont_data["type"], "svy") == 0)
				{
					require_once("./survey/classes/class.ilObjSurvey.php");
					$this->lng->loadLanguageModule("survey");
					$svy_data =& ilObjSurvey::_getGlobalSurveyData($cont_data["obj_id"]);
					if (($rbacsystem->checkAccess('write',$cont_data["ref_id"]) 
						 and $svy_data["complete"]) or ($svy_data["evaluation_access"] 
														and $svy_data["complete"]))
					{
						$tpl->setCurrentBlock("svy_evaluation");
						$tpl->setVariable("EVALUATION_LINK", "survey/survey.php?cmd=evaluation&ref_id=".$cont_data["ref_id"]);
						$tpl->setVariable("TXT_EVALUATION", $this->lng->txt("evaluation"));
						$tpl->parseCurrentBlock();
					}
				}

				// add test evaluation links
				if (strcmp($cont_data["type"], "tst") == 0)
				{
					require_once("./assessment/classes/class.ilObjTest.php");
					$this->lng->loadLanguageModule("assessment");
					$complete = ilObjTest::_isComplete($cont_data["obj_id"]);
					// add anonymous aggregated test results link
					if ($rbacsystem->checkAccess('write',$cont_data["ref_id"]) and ($complete))
					{
						$tpl->setCurrentBlock("tst_anon_eval");
						$tpl->setVariable("ANON_EVAL_LINK", "assessment/test.php?cmd=eval_a&ref_id=".$cont_data["ref_id"]);
						$tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
						$tpl->parseCurrentBlock();
					}
	
					// add statistical evaluation tool
					if ($rbacsystem->checkAccess('write',$cont_data["ref_id"]) and ($complete))
					{
						$tpl->setCurrentBlock("tst_statistical_evaluation");
						$tpl->setVariable("STATISTICAL_EVALUATION_LINK", "assessment/test.php?cmd=eval_stat&ref_id=".$cont_data["ref_id"]);
						$tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
						$tpl->parseCurrentBlock();
					}
				}

				// add to desktop link
				if ($ilias->account->getId() != ANONYMOUS_USER_ID and 
					!$ilias->account->isDesktopItem($cont_data['ref_id'], $cont_data["type"]) and
					$this->cci_course_obj->getAboStatus() == $this->cci_course_obj->ABO_ENABLED)
				{
					if ($rbacsystem->checkAccess('read', $cont_data['ref_id']))
					{
						$tpl->setCurrentBlock("crs_subscribe");
						$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDeskCourse&ref_id=".$this->cci_ref_id.
							"&item_ref_id=".$cont_data["ref_id"]."&type=".$cont_data["type"]);

						$tpl->setVariable("TXT_TO_DESK", $this->lng->txt("to_desktop"));
						$tpl->parseCurrentBlock();
					}
				}

				// OPTIONS
				if($write_perm)
				{
					if($this->cci_course_obj->getOrderType() == $this->cci_course_obj->SORT_MANUAL)
					{
						if($num != 0)
						{
							$tmp_array["gif"] = ilUtil::getImagePath("a_up.gif");
							$tmp_array["lng"] = $this->lng->txt("crs_move_up");
							$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"ref_id",
																	  $this->cci_client_obj->object->getRefId());
							$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"item_id",$cont_data["child"]);
							$tmp_array["lnk"] = $this->cci_client_obj->ctrl->getLinkTarget($this->cci_client_obj,"cciMove");
							$tmp_array["tar"] = "";

							$images[] = $tmp_array;
						}
						if($num != count($cont_arr) - 1)
						{
							$tmp_array["gif"] = ilUtil::getImagePath("a_down.gif");
							$tmp_array["lng"] = $this->lng->txt("crs_move_down");
							$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"ref_id",
																	  $this->cci_client_obj->object->getRefId());
							$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"item_id",-$cont_data["child"]);
							$tmp_array["lnk"] = $this->cci_client_obj->ctrl->getLinkTarget($this->cci_client_obj,"cciMove");
							$tmp_array["tar"] = "";

							$images[] = $tmp_array;
						}
					}
					$tmp_array["gif"] = ilUtil::getImagePath("edit.gif");
					$tmp_array["lng"] = $this->lng->txt("edit");
					$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"ref_id",$this->cci_client_obj->object->getRefId());
					$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"item_id",$cont_data["child"]);
					$tmp_array["lnk"] = $this->cci_client_obj->ctrl->getLinkTarget($this->cci_client_obj,"cciEdit");
					$tmp_array["tar"] = "";
					
					$images[] = $tmp_array;
					
					if ($rbacsystem->checkAccess('delete',$cont_data["ref_id"]))
					{
						$tmp_array["gif"] = ilUtil::getImagePath("delete.gif");
						$tmp_array["lng"] = $this->lng->txt("delete");
						$this->cci_client_obj->ctrl->setParameterByClass("ilRepositoryGUI","ref_id",$cont_data["child"]);
						$tmp_array["lnk"] = $this->cci_client_obj->ctrl->getLinkTargetByClass("ilRepositoryGUI","delete");
						$tmp_array["tar"] = "";

						$images[] = $tmp_array;
					}
					
					foreach($images as $key => $image)
					{
						$tpl->setCurrentBlock("img");
						$tpl->setVariable("IMG_TYPE",$image["gif"]);
						$tpl->setVariable("IMG_ALT",$image["lng"]);
						$tpl->setVariable("IMG_LINK",$image["lnk"]);
						$tpl->setVariable("IMG_TARGET",$image["tar"]);
						$tpl->parseCurrentBlock();
					}
					unset($images);

					$tpl->setCurrentBlock("options");
					$tpl->setVariable("OPT_ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				if($cont_data["type"] == "lm")
				{
					if ($rbacsystem->checkAccess('write',$cont_data["ref_id"]) && !$contentObj->getOnline())
					{
						$tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"]."_offline".".gif"));
						$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]) . " (" . $this->lng->txt("offline") . ")");
					}
					else
					{
						$tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"].".gif"));
						$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
					}
				}
				else
				{
					$tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"].".gif"));
					$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				}
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);

				// ACTIVATION
				$buyable = ilPaymentObject::_isBuyable($this->cci_ref_id);
				if (($rbacsystem->checkAccess('write',$this->cci_ref_id) ||
					 $buyable == false) &&
					$cont_data["activation_unlimited"])
				{
					$txt = $this->lng->txt("crs_unlimited");
				}
				else if ($buyable)
				{
					if (is_array($activation = ilPaymentObject::_getActivation($this->cci_ref_id)))
					{
						$txt = $this->lng->txt("crs_from")." ".strftime("%Y-%m-%d %R",$activation["activation_start"]).
							"<br>".$this->lng->txt("crs_to")." ".strftime("%Y-%m-%d %R",$activation["activation_end"]);
					}
					else
					{
						$txt = "N/A";
					}
				}
				else
				{
					$txt = $this->lng->txt("crs_from")." ".strftime("%Y-%m-%d %R",$cont_data["activation_start"]).
						"<br>".$this->lng->txt("crs_to")." ".strftime("%Y-%m-%d %R",$cont_data["activation_end"]);
				}
				$tpl->setVariable("ACTIVATION_END",$txt);

				$tpl->parseCurrentBlock();
				$num++;
			}
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("crs_content"),"icon_crs.gif",$this->lng->txt("courses"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		if($write_perm)
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),
									   $this->lng->txt("activation"),$this->lng->txt("options")));
			$tbl->setHeaderVars(array("type","title","activation","options"), 
								array("ref_id" => $this->cci_course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursegui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1%","69%","20%","10%"));
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),
									   $this->lng->txt("activation")));
			$tbl->setHeaderVars(array("type","title","activation","options"), 
								array("ref_id" => $this->cci_course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursegui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1%","89%","20%"));
		}

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("CONTENT_TABLE", $tpl->get());

		return true;
	}
	
	function cci_start_objects()
	{
		include_once './course/classes/class.ilCourseLMHistory.php';
		include_once './classes/class.ilRepositoryExplorer.php';

		global $rbacsystem,$ilias,$ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_start_view.html","course");
		#$this->__showButton('view',$this->lng->txt('refresh'));
		
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_info_start'));
		$this->tpl->setVariable("TBL_TITLE_START",$this->lng->txt('crs_table_start_objects'));
		$this->tpl->setVariable("HEADER_NR",$this->lng->txt('crs_nr'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_EDITED",$this->lng->txt('crs_objective_accomplished'));


		$lm_continue =& new ilCourseLMHistory($this->cci_ref_id,$ilUser->getId());
		$continue_data = $lm_continue->getLMHistory();

		$counter = 0;
		foreach($this->starter as $start)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($start['item_ref_id']);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($tmp_obj->getId());

			$obj_link = ilRepositoryExplorer::buildLinkTarget($tmp_obj->getRefId(),$tmp_obj->getType());
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($tmp_obj->getType(),$tmp_obj->getRefId(),$tmp_obj->getId());
			$obj_frame = $obj_frame ? $obj_frame : '';

			// Tmp fix for tests
			$obj_frame = $tmp_obj->getType() == 'tst' ? '' : $obj_frame;

			$contentObj = false;

			if(ilRepositoryExplorer::isClickable($tmp_obj->getType(),$tmp_obj->getRefId(),$tmp_obj->getId()))
			{
				$this->tpl->setCurrentBlock("start_read");
				$this->tpl->setVariable("READ_TITLE_START",$tmp_obj->getTitle());
				$this->tpl->setVariable("READ_TARGET_START",$obj_frame);
				$this->tpl->setVariable("READ_LINK_START", $obj_link.'&crs_show_result='.$this->cci_ref_id);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("start_visible");
				$this->tpl->setVariable("VISIBLE_LINK_START",$tmp_obj->getTitle());
				$this->tpl->parseCurrentBlock();
			}
			// add to desktop link
			if(!$ilias->account->isDesktopItem($tmp_obj->getRefId(),$tmp_obj->getType()) and 
			   ($this->cci_course_obj->getAboStatus() == $this->cci_course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$tmp_obj->getRefId()))
				{
					$this->tpl->setCurrentBlock("start_desklink");
					$this->tpl->setVariable("DESK_LINK_START", "repository.php?cmd=addToDeskCourse&ref_id=".$this->cci_ref_id.
											"&item_ref_id=".$tmp_obj->getRefId()."&type=".$tmp_obj->getType());

					$this->tpl->setVariable("TXT_DESK_START", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}

			// CONTINUE LINK
			if(isset($continue_data[$tmp_obj->getRefId()]))
			{
				$this->tpl->setCurrentBlock("start_continuelink");
				$this->tpl->setVariable("CONTINUE_LINK_START",'./content/lm_presentation.php?ref_id='.$tmp_obj->getRefId().'&obj_id='.
										$continue_data[$tmp_obj->getRefId()]['lm_page_id']);

				$target = $ilias->ini->readVariable("layout","view_target") == "frame" ? 
					'' :
					'ilContObj'.$cont_data[$obj_id]['obj_page_id'];
					
				$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
				$this->tpl->setVariable("TXT_CONTINUE_START",$this->lng->txt('continue_work'));
				$this->tpl->parseCurrentBlock();
			}

			// Description
			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("start_description");
				$this->tpl->setVariable("DESCRIPTION_START",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}

			switch($tmp_obj->getType())
			{
				case 'tst':
					include_once './assessment/classes/class.ilObjTest.php';
					$accomplished = ilObjTest::_checkCondition($tmp_obj->getId(),'finished','') ? 'accomplished' : 'not_accomplished';
					break;

				default:
					$accomplished = isset($continue_data[$tmp_obj->getRefId()]) ? 'accomplished' : 'not_accomplished';
					break;
			}
			$this->tpl->setCurrentBlock("start_row");
			$this->tpl->setVariable("EDITED_IMG",ilUtil::getImagePath('crs_'.$accomplished.'.gif'));
			$this->tpl->setVariable("EDITED_ALT",$this->lng->txt('crs_objective_'.$accomplished));
			$this->tpl->setVariable("ROW_CLASS",'option_value');
			$this->tpl->setVariable("ROW_CLASS_CENTER",'option_value_center');
			$this->tpl->setVariable("OBJ_NR_START",++$counter.'.');
			$this->tpl->parseCurrentBlock();
		}			
		return true;
	}

	function cci_objectives()
	{
		include_once "./course/classes/class.ilCourseStart.php";

		global $rbacsystem,$ilUser,$ilBench;

		$ilBench->start('Objectives','Objectives_view');

		// Jump to start objects if there is one

		$ilBench->start('Objectives','Objectives_start_objects');
		if(!$_SESSION['objectives_fullfilled'][$this->cci_course_obj->getId()])
		{
			$start_obj =& new ilCourseStart($this->cci_course_obj->getRefId(),$this->cci_course_obj->getId());
			if(count($this->starter = $start_obj->getStartObjects()) and !$start_obj->isFullfilled($ilUser->getId()))
			{
				$this->cci_start_objects();
				
				return true;
			}
			$_SESSION['objectives_fullfilled'][$this->cci_course_obj->getId()] = true;
		}
		$ilBench->stop('Objectives','Objectives_start_objects');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objective_view.html","course");
		$this->__showButton('cciObjectivesAskReset',$this->lng->txt('crs_reset_results'));

		$ilBench->start('Objectives','Objectives_read');

		$ilBench->start('Objectives','Objectives_read_accomplished');
		$this->__readAccomplished();
		$ilBench->stop('Objectives','Objectives_read_accomplished');

		$ilBench->start('Objectives','Objectives_read_suggested');
		$this->__readSuggested();
		$ilBench->stop('Objectives','Objectives_read_suggested');

		$ilBench->start('Objectives','Objectives_read_status');
		$this->__readStatus();
		$ilBench->stop('Objectives','Objectives_read_status');

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

	function cci_edit()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->cci_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!isset($_GET["item_id"]))
		{
			sendInfo($this->lng->txt("crs_no_item_id_given"));
			$this->cci_view();

			return false;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editItem.html","course");
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
	
		// display button

		$this->tpl->setCurrentBlock("btn_cell");

		$this->ctrl->setParameterByClass(strtolower(get_class($this->cci_client_obj)),'item_id',(int) $_GET['item_id']);
		$this->tpl->setVariable("BTN_LINK",
								$this->ctrl->getLinkTarget($this->cci_client_obj,'cciEdit'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('edit'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->ctrl->setParameterByClass('ilConditionHandlerInterface','item_id',(int) $_GET['item_id']);
		$this->tpl->setVariable("BTN_LINK",
								$this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('preconditions'));
		$this->tpl->parseCurrentBlock();
	
	
	
		$this->cci_client_obj->ctrl->setParameter($this->cci_client_obj,"item_id",$_GET["item_id"]);
		$this->tpl->setVariable("FORMACTION",$this->cci_client_obj->ctrl->getFormAction($this->cci_client_obj));

		$item_data = $this->cci_course_obj->items_obj->getItem((int) $_GET["item_id"]);

		$tmp_obj = ilObjectFactory::getInstanceByRefId($_GET["item_id"]);
		$title = $tmp_obj->getTitle();

		// LOAD SAVED DATA IN CASE OF ERROR
		$activation_unlimited = $_SESSION["error_post_vars"]["crs"]["activation_unlimited"] ? 
			1 : 
			(int) $item_data["activation_unlimited"];

		$activation_start = $_SESSION["error_post_vars"]["crs"]["activation_start"] ? 
			$this->cciToUnix($_SESSION["error_post_vars"]["crs"]["activation_start"]) :
			$item_data["activation_start"];
		
		$activation_end = $_SESSION["error_post_vars"]["crs"]["activation_end"] ? 
			$this->cciToUnix($_SESSION["error_post_vars"]["crs"]["activation_end"]) :
			$item_data["activation_end"];
		
		// SET TEXT VARIABLES
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_".$tmp_obj->getType()));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_".$tmp_obj->getType().".gif"));
		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("TXT_ACTIVATION",$this->lng->txt("activation"));
		$this->tpl->setVariable("TXT_ACTIVATION_UNLIMITED",$this->lng->txt("crs_unlimited"));
		$this->tpl->setVariable("TXT_ACTIVATION_START",$this->lng->txt("crs_start"));
		$this->tpl->setVariable("TXT_ACTIVATION_END",$this->lng->txt("crs_end"));
		$this->tpl->setVariable("CMD_SUBMIT","cciUpdate");
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("submit"));
		
		$this->tpl->setVariable("ACTIVATION_UNLIMITED",ilUtil::formCheckbox($activation_unlimited,"crs[activation_unlimited]",1));


		$this->tpl->setVariable("SELECT_ACTIVATION_START_DAY",$this->cciGetDateSelect("day","crs[activation_start][day]",
																					 date("d",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_MONTH",$this->cciGetDateSelect("month","crs[activation_start][month]",
																					   date("m",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_YEAR",$this->cciGetDateSelect("year","crs[activation_start][year]",
																					  date("Y",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_HOUR",$this->cciGetDateSelect("hour","crs[activation_start][hour]",
																					  date("G",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_MINUTE",$this->cciGetDateSelect("minute","crs[activation_start][minute]",
																					  date("i",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_DAY",$this->cciGetDateSelect("day","crs[activation_end][day]",
																				   date("d",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MONTH",$this->cciGetDateSelect("month","crs[activation_end][month]",
																					 date("m",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_YEAR",$this->cciGetDateSelect("year","crs[activation_end][year]",
																					date("Y",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_HOUR",$this->cciGetDateSelect("hour","crs[activation_end][hour]",
																					  date("G",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MINUTE",$this->cciGetDateSelect("minute","crs[activation_end][minute]",
																					  date("i",$activation_end)));
#		$this->cci_client_obj->initConditionHandlerGUI($_GET['item_id']);
#		$this->tpl->setVariable("PRECONDITION_TABLE",$this->cci_client_obj->chi_obj->chi_list());

	}

	function cci_update()
	{
		if(!isset($_GET["item_id"]))
		{
			echo "CourseContentInterface: No item_id given!";
			exit;
		}

		$this->cci_course_obj->items_obj->setActivationUnlimitedStatus((bool) $_POST["crs"]["activation_unlimited"]);
		$this->cci_course_obj->items_obj->setActivationStart($this->cciToUnix($_POST["crs"]["activation_start"]));
		$this->cci_course_obj->items_obj->setActivationEnd($this->cciToUnix($_POST["crs"]["activation_end"]));
		
		if(!$this->cci_course_obj->items_obj->validateActivation())
		{
			sendInfo($this->cci_course_obj->getMessage());
			$this->cci_edit();

			return true;
		}
		$this->cci_course_obj->items_obj->update((int) $_GET["item_id"]);
		$this->cci_view();

		return true;
	}
			
	function cci_move()
	{
		if($_GET["item_id"] > 0)
		{
			$this->cci_course_obj->items_obj->moveUp((int) $_GET["item_id"]);
		}
		else
		{
			$this->cci_course_obj->items_obj->moveDown((int) -$_GET["item_id"]);
		}
		sendInfo($this->lng->txt("crs_moved_item"));

		$this->cci_view();

		return true;
	}
	// PRIVATE
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

		$this->ctrl->setParameter($this->cci_client_obj,'show_hide_'.$a_part,$_SESSION['crs_hide_'.$a_part] ? 1 : 2);
		$this->tpl->setVariable("LINK_HIDE_SHOW_".strtoupper($a_part),$this->ctrl->getLinkTarget($this->cci_client_obj,'cciObjectives'));
		$this->tpl->setVariable("TXT_HIDE_SHOW_".strtoupper($a_part),$_SESSION['crs_hide_'.$a_part] ? 
								$this->lng->txt('crs_show_link_'.$a_part) :
								$this->lng->txt('crs_hide_link_'.$a_part));

		$this->ctrl->setParameter($this->cci_client_obj,'show_hide_'.$a_part,'');

		$this->tpl->setVariable("HIDE_SHOW_IMG_".strtoupper($a_part),$_SESSION['crs_hide_'.$a_part] ? 
								ilUtil::getImagePath('a_down.gif') :
								ilUtil::getImagePath('a_up.gif'));

		return true;
	}
	
	function __getAllLearningMaterials()
	{
		foreach($items = $this->cci_course_obj->items_obj->getItems() as $node)
		{
			switch($node['type'])
			{
				case 'lm':
				case 'htlm':
				case 'alm':
				case 'sahs':
					$all_lms[] = $node['ref_id'];
					break;
			}
		}
		return $all_lms ? $all_lms : array();
	}

	function __getAllTests()
	{
		foreach($items = $this->cci_course_obj->items_obj->getItems() as $node)
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
		foreach($items = $this->cci_course_obj->items_obj->getItems() as $node)
		{
			switch($node['type'])
			{
				case 'lm':
				case 'htlm':
				case 'sahs':
				case 'tst':
					continue;

				default:
					$all_lms[] = $node['ref_id'];
					break;
			}
		}
		return $all_lms ? $all_lms : array();
	}

	function __showInfo()
	{
		include_once './course/classes/class.ilCourseObjective.php';

		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->cci_course_obj->getId())))
		{
			return true;
		}

		$this->tpl->addBlockfile('INFO_BLOCK','info_block','tpl.crs_objectives_view_info_table.html','course');
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_info_'.$this->objective_status));
		
		return true;
	}
		

	function __showOtherResources()
	{
		global $ilias,$rbacsystem;

		if(!count($ors = $this->__getOtherResources()))
		{
			return false;
		}

		$this->tpl->addBlockfile('RESOURCES_BLOCK','resources_block','tpl.crs_objectives_view_or_table.html','course');
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
		$this->tpl->setVariable("TBL_HEADER_WIDTH_OR","95%");
		$this->tpl->setVariable("TBL_HEADER_NAME_OR",$this->lng->txt('description'));
		$this->tpl->parseCurrentBlock();

		$counter = 1;
		foreach($ors as $or_id)
		{

			$tmp_or = ilObjectFactory::getInstanceByRefId($or_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($tmp_or->getId());
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($tmp_or->getRefId(),$tmp_or->getType());
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($tmp_or->getType(),$tmp_or->getRefId(),$tmp_or->getId());
			$obj_frame = $obj_frame ? $obj_frame : '';

			if(ilRepositoryExplorer::isClickable($tmp_or->getType(),$tmp_or->getRefId(),$tmp_or->getId()))
			{
				$this->tpl->setCurrentBlock("or_read");
				$this->tpl->setVariable("READ_TITLE_OR",$tmp_or->getTitle());
				$this->tpl->setVariable("READ_TARGET_OR",$obj_frame);
				$this->tpl->setVariable("READ_LINK_OR", $obj_link);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("or_visible");
				$this->tpl->setVariable("VISIBLE_LINK_OR",$tmp_or->getTitle());
				$this->tpl->parseCurrentBlock();
			}
				// add to desktop link
			if(!$ilias->account->isDesktopItem($tmp_or->getRefId(),$tmp_or->getType()) and 
			   ($this->cci_course_obj->getAboStatus() == $this->cci_course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$tmp_or->getRefId()))
				{
					$this->tpl->setCurrentBlock("or_desklink");
					$this->tpl->setVariable("DESK_LINK_OR", "repository.php?cmd=addToDeskCourse&ref_id=".$this->cci_ref_id.
											"&item_ref_id=".$tmp_or->getRefId()."&type=".$tmp_or->getType());

					$this->tpl->setVariable("TXT_DESK_OR", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setCurrentBlock("or_row");
			$this->tpl->setVariable("OBJ_TITLE_OR",$tmp_or->getTitle());
			$this->tpl->setVariable("IMG_TYPE_OR",ilUtil::getImagePath('icon_'.$tmp_or->getType().'.gif'));
			$this->tpl->setVariable("TXT_IMG_OR",$this->lng->txt('obj_'.$tmp_or->getType()));
			$this->tpl->setVariable("OBJ_CLASS_CENTER_OR",'option_value_center');
			$this->tpl->setVariable("OBJ_CLASS_OR",'option_value');
			$this->tpl->parseCurrentBlock();

			unset($tmp_or);
			++$counter;
		}
	}


	function __showLearningMaterials()
	{
		global $rbacsystem,$ilias,$ilUser;

		include_once './course/classes/class.ilCourseObjectiveLM.php';
		include_once './classes/class.ilRepositoryExplorer.php';
		include_once './course/classes/class.ilCourseLMHistory.php';

		if(!count($lms = $this->__getAllLearningMaterials()))
		{
			return false;
		}
		if($this->details_id)
		{
			$objectives_lm_obj =& new ilCourseObjectiveLM($this->details_id);
		}

		$lm_continue =& new ilCourseLMHistory($this->cci_ref_id,$ilUser->getId());
		$continue_data = $lm_continue->getLMHistory();

		$this->tpl->addBlockfile('LM_BLOCK','lm_block','tpl.crs_objectives_view_lm_table.html','course');
		$this->tpl->setVariable("TBL_TITLE_LMS",$this->lng->txt('crs_learning_materials'));


		$this->__showHideLinks('lms');

		if(isset($_SESSION['crs_hide_lms']))
		{
			return true;
		}

		$this->tpl->setCurrentBlock("tbl_header_columns_lms");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_LMS","5%");
		$this->tpl->setVariable("TBL_HEADER_NAME_LMS",$this->lng->txt('crs_nr'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_header_columns_lms");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_LMS","95%");
		$this->tpl->setVariable("TBL_HEADER_NAME_LMS",$this->lng->txt('description'));
		$this->tpl->parseCurrentBlock();

		$counter = 1;
		foreach($lms as $lm_id)
		{
			$tmp_lm = ilObjectFactory::getInstanceByRefId($lm_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($tmp_lm->getId());
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($tmp_lm->getRefId(),$tmp_lm->getType());
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($tmp_lm->getType(),$tmp_lm->getRefId(),$tmp_lm->getId());
			$obj_frame = $obj_frame ? $obj_frame : '';
			$contentObj = false;

			if(ilRepositoryExplorer::isClickable($tmp_lm->getType(),$tmp_lm->getRefId(),$tmp_lm->getId()))
			{
				$this->tpl->setCurrentBlock("lm_read");
				$this->tpl->setVariable("READ_TITLE_LMS",$tmp_lm->getTitle());
				$this->tpl->setVariable("READ_TARGET_LMS",$obj_frame);
				$this->tpl->setVariable("READ_LINK_LMS", $obj_link);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("lm_visible");
				$this->tpl->setVariable("VISIBLE_LINK_LMS",$tmp_lm->getTitle());
				$this->tpl->parseCurrentBlock();
			}
			// add to desktop link
			if(!$ilias->account->isDesktopItem($tmp_lm->getRefId(),$tmp_lm->getType()) and 
			   ($this->cci_course_obj->getAboStatus() == $this->cci_course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$tmp_lm->getRefId()))
				{
					$this->tpl->setCurrentBlock("lm_desklink");
					$this->tpl->setVariable("DESK_LINK_LMS", "repository.php?cmd=addToDeskCourse&ref_id=".$this->cci_ref_id.
											"&item_ref_id=".$tmp_lm->getRefId()."&type=".$tmp_lm->getType());

					$this->tpl->setVariable("TXT_DESK_LMS", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}

			// CONTINUE LINK
			if(isset($continue_data["$lm_id"]))
			{
				$this->tpl->setCurrentBlock("lm_continuelink");
				$this->tpl->setVariable("CONTINUE_LINK_LMS",'./content/lm_presentation.php?ref_id='.$lm_id.'&obj_id='.
										$cont_data[$lm_id]['lm_page_id']);

				$target = $ilias->ini->readVariable("layout","view_target") == "frame" ? 
					'' :
					'ilContObj'.$cont_data[$lm_id]['lm_page_id'];
					
				$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
				$this->tpl->setVariable("TXT_CONTINUE_LMS",$this->lng->txt('continue_work'));
				$this->tpl->parseCurrentBlock();
			}

			// Description
			if(strlen($tmp_lm->getDescription()))
			{
				$this->tpl->setCurrentBlock("lms_description");
				$this->tpl->setVariable("DESCRIPTION_LMS",$tmp_lm->getDescription());
				$this->tpl->parseCurrentBlock();
			}
			// LAST ACCESS
			if(isset($continue_data["$lm_id"]))
			{
				$this->tpl->setVariable("TEXT_INFO_LMS",$this->lng->txt('last_access'));
				$this->tpl->setVariable("INFO_LMS",date('Y-m-d H:i:s',$continue_data["$lm_id"]['last_access']));
			}
			else
			{
				$this->tpl->setVariable("INFO_LMS",$this->lng->txt('not_accessed'));
			}
			

			#if($this->details_id and !$this->accomplished[$this->details_id] and $this->suggested[$this->details_id])
			if($this->details_id)
			{
				$objectives_lm_obj->setLMRefId($tmp_lm->getRefId());
				#$objectives_lm_obj->setLMObjId($tmp_lm->getId());
				if($objectives_lm_obj->checkExists())
				{
					$objectives_lm_obj =& new ilCourseObjectiveLM($this->details_id);

					if($conditions_ok)
					{
						foreach($objectives_lm_obj->getChapters() as $lm_obj_data)
						{
							if($lm_obj_data['ref_id'] != $lm_id)
							{
								continue;
							}

							include_once './content/classes/class.ilLMObjectFactory.php';
							
							$st_obj = ilLMObjectFactory::getInstance($tmp_lm,$lm_obj_data['obj_id']);
							
							$this->tpl->setCurrentBlock("chapters");
							$this->tpl->setVariable("TXT_CHAPTER",$this->lng->txt('chapter'));
							$this->tpl->setVariable("CHAPTER_LINK_LMS","content/lm_presentation.php?ref_id=".$lm_obj_data['ref_id'].
													'&obj_id='.$lm_obj_data['obj_id']);
							$this->tpl->setVariable("CHAPTER_LINK_TARGET_LMS",$obj_frame);
							$this->tpl->setVariable("CHAPTER_TITLE",$st_obj->getTitle());
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
			$this->tpl->setVariable("OBJ_NR_LMS",$counter.'.');
			$this->tpl->parseCurrentBlock();

			++$counter;
		}
	}

	function __showTests()
	{
		global $ilias,$rbacsystem;

		include_once './course/classes/class.ilCourseObjectiveLM.php';

		if(!count($tests = $this->__getAllTests()))
		{
			return false;
		}

		$this->tpl->addBlockfile('TEST_BLOCK','test_block','tpl.crs_objectives_view_tst_table.html','course');
		$this->tpl->setVariable("TBL_TITLE_TST",$this->lng->txt('tests'));


		$this->__showHideLinks('tst');

		if(isset($_SESSION['crs_hide_tst']))
		{
			return true;
		}

		$this->tpl->setCurrentBlock("tbl_header_columns_tst");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_TST","5%");
		$this->tpl->setVariable("TBL_HEADER_NAME_TST",$this->lng->txt('crs_nr'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_header_columns_tst");
		$this->tpl->setVariable("TBL_HEADER_WIDTH_TST","95%");
		$this->tpl->setVariable("TBL_HEADER_NAME_TST",$this->lng->txt('description'));
		$this->tpl->parseCurrentBlock();

		$counter = 1;
		foreach($tests as $tst_id)
		{

			$tmp_tst = ilObjectFactory::getInstanceByRefId($tst_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($tmp_tst->getId());
				
			$obj_link = ilRepositoryExplorer::buildLinkTarget($tmp_tst->getRefId(),$tmp_tst->getType());

			#$obj_frame = ilRepositoryExplorer::buildFrameTarget($tmp_tst->getType(),$tmp_tst->getRefId(),$tmp_tst->getId());
			#$obj_frame = $obj_frame ? $obj_frame : 'bottom';
			// Always open in frameset
			$obj_frame = '';

			if(ilRepositoryExplorer::isClickable($tmp_tst->getType(),$tmp_tst->getRefId(),$tmp_tst->getId()))
			{
				$this->tpl->setCurrentBlock("tst_read");
				$this->tpl->setVariable("READ_TITLE_TST",$tmp_tst->getTitle());
				$this->tpl->setVariable("READ_TARGET_TST",$obj_frame);
				$this->tpl->setVariable("READ_LINK_TST", $obj_link.'&crs_show_result='.$this->cci_ref_id);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("tst_visible");
				$this->tpl->setVariable("VISIBLE_LINK_TST",$tmp_tst->getTitle());
				$this->tpl->parseCurrentBlock();
			}
				// add to desktop link
			if(!$ilias->account->isDesktopItem($tmp_tst->getRefId(),$tmp_tst->getType()) and 
			   ($this->cci_course_obj->getAboStatus() == $this->cci_course_obj->ABO_ENABLED))
			{
				if ($rbacsystem->checkAccess('read',$tmp_tst->getRefId()))
				{
					$this->tpl->setCurrentBlock("tst_desklink");
					$this->tpl->setVariable("DESK_LINK_TST", "repository.php?cmd=addToDeskCourse&ref_id=".$this->cci_ref_id.
											"&item_ref_id=".$tmp_tst->getRefId()."&type=".$tmp_tst->getType());

					$this->tpl->setVariable("TXT_DESK_TST", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}




			$tmp_tst = ilObjectFactory::getInstanceByRefId($tst_id);
			
			$this->tpl->setCurrentBlock("tst_row");
			$this->tpl->setVariable("OBJ_TITLE_TST",$tmp_tst->getTitle());
			$this->tpl->setVariable("OBJ_NR_TST",$counter.'.');

			$this->tpl->setVariable("OBJ_CLASS_CENTER_TST",'option_value_center');
			$this->tpl->setVariable("OBJ_CLASS_TST",'option_value');
			$this->tpl->parseCurrentBlock();

			unset($tmp_tst);
			++$counter;
		}
	}

	function __showObjectives()
	{
		include_once './course/classes/class.ilCourseObjective.php';

		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->cci_course_obj->getId())))
		{
			return false;
		}
		// TODO
		if($_GET['details'])
		{
			$_SESSION['crs_details_id'] = $_GET['details'];
		}
		$this->details_id = $_SESSION['crs_details_id'] ? $_SESSION['crs_details_id'] : $objective_ids[0];

		// TODO get status for table header
		switch($this->objective_status)
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
		$this->tpl->addBlockfile('OBJECTIVE_BLOCK','objective_block','tpl.crs_objectives_view_table.html','course');

		$this->tpl->setVariable("TBL_TITLE_OBJECTIVES",$this->lng->txt('crs_objectives'));

		$this->__showHideLinks('objectives');

		if(isset($_SESSION['crs_hide_objectives']))
		{
			return true;
		}

		// show table header
		for($i = 0; $i < 2; ++$i)
		{
			$this->tpl->setCurrentBlock("tbl_header_columns");
			$this->tpl->setVariable("TBL_HEADER_WIDTH_OBJECTIVES","5%");
			$this->tpl->setVariable("TBL_HEADER_NAME_OBJECTIVES",$this->lng->txt('crs_nr'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tbl_header_columns");
			$this->tpl->setVariable("TBL_HEADER_WIDTH_OBJECTIVES","35%");
			$this->tpl->setVariable("TBL_HEADER_NAME_OBJECTIVES",$this->lng->txt('description'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tbl_header_columns");
			$this->tpl->setVariable("TBL_HEADER_WIDTH_OBJECTIVES","10%");
			$this->tpl->setVariable("TBL_HEADER_NAME_OBJECTIVES",$status);
			$this->tpl->parseCurrentBlock();
		}

		$max = count($objective_ids) % 2 ? count($objective_ids) + 1 : count($objective_ids); 
		for($i = 0; $i < $max/2; ++$i)
		{
			$tmp_objective =& new ilCourseObjective($this->cci_course_obj,$objective_ids[$i]);

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

			$this->ctrl->setParameter($this->cci_client_obj,'details',$objective_ids[$i]);
			$this->tpl->setVariable("OBJ_LINK_1_OBJECTIVES",$this->ctrl->getLinkTarget($this->cci_client_obj,'cciObjectives'));
			$this->tpl->setVariable("OBJ_TITLE_1_OBJECTIVES",$tmp_objective->getTitle());

			$img = !$this->suggested["$objective_ids[$i]"] ? 
				ilUtil::getImagePath('crs_accomplished.gif') :
				ilUtil::getImagePath('crs_not_accomplished.gif');

			$txt = !$this->suggested["$objective_ids[$i]"] ? 
				$this->lng->txt('crs_objective_accomplished') :
				$this->lng->txt('crs_objective_not_accomplished');

			$this->tpl->setVariable("OBJ_STATUS_IMG_1_OBJECTIVES",$img);
			$this->tpl->setVariable("OBJ_STATUS_ALT_1_OBJECTIVES",$txt);


			if(isset($objective_ids[$i + $max / 2]))
			{
				$tmp_objective =& new ilCourseObjective($this->cci_course_obj,$objective_ids[$i + $max / 2]);

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
				$this->ctrl->setParameter($this->cci_client_obj,'details',$objective_ids[$i + $max / 2]);
				$this->tpl->setVariable("OBJ_LINK_2_OBJECTIVES",$this->ctrl->getLinkTarget($this->cci_client_obj,'cciObjectives'));
				$this->tpl->setVariable("OBJ_TITLE_2_OBJECTIVES",$tmp_objective->getTitle());


				$objective_id = $objective_ids[$i + $max / 2];
				$img = !$this->suggested[$objective_id] ? 
					ilUtil::getImagePath('crs_accomplished.gif') :
					ilUtil::getImagePath('crs_not_accomplished.gif');

				$txt = !$this->suggested[$objective_id] ? 
					$this->lng->txt('crs_objective_accomplished') :
					$this->lng->txt('crs_objective_not_accomplished');

				$this->tpl->setVariable("OBJ_STATUS_IMG_2_OBJECTIVES",$img);
				$this->tpl->setVariable("OBJ_STATUS_ALT_2_OBJECTIVES",$txt);
			}
	
			$this->tpl->parseCurrentBlock();
			unset($tmp_objective);
		}
		$this->ctrl->setParameter($this->cci_client_obj,'details','');
	}

	function __readAccomplished()
	{
		global $ilUser;

		if(isset($_SESSION['accomplished'][$this->cci_course_obj->getId()]))
		{
			return $this->accomplished = $_SESSION['accomplished'][$this->cci_course_obj->getId()];
		}


		include_once './course/classes/class.ilCourseObjectiveResult.php';
		include_once './course/classes/class.ilCourseObjective.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		
		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->cci_course_obj->getId())))
		{
			return $this->accomplished = array();
		}
		$this->accomplished = array();
		foreach($objective_ids as $objective_id)
		{
			if($tmp_obj_res->hasAccomplishedObjective($objective_id))
			{
				$this->accomplished["$objective_id"] = true;
			}
			else
			{
				$this->accomplished["$objective_id"] = false;
			}
		}
		$_SESSION['accomplished'][$this->cci_course_obj->getId()] = $this->accomplished;
	}
	function __readSuggested()
	{
		global $ilUser;

		if(isset($_SESSION['objectives_suggested'][$this->cci_course_obj->getId()]))
		{
			return $this->suggested = $_SESSION['objectives_suggested'][$this->cci_course_obj->getId()];
		}

		include_once './course/classes/class.ilCourseObjectiveResult.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());

		$this->suggested = array();
		foreach($this->accomplished as $objective_id => $ok)
		{
			if($ok)
			{
				$this->suggested["$objective_id"] = false;
			}
			else
			{
				$this->suggested["$objective_id"] = $tmp_obj_res->isSuggested($objective_id);
			}
		}

		return $_SESSION['objectives_suggested'][$this->cci_course_obj->getId()] = $this->suggested;

	}

	function __readStatus()
	{
		global $ilUser;

		if(isset($_SESSION['objectives_status'][$this->cci_course_obj->getId()]))
		{
			return $this->objective_status = $_SESSION['objectives_status'][$this->cci_course_obj->getId()];
		}
		$all_success = true;

		foreach($this->accomplished as $id => $success)
		{
			if(!$success)
			{
				$all_success = false;
			}
		}
		if($all_success)
		{
			// set status passed
			include_once 'course/classes/class.ilCourseMembers.php';

			ilCourseMembers::_setPassed($this->cci_course_obj->getId(),$ilUser->getId());

			$this->objective_status = 'finished';
			$_SESSION['objectives_status'][$this->cci_course_obj->getId()] = $this->objective_status;

			return true;
		}
		include_once './course/classes/class.ilCourseObjectiveResult.php';
		include_once './course/classes/class.ilCourseObjective.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());

		$this->objective_status = $tmp_obj_res->getStatus($this->cci_course_obj->getId());

		if($this->objective_status == 'pretest')
		{
			$none_suggested = true;
			foreach($this->suggested as $value)
			{
				if($value)
				{
					$_SESSION['objectives_status'][$this->cci_course_obj->getId()] = $this->objective_status;
					return true;
				}
			}
			$this->objective_status = 'pretest_non_suggest';
		}
		$_SESSION['objectives_status'][$this->cci_course_obj->getId()] = $this->objective_status;

		return true;
	}

	function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this->cci_client_obj,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);

		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}



	function cci_read()
	{
		global $tree;

		if(!$this->cci_course_id = $tree->checkForParentType($this->cci_ref_id,'crs'))
		{
			echo "ilCourseContentInterface: Cannot find course object";
			exit;
		}
		return true;
	}

	function cciToUnix($a_time_arr)
	{
		return mktime($a_time_arr["hour"],
					  $a_time_arr["minute"],
					  $a_time_arr["second"],
					  $a_time_arr["month"],
					  $a_time_arr["day"],
					  $a_time_arr["year"]);
	}
	function cciGetDateSelect($a_type,$a_varname,$a_selected)
	{
		switch($a_type)
		{
			case "minute":
				for($i=0;$i<=60;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "hour":
				for($i=0;$i<24;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "day":
				for($i=1;$i<32;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);
			
			case "month":
				for($i=1;$i<13;$i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
		}
	}
}
?>
