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
* Class ilObjSurveyGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
*
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyEvaluationGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyExecutionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilMDEditorGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilInfoScreenGUI
*
* @extends ilObjectGUI
* @ingroup ModulesSurvey
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

class ilObjSurveyGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyGUI()
	{
    global $lng, $ilCtrl;

		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id");

		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
	}
	
	function backToRepositoryObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilAccess, $ilNavigationHistory;
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjSurveyGUI&cmd=infoScreen&ref_id=".$_GET["ref_id"], "svy");
		}

		$cmd = $this->ctrl->getCmd("properties");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");
		$this->prepareOutput();

		//echo "<br>nextclass:$next_class:cmd:$cmd:qtype=$q_type";
		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;
			case 'ilmdeditorgui':
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
			
			case "ilsurveyevaluationgui":
				include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
				$eval_gui = new ilSurveyEvaluationGUI($this->object);
				$ret =& $this->ctrl->forwardCommand($eval_gui);
				break;

			case "ilsurveyexecutiongui":
				include_once("./Modules/Survey/classes/class.ilSurveyExecutionGUI.php");
				$exec_gui = new ilSurveyExecutionGUI($this->object);
				$ret =& $this->ctrl->forwardCommand($exec_gui);
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}
		if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=".$newObj->getRefId()."&cmd=properties");
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* Cancel actions in the properties form
	*
	* Cancel actions in the properties form
	*
	* @access private
	*/
	function cancelPropertiesObject()
	{
    ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "properties");
	}
	
	/**
	* Save the survey properties
	*
	* Save the survey properties
	*
	* @access private
	*/
	function savePropertiesObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$status = STATUS_OFFLINE;
		if ($_POST["status"] == 1)
		{
			$status = STATUS_ONLINE;
		}
		$result = $this->object->setStatus($status);
		if ($result)
		{
			ilUtil::sendInfo($result, true);
		}
		$this->object->setEvaluationAccess($_POST["evaluation_access"]);
		$this->object->setStartDate(sprintf("%04d-%02d-%02d", $_POST["start_date"]["y"], $_POST["start_date"]["m"], $_POST["start_date"]["d"]));
		$this->object->setStartDateEnabled($_POST["checked_start_date"]);
		$this->object->setEndDate(sprintf("%04d-%02d-%02d", $_POST["end_date"]["y"], $_POST["end_date"]["m"], $_POST["end_date"]["d"]));
		$this->object->setEndDateEnabled($_POST["checked_end_date"]);

		include_once "./classes/class.ilObjAdvancedEditing.php";
		$introduction = ilUtil::stripSlashes($_POST["introduction"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
		$this->object->setIntroduction($introduction);
		$outro = ilUtil::stripSlashes($_POST["outro"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
		$this->object->setOutro($outro);

		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		if (!$hasDatasets)
		{
			$anonymize = $_POST["anonymize"];
			if ($anonymize)
			{
				$codes = $_POST["codes"];
				$anonymize += $codes;
			}
			$this->object->setAnonymize($anonymize);
		}
		if ($_POST["showQuestionTitles"])
		{
			$this->object->showQuestionTitles();
		}
		else
		{
			$this->object->hideQuestionTitles();
		}
		$this->update = $this->object->update();
		$this->object->saveToDb();
		if (strcmp($_SESSION["info"], "") != 0)
		{
			ilUtil::sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "properties");
	}

/**
* Checks for write access and returns to the parent object
*
* Checks for write access and returns to the parent object
*
* @access public
*/
  function handleWriteAccess()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), TRUE);
			$this->ctrl->redirect($this, "infoScreen");
		}
	}
	
/**
* Creates the properties form for the survey object
*
* Creates the properties form for the survey object
*
* @access public
*/
  function propertiesObject()
  {
		$this->handleWriteAccess();
		// to set the command class for the default command after object creation to make the RTE editor switch work
		if (strlen($this->ctrl->getCmdClass()) == 0) $this->ctrl->setCmdClass("ilobjsurveygui");
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$this->lng->loadLanguageModule("jscalendar");
		$this->tpl->addBlockFile("CALENDAR_LANG_JAVASCRIPT", "calendar_javascript", "tpl.calendar.html");
		$this->tpl->setCurrentBlock("calendar_javascript");
		$this->tpl->setVariable("FULL_SUNDAY", $this->lng->txt("l_su"));
		$this->tpl->setVariable("FULL_MONDAY", $this->lng->txt("l_mo"));
		$this->tpl->setVariable("FULL_TUESDAY", $this->lng->txt("l_tu"));
		$this->tpl->setVariable("FULL_WEDNESDAY", $this->lng->txt("l_we"));
		$this->tpl->setVariable("FULL_THURSDAY", $this->lng->txt("l_th"));
		$this->tpl->setVariable("FULL_FRIDAY", $this->lng->txt("l_fr"));
		$this->tpl->setVariable("FULL_SATURDAY", $this->lng->txt("l_sa"));
		$this->tpl->setVariable("SHORT_SUNDAY", $this->lng->txt("s_su"));
		$this->tpl->setVariable("SHORT_MONDAY", $this->lng->txt("s_mo"));
		$this->tpl->setVariable("SHORT_TUESDAY", $this->lng->txt("s_tu"));
		$this->tpl->setVariable("SHORT_WEDNESDAY", $this->lng->txt("s_we"));
		$this->tpl->setVariable("SHORT_THURSDAY", $this->lng->txt("s_th"));
		$this->tpl->setVariable("SHORT_FRIDAY", $this->lng->txt("s_fr"));
		$this->tpl->setVariable("SHORT_SATURDAY", $this->lng->txt("s_sa"));
		$this->tpl->setVariable("FULL_JANUARY", $this->lng->txt("l_01"));
		$this->tpl->setVariable("FULL_FEBRUARY", $this->lng->txt("l_02"));
		$this->tpl->setVariable("FULL_MARCH", $this->lng->txt("l_03"));
		$this->tpl->setVariable("FULL_APRIL", $this->lng->txt("l_04"));
		$this->tpl->setVariable("FULL_MAY", $this->lng->txt("l_05"));
		$this->tpl->setVariable("FULL_JUNE", $this->lng->txt("l_06"));
		$this->tpl->setVariable("FULL_JULY", $this->lng->txt("l_07"));
		$this->tpl->setVariable("FULL_AUGUST", $this->lng->txt("l_08"));
		$this->tpl->setVariable("FULL_SEPTEMBER", $this->lng->txt("l_09"));
		$this->tpl->setVariable("FULL_OCTOBER", $this->lng->txt("l_10"));
		$this->tpl->setVariable("FULL_NOVEMBER", $this->lng->txt("l_11"));
		$this->tpl->setVariable("FULL_DECEMBER", $this->lng->txt("l_12"));
		$this->tpl->setVariable("SHORT_JANUARY", $this->lng->txt("s_01"));
		$this->tpl->setVariable("SHORT_FEBRUARY", $this->lng->txt("s_02"));
		$this->tpl->setVariable("SHORT_MARCH", $this->lng->txt("s_03"));
		$this->tpl->setVariable("SHORT_APRIL", $this->lng->txt("s_04"));
		$this->tpl->setVariable("SHORT_MAY", $this->lng->txt("s_05"));
		$this->tpl->setVariable("SHORT_JUNE", $this->lng->txt("s_06"));
		$this->tpl->setVariable("SHORT_JULY", $this->lng->txt("s_07"));
		$this->tpl->setVariable("SHORT_AUGUST", $this->lng->txt("s_08"));
		$this->tpl->setVariable("SHORT_SEPTEMBER", $this->lng->txt("s_09"));
		$this->tpl->setVariable("SHORT_OCTOBER", $this->lng->txt("s_10"));
		$this->tpl->setVariable("SHORT_NOVEMBER", $this->lng->txt("s_11"));
		$this->tpl->setVariable("SHORT_DECEMBER", $this->lng->txt("s_12"));
		$this->tpl->setVariable("ABOUT_CALENDAR", $this->lng->txt("about_calendar"));
		$this->tpl->setVariable("ABOUT_CALENDAR_LONG", $this->lng->txt("about_calendar_long"));
		$this->tpl->setVariable("ABOUT_TIME_LONG", $this->lng->txt("about_time"));
		$this->tpl->setVariable("PREV_YEAR", $this->lng->txt("prev_year"));
		$this->tpl->setVariable("PREV_MONTH", $this->lng->txt("prev_month"));
		$this->tpl->setVariable("GO_TODAY", $this->lng->txt("go_today"));
		$this->tpl->setVariable("NEXT_MONTH", $this->lng->txt("next_month"));
		$this->tpl->setVariable("NEXT_YEAR", $this->lng->txt("next_year"));
		$this->tpl->setVariable("SEL_DATE", $this->lng->txt("select_date"));
		$this->tpl->setVariable("DRAG_TO_MOVE", $this->lng->txt("drag_to_move"));
		$this->tpl->setVariable("PART_TODAY", $this->lng->txt("part_today"));
		$this->tpl->setVariable("DAY_FIRST", $this->lng->txt("day_first"));
		$this->tpl->setVariable("CLOSE", $this->lng->txt("close"));
		$this->tpl->setVariable("TODAY", $this->lng->txt("today"));
		$this->tpl->setVariable("TIME_PART", $this->lng->txt("time_part"));
		$this->tpl->setVariable("DEF_DATE_FORMAT", $this->lng->txt("def_date_format"));
		$this->tpl->setVariable("TT_DATE_FORMAT", $this->lng->txt("tt_date_format"));
		$this->tpl->setVariable("WK", $this->lng->txt("wk"));
		$this->tpl->setVariable("TIME", $this->lng->txt("time"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("CalendarJS");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./Modules/Survey/js/calendar/calendar.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./Modules/Survey/js/calendar/calendar-setup.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./Modules/Survey/js/calendar/calendar.css");
		$this->tpl->parseCurrentBlock();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_properties.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "properties"));
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getIntroduction())));
		$this->tpl->setVariable("TEXT_OUTRO", $this->lng->txt("outro"));
		$this->tpl->setVariable("VALUE_OUTRO", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getOutro())));
		$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("online"));
		$this->tpl->setVariable("TEXT_START_DATE", $this->lng->txt("start_date"));
		$this->tpl->setVariable("VALUE_START_DATE", ilUtil::makeDateSelect("start_date", $this->object->getStartYear(), $this->object->getStartMonth(), $this->object->getStartDay()));
		$this->tpl->setVariable("IMG_START_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_START_DATE_CALENDAR", $this->lng->txt("open_calendar"));
		$this->tpl->setVariable("TEXT_END_DATE", $this->lng->txt("end_date"));
		$this->tpl->setVariable("VALUE_END_DATE", ilUtil::makeDateSelect("end_date", $this->object->getEndYear(), $this->object->getEndMonth(), $this->object->getEndDay()));
		$this->tpl->setVariable("IMG_END_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_END_DATE_CALENDAR", $this->lng->txt("open_calendar"));
		$this->tpl->setVariable("TEXT_EVALUATION_ACCESS", $this->lng->txt("evaluation_access"));
		$this->tpl->setVariable("DESCRIPTION_EVALUATION_ACCESS", $this->lng->txt("evaluation_access_description"));
		$this->tpl->setVariable("TEXT_ENABLED", $this->lng->txt("enabled"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("evaluation_access_off"));
		$this->tpl->setVariable("VALUE_ALL", $this->lng->txt("evaluation_access_all"));
		$this->tpl->setVariable("VALUE_PARTICIPANTS", $this->lng->txt("evaluation_access_participants"));

		$this->tpl->setVariable("TEXT_ANONYMIZATION", $this->lng->txt("anonymization"));

		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		if ($hasDatasets)
		{
			$this->tpl->setVariable("DISABLED_ANONYMIZATION", " disabled=\"disabled\"");
		}
		
		$this->tpl->setVariable("DESCRIPTION_ANONYMIZATION", $this->lng->txt("anonymize_survey_description"));
		$this->tpl->setVariable("ANON_VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("ANON_VALUE_ON", $this->lng->txt("on_additional"));
		$this->tpl->setVariable("VALUE_NOCODES", $this->lng->txt("anonymize_without_code"));
		$this->tpl->setVariable("VALUE_CODES", $this->lng->txt("anonymize_with_code"));
		switch ($this->object->getAnonymize())
		{
			case ANONYMIZE_OFF:
				$this->tpl->setVariable("ANON_CHECKED_OFF", " checked=\"checked\"");
				$this->tpl->setVariable("CHECKED_CODES", " checked=\"checked\"");
				break;
			case ANONYMIZE_ON:
				$this->tpl->setVariable("ANON_CHECKED_ON", " checked=\"checked\"");
				$this->tpl->setVariable("CHECKED_CODES", " checked=\"checked\"");
				break;
			case ANONYMIZE_FREEACCESS:
				$this->tpl->setVariable("ANON_CHECKED_ON", " checked=\"checked\"");
				$this->tpl->setVariable("CHECKED_NOCODES", " checked=\"checked\"");
				break;
		}
		
		if ($this->object->getEndDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_END_DATE", " checked=\"checked\"");
		}
		if ($this->object->getStartDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_START_DATE", " checked=\"checked\"");
		}
		switch ($this->object->getEvaluationAccess())
		{
			case EVALUATION_ACCESS_OFF:
				$this->tpl->setVariable("CHECKED_OFF", " checked=\"checked\"");
				break;
			case EVALUATION_ACCESS_ALL:
				$this->tpl->setVariable("CHECKED_ALL", " checked=\"checked\"");
				break;
			case EVALUATION_ACCESS_PARTICIPANTS:
				$this->tpl->setVariable("CHECKED_PARTICIPANTS", " checked=\"checked\"");
				break;
		}
		if ($this->object->getStatus() == STATUS_ONLINE)
		{
			$this->tpl->setVariable("CHECKED_STATUS", " checked=\"checked\"");
		}
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TEXT_SHOW_QUESTIONTITLES", $this->lng->txt("svy_show_questiontitles"));
		if ($this->object->getShowQuestionTitles())
		{
			$this->tpl->setVariable("QUESTIONTITLES_CHECKED", " checked=\"checked\"");
		}
    $this->tpl->parseCurrentBlock();

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		include_once "./classes/class.ilObject.php";
		$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "survey");
  }

	/**
	* Called when the filter in the question browser is activated
	*
	* Called when the filter in the question browser is activated
	*
	* @access private
	*/
	function filterQuestionsObject()
	{
		$this->browseForQuestionsObject($_POST["sel_questionpool"]);
	}
	
	/**
	* Called when the filter in the question browser has been resetted
	*
	* Called when the filter in the question browser has been resetted
	*
	* @access private
	*/
	function resetFilterQuestionsObject()
	{
		$this->browseForQuestionsObject("", true);
	}
	
	/**
	* Change the object type in the question browser
	*
	* Change the object type in the question browser
	*
	* @access private
	*/
	function changeDatatypeObject()
	{
		$this->browseForQuestionsObject("", true, $_POST["datatype"]);
	}
	
	/**
	* Insert questions into the survey
	*
	* Insert questions into the survey
	*
	* @access private
	*/
	function insertQuestionsObject()
	{
		// insert selected questions into test
		$inserted_objects = 0;
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/cb_(\d+)/", $key, $matches)) 
			{
				if ($_GET["browsetype"] == 1)
				{
					$this->object->insertQuestion($matches[1]);
				}
				else
				{
					$this->object->insertQuestionBlock($matches[1]);
				}
				$inserted_objects++;
			}
		}
		if ($inserted_objects)
		{
			$this->object->saveCompletionStatus();
			ilUtil::sendInfo($this->lng->txt("questions_inserted"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			if ($_GET["browsetype"] == 1)
			{
				ilUtil::sendInfo($this->lng->txt("insert_missing_question"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("insert_missing_questionblock"));
			}
			$this->browseForQuestionsObject("", false, $_GET["browsetype"]);
		}
	}
	
	/**
	* Remove questions from the survey
	*
	* Remove questions from the survey
	*
	* @access private
	*/
	function removeQuestionsObject()
	{
		$checked_questions = array();
		$checked_questionblocks = array();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/cb_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questions, $matches[1]);
			}
			if (preg_match("/cb_qb_(\d+)/", $key, $matches))
			{
				array_push($checked_questionblocks, $matches[1]);
			}
		}
		if (count($checked_questions) + count($checked_questionblocks) > 0) 
		{
			ilUtil::sendInfo($this->lng->txt("remove_questions"));
			$this->removeQuestionsForm($checked_questions, $checked_questionblocks);
			return;
		} 
		else 
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}
	
