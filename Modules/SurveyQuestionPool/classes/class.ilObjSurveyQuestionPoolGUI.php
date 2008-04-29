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

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Class ilObjSurveyQuestionPoolGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyNominalQuestionGUI, SurveyMetricQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyOrdinalQuestionGUI, SurveyTextQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMatrixQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilSurveyPhrasesGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilMDEditorGUI, ilPermissionGUI
*
* @extends ilObjectGUI
* @ingroup ModulesSurveyQuestionPool
*/

class ilObjSurveyQuestionPoolGUI extends ilObjectGUI
{
	var $defaultscript;
	
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyQuestionPoolGUI()
	{
    global $lng, $ilCtrl;

		$this->type = "spl";
		$lng->loadLanguageModule("survey");
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "calling_survey", "new_for_survey"));

		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
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
				"ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&cmd=questions&ref_id=".$_GET["ref_id"], "spl");
		}

		$this->prepareOutput();
		$cmd = $this->ctrl->getCmd("questions");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "questions");
		if ($_GET["q_id"] < 1)
		{
			$q_type = ($_POST["sel_question_types"] != "")
				? $_POST["sel_question_types"]
				: $_GET["sel_question_types"];
		}
		switch($next_class)
		{
			case 'ilmdeditorgui':
				include_once "./Services/MetaData/classes/class.ilMDEditorGUI.php";
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case "ilsurveyphrasesgui":
				include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrasesGUI.php");
				$phrases_gui =& new ilSurveyPhrasesGUI($this);
				$ret =& $this->ctrl->forwardCommand($phrases_gui);
				break;

			case "":
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
				
			default:
				if (strlen($q_type))
				{
					$question_type_gui = $q_type . "GUI";
				}
				else
				{
					include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
					$question_type_gui = SurveyQuestion::_getQuestionType($_GET["q_id"]) . "GUI";
				}
				include_once "./Modules/SurveyQuestionPool/classes/class.$question_type_gui" . ".php";
				$q_gui = new $question_type_gui($_GET["q_id"]);
				$q_gui->object->setObjId($this->object->getId());
				$q_gui->setQuestionTabs();
				$ret =& $this->ctrl->forwardCommand($q_gui);
				break;
		}
		if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
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

		ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
			"&baseClass=ilObjSurveyQuestionPoolGUI");
	}
	
/**
* Cancels any action and displays the question browser
*
* Cancels any action and displays the question browser
*
* @param string $question_id Sets the id of a newly created question for a calling survey
* @access public
*/
	function cancelAction($question_id = "") 
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* Questionpool properties
	*/
	function propertiesObject()
	{
		global $rbacsystem;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_properties.html", "Modules/SurveyQuestionPool");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("spl_general_properties"));
		$this->tpl->setVariable("PROPERTY_ONLINE", $this->lng->txt("spl_online_property"));
		$this->tpl->setVariable("PROPERTY_ONLINE_DESCRIPTION", $this->lng->txt("spl_online_property_description"));
		if ($this->object->getOnline() == 1)
		{
			$this->tpl->setVariable("PROPERTY_ONLINE_CHECKED", " checked=\"checked\"");
		}
		if ($rbacsystem->checkAccess('write', $this->ref_id)) 
		{
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		}
		else
		{
			$this->tpl->setVariable("PROPERTY_ONLINE_DISABLED", " disabled=\"disabled\"");
		}
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save questionpool properties
	*/
	function savePropertiesObject()
	{
		$qpl_online = $_POST["online"];
		if (strlen($qpl_online) == 0) $qpl_online = "0";
		$this->object->setOnline($qpl_online);
		$this->object->saveToDb();
		ilUtil::sendInfo($this->lng->txt("saved_successfully"), true);
		$this->ctrl->redirect($this, "properties");
	}
	

/**
* Copies checked questions in the questionpool to a clipboard
*
* Copies checked questions in the questionpool to a clipboard
*
* @access public
*/
	function copyObject()
	{
    // create an array of all checked checkboxes
    $checked_questions = array();
    foreach ($_POST as $key => $value) 
		{
      if (preg_match("/cb_(\d+)/", $key, $matches)) 
			{
        array_push($checked_questions, $matches[1]);
      }
    }
		
		// copy button was pressed
		if (count($checked_questions) > 0) 
		{
			$_SESSION["spl_copied_questions"] = join($checked_questions, ",");
		} 
		else if (count($checked_questions) == 0) 
		{
			ilUtil::sendInfo($this->lng->txt("qpl_copy_select_none"));
			$_SESSION["spl_copied_questions"] = "";
		}
		$this->questionsObject();
	}	
	
	/**
	* export a question
	*/
	function exportQuestionsObject()
	{
		// create an array of all checked checkboxes
		$checked_questions = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/cb_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}
		
		// export button was pressed
		if (count($checked_questions) > 0)
		{
			$this->createExportFileObject($checked_questions);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_export_select_none"));
			$this->questionsObject();
		}
	}
	
