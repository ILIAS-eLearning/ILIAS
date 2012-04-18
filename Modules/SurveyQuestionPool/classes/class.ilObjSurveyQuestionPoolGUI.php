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

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Class ilObjSurveyQuestionPoolGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMatrixQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilSurveyPhrasesGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilMDEditorGUI, ilPermissionGUI, ilObjectCopyGUI
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
	public function ilObjSurveyQuestionPoolGUI()
	{
		global $lng, $ilCtrl;

		$this->type = "spl";
		$lng->loadLanguageModule("survey");
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "calling_survey", "new_for_survey", "pgov", "pgov_pos"));

		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
	}

	/**
	* execute command
	*/
	public function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory;
		
		if ((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) && (!$ilAccess->checkAccess("visible", "", $_GET["ref_id"])))
		{
			global $ilias;
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&cmd=questions&ref_id=".$_GET["ref_id"], "spl");
		}

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "survey.css", "Modules/Survey"), "screen");
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
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case "ilsurveyphrasesgui":
				include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrasesGUI.php");
				$phrases_gui =& new ilSurveyPhrasesGUI($this);
				$ret =& $this->ctrl->forwardCommand($phrases_gui);
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('spl');
				$this->ctrl->forwardCommand($cp);
				break;
			
			case 'ilinfoscreengui':
				$this->infoScreenForward();
				break;

			case "":
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
				
			default:
				include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
				$q_gui = SurveyQuestionGUI::_getQuestionGUI($q_type, $_GET["q_id"]);
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
	* Questionpool properties
	*/
	public function propertiesObject()
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0)) ? true : false;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'properties'));
		$form->setTitle($this->lng->txt("properties"));
		$form->setMultipart(false);
		$form->setId("properties");

		// online
		$online = new ilCheckboxInputGUI($this->lng->txt("spl_online_property"), "online");
		$online->setInfo($this->lng->txt("spl_online_property_description"));
		$online->setChecked($this->object->getOnline());
		$form->addItem($online);

		$form->addCommandButton("saveProperties", $this->lng->txt("save"));

		if ($save)
		{
			$form->checkInput();
		}
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save questionpool properties
	*/
	public function savePropertiesObject()
	{
		$qpl_online = $_POST["online"];
		if (strlen($qpl_online) == 0) $qpl_online = "0";
		$this->object->setOnline($qpl_online);
		$this->object->saveToDb();
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
		$this->ctrl->redirect($this, "properties");
	}
	

	/**
	* Copies checked questions in the questionpool to a clipboard
	*/
	public function copyObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->copyToClipboard($value);
			}
			ilUtil::sendInfo($this->lng->txt("spl_copy_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("spl_copy_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}	
	
	/**
	* mark one or more question objects for moving
	*/
	public function moveObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->moveToClipboard($value);
			}
			ilUtil::sendInfo($this->lng->txt("spl_move_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("spl_move_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* export a question
	*/
	public function exportQuestionObject()
	{
		if (is_array($_POST['q_id']) && count($_POST['q_id']) > 0)
		{
			$this->createExportFileObject($_POST['q_id']);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_export_select_none"));
			$this->questionsObject();
		}
	}
	
	/**
	* Creates a confirmation form to delete questions from the question pool
	*/
	public function deleteQuestionsObject()
	{
		global $rbacsystem;
		
		ilUtil::sendInfo();
		// create an array of all checked checkboxes
		$checked_questions = $_POST['q_id'];
		if (count($checked_questions) > 0) 
		{
			if ($rbacsystem->checkAccess('write', $this->ref_id)) 
			{
				ilUtil::sendQuestion($this->lng->txt("qpl_confirm_delete_questions"));
			} 
			else 
			{
				ilUtil::sendFailure($this->lng->txt("qpl_delete_rbac_error"));
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
		$infos = $this->object->getQuestionInfos($checked_questions);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		foreach ($infos as $data)
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->setVariable("TXT_TITLE", $data["title"]);
			$this->tpl->setVariable("TXT_DESCRIPTION", $data["description"]);
			$this->tpl->setVariable("TXT_TYPE", SurveyQuestion::_getQuestionTypeName($data["type_tag"]));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "q_id[]");
			$this->tpl->setVariable("HIDDEN_VALUE", $id);
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
	public function confirmDeleteQuestionsObject()
	{
		// delete questions after confirmation
		ilUtil::sendSuccess($this->lng->txt("qpl_questions_deleted"), true);
		foreach ($_POST['q_id'] as $q_id) 
		{
			$this->object->removeQuestion($q_id);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* cancel delete questions
	*/
	public function cancelDeleteQuestionsObject()
	{
		// delete questions after confirmation
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* paste questios from the clipboard into the question pool
	*/
	public function pasteObject()
	{
		if (array_key_exists("spl_clipboard", $_SESSION))
		{
			$this->object->pasteFromClipboard();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("spl_paste_no_objects"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* display the import form to import questions into the questionpool
	*/
	public function importQuestionsObject()
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
	public function uploadQuestionsObject()
	{
		// check if file was uploaded
		$source = $_FILES["qtidoc"]["tmp_name"];
		$error = 0;
		if (($source == 'none') || (!$source) || $_FILES["qtidoc"]["error"] > UPLOAD_ERR_OK)
		{
			$error = 1;
		}
		// check correct file type
		if (strpos("xml", $_FILES["qtidoc"]["type"]) !== FALSE)
		{
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
			$source = $full_path;
			$this->object->importObject($source, TRUE);
			unlink($source);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	public function filterQuestionBrowserObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
		$table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'questions');
	}
	
	public function resetfilterQuestionBrowserObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
		$table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'questions');
	}
	
	/**
	* list questions of question pool
	*/
	public function questionsObject($arrFilter = null)
	{
		global $rbacsystem;
		global $ilUser;

		if(get_class($this->object) == "ilObjSurvey")
		{
			if (($_GET["calling_survey"] > 0) || ($_GET["new_for_survey"] > 0))
			{
				$ref_id = $_GET["calling_survey"];
				if (!strlen($ref_id)) $ref_id = $_GET["new_for_survey"];
				$addurl = "";
				if (strlen($_GET["new_for_survey"]))
				{
					$addurl = "&new_id=" . $_GET["q_id"];
				}
				if ($_REQUEST["pgov"])
				{
					$addurl .= "&pgov=".$_REQUEST["pgov"];
					$addurl .= "&pgov_pos=".$_REQUEST["pgov_pos"];
				}

				ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=".$ref_id."&cmd=questions".$addurl);
			}
		}

		$this->object->purgeQuestions();

		$_SESSION['q_id_table_nav'] = $_GET['q_id_table_nav'];
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_questionbrowser.html", "Modules/SurveyQuestionPool");
		include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
		$table_gui = new ilSurveyQuestionsTableGUI($this, 'questions', (($rbacsystem->checkAccess('write', $_GET['ref_id']) ? true : false)));
		$table_gui->setEditable($rbacsystem->checkAccess('write', $_GET['ref_id']));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getQuestionsData($arrFilter);
		$table_gui->setData($data);
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());	

		if ($rbacsystem->checkAccess('write', $_GET['ref_id']))
		{
			$this->tpl->setCurrentBlock("QTypes");
			$types =& ilObjSurveyQuestionPool::_getQuestionTypes();
			$lastquestiontype = $ilUser->getPref("svy_lastquestiontype");
			foreach ($types as $translation => $data)
			{
				if ($data["type_tag"] == $lastquestiontype)
				{
					$this->tpl->setVariable("QUESTION_TYPE_SELECTED", " selected=\"selected\"");
				}
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data["type_tag"]);
				$this->tpl->setVariable("QUESTION_TYPE", $translation);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("QUESTION_ADD", $this->lng->txt("create"));
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, 'questions'));
			$this->tpl->parseCurrentBlock();
		}
	}

	public function updateObject() 
	{
		$this->update = $this->object->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
	}
	
	public function afterSave(ilObject $a_new_object)
	{		
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		
		ilUtil::redirect("ilias.php?ref_id=".$a_new_object->getRefId().
			"&baseClass=ilObjSurveyQuestionPoolGUI");
	}		

	/*
	* list all export files
	*/
	public function exportObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionPoolExportTableGUI.php";
		$table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export');
		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		foreach ($export_files as $exp_file)
		{
			$file_arr = explode("__", $exp_file);
			array_push($data, array('file' => $exp_file, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => filesize($export_dir."/".$exp_file)));
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* create export file
	*/
	public function createExportFileObject($questions = null)
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
	public function downloadExportFileObject()
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
	public function confirmDeleteExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, "export");
		}

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));
		include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionPoolExportTableGUI.php";
		$table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export', true);
		$export_dir = $this->object->getExportDirectory();
		$data = array();
		foreach ($_POST['file'] as $exp_file)
		{
			$file_arr = explode("__", $exp_file);
			array_push($data, array('file' => $exp_file, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => filesize($export_dir."/".$exp_file)));
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}


	/**
	* cancel deletion of export files
	*/
	public function cancelDeleteExportFileObject()
	{
		session_unregister("ilExportFiles");
		$this->ctrl->redirect($this, "export");
	}

	/**
	* delete export files
	*/
	public function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach($_POST['file'] as $file)
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

	protected function initImportForm($a_new_type)
	{
		$form = parent::initImportForm($a_new_type);
		$form->getItemByPostVar('importfile')->setSuffixes(array("zip", "xml"));
	
		return $form;
	}

	protected function initCreationForms($a_new_type)
	{
		$form = $this->initImportForm($a_new_type);
		
		$forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $form);

		return $forms;
	}

	/**
	* form for new survey object import
	*/
	public function importFileObject()
	{
		global $tpl, $ilErr;

		$parent_id = $_GET["ref_id"];
		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->checkPermissionBool("create", "", $new_type))
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}

		$this->lng->loadLanguageModule($new_type);
		$this->ctrl->setParameter($this, "new_type", $new_type);

		$form = $this->initImportForm($new_type);
		if ($form->checkInput())
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
			$newObj = new ilObjSurveyQuestionPool();
			$newObj->setType($new_type);
			$newObj->setTitle("dummy");
			$newObj->create(true);
			$this->putObjectInTree($newObj);

			$newObj->createImportDirectory();

			// copy uploaded file to import directory
			$upload = $_FILES["importfile"];
			$file = pathinfo($upload["name"]);
			$full_path = $newObj->getImportDirectory()."/".$upload["name"];
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::moveUploadedFile($upload["tmp_name"], $upload["name"], 
				$full_path);

			// import qti data
			$qtiresult = $newObj->importObject($full_path);

			ilUtil::sendSuccess($this->lng->txt("object_imported"),true);
			ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjSurveyQuestionPoolGUI");
		}
		
		// display form to correct errors
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	* create new question
	*/
	public function &createQuestionObject()
	{
		global $ilUser;
		$ilUser->writePref("svy_lastquestiontype", $_POST["sel_question_types"]);
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$q_gui->object->createNewQuestion();
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_POST["sel_question_types"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* edit question
	*/
	public function &editQuestionForSurveyObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $_GET["q_id"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "pgov", $_GET["pgov"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "pgov_pos", $_GET["pgov_pos"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create question from survey
	*/
	public function &createQuestionForSurveyObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI($_GET["sel_question_types"]);
		$q_gui->object->createNewQuestion();
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->setParameterByClass(get_class($q_gui), "pgov", $_GET["pgov"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "pgov_pos", $_GET["pgov_pos"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create preview of object
	*/
	public function &previewObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui =& SurveyQuestionGUI::_getQuestionGUI("", $_GET["preview"]);
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $_GET["preview"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "preview");
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
		$this->infoScreenForward();
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $ilErr, $ilAccess;
		
		if(!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();

		// standard meta data
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$this->ctrl->forwardCommand($info);
	}
	
	public function addLocatorItems()
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
	public function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilHelp;
		
		$ilHelp->setScreenIdComponent("spl");

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
		// questions
		$force_active = (($this->ctrl->getCmdClass() == "" &&
			$this->ctrl->getCmd() != "properties") ||
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
			 array("questions", "filterQuestionBrowser", "filter", "reset", "createQuestion", 
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
		
		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTarget($this, "infoScreen"),
				array("infoScreen", "showSummary"));		
		}

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			// properties
			$tabs_gui->addTarget("settings",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 "properties",
			 "", "");
			 
			// manage phrases
			$tabs_gui->addTarget("manage_phrases",
				 $this->ctrl->getLinkTargetByClass("ilsurveyphrasesgui", "phrases"),
				 array("phrases", "deletePhrase", "confirmDeletePhrase", "cancelDeletePhrase", "editPhrase", "newPhrase", "saveEditPhrase", "phraseEditor"),
				 "ilsurveyphrasesgui", "");
				 
			// meta data
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", "ilmdeditorgui");
				 
			// export
			$tabs_gui->addTarget("export",
				 $this->ctrl->getLinkTarget($this,'export'),
				 array("export", "createExportFile", "confirmDeleteExportFile",
				 "downloadExportFile", "cancelDeleteExportFile", "deleteExportFile"),
				 "", "");
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* Redirect script to call a survey question pool reference id
	*
	* @param integer $a_target The reference id of the question pool
	* @access	public
	*/
	public static function _goto($a_target)
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
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}
} // END class.ilObjSurveyQuestionPoolGUI
?>