/**
* Creates the questionbrowser to select questions from question pools
*
* Creates the questionbrowser to select questions from question pools
*
* @access public
*/
	function browseForQuestionsObject($filter_questionpool = "", $reset_filter = false, $browsequestions = 1) 
	{
		global $rbacsystem;

		$this->setBrowseForQuestionsSubtabs();
		if (strcmp($this->ctrl->getCmd(), "filterQuestions") != 0)
		{
			if (array_key_exists("sel_questionpool", $_GET)) $filter_questionpool = $_GET["sel_questionpool"];
		}
		if (strcmp($this->ctrl->getCmd(), "changeDatatype") != 0)
		{
			if (array_key_exists("browsetype", $_GET))	$browsequestions = $_GET["browsetype"];
		}
		if ($_POST["cmd"]["back"]) 
		{
			$show_questionbrowser = false;
		}

		$add_parameter = "&insert_question=1";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questionbrowser.html", "Modules/Survey");
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_svy_action_buttons.html", "Modules/Survey");
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_svy_filter_questions.html", "Modules/Survey");

		$questionpools =& $this->object->getQuestionpoolTitles();
		if (count($questionpools) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_questions_available"));
			return;
		}
		$filter_type = $_GET["sel_filter_type"];
		if (!$filter_type)
		{
			$filter_type = $_POST["sel_filter_type"];
		}
		if ($reset_filter)
		{
			$filter_type = "";
		}
		$add_parameter .= "&sel_filter_type=$filter_type";

		$filter_text = $_GET["filter_text"];
		if (!$filter_text)
		{
			$filter_text = $_POST["filter_text"];
		}
		if ($reset_filter)
		{
			$filter_text = "";
		}
		$add_parameter .= "&filter_text=$filter_text";

		$add_parameter .= "&browsetype=$browsequestions";
		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) 
		{
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (!$reset_filter) 
			{
				if (strcmp($filter_type, $key) == 0) 
				{
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		$filter_question_type = $_POST["sel_question_type"];
		if (!$filter_question_type)
		{
			$filter_question_type = $_GET["sel_question_type"];
		}
		if ($reset_filter)
		{
			$filter_question_type = "";
		}
		$add_parameter .= "&sel_question_type=$filter_question_type";

		if ($browsequestions)
		{
			$questiontypes =& $this->object->_getQuestiontypes();
			foreach ($questiontypes as $key => $value)
			{
				$this->tpl->setCurrentBlock("questiontype_row");
				$this->tpl->setVariable("VALUE_QUESTION_TYPE", $value);
				$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($value));
				if (strcmp($filter_question_type, $value) == 0)
				{
					$this->tpl->setVariable("SELECTED_QUESTION_TYPE", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($reset_filter)
		{
			$filter_questionpool = "";
		}
		$add_parameter .= "&sel_questionpool=$filter_questionpool";
		
		if ($browsequestions)
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("questionpool_row");
				$this->tpl->setVariable("VALUE_QUESTIONPOOL", $key);
				$this->tpl->setVariable("TEXT_QUESTIONPOOL", $value);
				if (strcmp($filter_questionpool, $key) == 0)
				{
					$this->tpl->setVariable("SELECTED_QUESTIONPOOL", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($browsequestions)
		{
			$this->tpl->setCurrentBlock("question_filters");
			$this->tpl->setVariable("SHOW_QUESTION_TYPES", $this->lng->txt("filter_show_question_types"));
			$this->tpl->setVariable("TEXT_ALL_QUESTION_TYPES", $this->lng->txt("filter_all_question_types"));
			$this->tpl->setVariable("SHOW_QUESTIONPOOLS", $this->lng->txt("filter_show_questionpools"));
			$this->tpl->setVariable("TEXT_ALL_QUESTIONPOOLS", $this->lng->txt("filter_all_questionpools"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		if (!$_POST["cmd"]["reset"]) 
		{
			$this->tpl->setVariable("VALUE_FILTER_TEXT", $filter_text);
		}
		$this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setVariable("OPTION_QUESTIONS", $this->lng->txt("questions"));
		$this->tpl->setVariable("OPTION_QUESTIONBLOCKS", $this->lng->txt("questionblocks"));
		if ($browsequestions)
		{
			$this->tpl->setVariable("SELECTED_QUESTIONS", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_QUESTIONBLOCKS", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_DATATYPE", $this->lng->txt("display_all_available"));
		$this->tpl->setVariable("BTN_CHANGE", $this->lng->txt("change"));
		$this->tpl->parseCurrentBlock();

		if ($_POST["cmd"]["reset"])
		{
			$_POST["filter_text"] = "";
		}
		$startrow = 0;
		if ($_GET["prevrow"])
		{
			$startrow = $_GET["prevrow"];
		}
		if ($_GET["nextrow"])
		{
			$startrow = $_GET["nextrow"];
		}
		if ($_GET["startrow"])
		{
			$startrow = $_GET["startrow"];
		}
		$sort = ($_GET["sort"]) ? $_GET["sort"] : "title";
		$sortorder = ($_GET["sortorder"]) ? $_GET["sortorder"] : "ASC";
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		if ($browsequestions)
		{
			$table = $this->object->getQuestionsTable($sort, $sortorder, $filter_text, $filter_type, $startrow, 1, $filter_question_type, $filter_questionpool);
		}
		else
		{
			$table = $this->object->getQuestionblocksTable($sort, $sortorder, $filter_text, $filter_type, $startrow);
		}
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionblock_id = 0;
		if ($browsequestions)
		{
			include_once "./classes/class.ilFormat.php";
			foreach ($table["rows"] as $data)
			{
				if ($rbacsystem->checkAccess("write", $data["ref_id"])) 
				{
					if ($data["complete"]) 
					{
						// make only complete questions selectable
						$this->tpl->setCurrentBlock("checkable");
						$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
						$this->tpl->setVariable("COUNTER", $data["question_id"]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
					$this->tpl->setVariable("TEXT_PREVIEW", $this->lng->txt("preview"));
					$this->tpl->setVariable("URL_PREVIEW", "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=" . $data["ref_id"] . "&cmd=preview&preview=" . $data["question_id"]);
					$this->tpl->setVariable("COUNTER", $data["question_id"]);
					$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
					$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
					$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
					$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP14"]), "date"));
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data["obj_fi"]]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
			if ($table["rowcount"] > count($table["rows"]))
			{
				$nextstep = $table["nextrow"] + $table["step"];
				if ($nextstep > $table["rowcount"])
				{
					$nextstep = $table["rowcount"];
				}
				$counter = 1;
				for ($i = 0; $i < $table["rowcount"]; $i += $table["step"])
				{
					$this->tpl->setCurrentBlock("pages_questions");
					if ($table["startrow"] == $i)
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<span class=\"inactivepage\">$counter</span>");
					}
					else
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&nextrow=$i" . "\">$counter</a>");
					}
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("questions_navigation_bottom");
				$this->tpl->setVariable("TEXT_ITEM", $this->lng->txt("item"));
				$this->tpl->setVariable("TEXT_ITEM_START", $table["startrow"] + 1);
				$end = $table["startrow"] + $table["step"];
				if ($end > $table["rowcount"])
				{
					$end = $table["rowcount"];
				}
				$this->tpl->setVariable("TEXT_ITEM_END", $end);
				$this->tpl->setVariable("TEXT_OF", strtolower($this->lng->txt("of")));
				$this->tpl->setVariable("TEXT_ITEM_COUNT", $table["rowcount"]);
				$this->tpl->setVariable("TEXT_PREVIOUS", $this->lng->txt("previous"));
				$this->tpl->setVariable("TEXT_NEXT", $this->lng->txt("next"));
				$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&prevrow=" . $table["prevrow"]);
				$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&nextrow=" . $table["nextrow"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			foreach ($table["rows"] as $data)
			{
				$this->tpl->setCurrentBlock("questionblock_row");
				$this->tpl->setVariable("QUESTIONBLOCK_ID", $data["questionblock_id"]);
				$this->tpl->setVariable("QUESTIONBLOCK_TITLE", "<strong>" . $data["title"] . "</strong>");
				$this->tpl->setVariable("SURVEY_TITLE", $data["surveytitle"]);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("QUESTIONS_TITLE", $data["questions"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
			if ($table["rowcount"] > count($table["rows"]))
			{
				$nextstep = $table["nextrow"] + $table["step"];
				if ($nextstep > $table["rowcount"])
				{
					$nextstep = $table["rowcount"];
				}
				$counter = 1;
				for ($i = 0; $i < $table["rowcount"]; $i += $table["step"])
				{
					$this->tpl->setCurrentBlock("pages_questionblocks");
					if ($table["startrow"] == $i)
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<strong>$counter</strong>");
					}
					else
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&nextrow=$i" . "\">$counter</a>");
					}
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("questionblocks_navigation_bottom");
				$this->tpl->setVariable("TEXT_ITEM", $this->lng->txt("item"));
				$this->tpl->setVariable("TEXT_ITEM_START", $table["startrow"] + 1);
				$end = $table["startrow"] + $table["step"];
				if ($end > $table["rowcount"])
				{
					$end = $table["rowcount"];
				}
				$this->tpl->setVariable("TEXT_ITEM_END", $end);
				$this->tpl->setVariable("TEXT_OF", strtolower($this->lng->txt("of")));
				$this->tpl->setVariable("TEXT_ITEM_COUNT", $table["rowcount"]);
				$this->tpl->setVariable("TEXT_PREVIOUS", $this->lng->txt("previous"));
				$this->tpl->setVariable("TEXT_NEXT", $this->lng->txt("next"));
				$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&prevrow=" . $table["prevrow"]);
				$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . $add_parameter . "&nextrow=" . $table["nextrow"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		// if there are no questions, display a message
		if ($counter == 0) 
		{
			$this->tpl->setCurrentBlock("Emptytable");
			if ($browsequestions)
			{
				$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			}
			else
			{
				$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questionblocks_available"));
			}
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$counter++;
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			// create edit buttons & table footer
			$this->tpl->setCurrentBlock("selection");
			$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("Footer");
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->parseCurrentBlock();
		}
		// define the sort column parameters
		$sortarray = array(
			"title" => (strcmp($sort, "title") == 0) ? $sortorder : "",
			"description" => (strcmp($sort, "description") == 0) ? $sortorder : "",
			"type" => (strcmp($sort, "type") == 0) ? $sortorder : "",
			"author" => (strcmp($sort, "author") == 0) ? $sortorder : "",
			"created" => (strcmp($sort, "created") == 0) ? $sortorder : "",
			"updated" => (strcmp($sort, "updated") == 0) ? $sortorder : "",
			"qpl" => (strcmp($sort, "qpl") == 0) ? $sortorder : "",
			"svy" => (strcmp($sort, "svy") == 0) ? $sortorder : ""
		);
		foreach ($sortarray as $key => $value) 
		{
			if (strcmp($value, "ASC") == 0) 
			{
				$sortarray[$key] = "DESC";
			} 
			else 
			{
				$sortarray[$key] = "ASC";
			}
		}

		if ($browsequestions)
		{
			$this->tpl->setCurrentBlock("questions_header");
			$this->ctrl->setParameter($this, "sort", "title");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["title"]);
			$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"]  . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
			$this->ctrl->setParameter($this, "sort", "description");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["description"]);
			$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["description"]);
			$this->ctrl->setParameter($this, "sort", "type");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["type"]);
			$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
			$this->ctrl->setParameter($this, "sort", "author");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["author"]);
			$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"]  . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
			$this->ctrl->setParameter($this, "sort", "created");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["created"]);
			$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"]  . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
			$this->ctrl->setParameter($this, "sort", "updated");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["updated"]);
			$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
			$this->ctrl->setParameter($this, "sort", "qpl");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["qpl"]);
			$this->tpl->setVariable("QUESTION_POOL", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] .  "\">" . $this->lng->txt("obj_spl") . "</a>" . $table["images"]["qpl"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("questionblocks_header");
			$this->ctrl->setParameter($this, "sort", "title");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["title"]);
			$this->tpl->setVariable("QUESTIONBLOCK_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] .  "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
			$this->ctrl->setParameter($this, "sort", "svy");
			$this->ctrl->setParameter($this, "sortorder", $sortarray["svy"]);
			$this->tpl->setVariable("SURVEY_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "browseForQuestions") . "$add_parameter&startrow=" . $table["startrow"] . "\">" . $this->lng->txt("obj_svy") . "</a>" . $table["images"]["svy"]);
			$this->tpl->setVariable("QUESTIONS_TITLE", $this->lng->txt("contains"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		// create table header
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "browseForQuestions") . $add_parameter);
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Creates a confirmation form to remove questions from the survey
*
* Creates a confirmation form to remove questions from the survey
*
* @param array $checked_questions An array containing the id's of the questions to be removed
* @param array $checked_questionblocks An array containing the id's of the question blocks to be removed
* @access public
*/
	function removeQuestionsForm($checked_questions, $checked_questionblocks)
	{
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_remove_questions.html", "Modules/Survey");
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$surveyquestions =& $this->object->getSurveyQuestions();
		foreach ($surveyquestions as $question_id => $data)
		{
			if (in_array($data["question_id"], $checked_questions) or (in_array($data["questionblock_id"], $checked_questionblocks)))
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TEXT_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_DESCRIPTION", $data["description"]);
				$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $data["questionblock_title"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}
		foreach ($checked_questionblocks as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_qb_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
		$this->tpl->parseCurrentBlock();
	}


/**
* Displays the definition form for a question block
*
* Displays the definition form for a question block
*
* @param integer $questionblock_id The database id of the questionblock to edit an existing questionblock
* @access public
*/
	function defineQuestionblock($questionblock_id = "")
	{
		$this->questionsSubtabs("questions");
		ilUtil::sendInfo();
		if ($questionblock_id)
		{
			$questionblock = $this->object->getQuestionblock($questionblock_id);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_define_questionblock.html", "Modules/Survey");
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "cb_$matches[1]");
				$this->tpl->setVariable("HIDDEN_VALUE", $matches[1]);
				$this->tpl->parseCurrentBlock();
			}
		}
		if ($questionblock_id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "questionblock_id");
			$this->tpl->setVariable("HIDDEN_VALUE", $questionblock_id);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		if ($questionblock_id)
		{
			$this->tpl->setVariable("VALUE_TITLE", $questionblock["title"]);
		}
		$this->tpl->setVariable("TXT_QUESTIONTEXT_DESCRIPTION", $this->lng->txt("show_questiontext_description"));
		$this->tpl->setVariable("TXT_QUESTIONTEXT", $this->lng->txt("show_questiontext"));
		if (($questionblock["show_questiontext"]) || (strlen($questionblock_id) == 0))
		{
			$this->tpl->setVariable("CHECKED_QUESTIONTEXT", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("HEADING_QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "saveDefineQuestionblock"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a form to select a survey question pool for storage
*
* Creates a form to select a survey question pool for storage
*
* @access public
*/
	function createQuestionObject()
	{
		global $ilUser;
		$this->questionsSubtabs("questions");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_qpl_select.html", "Modules/Survey");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, TRUE);
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "executeCreateQuestion"));
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("select_questionpool"));
		if (count($questionpools))
		{
			$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("create_questionpool_before_add_question"));
		}
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Cancel the creation of a new questions in a survey
*
* Cancel the creation of a new questions in a survey
*
* @access private
*/
	function cancelCreateQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Execute the creation of a new questions in a survey
*
* Execute the creation of a new questions in a survey
*
* @access private
*/
	function executeCreateQuestionObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=" . $_POST["sel_spl"] . "&cmd=createQuestionForSurvey&new_for_survey=".$_GET["ref_id"]."&sel_question_types=".$_POST["sel_question_types"]);
	}
	
/**
* Creates a form to add a heading to a survey
*
* Creates a form to add a heading to a survey
*
* @param integer $question_id The id of the question directly after the heading. If the id is given, an existing heading will be edited
* @access public
*/
	function addHeadingObject($question_id = "")
	{
		$this->questionsSubtabs("questions");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_heading.html", "Modules/Survey");
		$survey_questions =& $this->object->getSurveyQuestions();
		if ($question_id)
		{
			$_POST["insertbefore"] = $question_id;
			$_POST["heading"] = $survey_questions[$question_id]["heading"];
		}
		foreach ($survey_questions as $key => $value)
		{
			$this->tpl->setCurrentBlock("insertbefore_row");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$option = $this->lng->txt("before") . ": \"" . $value["title"] . "\"";
			if (strlen($option) > 80)
			{
				$option = preg_replace("/^(.{40}).*(.{40})$/", "\\1 [...] \\2", $option);
			}
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$this->tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($option));
			if ($key == $_POST["insertbefore"])
			{
				$this->tpl->setVariable("SELECTED_OPTION", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		if ($question_id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("INSERTBEFORE_ORIGINAL", $question_id);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "saveHeading"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($question_id)
		{
			$this->tpl->setVariable("TEXT_ADD_HEADING", $this->lng->txt("edit_heading"));
			$this->tpl->setVariable("SELECT_DISABLED", " disabled=\"disabled\"");
		}
		else
		{
			$this->tpl->setVariable("TEXT_ADD_HEADING", $this->lng->txt("add_heading"));
		}
		$this->tpl->setVariable("TEXT_HEADING", $this->lng->txt("heading"));
		$this->tpl->setVariable("VALUE_HEADING", $_POST["heading"]);
		$this->tpl->setVariable("TEXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->removePlugin("ibrowser");
		include_once "./classes/class.ilObject.php";
		$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "survey");
	}

/**
* Insert questions or question blocks into the survey after confirmation
*
* Insert questions or question blocks into the survey after confirmation
*
* @access public
*/
	function confirmInsertQuestionObject()
	{
		// insert questions from test after confirmation
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				if ($_GET["browsetype"] == 1)
				{
					$this->object->insertQuestion($matches[1]);
				}
				else
				{
					$this->object->insertQuestionBlock($matches[1]);
				}
			}
		}
		$this->object->saveCompletionStatus();
		ilUtil::sendInfo($this->lng->txt("questions_inserted"), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancels insert questions or question blocks into the survey
*
* Cancels insert questions or question blocks into the survey
*
* @access public
*/
	function cancelInsertQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

/**
* Saves an edited heading in the survey questions list
*
* Saves an edited heading in the survey questions list
*
* @access public
*/
	function saveHeadingObject()
	{
		if ($_POST["heading"])
		{
			$insertbefore = $_POST["insertbefore"];
			if (!$insertbefore)
			{
				$insertbefore = $_POST["insertbefore_original"];
			}
			include_once "./classes/class.ilObjAdvancedEditing.php";
			$this->object->saveHeading(ilUtil::stripSlashes($_POST["heading"], TRUE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")), $insertbefore);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("error_add_heading"));
			$this->addHeadingObject();
			return;
		}
	}
	
/**
* Cancels saving a heading in the survey questions list
*
* Cancels saving a heading in the survey questions list
*
* @access public
*/
	function cancelHeadingObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

/**
* Remove a survey heading after confirmation
*
* Remove a survey heading after confirmation
*
* @access public
*/
	function confirmRemoveHeadingObject()
	{
		$this->object->saveHeading("", $_POST["removeheading"]);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancels the removal of survey headings
*
* Cancels the removal of survey headings
*
* @access public
*/
	function cancelRemoveHeadingObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Displays a confirmation form to delete a survey heading
*
* Displays a confirmation form to delete a survey heading
*
* @access public
*/
	function confirmRemoveHeadingForm()
	{
		ilUtil::sendInfo($this->lng->txt("confirm_remove_heading"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_confirm_removeheading.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BTN_CONFIRM_REMOVE", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_REMOVE", $this->lng->txt("cancel"));
		$this->tpl->setVariable("REMOVE_HEADING", $_GET["removeheading"]);
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "confirmRemoveHeading"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Remove questions from survey after confirmation
*
* Remove questions from survey after confirmation
*
* @access private
*/
	function confirmRemoveQuestionsObject()
	{
		$checked_questions = array();
		$checked_questionblocks = array();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/id_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questions, $matches[1]);
			}
			if (preg_match("/id_qb_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questionblocks, $matches[1]);
			}
		}
		$this->object->removeQuestions($checked_questions, $checked_questionblocks);
		$this->object->saveCompletionStatus();
		ilUtil::sendInfo($this->lng->txt("questions_removed"), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancel remove questions from survey after confirmation
*
* Cancel remove questions from survey after confirmation
*
* @access private
*/
	function cancelRemoveQuestionsObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

/**
* Cancel remove questions from survey after confirmation
*
* Cancel remove questions from survey after confirmation
*
* @access private
*/
	function defineQuestionblockObject()
	{
		$questionblock = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				array_push($questionblock, $value);
			}
		}
		if (count($questionblock) < 2)
		{
			ilUtil::sendInfo($this->lng->txt("qpl_define_questionblock_select_missing"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$this->defineQuestionblock();
			return;
		}
	}
	
/**
* Confirm define a question block
*
* Confirm define a question block
*
* @access private
*/
	function saveDefineQuestionblockObject()
	{
		if ($_POST["title"])
		{
			$show_questiontext = ($_POST["show_questiontext"]) ? 1 : 0;
			if ($_POST["questionblock_id"])
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->object->modifyQuestionblock($_POST["questionblock_id"], ilUtil::stripSlashes($_POST["title"]), $show_questiontext);
			}
			else
			{
				$questionblock = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/cb_(\d+)/", $key, $matches))
					{
						array_push($questionblock, $value);
					}
				}
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->object->createQuestionblock(ilUtil::stripSlashes($_POST["title"]), $show_questiontext, $questionblock);
			}
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("enter_questionblock_title"));
			$this->defineQuestionblockObject();
			return;
		}
	}

/**
* Unfold a question block
*
* Unfold a question block
*
* @access private
*/
	function unfoldQuestionblockObject()
	{
		$unfoldblocks = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_qb_(\d+)/", $key, $matches))
			{
				array_push($unfoldblocks, $matches[1]);
			}
		}
		if (count($unfoldblocks))
		{
			$this->object->unfoldQuestionblocks($unfoldblocks);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_unfold_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancel define a question block
*
* Cancel define a question block
*
* @access private
*/
	function cancelDefineQuestionblockObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Move questions
*
* Move questions
*
* @access private
*/
	function moveQuestionsObject()
	{
		$move_questions = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				array_push($move_questions, $matches[1]);
			}
			if (preg_match("/cb_qb_(\d+)/", $key, $matches))
			{
				$ids = $this->object->getQuestionblockQuestionIds($matches[1]);
				foreach ($ids as $qkey => $qid)
				{
					array_push($move_questions, $qid);
				}
			}
		}
		if (count($move_questions) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_move"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$_SESSION["move_questions"] = $move_questions;
			ilUtil::sendInfo($this->lng->txt("select_target_position_for_move_question"));
			$this->questionsObject();
		}
	}

/**
* Insert questions from move clipboard
*
* Insert questions from move clipboard
*
* @access private
*/
	function insertQuestions($insert_mode)
	{
		// get all questions to move
		$move_questions = $_SESSION["move_questions"];
		// get insert point
		$insert_id = -1;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^cb_(\d+)$/", $key, $matches))
			{
				if ($insert_id < 0)
				{
					$insert_id = $matches[1];
				}
			}
			if (preg_match("/^cb_qb_(\d+)$/", $key, $matches))
			{
				if ($insert_id < 0)
				{
					$ids =& $this->object->getQuestionblockQuestionIds($matches[1]);
					if (count($ids))
					{
						if ($insert_mode == 0)
						{
							$insert_id = $ids[0];
						}
						else if ($insert_mode == 1)
						{
							$insert_id = $ids[count($ids)-1];
						}
					}
				}
			}
		}
		if ($insert_id <= 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_target_selected_for_move"), true);
		}
		else
		{
			$this->object->moveQuestions($move_questions, $insert_id, $insert_mode);
		}
		unset($_SESSION["move_questions"]);
		$this->ctrl->redirect($this, "questions");
	}

/**
* Insert questions before selection
*
* Insert questions before selection
*
* @access private
*/
	function insertQuestionsBeforeObject()
	{
		$this->insertQuestions(0);
	}
	
/**
* Insert questions after selection
*
* Insert questions after selection
*
* @access private
*/
	function insertQuestionsAfterObject()
	{
		$this->insertQuestions(1);
	}

/**
* Save obligatory states
*
* Save obligatory states
*
* @access private
*/
	function saveObligatoryObject()
	{
		$obligatory = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/obligatory_(\d+)/", $key, $matches))
			{
				$obligatory[$matches[1]] = 1;
			}
		}
		$this->object->setObligatoryStates($obligatory);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Creates the questions form for the survey object
*
* Creates the questions form for the survey object
*
* @access public
*/
	function questionsObject() 
	{
		$this->handleWriteAccess();
		$this->questionsSubtabs("questions");
		global $rbacsystem;

		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if ($_GET["new_id"] > 0)
		{
			// add a question to the survey previous created in a questionpool
			$inserted = $this->object->insertQuestion($_GET["new_id"]);
			if (!$inserted)
			{
				ilUtil::sendInfo($this->lng->txt("survey_error_insert_incomplete_question"));
			}
		}
		
		if ($_GET["eqid"] and $_GET["eqpl"])
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForSurvey&calling_survey=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}


		$_SESSION["calling_survey"] = $this->object->getRefId();
		unset($_SESSION["survey_id"]);

		if ($_GET["editheading"])
		{
			$this->addHeadingObject($_GET["editheading"]);
			return;
		}
		
		if ($_GET["up"] > 0)
		{
			$this->object->moveUpQuestion($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->moveDownQuestion($_GET["down"]);
		}
		if ($_GET["qbup"] > 0)
		{
			$this->object->moveUpQuestionblock($_GET["qbup"]);
		}
		if ($_GET["qbdown"] > 0)
		{
			$this->object->moveDownQuestionblock($_GET["qbdown"]);
		}
		
		if ($_GET["removeheading"])
		{
			$this->confirmRemoveHeadingForm();
			return;
		}
		
		if ($_GET["editblock"])
		{
			$this->defineQuestionblock($_GET["editblock"]);
			return;
		}

		if ($_GET["add"])
		{
			// called after a new question was created from a questionpool
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			ilUtil::sendInfo($this->lng->txt("ask_insert_questions"));
			$this->insertQuestionsForm($selected_array);
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questions.html", "Modules/Survey");

		$survey_questions =& $this->object->getSurveyQuestions();
		$questionblock_titles =& $this->object->getQuestionblockTitles();
		$questionpools =& $this->object->getQuestionpoolTitles();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$title_counter = 0;
		$last_color_class = "";
		$obligatory = "<img src=\"" . ilUtil::getImagePath("obligatory.gif", "Modules/Survey") . "\" alt=\"" . $this->lng->txt("question_obligatory") . "\" title=\"" . $this->lng->txt("question_obligatory") . "\" />";
		if (count($survey_questions) > 0)
		{
			foreach ($survey_questions as $question_id => $data)
			{
				$title_counter++;
				if (($last_questionblock_id > 0) && ($data["questionblock_id"] == 0))
				{
					$counter++;
				}
				if (($last_questionblock_id > 0) && ($data["questionblock_id"] > 0) && ($data["questionblock_id"] != $last_questionblock_id))
				{
					$counter++;
				}
				if (($data["questionblock_id"] > 0) and ($data["questionblock_id"] != $last_questionblock_id))
				{
					// add a separator line for the beginning of a question block
					$this->tpl->setCurrentBlock("separator");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("block");
					$this->tpl->setVariable("TYPE_ICON", "<img src=\"" . ilUtil::getImagePath("questionblock.gif", "Modules/Survey") . "\" alt=\"".$this->lng->txt("questionblock_icon")."\" />");
					$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock") . ": " . $data["questionblock_title"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
					{
						if ($data["question_id"] != $this->object->questions[0])
						{
							$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "$&qbup=" . $data["questionblock_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"" . $this->lng->txt("up") . "\" title=\"" . $this->lng->txt("up") . "\" border=\"0\" /></a>");
						}
						$akeys = array_keys($survey_questions);
						if ($data["questionblock_id"] != $survey_questions[$akeys[count($akeys)-1]]["questionblock_id"])
						{
							$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&qbdown=" . $data["questionblock_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"" . $this->lng->txt("down") . "\" title=\"" . $this->lng->txt("down") . "\" border=\"0\" /></a>");
						}
						$this->tpl->setVariable("TEXT_EDIT", $this->lng->txt("edit"));
						$this->tpl->setVariable("HREF_EDIT", $this->ctrl->getLinkTarget($this, "questions") . "&editblock=" . $data["questionblock_id"]);
					}
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("QUESTION_ID", "qb_" . $data["questionblock_id"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				if (($last_questionblock_id > 0) && ($data["questionblock_id"] == 0))
				{
					// add a separator line for the end of a question block
					$this->tpl->setCurrentBlock("separator");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				if ($data["heading"])
				{
					$this->tpl->setCurrentBlock("heading");
					$this->tpl->setVariable("TEXT_HEADING", $data["heading"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
					{
						$this->tpl->setVariable("TEXT_EDIT", $this->lng->txt("edit"));
						$this->tpl->setVariable("HREF_EDIT", $this->ctrl->getLinkTarget($this, "questions") . "&editheading=" . $data["question_id"]);
						$this->tpl->setVariable("TEXT_DELETE", $this->lng->txt("remove"));
						$this->tpl->setVariable("HREF_DELETE", $this->ctrl->getLinkTarget($this, "questions") . "&removeheading=" . $data["question_id"]);
					}
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				if (!$data["questionblock_id"])
				{
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("QTab");
				include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
				$ref_id = SurveyQuestion::_getRefIdFromObjId($data["obj_fi"]);
				if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
				{
					$q_id = $data["question_id"];
					$qpl_ref_id = $this->object->_getRefIdFromObjId($data["obj_fi"]);
					$this->tpl->setVariable("QUESTION_TITLE", "$title_counter. <a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&eqid=$q_id&eqpl=$qpl_ref_id" . "\">" . $data["title"] . "</a>");
				}
				else
				{
					$this->tpl->setVariable("QUESTION_TITLE", "$title_counter. ". $data["title"]);
				}
				$this->tpl->setVariable("TYPE_ICON", "<img src=\"" . ilUtil::getImagePath("question.gif", "Modules/Survey") . "\" alt=\"".$this->lng->txt("question_icon")."\" />");
				if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
				{
					$obligatory_checked = "";
					if ($data["obligatory"] == 1)
					{
						$obligatory_checked = " checked=\"checked\"";
					}
					$this->tpl->setVariable("QUESTION_OBLIGATORY", "<input type=\"checkbox\" name=\"obligatory_" . $data["question_id"] . "\" value=\"1\"$obligatory_checked />");
				}
				else
				{
					if ($data["obligatory"] == 1)
					{
						$this->tpl->setVariable("QUESTION_OBLIGATORY", $obligatory);
					}
				}
				$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
				if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
				{
					if (!$data["questionblock_id"])
					{
						// up/down buttons for non-questionblock questions
						if ($data["question_id"] != $this->object->questions[0])
						{
							$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&up=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"".$this->lng->txt("up")."\" border=\"0\" /></a>");
						}
						if ($data["question_id"] != $this->object->questions[count($this->object->questions)-1])
						{
							$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&down=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"".$this->lng->txt("down")."\" border=\"0\" /></a>");
						}
					}
					else
					{
						// up/down buttons for questionblock questions
						if ($data["questionblock_id"] == $last_questionblock_id)
						{
							$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&up=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"".$this->lng->txt("up")."\" border=\"0\" /></a>");
						}
						$tmp_questions = array_keys($survey_questions);
						$blockkey = array_search($question_id, $tmp_questions);
						if (($blockkey !== FALSE) && ($blockkey < count($tmp_questions)-1))
						{
							if ($data["questionblock_id"] == $survey_questions[$tmp_questions[$blockkey+1]]["questionblock_id"])
							{
								$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&down=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"".$this->lng->txt("down")."\" border=\"0\" /></a>");
							}
						}
					}
				}
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$last_color_class = $colors[$counter % 2];
				if (!$data["questionblock_id"])
				{
					$counter++;
				}
				$this->tpl->parseCurrentBlock();
				$last_questionblock_id = $data["questionblock_id"];
			}

			if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
			{
				$this->tpl->setCurrentBlock("selectall");
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->setVariable("COLOR_CLASS", $last_color_class);
				$this->tpl->parseCurrentBlock();
				if (array_key_exists("move_questions", $_SESSION))
				{
					$this->tpl->setCurrentBlock("move_buttons");
					$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
					$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
				$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
				$this->tpl->setVariable("QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
				$this->tpl->setVariable("UNFOLD", $this->lng->txt("unfold"));
				$this->tpl->setVariable("SAVE", $this->lng->txt("save_obligatory_state"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
		if (($last_questionblock_id > 0))
		{
			// add a separator line for the end of a question block (if the last question is a questionblock question)
			$this->tpl->setCurrentBlock("separator");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}

		if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets) 
		{
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM survey_questiontype";
			$query_result = $this->ilias->db->query($query);
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "questions"));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_OBLIGATORY", $this->lng->txt("obligatory"));
		$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("sequence"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));

		if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets)
		{
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("browse_for_questions"));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
			$this->tpl->setVariable("HEADING", $this->lng->txt("add_heading"));
		}
		if ($hasDatasets)
		{
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning"));
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* Redirects the evaluation object call to the ilSurveyEvaluationGUI class
	*
	* Redirects the evaluation object call to the ilSurveyEvaluationGUI class
	*
	* @access	private
	*/
	function evaluationObject()
	{
		include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
		$eval_gui = new ilSurveyEvaluationGUI($this->object);
		$this->ctrl->setCmdClass(get_class($eval_gui));
		$this->ctrl->redirect($eval_gui, "evaluation");
	}
	
	/**
	* Creates the search output for the user/group search form
	*
	* Creates the search output for the user/group search form
	*
	* @access	public
	*/
	function outUserGroupTable($a_type, $id_array, $block_result, $block_row, $title_text, $buttons)
	{
		global $rbacsystem;
		
		$rowclass = array("tblrow1", "tblrow2");
		switch($a_type)
		{
			case "usr":
				include_once './Services/User/classes/class.ilObjUser.php';
				$counter = 0;
				foreach ($id_array as $user_id)
				{
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					if (ilObjUser::_lookupLogin($user_id))
					{
						$user = new ilObjUser($user_id);
						$this->tpl->setVariable("COUNTER", $user->getId());
						$this->tpl->setVariable("VALUE_LOGIN", $user->getLogin());
						$this->tpl->setVariable("VALUE_FIRSTNAME", $user->getFirstname());
						$this->tpl->setVariable("VALUE_LASTNAME", $user->getLastname());
					}
					else
					{
						$this->tpl->setVariable("COUNTER", $user_id);
						$this->tpl->setVariable("VALUE_LOGIN", $this->lng->txt("deleted_user"));
						$this->tpl->setVariable("VALUE_FIRSTNAME", $this->lng->txt("unknown"));
						$this->tpl->setVariable("VALUE_LASTNAME", $this->lng->txt("unknown"));
					}
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($id_array))
				{
					$this->tpl->setCurrentBlock("selectall_$block_result");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("TEXT_USER_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_usr.gif") . "\" alt=\"".$this->lng->txt("obj_usr")."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				if ($rbacsystem->checkAccess('invite', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "grp":
				include_once "./Modules/Group/classes/class.ilObjGroup.php";
				$counter = 0;
				foreach ($id_array as $group_id)
				{
					$group = new ilObjGroup($group_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $group->getRefId());
					$this->tpl->setVariable("VALUE_TITLE", $group->getTitle());
					$this->tpl->setVariable("VALUE_DESCRIPTION", $group->getDescription());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($id_array))
				{
					$this->tpl->setCurrentBlock("selectall_$block_result");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("TEXT_GROUP_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_grp.gif") . "\" alt=\"".$this->lng->txt("obj_grp")."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess('invite', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "role":
				include_once "./classes/class.ilObjRole.php";
				$counter = 0;
				foreach ($id_array as $role_id)
				{
					$role = new ilObjRole($role_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $role->getId());
					$this->tpl->setVariable("VALUE_TITLE", $role->getTitle());
					$this->tpl->setVariable("VALUE_DESCRIPTION", $role->getDescription());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($id_array))
				{
					$this->tpl->setCurrentBlock("selectall_$block_result");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("TEXT_ROLE_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_role.gif") . "\" alt=\"".$this->lng->txt("obj_role")."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess('invite', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
		}
	}
	
	/**
	* Cancels an action on the invitation tab
	*
	* Cancels an action on the invitation tab
	*
	* @access private
	*/
	function cancelInvitationStatusObject()
	{
		$this->ctrl->redirect($this, "invite");
	}

	/**
	* Saves the status of the invitation tab
	*
	* Saves the status of the invitation tab
	*
	* @access private
	*/
	function saveInvitationStatusObject()
	{
		$this->object->setInvitationAndMode($_POST["invitation"], $_POST["mode"]);
		$this->object->saveToDb();
		$this->ctrl->redirect($this, "invite");
	}
	
	/**
	* Searches users for the invitation tab
	*
	* Searches users for the invitation tab
	*
	* @access private
	*/
	function searchInvitationObject()
	{
		$this->inviteObject();
	}

	/**
	* Disinvite users or groups from a survey
	*
	* Disinvite users or groups from a survey
	*
	* @access	private
	*/
	function disinviteUserGroupObject()
	{
		// disinvite users
		if (is_array($_POST["invited_users"]))
		{
			foreach ($_POST["invited_users"] as $user_id)
			{
				$this->object->disinviteUser($user_id);
			}
		}
		$this->ctrl->redirect($this, "invite");
	}
	
	/**
	* Invite users or groups to a survey
	*
	* Invite users or groups to a survey
	*
	* @access	private
	*/
	function inviteUserGroupObject()
	{
		// add users to invitation
		if (is_array($_POST["user_select"]))
		{
			foreach ($_POST["user_select"] as $user_id)
			{
				$this->object->inviteUser($user_id);
			}
		}
		// add groups to invitation
		if (is_array($_POST["group_select"]))
		{
			foreach ($_POST["group_select"] as $group_id)
			{
				$this->object->inviteGroup($group_id);
			}
		}
		// add roles to invitation
		if (is_array($_POST["role_select"]))
		{
			foreach ($_POST["role_select"] as $role_id)
			{
				$this->object->inviteRole($role_id);
			}
		}
		$this->ctrl->redirect($this, "invite");
	}

	
	/**
	* Creates the output for user/group invitation to a survey
	*
	* Creates the output for user/group invitation to a survey
	*
	* @access	public
	*/
	function inviteObject()
	{
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("visible,invite", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_invite.html", "Modules/Survey");

		if ($this->object->getStatus() == STATUS_OFFLINE)
		{
			$this->tpl->setCurrentBlock("survey_offline");
			$this->tpl->setVariable("SURVEY_OFFLINE_MESSAGE", $this->lng->txt("survey_offline_message"));
			$this->tpl->parseCurrentBlock();
			return;
		}

		$concat = ($_POST["concatenation"]) ? $_POST["concatenation"] : "or";
		$searchfor = ($_POST["search_for"]) ? $_POST["search_for"] : array("usr");
		
		if (strcmp($this->ctrl->getCmd(), "searchInvitation") == 0)
		{
			if (is_array($_POST["search_for"]))
			{
				if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]) or in_array("role", $_POST["search_for"]))
				{
					include_once "./classes/class.ilSearch.php";
					$search =& new ilSearch($ilUser->id);
					$search->setSearchString($_POST["search_term"]);
					$search->setCombination($concat);
					$search->setSearchFor($searchfor);
					$search->setSearchType("new");
					if($search->validate($message))
					{
						$search->performSearch();
					}
					if ($message)
					{
						ilUtil::sendInfo($message);
					}
					if(!$search->getNumberOfResults() && $search->getSearchFor())
					{
						ilUtil::sendInfo($this->lng->txt("search_no_match"));
					}
					$buttons = array("add");
					$invited_users = $this->object->getInvitedUsers();
					if ($searchresult = $search->getResultByType("usr"))
					{
						$users = array();
						foreach ($searchresult as $result_array)
						{
							if (!in_array($result_array["id"], $invited_users))
							{
								array_push($users, $result_array["id"]);
							}
						}
						$this->outUserGroupTable("usr", $users, "user_result", "user_row", $this->lng->txt("search_users"), $buttons);
					}
					$searchresult = array();
					if ($searchresult = $search->getResultByType("grp"))
					{
						$groups = array();
						foreach ($searchresult as $result_array)
						{
							array_push($groups, $result_array["id"]);
						}
						$this->outUserGroupTable("grp", $groups, "group_result", "group_row", $this->lng->txt("search_groups"), $buttons);
					}
					$searchresult = array();
					if ($searchresult = $search->getResultByType("role"))
					{
						$roles = array();
						foreach ($searchresult as $result_array)
						{
							array_push($roles, $result_array["id"]);
						}
						$this->outUserGroupTable("role", $roles, "role_result", "role_row", $this->lng->txt("search_roles"), $buttons);
					}
				}
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("no_user_or_group_selected"));
			}
		}

		if (($this->object->getInvitationMode() == MODE_PREDEFINED_USERS) and ($this->object->getInvitation() == INVITATION_ON))
		{
			if ($rbacsystem->checkAccess('invite', $this->ref_id))
			{
				$this->tpl->setCurrentBlock("invitation");
				$this->tpl->setVariable("SEARCH_INVITATION", $this->lng->txt("search_invitation"));
				$this->tpl->setVariable("SEARCH_TERM", $this->lng->txt("search_term"));
				$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("search_for"));
				$this->tpl->setVariable("SEARCH_USERS", $this->lng->txt("objs_usr"));
				$this->tpl->setVariable("SEARCH_GROUPS", $this->lng->txt("objs_grp"));
				$this->tpl->setVariable("SEARCH_ROLES", $this->lng->txt("objs_role"));
				$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("concatenation"));
				$this->tpl->setVariable("TEXT_AND", $this->lng->txt("and"));
				$this->tpl->setVariable("TEXT_OR", $this->lng->txt("or"));
				$this->tpl->setVariable("VALUE_SEARCH_TERM", $_POST["search_term"]);
				if (is_array($searchfor))
				{
					if (in_array("usr", $searchfor))
					{
						$this->tpl->setVariable("CHECKED_USERS", " checked=\"checked\"");
					}
					if (in_array("grp", $searchfor))
					{
						$this->tpl->setVariable("CHECKED_GROUPS", " checked=\"checked\"");
					}
					if (in_array("role", $searchfor))
					{
						$this->tpl->setVariable("CHECKED_ROLES", " checked=\"checked\"");
					}
				}
				if (strcmp($concat, "and") == 0)
				{
					$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
				}
				else if (strcmp($concat, "or") == 0)
				{
					$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
				}
				$this->tpl->setVariable("SEARCH", $this->lng->txt("search"));
				$this->tpl->parseCurrentBlock();
			}
		}
		if ($this->object->getInvitationMode() == MODE_PREDEFINED_USERS)
		{
			$invited_users = $this->object->getInvitedUsers();
			$buttons = array("disinvite");
			if (count($invited_users))
			{
				$this->outUserGroupTable("usr", $invited_users, "invited_user_result", "invited_user_row", $this->lng->txt("invited_users"), $buttons);
			}
		}
		if ($this->object->getInvitation() == INVITATION_ON)
		{
			$this->tpl->setCurrentBlock("invitation_mode");
			$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("invitation_mode"));
			$this->tpl->setVariable("VALUE_UNLIMITED", $this->lng->txt("unlimited_users"));
			$this->tpl->setVariable("VALUE_PREDEFINED", $this->lng->txt("predefined_users"));
			if ($this->object->getInvitationMode() == MODE_PREDEFINED_USERS)
			{
				$this->tpl->setVariable("SELECTED_PREDEFINED", " selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("SELECTED_UNLIMITED", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "invite"));
		$this->tpl->setVariable("TEXT_INVITATION", $this->lng->txt("invitation"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($this->object->getInvitation() == INVITATION_ON)
		{
			$this->tpl->setVariable("SELECTED_ON", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFF", " selected=\"selected\"");
		}
    if ($rbacsystem->checkAccess("write", $this->ref_id) or $rbacsystem->checkAccess('invite', $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates a confirmation form for delete all user data
	*
	* Creates a confirmation form for delete all user data
	*
	* @access	private
	*/
	function deleteAllUserDataObject()
	{
		ilUtil::sendInfo($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_maintenance.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("confirm_delete");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "deleteAllUserData"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Deletes all user data of the survey after confirmation
	*
	* Deletes all user data of the survey after confirmation
	*
	* @access	private
	*/
	function confirmDeleteAllUserDataObject()
	{
		$this->object->deleteAllUserData();
		ilUtil::sendInfo($this->lng->txt("svy_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Cancels delete of all user data in maintenance
	*
	* Cancels delete of all user data in maintenance
	*
	* @access	private
	*/
	function cancelDeleteAllUserDataObject()
	{
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Deletes all user data for the test object
	*
	* Deletes all user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteSelectedUserDataObject()
	{
		$this->object->removeSelectedSurveyResults($_POST["chbUser"]);
		ilUtil::sendInfo($this->lng->txt("svy_selected_user_data_deleted"), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Cancels the deletion of all user data for the test object
	*
	* Cancels the deletion of all user data for the test object
	*
	* @access	public
	*/
	function cancelDeleteSelectedUserDataObject()
	{
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Asks for a confirmation to delete selected user data of the test object
	*
	* Asks for a confirmation to delete selected user data of the test object
	*
	* @access	public
	*/
	function deleteSingleUserResultsObject()
	{
		if (count($_POST["chbUser"]) == 0)
		{
			$this->ctrl->redirect($this, "maintenance");
		}
		ilUtil::sendInfo($this->lng->txt("confirm_delete_single_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_maintenance.html", "Modules/Survey");

		$this->tpl->setCurrentBlock("confirm_delete_selected");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_SELECTED", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_SELECTED", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
		
		foreach ($_POST["chbUser"] as $key => $value)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("USER_ID", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "deleteSingleUserResults"));
		$this->tpl->parseCurrentBlock();
	}
	
	function maintenanceObject()
	{
		$this->handleWriteAccess();

		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id)) 
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_maintenance.html", "Modules/Survey");
			$total =& $this->object->getSurveyParticipants();
			if (count($total))
			{
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				foreach ($total as $user_data)
				{
					$user_name = $user_data["sortname"];
					$user_login = $user_data["login"];
					$this->tpl->setCurrentBlock("userrow");
					$this->tpl->setVariable("ROW_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("USER_ID", $user_data["active_id"]);
					$this->tpl->setVariable("VALUE_USER_NAME", $user_name);
					$this->tpl->setVariable("VALUE_USER_LOGIN", $user_login);
					$last_access = $this->object->_getLastAccess($user_data["active_id"]);
					$this->tpl->setVariable("LAST_ACCESS", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($last_access)));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("selectall");
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$counter++;
				$this->tpl->setVariable("ROW_CLASS", $color_class[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("participanttable");
				$this->tpl->setVariable("USER_NAME", $this->lng->txt("name"));
				$this->tpl->setVariable("USER_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("LAST_ACCESS", $this->lng->txt("last_access"));
				$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
				$this->tpl->setVariable("ARROW", $this->lng->txt("arrow_downright"));
				$this->tpl->setVariable("DELETE", $this->lng->txt("delete_user_data"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("adm_content");
				$this->tpl->setVariable("BTN_DELETE_ALL", $this->lng->txt("svy_delete_all_user_data"));
	//			$this->tpl->setVariable("BTN_CREATE_SOLUTIONS", $this->lng->txt("tst_create_solutions"));
				$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "deleteSingleUserResults"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("maintenance_information");
				$this->tpl->setVariable("MAINTENANCE_INFORMATION", $this->lng->txt("svy_maintenance_information_no_results"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cannot_maintain_survey"));
		}
	}	

  /*
	* list all export files
	*/
	function exportObject()
	{
		$this->handleWriteAccess();

		global $tree;
		global $rbacsystem;

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "createExportFile"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("svy_create_export_file"));
		$this->tpl->parseCurrentBlock();

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);

		// create table
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/Survey");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "export"));

		$tbl->setTitle($this->lng->txt("svy_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("svy_file"),
			$this->lng->txt("svy_size"), $this->lng->txt("date") ));

		$tbl->enabled["sort"] = false;
		$tbl->setColumnWidth(array("1%", "49%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		$header_params = $this->ctrl->getParameterArray($this, "export");
		$tbl->setHeaderVars(array("", "file", "size", "date"), $header_params);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($export_files) > 0)
		{
			$this->tpl->setVariable("COLUMN_COUNTS", 4);
			
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", filesize($export_dir."/".$exp_file));
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file);

				$file_arr = explode("__", $exp_file);
				$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
			// delete button
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
			$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
	
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME", "downloadExportFile");
			$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
			$this->tpl->parseCurrentBlock();	
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* create export file
	*/
	function createExportFileObject()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			include_once("./Modules/Survey/classes/class.ilSurveyExport.php");
			$survey_exp = new ilSurveyExport($this->object);
			$survey_exp->buildExportFile();
			$this->ctrl->redirect($this, "export");
		}
		else
		{
			ilUtil::sendInfo("cannot_export_survey");
		}
	}

	/**
	* display dialogue for importing tests
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import", "svy");
		$this->tpl->setCurrentBlock("option_qpl");
		include_once("./Modules/Survey/classes/class.ilObjSurvey.php");
		$svy = new ilObjSurvey();
		$questionpools =& $svy->getAvailableQuestionpools(TRUE, FALSE, TRUE);
		if (count($questionpools) == 0)
		{
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option_spl");
				$this->tpl->setVariable("OPTION_VALUE", $key);
				$this->tpl->setVariable("TXT_OPTION", $value);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
		$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "import"));
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_TST", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject($redirect = true)
	{
		if ($_POST["spl"] < 1)
		{
			ilUtil::sendInfo($this->lng->txt("svy_select_questionpools"));
			$this->importObject();
			return;
		}
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("svy_select_file_for_import"));
			$this->importObject();
			return;
		}
		
		include_once("./Modules/Survey/classes/class.ilObjSurvey.php");
		$newObj = new ilObjSurvey();
		$newObj->setType($_GET["new_type"]);
		$newObj->setTitle("dummy");
		$newObj->setDescription("dummy");
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		// copy uploaded file to import directory
		$error = $newObj->importObject($_FILES["xmldoc"], $_POST["spl"]);
		if (strlen($error)) 
		{  
			$newObj->delete();
			$this->ilias->raiseError($error, $this->ilias->error_obj->MESSAGE);
			return;
		}
		else
		{
			$ref_id = $newObj->getRefId();
		}
		if ($redirect)
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("upload",$this->ctrl->getTargetScript()."?".$this->link_params));
		}
		return $ref_id;
	}

	/**
	* form for new content object creation
	*/
	function createObject()
	{
		global $rbacsystem;
		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->getTemplateFile("create", $new_type);

			include_once("./Modules/Survey/classes/class.ilObjSurvey.php");
			$svy = new ilObjSurvey();
			
			$this->fillCloneTemplate('DUPLICATE','svy');
			$questionpools =& $svy->getAvailableQuestionpools($use_obj_id = TRUE, $could_be_offline = TRUE, $showPath = TRUE);
			if (count($questionpools) > 0)
			{
				foreach ($questionpools as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_spl");
					$this->tpl->setVariable("OPTION_VALUE", $key);
					$this->tpl->setVariable("TXT_OPTION", $value);
					if ($_POST["spl"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["desc"]);

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->ctrl->setParameter($this, "new_type", $this->type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "create"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_short"));
			$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", ' target="'.
				ilFrameTargetInfo::_getFrame("MainContent").'" ');
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_SVY", $this->lng->txt("import_svy"));
			$this->tpl->setVariable("TXT_SVY_FILE", $this->lng->txt("svy_upload_file"));
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TYPE_IMG", ilUtil::getImagePath('icon_svy.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_svy"));
			$this->tpl->setVariable("TYPE_IMG2", ilUtil::getImagePath('icon_svy.gif'));
			$this->tpl->setVariable("ALT_IMG2",$this->lng->txt("obj_svy"));
		}
	}
	
	/**
	* form for new survey object import
	*/
	function importFileObject()
	{
		if ($_POST["spl"] < 1)
		{
			ilUtil::sendInfo($this->lng->txt("svy_select_questionpools"));
			$this->createObject();
			return;
		}
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("svy_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->ctrl->setParameter($this, "new_type", $this->type);
		$ref_id = $this->uploadObject(false);
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_imported"),true);

		ilUtil::redirect("ilias.php?ref_id=".$ref_id.
			"&baseClass=ilObjSurveyGUI");
//		$this->ctrl->redirect($this, "importFile");
	}

	/**
	* download export file
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		if (count($_POST["file"]) > 1)
		{
			ilUtil::sendInfo($this->lng->txt("select_max_one_item"), true);
			$this->ctrl->redirect($this, "export");
		}


		$export_dir = $this->object->getExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		//$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Survey");

		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "deleteExportFile"));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		foreach($_POST["file"] as $file)
		{
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_file.gif"));
				$this->tpl->setVariable("TEXT_IMG_OBJ", $this->lng->txt("file_icon"));
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("TEXT_CONTENT", $file);
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array("deleteExportFile"  => $this->lng->txt("confirm"),
			"cancelDeleteExportFile"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		session_unregister("ilExportFiles");
		$this->ctrl->redirect($this, "export");
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$exp_file = $export_dir."/".$file;
			$exp_dir = $export_dir."/".substr($file, 0, strlen($file) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::delDir($exp_dir);
			}
		}
		$this->ctrl->redirect($this, "export");
	}

	function setCodeLanguageObject()
	{
		if (strcmp($_POST["lang"], "-1") != 0)
		{
			global $ilUser;
			$ilUser->writePref("survey_code_language", $_POST["lang"]);
		}
		$this->ctrl->redirect($this, "codes");
	}
	
	/**
	* Display the survey access codes tab
	*
	* Display the survey access codes tab
	*
	* @access private
	*/
	function codesObject()
	{
		$this->handleWriteAccess();

		if ($this->object->getAnonymize() != 1)
		{
			return ilUtil::sendInfo($this->lng->txt("survey_codes_no_anonymization"));
		}
		global $rbacsystem;
		global $ilUser;
		
		$default_lang = $ilUser->getPref("survey_code_language");
		$tableoutput = "";
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_codes.html", true);
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$codecount = $this->object->getSurveyCodesCount();
			if ($codecount)
			{
				$maxentries = $ilUser->getPref("hits_per_page");
				if ($maxentries < 1)
				{
					$maxentries = 9999;
				}
	
				$survey_codes =& $this->object->getSurveyCodesTableData($default_lang, $_GET["offset"], $maxentries, $_GET["sort_by"], $_GET["sort_order"]);
				$headervars = array("", "counter", "date", "used", "url");
	
				include_once "./Services/Table/classes/class.ilTableGUI.php";
				$tbl = new ilTableGUI(0, FALSE);
				$tbl->setTitle($this->lng->txt("survey_code"));
				$header_names = array(
					"",
					$this->lng->txt("survey_code"),
					$this->lng->txt("create_date"),
					$this->lng->txt("survey_code_used"),
					$this->lng->txt("survey_code_url")
				);
				$tbl->setHeaderNames($header_names);
	
				$tbl->disable("sort");
				$tbl->disable("auto_sort");
				$tbl->disable("title");
				$tbl->disable("form");
				$tbl->enable("action");
				$tbl->enable("select_all");
				$tbl->setLimit($maxentries);
				$tbl->setOffset($_GET["offset"]);
				$tbl->setData($survey_codes);
				$tbl->setMaxCount($codecount);
				$tbl->setOrderDirection($_GET["sort_order"]);
				$tbl->setSelectAllCheckbox("chb_code");
				$tbl->setFormName("form_codes");
				$tbl->addActionButton("deleteCodes", $this->lng->txt("delete"));
				$tbl->addActionButton("exportCodes", $this->lng->txt("export"));
	
				$header_params = $this->ctrl->getParameterArray($this, "codes");
				$tbl->setHeaderVars($headervars, $header_params);
				
				// footer
				$tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
				// render table
				$tableoutput = $tbl->render();
				$this->tpl->setCurrentBlock("exportall");
				$this->tpl->setVariable("VALUE_EXPORT_ALL_CODES", $this->lng->txt("export_all_survey_codes"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("emptyrow");
				$this->tpl->setVariable("NO_CODES", $this->lng->txt("survey_code_no_codes"));
				$this->tpl->parseCurrentBlock();
			}
			
			$languages = $this->lng->getInstalledLanguages();
			foreach ($languages as $lang)
			{
				$this->tpl->setCurrentBlock("option_lang");
				$this->tpl->setVariable("VALUE_LANG", $lang);
				$this->tpl->setVariable("TEXT_LANG", $this->lng->txt("lang_$lang"));
				if (strcmp($lang, $default_lang) == 0)
				{
					$this->tpl->setVariable("SELECTED_LANG", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "codes"));
			$this->tpl->setVariable("CODES_TABLE", $tableoutput);
			$this->tpl->setVariable("TEXT_CREATE", $this->lng->txt("create"));
			$this->tpl->setVariable("TEXT_SURVEY_CODES", $this->lng->txt("new_survey_codes"));
			$this->tpl->setVariable("TEXT_SURVEY_CODES_LANG", $this->lng->txt("survey_codes_lang"));
			$this->tpl->setVariable("TEXT_NO_LANGUAGE_SELECTED", $this->lng->txt("please_select"));
			$this->tpl->setVariable("VALUE_ACTIVATE", $this->lng->txt("select"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cannot_create_survey_codes"));
		}
	}
	
	/**
	* Delete a list of survey codes
	*
	* Delete a list of survey codes
	*
	* @access private
	*/
	function deleteCodesObject()
	{
		if (is_array($_POST["chb_code"]) && (count($_POST["chb_code"]) > 0))
		{
			foreach ($_POST["chb_code"] as $survey_code)
			{
				$this->object->deleteSurveyCode($survey_code);
			}
		}
		$this->codesObject();
	}
	
	/**
	* Exports a list of survey codes
	*
	* Exports a list of survey codes
	*
	* @access private
	*/
	function exportCodesObject()
	{
		if (is_array($_POST["chb_code"]) && (count($_POST["chb_code"]) > 0))
		{
			$export = $this->object->getSurveyCodesForExport($_POST["chb_code"]);
			ilUtil::deliverData($export, ilUtil::getASCIIFilename($this->object->getTitle() . ".txt"));
		}
		else
		{
			$this->codesObject();
		}
	}
	
	/**
	* Exports all survey codes
	*
	* Exports all survey codes
	*
	* @access private
	*/
	function exportAllCodesObject()
	{
		$export = $this->object->getSurveyCodesForExport(array());
		ilUtil::deliverData($export, ilUtil::getASCIIFilename($this->object->getTitle() . ".txt"));
	}
	
	/**
	* Create access codes for the survey
	*
	* Create access codes for the survey
	*
	* @access private
	*/
	function createSurveyCodesObject()
	{
		if (preg_match("/\d+/", $_POST["nrOfCodes"]))
		{
			$this->object->createSurveyCodes($_POST["nrOfCodes"]);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("enter_valid_number_of_codes"), true);
		}
		$this->ctrl->redirect($this, "codes");
	}

	/**
	* Display the form to add preconditions for survey questions
	*
	* Display the form to add preconditions for survey questions
	*
	* @access private
	*/
	function addConstraintForm($step, $postvalues, &$survey_questions, $questions = FALSE)
	{
		$this->ctrl->saveParameter($this, "preid");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_add_constraint.html", "Modules/Survey");
		if (is_array($questions))
		{
			foreach ($questions as $question)
			{
				$this->tpl->setCurrentBlock("option_q");
				$this->tpl->setVariable("OPTION_VALUE", $question["question_id"]);
				$this->tpl->setVariable("OPTION_TEXT", $question["title"] . " (" . $this->lng->txt($question["type_tag"]) . ")");
				if ($question["question_id"] == $postvalues["q"])
				{
					$this->tpl->setVariable("OPTION_CHECKED", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		if ($step > 1)
		{
			$relations = $this->object->getAllRelations();
			foreach ($relations as $rel_id => $relation)
			{
				if (in_array($relation["short"], $survey_questions[$postvalues["q"]]["availableRelations"]))
				{
					$this->tpl->setCurrentBlock("option_r");
					$this->tpl->setVariable("OPTION_VALUE", $rel_id);
					$this->tpl->setVariable("OPTION_TEXT", $relation["short"]);
					if ($rel_id == $postvalues["r"])
					{
						$this->tpl->setVariable("OPTION_CHECKED", " selected=\"selected\"");
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("select_relation");
			$this->tpl->setVariable("SELECT_RELATION", $this->lng->txt("step") . " 2: " . $this->lng->txt("select_relation"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($step > 2)
		{
			$variables =& $this->object->getVariables($postvalues["q"]);
			$question_type = $survey_questions[$postvalues["q"]]["type_tag"];
			include_once "./Modules/SurveyQuestionPool/classes/class.$question_type.php";
			$question = new $question_type();
			$question->loadFromDb($postvalues["q"]);
			$select_value = $question->getPreconditionSelectValue($postvalues["v"]);
			$this->tpl->setCurrentBlock("select_value");
			$this->tpl->setVariable("SELECT_VALUE", $select_value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("buttons");
		$this->tpl->setVariable("BTN_CONTINUE", $this->lng->txt("continue"));
		switch ($step)
		{
			case 1:
				$this->tpl->setVariable("COMMAND", "constraintStep2");
				$this->tpl->setVariable("COMMAND_BACK", "constraints");
				break;
			case 2:
				$this->tpl->setVariable("COMMAND", "constraintStep3");
				$this->tpl->setVariable("COMMAND_BACK", "constraintStep1");
				break;
			case 3:
				$this->tpl->setVariable("COMMAND", "constraintsAdd");
				$this->tpl->setVariable("COMMAND_BACK", "constraintStep2");
				break;
		}
		$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$title = "";
		if ($survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["questionblock_id"] > 0)
		{
			$title = $this->lng->txt("questionblock") . ": " . $survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["questionblock_title"];
		}
		else
		{
			$title = $this->lng->txt($survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["type_tag"]) . ": " . $survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["title"];
		}
		$this->tpl->setVariable("CONSTRAINT_QUESTION_TEXT", $title);
		$this->tpl->setVariable("SELECT_PRIOR_QUESTION", $this->lng->txt("step") . " 1: " . $this->lng->txt("select_prior_question"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this) . "&start=" . $_GET["start"]);
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Add a precondition for a survey question or question block
	*
	* Add a precondition for a survey question or question block
	*
	* @access private
	*/
	function constraintsAddObject()
	{
		if (strlen($_POST["v"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("msg_enter_value_for_valid_constraint"));
			return $this->constraintStep3Object();
		}
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$include_elements = $_SESSION["includeElements"];
		foreach ($include_elements as $elementCounter)
		{
			if (is_array($structure[$elementCounter]))
			{
				foreach ($structure[$elementCounter] as $key => $question_id)
				{
					if (strlen($_GET["preid"]))
					{
						$this->object->updateConstraint($question_id, $_POST["q"], $_POST["r"], $_POST["v"]);
					}
					else
					{
						$this->object->addConstraint($question_id, $_POST["q"], $_POST["r"], $_POST["v"]);
					}
				}
			}
		}
		unset($_SESSION["includeElements"]);
		unset($_SESSION["constraintstructure"]);
		$this->ctrl->redirect($this, "constraints");
	}

	/**
	* Handles the third step of the precondition add action
	*
	* Handles the third step of the precondition add action
	*
	* @access private
	*/
	function constraintStep3Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		if (strlen($_GET["precondition"]))
		{
			$pc = $this->object->getPrecondition($_GET["precondition"]);
			$postvalues = array(
				"q" => $pc["question_fi"],
				"r" => $pc["relation_id"],
				"v" => $pc["value"]
			);
			$this->ctrl->setParameter($this, "preid", $_GET["precondition"]);
			array_push($option_questions, array("question_id" => $pc["question_fi"], "title" => $survey_questions[$pc["question_fi"]]["title"], "type_tag" => $survey_questions[$pc["question_fi"]]["type_tag"]));
			$this->addConstraintForm(3, $postvalues, $survey_questions, $option_questions);
		}
		else
		{
			array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
			$this->addConstraintForm(3, $_POST, $survey_questions, $option_questions);
		}
	}
	
	/**
	* Handles the second step of the precondition add action
	*
	* Handles the second step of the precondition add action
	*
	* @access private
	*/
	function constraintStep2Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
		$this->addConstraintForm(2, $_POST, $survey_questions, $option_questions);
	}
	
	/**
	* Handles the first step of the precondition add action
	*
	* Handles the first step of the precondition add action
	*
	* @access private
	*/
	function constraintStep1Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$start = $_GET["start"];
		$option_questions = array();
		for ($i = 1; $i < $start; $i++)
		{
			if (is_array($structure[$i]))
			{
				foreach ($structure[$i] as $key => $question_id)
				{
					if ($survey_questions[$question_id]["usableForPrecondition"])
					{
						array_push($option_questions, array("question_id" => $survey_questions[$question_id]["question_id"], "title" => $survey_questions[$question_id]["title"], "type_tag" => $survey_questions[$question_id]["type_tag"]));
					}
				}
			}
		}
		if (count($option_questions) == 0)
		{
			unset($_SESSION["includeElements"]);
			unset($_SESSION["constraintstructure"]);
			ilUtil::sendInfo($this->lng->txt("constraints_no_nonessay_available"), true);
			$this->ctrl->redirect($this, "constraints");
		}
		$this->addConstraintForm(1, $_POST, $survey_questions, $option_questions);
	}
	
	/**
	* Delete constraints of a survey
	*
	* Delete constraints of a survey
	*
	* @access private
	*/
	function deleteConstraintsObject()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^constraint_(\d+)_(\d+)/", $key, $matches)) 
			{
				foreach ($structure[$matches[1]] as $key => $question_id)
				{
					$this->object->deleteConstraint($matches[2], $question_id);
				}
			}
		}

		$this->ctrl->redirect($this, "constraints");
	}
	
	function createConstraintsObject()
	{
		$include_elements = $_POST["includeElements"];
		if ((!is_array($include_elements)) || (count($include_elements) == 0))
		{
			ilUtil::sendInfo($this->lng->txt("constraints_no_questions_or_questionblocks_selected"), true);
			$this->ctrl->redirect($this, "constraints");
		}
		else if (count($include_elements) >= 1)
		{
			$_SESSION["includeElements"] = $include_elements;
			sort($include_elements, SORT_NUMERIC);
			$_GET["start"] = $include_elements[0];
			$this->constraintStep1Object();
		}
	}
	
	function editPreconditionObject()
	{
		$_SESSION["includeElements"] = array($_GET["start"]);
		$this->ctrl->setParameter($this, "precondition", $_GET["precondition"]);
		$this->ctrl->setParameter($this, "start", $_GET["start"]);
		$this->ctrl->redirect($this, "constraintStep3");
	}
	
	/**
	* Administration page for survey constraints
	*
	* Administration page for survey constraints
	*
	* @access public
	*/
	function constraintsObject()
	{
		$this->handleWriteAccess();

		global $rbacsystem;
		
		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		$step = 0;
		if (array_key_exists("step", $_GET))	$step = $_GET["step"];
		switch ($step)
		{
			case 1:
				$this->constraintStep1Object();
				return;
				break;
			case 2:
				return;
				break;
			case 3:
				return;
				break;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_constraints_list.html", "Modules/Survey");
		$survey_questions =& $this->object->getSurveyQuestions();
		$last_questionblock_title = "";
		$counter = 1;
		$hasPreconditions = FALSE;
		$structure = array();
		$colors = array("tblrow1", "tblrow2");
		foreach ($survey_questions as $question_id => $data)
		{
			$title = $data["title"];
			$show = true;
			if ($data["questionblock_id"] > 0)
			{
				$title = $data["questionblock_title"];
				$type = $this->lng->txt("questionblock");
				if (strcmp($title, $last_questionblock_title) != 0) 
				{
					$last_questionblock_title = $title;
					$structure[$counter] = array();
					array_push($structure[$counter], $data["question_id"]);
				}
				else
				{
					array_push($structure[$counter-1], $data["question_id"]);
					$show = false;
				}
			}
			else
			{
				$structure[$counter] = array($data["question_id"]);
				$type = $this->lng->txt("question");
			}
			if ($show)
			{
				if ($counter == 1)
				{
					$this->tpl->setCurrentBlock("description");
					$this->tpl->setVariable("DESCRIPTION", $this->lng->txt("constraints_first_question_description"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$constraints =& $this->object->getConstraints($data["question_id"]);
					$rowcount = 0;
					if (count($constraints))
					{
						$hasPreconditions = TRUE;
						foreach ($constraints as $constraint)
						{
							$this->tpl->setCurrentBlock("constraint");
							$this->tpl->setVariable("CONSTRAINT_TEXT", $survey_questions[$constraint["question"]]["title"] . " " . $constraint["short"] . " " . $constraint["valueoutput"]);
							$this->tpl->setVariable("SEQUENCE_ID", $counter);
							$this->tpl->setVariable("CONSTRAINT_ID", $constraint["id"]);
							$this->tpl->setVariable("COLOR_CLASS", $colors[$rowcount % 2]);
							$this->tpl->setVariable("TEXT_EDIT_PRECONDITION", $this->lng->txt("edit"));
							$this->ctrl->setParameter($this, "precondition", $constraint["id"]);
							$this->ctrl->setParameter($this, "start", $counter);
							$this->tpl->setVariable("EDIT_PRECONDITION", $this->ctrl->getLinkTarget($this, "editPrecondition"));
							$this->ctrl->setParameter($this, "precondition", "");
							$this->ctrl->setParameter($this, "start", "");
							$rowcount++;
							$this->tpl->parseCurrentBlock();
						}
					}
				}
				if ($counter != 1)
				{
					$this->tpl->setCurrentBlock("include_elements");
					$this->tpl->setVariable("QUESTION_NR", "$counter");
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("constraint_section");
				$this->tpl->setVariable("QUESTION_NR", "$counter");
				$this->tpl->setVariable("TITLE", "$title");
				$icontype = "question.gif";
				if ($data["questionblock_id"] > 0)
				{
					$icontype = "questionblock.gif";
					$this->tpl->setVariable("TYPE", "$type: ");
				}
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ICON_HREF", ilUtil::getImagePath($icontype, "Modules/Survey"));
				$this->tpl->setVariable("ICON_ALT", $type);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets)
		{
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$counter++;
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			if ($hasPreconditions)
			{
				$this->tpl->setVariable("SELECT_ALL_PRECONDITIONS", $this->lng->txt("select_all"));
			}
			$this->tpl->parseCurrentBlock();

			if ($hasPreconditions)
			{
				$this->tpl->setCurrentBlock("delete_button");
				$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("buttons");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->setVariable("BTN_CREATE_CONSTRAINTS", $this->lng->txt("constraint_add"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CONSTRAINTS_INTRODUCTION", $this->lng->txt("constraints_introduction"));
		$this->tpl->setVariable("DEFINED_PRECONDITIONS", $this->lng->txt("existing_constraints"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "constraints"));
		$this->tpl->setVariable("CONSTRAINTS_HEADER", $this->lng->txt("constraints_list_of_entities"));
		$this->tpl->parseCurrentBlock();
		$_SESSION["constraintstructure"] = $structure;
		if ($hasDatasets)
		{
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning"));
		}
	}

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}
	
	function setNewTemplate()
	{
		global $tpl;
		$tpl = new ilTemplate("tpl.il_svy_svy_main.html", TRUE, TRUE, "Modules/Survey");
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
		$tpl->setVariable("LOCATION_JAVASCRIPT",dirname($location_stylesheet));
	}
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;
		global $ilUser;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		include_once "./Modules/Survey/classes/class.ilSurveyExecutionGUI.php";
		$output_gui =& new ilSurveyExecutionGUI($this->object);
		$info->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));
		$info->enablePrivateNotes();
		$anonymize_key = NULL;
		if ($this->object->getAnonymize() == 1)
		{
			if ($_SESSION["anonymous_id"])
			{
				$anonymize_key = $_SESSION["anonymous_id"];
			}
			else if ($_POST["anonymous_id"])
			{
				$anonymize_key = $_POST["anonymous_id"];
			}
		}
		$canStart = $this->object->canStartSurvey($anonymize_key);
		$showButtons = $canStart["result"];
		if (!$showButtons) ilUtil::sendInfo(implode("<br />", $canStart["messages"]));

		if ($showButtons)
		{
			// output of start/resume buttons for personalized surveys
			if (!$this->object->getAnonymize())
			{
				$survey_started = $this->object->isSurveyStarted($ilUser->getId(), "");
				// Anonymous User tries to start a personalized survey
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					ilUtil::sendInfo($this->lng->txt("anonymous_with_personalized_survey"));
				}
				else
				{
					if ($survey_started === 1)
					{
						ilUtil::sendInfo($this->lng->txt("already_completed_survey"));
					}
					elseif ($survey_started === 0)
					{
						$info->addFormButton("resume", $this->lng->txt("resume_survey"));
					}
					elseif ($survey_started === FALSE)
					{
						$info->addFormButton("start", $this->lng->txt("start_survey"));
					}
				}
			}
			// output of start/resume buttons for anonymized surveys
			else if ($this->object->getAnonymize() && !$this->object->isAccessibleWithoutCode())
			{
				if (($_SESSION["AccountId"] == ANONYMOUS_USER_ID) && (strlen($_POST["anonymous_id"]) == 0) && (strlen($_SESSION["anonymous_id"]) == 0))
				{
					$info->setFormAction($this->ctrl->getFormAction($this, "infoScreen"));
					$info->addSection($this->lng->txt("anonymization"));
					$info->addProperty("", $this->lng->txt("anonymize_anonymous_introduction"));
					$info->addPropertyTextinput($this->lng->txt("enter_anonymous_id"), "anonymous_id", "", 8, "infoScreen", $this->lng->txt("submit"));
				}
				else
				{
					if (strlen($_POST["anonymous_id"]) > 0)
					{
						if (!$this->object->checkSurveyCode($_POST["anonymous_id"]))
						{
							ilUtil::sendInfo($this->lng->txt("wrong_survey_code_used"));
						}
						else
						{
							$anonymize_key = $_POST["anonymous_id"];
						}
					}
					else if (strlen($_SESSION["anonymous_id"]) > 0)
					{
						if (!$this->object->checkSurveyCode($_SESSION["anonymous_id"]))
						{
							ilUtil::sendInfo($this->lng->txt("wrong_survey_code_used"));
						}
						else
						{
							$anonymize_key = $_SESSION["anonymous_id"];
						}
					}
					else
					{
						// registered users do not need to know that there is an anonymous key. The data is anonymized automatically
						$anonymize_key = $this->object->getUserAccessCode($ilUser->getId());
						if (!strlen($anonymize_key))
						{
							$anonymize_key = $this->object->createNewAccessCode();
							$this->object->saveUserAccessCode($ilUser->getId(), $anonymize_key);
						}
					}
					$info->addHiddenElement("anonymous_id", $anonymize_key);
					$survey_started = $this->object->isSurveyStarted($ilUser->getId(), $anonymize_key);
					if ($survey_started === 1)
					{
						ilUtil::sendInfo($this->lng->txt("already_completed_survey"));
					}
					elseif ($survey_started === 0)
					{
						$info->addFormButton("resume", $this->lng->txt("resume_survey"));
					}
					elseif ($survey_started === FALSE)
					{
						$info->addFormButton("start", $this->lng->txt("start_survey"));
					}
				}
			}
			else
			{
				// free access
				$survey_started = $this->object->isSurveyStarted($ilUser->getId(), "");
				if ($survey_started === 1)
				{
					ilUtil::sendInfo($this->lng->txt("already_completed_survey"));
				}
				elseif ($survey_started === 0)
				{
					$info->addFormButton("resume", $this->lng->txt("resume_survey"));
				}
				elseif ($survey_started === FALSE)
				{
					$info->addFormButton("start", $this->lng->txt("start_survey"));
				}
			}
		}
		
		if (strlen($this->object->getIntroduction()))
		{
			$introduction = $this->object->getIntroduction();
			$info->addSection($this->lng->txt("introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($introduction));
		}
		
		$info->addSection($this->lng->txt("svy_general_properties"));
		$info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
		$info->addProperty($this->lng->txt("title"), $this->object->getTitle());
		switch ($this->object->getAnonymize())
		{
			case ANONYMIZE_OFF:
				$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_personalized"));
				break;
			case ANONYMIZE_ON:
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_with_code"));
				}
				else
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_registered_user"));
				}
				break;
			case ANONYMIZE_FREEACCESS:
				$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_without_code"));
				break;
		}
		include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if ($ilAccess->checkAccess("write", "", $this->ref_id) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
		{
			$info->addProperty($this->lng->txt("evaluation_access"), $this->lng->txt("evaluation_access_info"));
		}
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		$this->ctrl->forwardCommand($info);
	}

	/**
	* Creates a print view of the survey questions
	*
	* Creates a print view of the survey questions
	*
	* @access public
	*/
	function printViewObject()
	{
		global $ilias;
		
		$this->questionsSubtabs("printview");
		$template = new ilTemplate("tpl.il_svy_svy_printview.html", TRUE, TRUE, "Modules/Survey");

		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "printView"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		$pages =& $this->object->getSurveyPages();
		foreach ($pages as $page)
		{
			if (count($page) > 0)
			{
				foreach ($page as $question)
				{
					$questionGUI = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
					if (is_object($questionGUI))
					{
						if (strlen($question["heading"]))
						{
							$template->setCurrentBlock("textblock");
							$template->setVariable("TEXTBLOCK", $question["heading"]);
							$template->parseCurrentBlock();
						}
						$template->setCurrentBlock("question");
						$template->setVariable("QUESTION_DATA", $questionGUI->getPrintView($this->object->getShowQuestionTitles(), $question["questionblock_show_questiontext"]));
						$template->parseCurrentBlock();
					}
				}
				if (count($page) > 1)
				{
					$template->setCurrentBlock("page");
					$template->setVariable("BLOCKTITLE", $page[0]["questionblock_title"]);
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("page");
					$template->parseCurrentBlock();
				}
			}
		}
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", TRUE, TRUE, "Modules/Test");
			$printbody->setVariable("TITLE", sprintf($this->lng->txt("tst_result_user_name"), $uname));
			$printbody->setVariable("ADM_CONTENT", $template->get());
			$printoutput = $printbody->get();
			$printoutput = preg_replace("/href=\".*?\"/", "", $printoutput);
			$fo = $this->object->processPrintoutput2FO($printoutput);
			$this->object->deliverPDFfromFO($fo);
		}
		else
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
		}
	}
	
	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "next":
			case "previous":
			case "start":
			case "resume":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
			case "evaluation":
			case "checkEvaluationAccess":
			case "evaluationdetails":
			case "evaluationuser":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"), "", $_GET["ref_id"]);
				break;
			case "create":
			case "save":
			case "cancel":
			case "importFile":
			case "cloneAll":
				break;
			case "infoScreen":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
		default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
	}
	
	/**
	* Set the subtabs for the questions tab
	*
	* Set the subtabs for the questions tab
	*
	* @access private
	*/
	function questionsSubtabs($a_cmd)
	{
		$questions = ($a_cmd == 'questions') ? true : false;
		$printview = ($a_cmd == 'printview') ? true : false;

		$this->tabs_gui->addSubTabTarget("survey_question_editor", $this->ctrl->getLinkTarget($this, "questions"),
										 "", "", "", $questions);
		$this->tabs_gui->addSubTabTarget("print_view", $this->ctrl->getLinkTarget($this, "printView"),
											"", "", "", $printview);
	}
	/**
	* Set the tabs for the evaluation output
	*
	* Set the tabs for the evaluation output
	*
	* @access private
	*/
	function setEvalSubtabs()
	{
		global $ilTabs;
		global $ilAccess;

		$ilTabs->addSubTabTarget(
			"svy_eval_cumulated", 
			$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"), 
			array("evaluation", "checkEvaluationAccess"),	
			""
		);

		$ilTabs->addSubTabTarget(
			"svy_eval_detail", 
			$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluationdetails"), 
			array("evaluationdetails"),	
			""
		);
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addSubTabTarget(
				"svy_eval_user", 
				$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluationuser"), 
				array("evaluationuser"),	
				""
			);
		}
	}

	function setBrowseForQuestionsSubtabs()
	{
		global $ilAccess;
		global $ilTabs;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $this->ctrl->getLinkTarget($this, "questions"));
			$ilTabs->addTarget("browse_for_questions",
				$this->ctrl->getLinkTarget($this, "browseForQuestions"),
				 array("browseForQuestions", 
				 "filterQuestions", "resetFilterQuestions", "changeDatatype", "insertQuestions",),
				"", ""
			);
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser;
		
		switch ($this->ctrl->getCmd())
		{
			case "browseForQuestions":
			case "insertQuestions":
			case "filterQuestions":
			case "resetFilterQuestions":
			case "changeDatatype":

			case "start":
			case "resume":
			case "next":
			case "previous":
				return;
				break;
			case "evaluation":
			case "checkEvaluationAccess":
			case "evaluationdetails":
			case "evaluationuser":
				$this->setEvalSubtabs();
				break;
		}
		
		// questions
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
				? true
				: false;
	
			$tabs_gui->addTarget("survey_questions",
				 $this->ctrl->getLinkTarget($this,'questions'),
				 array("questions", "browseForQuestions", "createQuestion",
				 "filterQuestions", "resetFilterQuestions", "changeDatatype", "insertQuestions",
				 "removeQuestions", "cancelRemoveQuestions", "confirmRemoveQuestions",
				 "defineQuestionblock", "saveDefineQuestionblock", "cancelDefineQuestionblock",
				 "unfoldQuestionblock", "moveQuestions",
				 "insertQuestionsBefore", "insertQuestionsAfter", "saveObligatory",
				 "addHeading", "saveHeading", "cancelHeading", "editHeading",
				 "confirmRemoveHeading", "cancelRemoveHeading", "printView"),
				 "", "", $force_active);
		}
		
		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$tabs_gui->addTarget("info",
				 $this->ctrl->getLinkTarget($this,'infoScreen'),
				 array("infoScreen", "showSummary"));
		}
			
		// properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$force_active = ($this->ctrl->getCmd() == "")
				? true
				: false;
			$tabs_gui->addTarget("properties",
				 $this->ctrl->getLinkTarget($this,'properties'),
				 array("properties", "save", "cancel"), "",
				 "", $force_active);
		}

		// questions
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// meta data
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", "ilmdeditorgui");
	
			// constraints
			$tabs_gui->addTarget("constraints",
				 $this->ctrl->getLinkTarget($this, "constraints"),
				 array("constraints", "constraintStep1", "constraintStep2",
				 "constraintStep3", "constraintsAdd", "createConstraints",
				"editPrecondition"),
				 "");
		}
		if (($ilAccess->checkAccess("write", "", $this->ref_id)) || ($ilAccess->checkAccess("invite", "", $this->ref_id)))
		{
			// invite
			$tabs_gui->addTarget("invitation",
				 $this->ctrl->getLinkTarget($this, "invite"),
				 array("invite", "saveInvitationStatus",
				 "cancelInvitationStatus", "searchInvitation", "inviteUserGroup",
				 "disinviteUserGroup"),
				 "");
		}
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// export
			$tabs_gui->addTarget("export",
				 $this->ctrl->getLinkTarget($this,'export'),
				 array("export", "createExportFile", "confirmDeleteExportFile",
				 "downloadExportFile"), 
				 ""
				);
	
			// maintenance
			$tabs_gui->addTarget("maintenance",
				 $this->ctrl->getLinkTarget($this,'maintenance'),
				 array("maintenance", "deleteAllUserData"),
				 "");

			if ($this->object->getAnonymize() == 1)
			{
				// code
				$tabs_gui->addTarget("codes",
					 $this->ctrl->getLinkTarget($this,'codes'),
					 array("codes", "createSurveyCodes", "setCodeLanguage", "deleteCodes", "exportCodes"),
					 "");
			}
		}

		include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if ($ilAccess->checkAccess("write", "", $this->ref_id) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
		{
			// evaluation
			$tabs_gui->addTarget("svy_evaluation",
				 $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"),
				 array("evaluation", "checkEvaluationAccess", "evaluationdetails",
				 	"evaluationuser"),
				 "");
		}
				 
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// permissions
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target, $a_access_code = "")
	{
		global $ilAccess, $ilErr, $lng;
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			if (strlen($a_access_code))
			{
				$_SESSION["anonymous_id"] = $a_access_code;
				$_GET["baseClass"] = "ilObjSurveyGUI";
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("ilias.php");
				exit;
			}
			else
			{
				$_GET["baseClass"] = "ilObjSurveyGUI";
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("ilias.php");
				exit;
			}
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

} // END class.ilObjSurveyGUI
?>