/**
* Creates a confirmation form to delete questions from the question pool
*
* Creates a confirmation form to delete questions from the question pool
*
* @access public
*/
	function deleteQuestionsObject()
	{
		global $rbacsystem;
		
		ilUtil::sendInfo();
		// create an array of all checked checkboxes
		$checked_questions = array();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/cb_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questions, $matches[1]);
			}
		}
		
		if (count($checked_questions) > 0) 
		{
			if ($rbacsystem->checkAccess('write', $this->ref_id)) 
			{
				ilUtil::sendInfo($this->lng->txt("qpl_confirm_delete_questions"));
			} 
			else 
			{
				ilUtil::sendInfo($this->lng->txt("qpl_delete_rbac_error"));
				$this->questionsObject();
				return;
			}
		} 
		elseif (count($checked_questions) == 0) 
		{
			ilUtil::sendInfo($this->lng->txt("qpl_delete_select_none"));
			$this->questionsObject();
			return;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_delete_questions.html", "Modules/SurveyQuestionPool");
		$whereclause = join($checked_questions, " OR survey_question.question_id = ");
		$whereclause = " AND (survey_question.question_id = " . $whereclause . ")";
		$query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id$whereclause ORDER BY survey_question.title";
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(MDB2_FETCHMODE_OBJECT))
			{
				if (in_array($data->question_id, $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("TXT_TITLE", $data->title);
					$this->tpl->setVariable("TXT_DESCRIPTION", $data->description);
					$this->tpl->setVariable("TXT_TYPE", $this->lng->txt($data->type_tag));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* delete questions
	*/
	function confirmDeleteQuestionsObject()
	{
		// delete questions after confirmation
		ilUtil::sendInfo($this->lng->txt("qpl_questions_deleted"), true);
		$checked_questions = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}

		foreach ($checked_questions as $key => $value) {
			$this->object->removeQuestion($value);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* cancel delete questions
	*/
	function cancelDeleteQuestionsObject()
	{
		// delete questions after confirmation
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Creates a confirmation form to paste copied questions in the question pool
*
* Creates a confirmation form to paste copied questions in the question pool
*
* @access public
*/
	function pasteObject()
	{
		ilUtil::sendInfo();

    // create an array of all checked checkboxes
    $checked_questions = array();
    foreach ($_POST as $key => $value) {
      if (preg_match("/cb_(\d+)/", $key, $matches)) {
        array_push($checked_questions, $matches[1]);
      }
    }
		
		// paste button was pressed
		if (strcmp($_SESSION["spl_copied_questions"], "") != 0)
		{
			$copied_questions = split("/,/", $_SESSION["spl_copied_questions"]);
			ilUtil::sendInfo($this->lng->txt("qpl_past_questions_confirmation"));
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_paste_questions.html", "Modules/SurveyQuestionPool");
		$questions_info =& $this->object->getQuestionsInfo($copied_questions);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($questions_info as $data)
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->setVariable("TXT_TITLE", $data->title);
			$this->tpl->setVariable("TXT_DESCRIPTION", $data->description);
			$this->tpl->setVariable("TXT_TYPE", $this->lng->txt($data->type_tag));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		foreach ($questions_info as $data)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$data->question_id");
			$this->tpl->setVariable("HIDDEN_VALUE", $data->question_id);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* paste questions
	*/
	function confirmPasteQuestionsObject()
	{
		// paste questions after confirmation
		ilUtil::sendInfo($this->lng->txt("qpl_questions_pasted"), true);
		$checked_questions = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}
		foreach ($checked_questions as $key => $value) {
			$this->object->paste($value);
		}
		
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* cancel paste questions
	*/
	function cancelPasteQuestionsObject()
	{
		// delete questions after confirmation
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* display the import form to import questions into the questionpool
	*/
	function importQuestionsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_import_question.html", "Modules/SurveyQuestionPool");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_IMPORT_QUESTION", $this->lng->txt("import_question"));
		$this->tpl->setVariable("TEXT_SELECT_FILE", $this->lng->txt("select_file"));
		$this->tpl->setVariable("TEXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* imports question(s) into the questionpool
	*/
	function uploadQuestionsObject()
	{
		// check if file was uploaded
		$source = $_FILES["qtidoc"]["tmp_name"];
		$error = 0;
		if (($source == 'none') || (!$source) || $_FILES["qtidoc"]["error"] > UPLOAD_ERR_OK)
		{
//			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
			$error = 1;
		}
		// check correct file type
		if (strpos("xml", $_FILES["qtidoc"]["type"]) !== FALSE)
		{
//			$this->ilias->raiseError("Wrong file type!",$this->ilias->error_obj->MESSAGE);
			$error = 1;
		}
		if (!$error)
		{
			// import file into questionpool
			// create import directory
			$this->object->createImportDirectory();

			// copy uploaded file to import directory
			$full_path = $this->object->getImportDirectory()."/".$_FILES["qtidoc"]["name"];

			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::moveUploadedFile($_FILES["qtidoc"]["tmp_name"], 
				$_FILES["qtidoc"]["name"], $full_path);
			//move_uploaded_file($_FILES["qtidoc"]["tmp_name"], $full_path);
			$source = $full_path;
			$this->object->importObject($source, TRUE);
			unlink($source);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	function filterObject()
	{
		$this->questionsObject();
	}
	
	function resetObject()
	{
		$this->questionsObject();
	}
	
	/**
	* Displays the question browser
	* @access	public
	*/
	function questionsObject()
	{
		global $rbacsystem;
		global $ilUser;

		$lastquestiontype = $ilUser->getPref("svy_lastquestiontype");
		$filter_text = "";
		$filter_type = "";
		if (count($_POST))
		{
			$filter_text = $_POST["filter_text"];
			$filter_type = $_POST["sel_filter_type"];
		}
		else
		{
			$filter_text = $_GET["filter_text"];
			$filter_type = $_GET["sel_filter_type"];
		}
		
		if (strcmp($this->ctrl->getCmd(), "reset") == 0)
		{
			$filter_text = "";
			$filter_type = "";
		}
		else
		{
			$this->ctrl->setParameter($this, "filter_text", $filter_text);
			$this->ctrl->setParameter($this, "sel_filter_type", $filter_type);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_questions.html", "Modules/SurveyQuestionPool");
		if ($rbacsystem->checkAccess("write", $this->ref_id)) 
		{
			$this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_svy_qpl_create_new_question.html", "Modules/SurveyQuestionPool");
		}
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_qpl_action_buttons.html", "Modules/SurveyQuestionPool");
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_qpl_filter_questions.html", "Modules/SurveyQuestionPool");

		// create filter form
		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"description" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) 
		{
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (!$_POST["cmd"]["reset"]) 
			{
				if (strcmp($filter_type, $key) == 0) 
				{
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		if (strcmp($this->ctrl->getCmd(), "reset") != 0)
		{
			$this->tpl->setVariable("VALUE_FILTER_TEXT", $filter_text);
		}
		$this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->parseCurrentBlock();
    
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
		if (!$_GET["sort"])
		{
			// default sort order
			$_GET["sort"] = array("title" => "ASC");
		}
		$sort = ($_GET["sort"]) ? $_GET["sort"] : "title";
		$sortorder = ($_GET["sortorder"]) ? $_GET["sortorder"] : "ASC";
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$table = $this->object->getQuestionsTable($sort, $sortorder, $filter_text, $filter_type, $startrow);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$last_questionblock_id = 0;
		$editable = $rbacsystem->checkAccess('write', $this->ref_id);
		foreach ($table["rows"] as $data)
		{
			$this->tpl->setCurrentBlock("checkable");
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			$this->tpl->parseCurrentBlock();
			if ($data["complete"] == 0)
			{
				$this->tpl->setCurrentBlock("qpl_warning");
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("warning.gif"));
				$this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->parseCurrentBlock();
			}
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$classname = SurveyQuestion::_getQuestionType($data["question_id"]);
			$classnamegui = $classname . "GUI";
			$sel_question_types = $classname; 
			$this->ctrl->setParameterByClass(strtolower($classnamegui), "q_id", $data["question_id"]);
			$this->ctrl->setParameterByClass(strtolower($classnamegui), "sel_question_types", $sel_question_types);
			if ($editable)
			{
				$this->tpl->setCurrentBlock("url_edit");
				$this->tpl->setVariable("URL_EDIT", $this->ctrl->getLinkTargetByClass(strtolower($classnamegui), "editQuestion"));
				$this->tpl->setVariable("TEXT_EDIT", $this->lng->txt("edit"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
			$this->tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTargetByClass(strtolower($classnamegui), "preview"));
			$this->tpl->setVariable("TEXT_PREVIEW", $this->lng->txt("preview"));
			$this->tpl->setVariable("QUESTION_DESCRIPTION", $data["description"]);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
			$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
			include_once "./classes/class.ilFormat.php";
			$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
			$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["timestamp14"]), "date"));
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
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
				$this->tpl->setCurrentBlock("pages");
				if ($table["startrow"] == $i)
				{
					$this->tpl->setVariable("PAGE_NUMBER", "<span class=\"inactivepage\">$counter</span>");
				}
				else
				{
					$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&nextrow=$i" . "\">$counter</a>");
				}
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
			$this->tpl->setCurrentBlock("navigation_bottom");
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
			$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getLinkTarget($this, "questions") . "&prevrow=" . $table["prevrow"]);
			$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getLinkTarget($this, "questions") . "&nextrow=" . $table["nextrow"]);
			$this->tpl->parseCurrentBlock();
		}

		// if there are no questions, display a message
		if ($counter == 0) 
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
		if ($counter > 0) 
		{
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$counter++;
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
		$this->tpl->setCurrentBlock("copy");
		$this->tpl->setVariable("COPY", $this->lng->txt("copy"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("exportquestions");
		$this->tpl->setVariable("EXPORT", $this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();

		// create edit buttons & table footer
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("PASTE", $this->lng->txt("paste"));
			if (strcmp($_SESSION["spl_copied_questions"], "") == 0)
			{
				$this->tpl->setVariable("PASTE_DISABLED", " disabled=\"disabled\"");
			}
		}
    
		if ($rbacsystem->checkAccess("write", $this->ref_id)) 
		{
			// "create question" form
			$questiontypes =& $this->object->_getQuestiontypes();
			$this->tpl->setCurrentBlock("QTypes");
			foreach ($questiontypes as $questiontype)
			{
				if (strcmp($lastquestiontype, $questiontype) == 0)
				{
					$this->tpl->setVariable("QUESTION_TYPE_SELECTED", " selected=\"selected\"");
				}
				$this->tpl->setVariable("QUESTION_TYPE_ID", $questiontype);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($questiontype));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("CreateQuestion");
			$this->tpl->setVariable("QUESTION_ADD", $this->lng->txt("create"));
			$this->tpl->setVariable("QUESTION_IMPORT", $this->lng->txt("import"));
			$this->tpl->setVariable("ACTION_QUESTION_ADD", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		// define the sort column parameters
		$sortarray = array(
			"title" => (strcmp($sort, "title") == 0) ? $sortorder : "",
			"description" => (strcmp($sort, "description") == 0) ? $sortorder : "",
			"type" => (strcmp($sort, "type") == 0) ? $sortorder : "",
			"author" => (strcmp($sort, "author") == 0) ? $sortorder : "",
			"created" => (strcmp($sort, "created") == 0) ? $sortorder : "",
			"updated" => (strcmp($sort, "updated") == 0) ? $sortorder : ""
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
    
		$this->tpl->setCurrentBlock("adm_content");
		// create table header
		$this->ctrl->setParameterByClass(get_class($this), "startrow", $table["startrow"]);
		$this->ctrl->setParameter($this, "sort", "title");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["title"]);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
		$this->ctrl->setParameter($this, "sort", "description");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["description"]);
		$this->tpl->setVariable("QUESTION_DESCRIPTION", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["description"]);
		$this->ctrl->setParameter($this, "sort", "type");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["type"]);
		$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
		$this->ctrl->setParameter($this, "sort", "author");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["author"]);
		$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
		$this->ctrl->setParameter($this, "sort", "created");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["created"]);
		$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
		$this->ctrl->setParameter($this, "sort", "updated");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["updated"]);
		$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
		$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
		unset($_SESSION["calling_survey"]);
  }


	function updateObject() 
	{
		$this->update = $this->object->update();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
	}

	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;

		//$this->setTabs();

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
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/SurveyQuestionPool");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

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

		// delete button
		include_once "./Services/Utilities/classes/class.ilUtil.php";

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
	function createExportFileObject($questions = null)
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyQuestionpoolExport.php");
			$survey_exp = new ilSurveyQuestionpoolExport($this->object);
			$survey_exp->buildExportFile($questions);
			$this->ctrl->redirect($this, "export");
		}
		else
		{
			ilUtil::sendInfo("cannot_export_questionpool");
		}
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
			ilUtil::sendInfo($this->lng->txt("select_max_one_item"),true);
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
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, "export");
		}

		//$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/SurveyQuestionPool");

		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

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
		$buttons = array( 
			"deleteExportFile"  => $this->lng->txt("confirm"),
			"cancelDeleteExportFile"  => $this->lng->txt("cancel")
			);
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

	/**
	* display dialogue for importing questionpools
	*
	* @access	public
	*/
	function importObject()
	{
		global $rbacsystem;
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		$this->getTemplateFile("import", "spl");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_NAME", "uploadSpl");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_SPL", $this->lng->txt("import_spl"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
	}

	/**
	* imports question(s) into the questionpool
	*/
	function uploadSplObject($redirect = true)
	{
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendInfo($this->lng->txt("spl_select_file_for_import"));
			$this->importObject();
			return;
		}
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		// create new questionpool object
		$newObj = new ilObjSurveyQuestionPool();
		// set type of questionpool object
		$newObj->setType($_GET["new_type"]);
		// set title of questionpool object to "dummy"
		$newObj->setTitle("dummy");
		// set description of questionpool object to "dummy"
		//$newObj->setDescription("dummy");
		// create the questionpool class in the ILIAS database (object_data table)
		$newObj->create(true);
		// create a reference for the questionpool object in the ILIAS database (object_reference table)
		$newObj->createReference();
		// put the questionpool object in the administration tree
		$newObj->putInTree($_GET["ref_id"]);
		// get default permissions and set the permissions for the questionpool object
		$newObj->setPermissions($_GET["ref_id"]);
		// notify the questionpool object and all its parent objects that a "new" object was created
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create import directory
		$newObj->createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = $newObj->getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], 
			$_FILES["xmldoc"]["name"], $full_path);
		//move_uploaded_file($_FILES["xmldoc"]["tmp_name"], $full_path);

		// import qti data
		$qtiresult = $newObj->importObject($full_path);

		if ($redirect)
		{
			$this->ctrl->redirect($this, "cancel");
//			ilUtil::redirect("adm_object.php?".$this->link_params);
		}
		return $newObj->getRefId();
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
			$this->fillCloneTemplate('DUPLICATE','spl');
			$this->tpl->setCurrentBlock("adm_content");

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
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", ' target="'.
				ilFrameTargetInfo::_getFrame("MainContent").'" ');
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_SPL", $this->lng->txt("import_spl"));
			$this->tpl->setVariable("TXT_SPL_FILE", $this->lng->txt("spl_upload_file"));
			$this->tpl->setVariable("NEW_TYPE", $this->type);
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TYPE_IMG", ilUtil::getImagePath('icon_spl.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_spl"));
			$this->tpl->setVariable("TYPE_IMG2", ilUtil::getImagePath('icon_spl.gif'));
			$this->tpl->setVariable("ALT_IMG2",$this->lng->txt("obj_spl"));

			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* form for new survey object import
	*/
	function importFileObject()
	{
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("spl_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->ctrl->setParameter($this, "new_type", $this->type);
		$ref_id = $this->uploadSplObject(false);
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_imported"),true);

		ilUtil::redirect("ilias.php?ref_id=".$ref_id.
			"&baseClass=ilObjSurveyQuestionPoolGUI");
	}

	/**
	* create new question
	*/
	function &createQuestionObject()
	{
		global $ilUser;
		$ilUser->writePref("svy_lastquestiontype", $_POST["sel_question_types"]);
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$this->ctrl->setParameter($this, "sel_question_types", $_POST["sel_question_types"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* edit question
	*/
	function &editQuestionForSurveyObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $_GET["q_id"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create question from survey
	*/
	function &createQuestionForSurveyObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI($_GET["sel_question_types"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create preview of object
	*/
	function &previewObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI("", $_GET["preview"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $_GET["preview"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "preview");
	}

	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "create":
			case "importFile":
			case "cancel":
				break;
			default:
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
		if ($_GET["q_id"] > 0)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$q_type = SurveyQuestion::_getQuestionType($_GET["q_id"]) . "GUI";
			$this->ctrl->setParameterByClass($q_type, "q_id", $_GET["q_id"]);
			$ilLocator->addItem(SurveyQuestion::_getTitle($_GET["q_id"]), $this->ctrl->getLinkTargetByClass($q_type, "editQuestion"));
		}
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class)
		{
			case "":
			case "ilpermissiongui":
			case "ilmdeditorgui":
			case "ilsurveyphrasesgui":
				break;
			default:
				return;
				break;
		}
		if (($_GET["calling_survey"] > 0) || ($_GET["new_for_survey"] > 0)) return;
		// properties
		$tabs_gui->addTarget("properties",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 "properties", 
			 "", "");
		// questions
		$force_active = ($this->ctrl->getCmdClass() == "" ||
			$this->ctrl->getCmd() == "")
			? true
			: false;
		if (!$force_active)
		{
			if (is_array($_GET["sort"]))
			{
				$force_active = true;
			}
		}
		$tabs_gui->addTarget("survey_questions",
			 $this->ctrl->getLinkTarget($this,'questions'),
			 array("questions", "filter", "reset", "createQuestion", 
			 "importQuestions", "deleteQuestions", "copy", "paste", 
			 "exportQuestions", "confirmDeleteQuestions", "cancelDeleteQuestions",
			 "confirmPasteQuestions", "cancelPasteQuestions", "uploadQuestions",
			 "editQuestion", "addMaterial", "removeMaterial", "save", "cancel",
			 "cancelExplorer", "linkChilds", "addGIT", "addST", "addPG", "preview",
			 "moveCategory", "deleteCategory", "addPhrase", "addCategory", "savePhrase",
			 "addSelectedPhrase", "cancelViewPhrase", "confirmSavePhrase", "cancelSavePhrase",
			 "insertBeforeCategory", "insertAfterCategory", "confirmDeleteCategory",
			 "cancelDeleteCategory", "categories", "saveCategories", 
			 "savePhrase", "addPhrase"
			 ),
			 array("ilobjsurveyquestionpoolgui", "ilsurveyphrasesgui"), "", $force_active);

		global $rbacsystem;
		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			// meta data
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", "ilmdeditorgui");

			// manage phrases
			$tabs_gui->addTarget("manage_phrases",
				 $this->ctrl->getLinkTargetByClass("ilsurveyphrasesgui", "phrases"),
				 array("phrases", "deletePhrase", "confirmDeletePhrase", "cancelDeletePhrase"),
				 "ilsurveyphrasesgui", "");
		}

		// export
		$tabs_gui->addTarget("export",
			 $this->ctrl->getLinkTarget($this,'export'),
			 array("export", "createExportFile", "confirmDeleteExportFile", 
			 "downloadExportFile", "cancelDeleteExportFile", "deleteExportFile"),
			 "", "");

		// permissions
		$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
	}

	/**
	* Redirect script to call a survey question pool reference id
	* 
	* Redirect script to call a survey question pool reference id
	*
	* @param integer $a_target The reference id of the question pool
	* @access	public
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;
		if ($ilAccess->checkAccess("write", "", $a_target))
		{
			$_GET["baseClass"] = "ilObjSurveyQuestionPoolGUI";
			$_GET["cmd"] = "questions";
			$_GET["ref_id"] = $a_target;
			include_once("ilias.php");
			exit;
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
	

} // END class.ilObjSurveyQuestionPoolGUI
?>
