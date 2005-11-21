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
* Class ilObjQuestionPoolGUI
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version  $Id$
*
* @ilCtrl_Calls ilObjQuestionPoolGUI: ilPageObjectGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: ASS_MultipleChoiceGUI, ASS_ClozeTestGUI, ASS_MatchingQuestionGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: ASS_OrderingQuestionGUI, ASS_ImagemapQuestionGUI, ASS_JavaAppletGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: ASS_TextQuestionGUI, ilMDEditorGUI
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./assessment/classes/class.assQuestionGUI.php";
include_once "./assessment/classes/class.ilObjQuestionPool.php";

class ilObjQuestionPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjQuestionPoolGUI()
	{
		global $lng, $ilCtrl;
		$lng->loadLanguageModule("assessment");
		$this->type = "qpl";
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "test_ref_id", "calling_test"));
		//$this->id = $_GET["ref_id"];

		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
		if (strlen($this->ctrl->getModuleDir()) == 0)
		{
			$this->setTabTargetScript("adm_object.php");
			switch ($this->ctrl->getCmd())
			{
				case "create":
				case "importFile":
					break;
				default:
				$this->prepareOutput();
				return;
				break;
			}
			global $ilLocator;
			$ilLocator->addAdministrationItems();
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""));
			$this->tpl->setLocator();
		}
	}

	/**
	* execute command
	*/
	function &executeCommand($prepare_output = true)
	{
		global $ilLocator;		
		if ($prepare_output)
		{
			// Alle Repository Einträge der derzeitigen ref_id einfügen
			$ilLocator->addRepositoryItems();
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""));
		}
		$cmd = $this->ctrl->getCmd("questions");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "questions");
		if ($_GET["q_id"] < 1)
		{
			$q_type = ($_POST["sel_question_types"] != "")
				? $_POST["sel_question_types"]
				: $_GET["sel_question_types"];
		}

		if ($cmd != "createQuestion" && $cmd != "createQuestionForTest"
			&& $next_class != "ilpageobjectgui")
		{
			if (($_GET["test_ref_id"] != "") or ($_GET["calling_test"]))
			{
				$ref_id = $_GET["test_ref_id"];
				if (!$ref_id)
				{
					$ref_id = $_GET["calling_test"];
				}
				include_once "./classes/class.ilTabsGUI.php";
				$tabs_gui =& new ilTabsGUI();

				$tabs_gui->addTarget("back",
					"ilias.php?baseClass=ilObjTestGUI&ref_id=$ref_id&cmd=questions", "", "");

				// output tabs
				$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
			}
		}

		if ($prepare_output) $this->prepareOutput();

//echo "<br>nextclass:$next_class:cmd:$cmd:";
		switch($next_class)
		{
			case 'ilmdeditorgui':
				$this->setAdminTabs();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				$this->ctrl->forwardCommand($md_gui);
				break;
			case "ilpageobjectgui":
				include_once("classes/class.ilObjStyleSheet.php");
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();
		
				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				$this->setAdminTabs();
				$this->setQuestionTabs();
				include_once "./assessment/classes/class.assQuestionGUI.php";
				$q_gui =& ASS_QuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
				$q_gui->object->setObjId($this->object->getId());
				if ($_GET["q_id"] > 0)
				{
					$ilLocator->addItem($q_gui->object->getTitle(), $this->ctrl->getLinkTargetByClass($next_class, $_GET["cmd"]));
				}
				$question =& $q_gui->object;
				$this->tpl->setVariable("HEADER", $question->getTitle());
				$this->ctrl->saveParameter($this, "q_id");
				$count = $question->isInUse();
				if ($count)
				{
					global $rbacsystem;
					if ($rbacsystem->checkAccess("write", $this->ref_id))
					{
						sendInfo(sprintf($this->lng->txt("qpl_question_is_in_use"), $count));
					}
				}
				include_once("content/classes/Pages/class.ilPageObject.php");
				include_once("content/classes/Pages/class.ilPageObjectGUI.php");
				$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilPageObjectGUI", "view");
				$this->ctrl->setReturn($this, "questions");
				$page =& new ilPageObject("qpl", $_GET["q_id"]);
				$page_gui =& new ilPageObjectGUI($page);
				if (strlen($this->ctrl->getCmd()) == 0)
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}
				$page_gui->setQuestionXML($question->to_xml(false, false, true));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode("edit");
				$page_gui->setHeader($question->getTitle());
				$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
				$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
				$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
				$page_gui->setPresentationTitle($question->getTitle());
				$ret =& $this->ctrl->forwardCommand($page_gui);
				break;
			case "ass_multiplechoicegui":
			case "ass_clozetestgui":
			case "ass_orderingquestiongui":
			case "ass_matchingquestiongui":
			case "ass_imagemapquestiongui":
			case "ass_javaappletgui":
			case "ass_textquestiongui":
				$this->setAdminTabs();
				$this->setQuestionTabs();
				$this->ctrl->setReturn($this, "questions");
				include_once "./assessment/classes/class.assQuestionGUI.php";
				$q_gui =& ASS_QuestionGUI::_getQuestionGUI($q_type, $_GET["q_id"]);
				$q_gui->object->setObjId($this->object->getId());
				if ($_GET["q_id"] > 0)
				{
					$ilLocator->addItem($q_gui->object->getTitle(), $this->ctrl->getLinkTargetByClass($next_class, $_GET["cmd"]));
				}
				$ret =& $this->ctrl->forwardCommand($q_gui);
				break;

			default:
				if ($cmd != "createQuestion" && $cmd != "createQuestionForTest" && $cmd != "editQuestionForTest")
				{
					$this->setAdminTabs();
				}
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}
		if ($prepare_output)
		{
			$this->tpl->setLocator();
			$this->tpl->show();
		}
	}

	/**
	* Questionpool properties
	*/
	function propertiesObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_properties.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("qpl_general_properties"));
		$this->tpl->setVariable("PROPERTY_ONLINE", $this->lng->txt("qpl_online_property"));
		$this->tpl->setVariable("PROPERTY_ONLINE_DESCRIPTION", $this->lng->txt("qpl_online_property_description"));
		if ($this->object->getOnline() == 1)
		{
			$this->tpl->setVariable("PROPERTY_ONLINE_CHECKED", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
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
		sendInfo($this->lng->txt("saved_successfully"), true);
		$this->ctrl->redirect($this, "properties");
	}
	
	/**
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* show fullscreen view
	*/
	function fullscreenObject()
	{
		include_once("content/classes/Pages/class.ilPageObject.php");
		include_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$page =& new ilPageObject("qpl", $_GET["pg_id"]);
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->showMediaFullscreen();
		
	}


	/**
	* set question list filter
	*/
	function filterObject()
	{
		$this->questionsObject();
	}

	/**
	* resets filter
	*/
	function resetFilterObject()
	{
		$_POST["filter_text"] = "";
		$_POST["sel_filter_type"] = "";
		$this->questionsObject();
	}

	/**
	* download source code paragraph
	*/
	function download_paragraphObject()
	{
		include_once("content/classes/Pages/class.ilPageObject.php");
		$pg_obj =& new ilPageObject("qpl", $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
		exit;
	}

	/**
	* imports question(s) into the questionpool
	*/
	function uploadQplObject($questions_only = false)
	{
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			sendInfo($this->lng->txt("error_upload"));
			$this->importObject();
			return;
		}
		// create import directory
		include_once "./assessment/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = ilObjQuestionPool::_getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		include_once "./classes/class.ilUtil.php";
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		// determine filenames of xml files
		$subdir = basename($file["basename"],".".$file["extension"]);
		$xml_file = ilObjQuestionPool::_getImportDirectory()."/".$subdir."/".$subdir.".xml";
		$qti_file = ilObjQuestionPool::_getImportDirectory()."/".$subdir."/". str_replace("qpl", "qti", $subdir).".xml";

		// start verification of QTI files
		include_once "./assessment/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir(ilObjQuestionPool::_getImportDirectory());

			sendInfo($this->lng->txt("qpl_import_no_items"));
			$this->importObject();
			return;
		}
		
		$complete = 0;
		$incomplete = 0;
		foreach ($founditems as $item)
		{
			if (strlen($item["type"]))
			{
				$complete++;
			}
			else
			{
				$incomplete++;
			}
		}
		
		if ($complete == 0)
		{
			// delete import directory
			ilUtil::delDir(ilObjQuestionPool::_getImportDirectory());

			sendInfo($this->lng->txt("qpl_import_non_ilias_files"));
			$this->importObject();
			return;
		}
		
		$_SESSION["qpl_import_xml_file"] = $xml_file;
		$_SESSION["qpl_import_qti_file"] = $qti_file;
		$_SESSION["qpl_import_subdir"] = $subdir;
		// display of found questions
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_import_verification.html");
		$row_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($founditems as $item)
		{
			$this->tpl->setCurrentBlock("verification_row");
			$this->tpl->setVariable("ROW_CLASS", $row_class[$counter++ % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $item["title"]);
			$this->tpl->setVariable("QUESTION_IDENT", $item["ident"]);
			switch ($item["type"])
			{
				case "MULTIPLE CHOICE QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_multiple_choice"));
					break;
				case "CLOZE QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_cloze"));
					break;
				case "IMAGE MAP QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_imagemap"));
					break;
				case "JAVA APPLET QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_javaapplet"));
					break;
				case "MATCHING QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_matching"));
					break;
				case "ORDERING QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_ordering"));
					break;
				case "TEXT QUESTION":
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_text"));
					break;
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("import_qpl");
		if (is_file($xml_file))
		{
			// read file into a string
			$fh = @fopen($xml_file, "r") or die("");
			$xml = @fread($fh, filesize($xml_file));
			@fclose($fh);
			if (preg_match("/<ContentObject.*?MetaData.*?General.*?Title[^>]*?>([^<]*?)</", $xml, $matches))
			{
				$this->tpl->setVariable("VALUE_NEW_QUESTIONPOOL", $matches[1]);
			}
		}
		$this->tpl->setVariable("TEXT_CREATE_NEW_QUESTIONPOOL", $this->lng->txt("qpl_import_create_new_qpl"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("question_title"));
		$this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("qpl_import_verify_found_questions"));
		if ($questions_only)
		{
			$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_questions_into_qpl"));
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		}
		else
		{
			$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_qpl"));
			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$this->type));
		}
		$this->tpl->setVariable("ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("VALUE_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
		$value_questions_only = 0;
		if ($questions_only) $value_questions_only = 1;
		$this->tpl->setVariable("VALUE_QUESTIONS_ONLY", $value_questions_only);

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* imports question(s) into the questionpool (after verification)
	*/
	function importVerifiedFileObject()
	{
		if ($_POST["questions_only"] == 1)
		{
			$newObj =& $this->object;
		}
		else
		{
			include_once("./assessment/classes/class.ilObjQuestionPool.php");
			// create new questionpool object
			$newObj = new ilObjQuestionPool(true);
			// set type of questionpool object
			$newObj->setType($_GET["new_type"]);
			// set title of questionpool object to "dummy"
			$newObj->setTitle("dummy");
			// set description of questionpool object
			$newObj->setDescription("questionpool import");
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
		}

		// start parsing of QTI files
		include_once "./assessment/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($_SESSION["qpl_import_qti_file"], IL_MO_PARSE_QTI, $newObj->getId(), $_POST["ident"]);
		$result = $qtiParser->startParsing();

		// import page data
		include_once ("content/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $_SESSION["qpl_import_xml_file"], $_SESSION["qpl_import_subdir"]);
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		// set another question pool name (if possible)
		$qpl_name = $_POST["qpl_new"];
		if ((strcmp($qpl_name, $newObj->getTitle()) != 0) && (strlen($qpl_name) > 0))
		{
			$newObj->setTitle($qpl_name);
			$newObj->update();
		}
		
		// delete import directory
		include_once "./classes/class.ilUtil.php";
		ilUtil::delDir(ilObjQuestionPool::_getImportDirectory());

		if ($_POST["questions_only"] == 1)
		{
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::redirect($this->getReturnLocation("save", "adm_object.php?ref_id=" . $_GET["ref_id"]));
		}
	}
	
	function cancelImportObject()
	{
		if ($_POST["questions_only"] == 1)
		{
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			include_once "./classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("cancel", "adm_object.php?ref_id=" . $_GET["ref_id"]));
		}
	}
	
	/**
	* imports question(s) into the questionpool
	*/
	function uploadObject()
	{
		$this->uploadQplObject(true);
	}
	
	/**
	* display the import form to import questions into the questionpool
	*/
		function importQuestionsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_import_question.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_IMPORT_QUESTION", $this->lng->txt("import_question"));
		$this->tpl->setVariable("TEXT_SELECT_FILE", $this->lng->txt("select_file"));
		$this->tpl->setVariable("TEXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display dialogue for importing questionpools
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import", "qpl");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=".$this->type);
		$this->tpl->setVariable("BTN_NAME", "uploadQpl");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("import"));
		$this->tpl->setVariable("NEW_TYPE", $this->type);
		$this->tpl->setVariable("TXT_IMPORT_QPL", $this->lng->txt("import_qpl"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* create new question
	*/
	function &createQuestionObject()
	{
		include_once "./assessment/classes/class.assQuestionGUI.php";
		$q_gui =& ASS_QuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$this->ctrl->setCmdClass(get_class($q_gui));
		$this->ctrl->setCmd("editQuestion");
		$ret =& $this->executeCommand(false);
		return $ret;
	}

	/**
	* create new question
	*/
	function &createQuestionForTestObject()
	{
		include_once "./assessment/classes/class.assQuestionGUI.php";
		$q_gui =& ASS_QuestionGUI::_getQuestionGUI($_GET["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$this->ctrl->setCmdClass(get_class($q_gui));
		$this->ctrl->setCmd("editQuestion");

		$ret =& $this->executeCommand(false);
		return $ret;
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

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);

		if (strlen($this->ctrl->getModuleDir()) == 0)
		{
			include_once "./classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("save","adm_object.php?ref_id=".$_GET["ref_id"]));
		}
		else
		{
			$this->ctrl->redirect($this, "questions");
		}
	}

	/**
	* show assessment data of object
	*/
	function assessmentObject()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		sendInfo();

		include_once "./assessment/classes/class.assQuestion.php";
		$question_title = ASS_Question::_getTitle($_GET["q_id"]);
		$title = $this->lng->txt("statistics") . " - $question_title";
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		include_once("./assessment/classes/class.assQuestion.php");
		$total_of_answers = ASS_Question::_getTotalAnswers($_GET["q_id"]);
		$counter = 0;
		$color_class = array("tblrow1", "tblrow2");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_assessment_of_questions.html", true);
		if (!$total_of_answers)
		{
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ASSESSMENT", $this->lng->txt("qpl_assessment_no_assessment_of_questions"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_answers"));
			$this->tpl->setVariable("TXT_VALUE", $total_of_answers);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_right_answers"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", ASS_Question::_getTotalRightAnswers($_GET["edit"]) * 100.0) . " %");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_QUESTION_TITLE", $question_title);
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->parseCurrentBlock();
	}

	function questionObject()
	{
//echo "<br>ilObjQuestionPoolGUI->questionObject()";
		$type = $_GET["sel_question_types"];
		$this->editQuestionForm($type);
		//$this->set_question_form($type, $_GET["edit"]);
	}

	/**
	* delete questions confirmation screen
	*/
	function deleteQuestionsObject()
	{
//echo "<br>ilObjQuestionPoolGUI->deleteQuestions()";
		// duplicate button was pressed
		if (count($_POST["q_id"]) < 1)
		{
			sendInfo($this->lng->txt("qpl_delete_select_none"), true);
			$this->ctrl->redirect($this, "questions");
		}

		$checked_questions = $_POST["q_id"];
		$_SESSION["ass_q_id"] = $_POST["q_id"];
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_confirm_delete_questions.html", true);

		// buidling SQL statements is not allowed in GUI classes!
		$whereclause = join($checked_questions, " OR qpl_questions.question_id = ");
		$whereclause = " AND (qpl_questions.question_id = " . $whereclause . ")";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id$whereclause ORDER BY qpl_questions.title";
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./classes/class.ilUtil.php";
		$img_locked = "<img src=\"" . ilUtil::getImagePath("locked.gif", true) . "\" alt=\"" . $this->lng->txt("locked") . "\" title=\"" . $this->lng->txt("locked") . "\" border=\"0\" />";
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if (in_array($data->question_id, $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					if ($this->object->isInUse($data->question_id))
					{
						$this->tpl->setVariable("TXT_LOCKED", $img_locked);
					}
					$this->tpl->setVariable("TXT_TITLE", $data->title);
					$this->tpl->setVariable("TXT_DESCRIPTION", $data->comment);
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
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("TXT_LOCKED", $this->lng->txt("locked"));
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
		sendInfo($this->lng->txt("qpl_questions_deleted"), true);
		foreach ($_SESSION["ass_q_id"] as $key => $value)
		{
			$this->object->deleteQuestion($value);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* duplicate a question
	*/
	function duplicateObject()
	{
		// duplicate button was pressed
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->duplicateQuestion($value);
			}
		}
		else
		{
			sendInfo($this->lng->txt("qpl_duplicate_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* export question
	*/
	function exportQuestionObject()
	{
		// export button was pressed
		if (count($_POST["q_id"]) > 0)
		{
			include_once("assessment/classes/class.ilQuestionpoolExport.php");
			$qpl_exp = new ilQuestionpoolExport($this->object, "xml", $_POST["q_id"]);
			$export_file = $qpl_exp->buildExportFile();
			$filename = $export_file;
			$filename = preg_replace("/.*\//", "", $filename);
			include_once "./classes/class.ilUtil.php";
			ilUtil::deliverFile($export_file, $filename);
			exit();
		}
		else
		{
			sendInfo($this->lng->txt("qpl_export_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* list questions of question pool
	*/
	function questionsObject()
	{
		global $rbacsystem;

		$type = $_GET["sel_question_types"];

		// reset test_id SESSION variable
		$_SESSION["test_id"] = "";

		// create an array of all checked checkboxes
		$checked_questions = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				array_push($checked_questions, $matches[1]);
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_questions.html", true);
		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			$this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_as_create_new_question.html", true);
			$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", true);
		}
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_qpl_filter_questions.html", true);

		// create filter form
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
			if (strcmp($_POST["sel_filter_type"], $key) == 0)
			{
				$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		$this->tpl->setVariable("VALUE_FILTER_TEXT", $_POST["filter_text"]);
		$this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->parseCurrentBlock();

		// create edit buttons & table footer
		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			$this->tpl->setCurrentBlock("standard");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("DUPLICATE", $this->lng->txt("duplicate"));
			$this->tpl->setVariable("EXPORT", $this->lng->txt("export"));
			$this->tpl->setVariable("COPY", $this->lng->txt("copy"));
			$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
			$this->tpl->parseCurrentBlock();
			if (array_key_exists("qpl_clipboard", $_SESSION))
			{
				$this->tpl->setCurrentBlock("pastebutton");
				$this->tpl->setVariable("PASTE", $this->lng->txt("paste"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("Footer");
			include_once "./classes/class.ilUtil.php";
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("QTab");

		// reset the filter
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
		$table = $this->object->getQuestionsTable($_GET["sort"], $_POST["filter_text"], $_POST["sel_filter_type"], $startrow);
		$colors = array("tblrow1", "tblrow2");
		include_once "./classes/class.ilUtil.php";
		$img_locked = "<img src=\"" . ilUtil::getImagePath("locked.gif", true) . "\" alt=\"" . $this->lng->txt("locked") . "\" title=\"" . $this->lng->txt("locked") . "\" border=\"0\" />";
		$counter = 0;
		$editable = $rbacsystem->checkAccess('write', $this->ref_id);
		foreach ($table["rows"] as $data)
		{
			if ($data["complete"] == 0)
			{
				$this->tpl->setCurrentBlock("qpl_warning");
				$this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("warning.png"));
				$this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("QTab");
			}
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			include_once "./assessment/classes/class.assQuestionGUI.php";
			$class = strtolower(ASS_QuestionGUI::_getGUIClassNameForId($data["question_id"]));
			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $data["question_id"]);
			$this->ctrl->setParameterByClass($class, "q_id", $data["question_id"]);
			if ($editable)
			{
				$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
				$this->tpl->setVariable("LINK_EDIT", $this->ctrl->getLinkTargetByClass("ilpageobjectgui", "view"));
			}
			$this->tpl->setVariable("QUESTION_TITLE", "<strong>" .$data["title"] . "</strong>");

			$this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
			$this->tpl->setVariable("LINK_PREVIEW", $this->ctrl->getLinkTargetByClass("ilpageobjectgui", "preview"));

			$this->tpl->setVariable("QUESTION_COMMENT", $data["comment"]);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
			$this->tpl->setVariable("LINK_ASSESSMENT", $this->ctrl->getLinkTargetByClass($class, "assessment"));
			$this->tpl->setVariable("TXT_ASSESSMENT", $this->lng->txt("statistics"));
			include_once "./classes/class.ilUtil.php";
			$this->tpl->setVariable("IMG_ASSESSMENT", ilUtil::getImagePath("assessment.gif", true));
			$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
			include_once "./classes/class.ilFormat.php";
			$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
			$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP14"]), "date"));
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
			$sort = "";
			if (is_array($_GET["sort"]))
			{
				$key = key($_GET["sort"]);
				$sort = "&sort[$key]=" . $_GET["sort"]["$key"];
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
					$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getFormAction($this) . "$sort&nextrow=$i" . "\">$counter</a>");
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
			$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getFormAction($this) . "$sort&prevrow=" . $table["prevrow"]);
			$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getFormAction($this) . "$sort&nextrow=" . $table["nextrow"]);
			$this->tpl->parseCurrentBlock();
		}

		// if there are no questions, display a message
		if ($counter == 0)
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			if ($rbacsystem->checkAccess('write', $this->ref_id))
			{
				$counter++;
				$this->tpl->setCurrentBlock("selectall");
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			// "create question" form
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM qpl_question_type ORDER BY question_type_id";
			$query_result = $this->ilias->db->query($query);
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
// temporary disable java questions
//				if ($data->type_tag != "qt_javaapplet")
//				{
					$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
					$this->tpl->parseCurrentBlock();
//				}
			}
			$this->tpl->setCurrentBlock("CreateQuestion");
			$this->tpl->setVariable("QUESTION_ADD", $this->lng->txt("create"));
			$this->tpl->setVariable("ACTION_QUESTION_ADD", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("QUESTION_IMPORT", $this->lng->txt("import"));
			$this->tpl->parseCurrentBlock();
		}

		// define the sort column parameters
		$sort = array(
			"title" => $_GET["sort"]["title"],
			"comment" => $_GET["sort"]["comment"],
			"type" => $_GET["sort"]["type"],
			"author" => $_GET["sort"]["author"],
			"created" => $_GET["sort"]["created"],
			"updated" => $_GET["sort"]["updated"]
		);
		foreach ($sort as $key => $value)
		{
			if (strcmp($value, "ASC") == 0)
			{
				$sort[$key] = "DESC";
			}
			else
			{
				$sort[$key] = "ASC";
			}
		}

		$this->tpl->setCurrentBlock("adm_content");
		// create table header
		$this->ctrl->setParameterByClass(get_class($this), "startrow", $table["startrow"]);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
		$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[comment]=" . $sort["comment"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["comment"]);
		$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
		$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
		$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
		$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
		$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	function updateObject()
	{
//		$this->update = $this->object->updateMetaData();
		$this->update = $this->object->update();
		sendInfo($this->lng->txt("msg_obj_modified"), true);
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();

		$this->tpl->setCurrentBlock("header_image");
		include_once "./classes/class.ilUtil.php";
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_qpl_b.gif"));
		$this->tpl->parseCurrentBlock();
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		if (strlen($this->ctrl->getModuleDir()) == 0)
		{
			$this->setAdminTabs($_POST["new_type"]);
		}
	}

	/**
	* paste questios from the clipboard into the question pool
	*/
	function pasteObject()
	{
		if (array_key_exists("qpl_clipboard", $_SESSION))
		{
			$this->object->pasteFromClipboard();
			sendInfo($this->lng->txt("qpl_paste_success"), true);
		}
		else
		{
			sendInfo($this->lng->txt("qpl_paste_no_objects"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* copy one or more question objects to the clipboard
	*/
	function copyObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->copyToClipboard($value);
			}
			sendInfo($this->lng->txt("qpl_copy_insert_clipboard"), true);
		}
		else
		{
			sendInfo($this->lng->txt("qpl_copy_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* mark one or more question objects for moving
	*/
	function moveObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->moveToClipboard($value);
			}
			sendInfo($this->lng->txt("qpl_move_insert_clipboard"), true);
		}
		else
		{
			sendInfo($this->lng->txt("qpl_move_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "createExportFile"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("ass_create_export_file"));
		$this->tpl->parseCurrentBlock();

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);

		// create table
		include_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", true);

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($this->lng->txt("ass_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("ass_file"),
		$this->lng->txt("ass_size"), $this->lng->txt("date") ));
		$tbl->enabled["sort"] = false;
		$tbl->setColumnWidth(array("1%", "49%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);

		$this->tpl->setVariable("COLUMN_COUNTS", 4);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		include_once "./classes/class.ilUtil.php";
		if(count($export_files) > 0)
		{
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
			include_once("assessment/classes/class.ilQuestionpoolExport.php");
			$question_ids =& $this->object->getAllQuestionIds();
			$qpl_exp = new ilQuestionpoolExport($this->object, "xml", $question_ids);
			$qpl_exp->buildExportFile();
			$this->exportObject();
		}
		else
		{
			sendInfo("cannot_export_qpl");
		}
	}
	
	/**
	* download export file
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		if (count($_POST["file"]) > 1)
		{
			sendInfo($this->lng->txt("cont_select_max_one_item"), true);
			$this->ctrl->redirect($this, "export");
		}


		$export_dir = $this->object->getExportDirectory();
		include_once "./classes/class.ilUtil.php";
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
		$this->ctrl->redirect($this, "export");
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		//$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", true);

		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		include_once "./classes/class.ilUtil.php";
		foreach($_POST["file"] as $file)
		{
				$this->tpl->setCurrentBlock("table_row");
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
		include_once "./classes/class.ilUtil.php";
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
				ilUtil::delDir($exp_dir);
			}
		}
		$this->ctrl->redirect($this, "export");
	}

	/**
	* edit question
	*/
	function &editQuestionForTestObject()
	{
		include_once "./assessment/classes/class.assQuestionGUI.php";
		$q_gui =& ASS_QuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
		$this->ctrl->setCmdClass(get_class($q_gui));
		$this->ctrl->setCmd("editQuestion");

		$ret =& $this->executeCommand(false);
		return $ret;
	}

	/**
	* form for new content object creation
	*/
	function createObject()
	{
		global $rbacsystem;
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->getTemplateFile("create", $new_type);

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			include_once "./classes/class.ilUtil.php";
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

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																	   $_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_QPL", $this->lng->txt("import_qpl"));
			$this->tpl->setVariable("TXT_QPL_FILE", $this->lng->txt("qpl_upload_file"));
			$this->tpl->setVariable("NEW_TYPE", $this->type);
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* form for new questionpool object import
	*/
	function importFileObject()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			sendInfo($this->lng->txt("qpl_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->uploadQplObject();
	}

	function setQuestionTabs()
	{
		global $rbacsystem;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./assessment/classes/class.assQuestion.php";
		$q_type = ASS_Question::getQuestionTypeFromDb($_GET["q_id"]);
		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		$tabs_gui->setSubTabs();
		
		switch ($q_type)
		{
			case "qt_multiple_choice_sr":
				$classname = "ASS_MultipleChoiceGUI";
				$this->ctrl->setParameterByClass("ass_multiplechoicegui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_multiplechoicegui", "q_id", $_GET["q_id"]);
				break;

			case "qt_multiple_choice_mr":
				$classname = "ASS_MultipleChoiceGUI";
				$this->ctrl->setParameterByClass("ass_multiplechoicegui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_multiplechoicegui", "q_id", $_GET["q_id"]);
				break;

			case "qt_cloze":
				$classname = "ASS_ClozeTestGUI";
				$this->ctrl->setParameterByClass("ass_clozetestgui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_clozetestgui", "q_id", $_GET["q_id"]);
				break;

			case "qt_matching":
				$classname = "ASS_MatchingQuestionGUI";
				$this->ctrl->setParameterByClass("ass_matchingquestiongui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_matchingquestiongui", "q_id", $_GET["q_id"]);
				break;

			case "qt_ordering":
				$classname = "ASS_OrderingQuestionGUI";
				$this->ctrl->setParameterByClass("ass_orderingquestiongui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_orderingquestiongui", "q_id", $_GET["q_id"]);
				break;

			case "qt_imagemap":
				$classname = "ASS_ImagemapQuestionGUI";
				$this->ctrl->setParameterByClass("ass_imagemapquestiongui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_imagemapquestiongui", "q_id", $_GET["q_id"]);
				break;

			case "qt_javaapplet":
				$classname = "ASS_JavaAppletGUI";
				$this->ctrl->setParameterByClass("ass_javaappletgui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_javaappletgui", "q_id", $_GET["q_id"]);
				break;

			case "qt_text":
				$classname = "ASS_TextQuestionGUI";
				$this->ctrl->setParameterByClass("ass_textquestiongui", "sel_question_types", $q_type);
				$this->ctrl->setParameterByClass("ass_textquestiongui", "q_id", $_GET["q_id"]);
				break;
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $this->ref_id))
			{
				// edit page
				$tabs_gui->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "view"),
					array("view", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$tabs_gui->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		if ($classname)
		{
			$force_active = false;
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^delete_.*/", $key, $matches) || 
						preg_match("/^addSelectGap_.*/", $key, $matches) ||
						preg_match("/^addTextGap_.*/", $key, $matches) ||
						preg_match("/^addSuggestedSolution_.*/", $key, $matches)
						)
					{
						$force_active = true;
					}
				}
			}
			if (array_key_exists("imagemap_x", $_POST))
			{
				$force_active = true;
			}
			if ($rbacsystem->checkAccess('write', $this->ref_id))
			{
				// edit question properties
				$tabs_gui->addTarget("edit_properties",
					$this->ctrl->getLinkTargetByClass($classname, "editQuestion"),
					array("questions", "filter", "resetFilter", "createQuestion", 
					"importQuestions", "deleteQuestions", "duplicate", 
					"view", "preview", "editQuestion", "exec_pg",
					"addItem", "upload", "save", "cancel", "addSuggestedSolution",
					"cancelExplorer", "linkChilds", "removeSuggestedSolution",
					"add", "addYesNo", "addTrueFalse", "createGaps", "saveEdit",
					"setMediaMode", "uploadingImage", "uploadingImagemap", "addArea",
					"deletearea", "saveShape", "back", "addPair", "uploadingJavaapplet",
					"addParameter", "addGIT", "addST", "addPG", "delete"),
					$classname, "", $force_active);
			}
		}

		// Assessment of questions sub menu entry
		$tabs_gui->addTarget("statistics",
			$this->ctrl->getLinkTargetByClass($classname, "assessment"),
			array("assessment"),
			$classname, "");
		
		$this->tpl->setVariable("SUB_TABS", $tabs_gui->getHTML());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		// properties
		$tabs_gui->addTarget("properties",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 "properties", "",
			 "");

		// questions
		$force_active = false;
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^delete_.*/", $key, $matches) || 
					preg_match("/^addSelectGap_.*/", $key, $matches) ||
					preg_match("/^addTextGap_.*/", $key, $matches) ||
					preg_match("/^addSuggestedSolution_.*/", $key, $matches)
					)
				{
					$force_active = true;
				}
			}
		}
		if (array_key_exists("imagemap_x", $_POST))
		{
			$force_active = true;
		}
		if (!$force_active)
		{
			$force_active = (strtolower($this->ctrl->getCmdClass()) == strtolower(get_class($this)) &&
				$this->ctrl->getCmd() == "")
				? true
				: false;
		}
		$tabs_gui->addTarget("ass_questions",
			 $this->ctrl->getLinkTarget($this,'questions'),
			 array("questions", "filter", "resetFilter", "createQuestion", 
			 	"importQuestions", "deleteQuestions", "duplicate", 
				"view", "preview", "editQuestion", "exec_pg",
				"addItem", "upload", "save", "cancel", "addSuggestedSolution",
				"cancelExplorer", "linkChilds", "removeSuggestedSolution",
				"add", "addYesNo", "addTrueFalse", "createGaps", "saveEdit",
				"setMediaMode", "uploadingImage", "uploadingImagemap", "addArea",
				"deletearea", "saveShape", "back", "addPair", "uploadingJavaapplet",
				"addParameter", "assessment", "addGIT", "addST", "addPG", "delete"),
			 "", "", $force_active);

		// export
		$tabs_gui->addTarget("export",
			 $this->ctrl->getLinkTarget($this,'export'),
			 array("export", "createExportFile", "confirmDeleteExportFile", "downloadExportFile"),
			 "", "");
			
		// permissions
		$tabs_gui->addTarget("perm_settings",
			 $this->ctrl->getLinkTarget($this,'perm'),
			 array("perm", "info"),
			 "");
			 
		// meta data
		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
			 "", "ilmdeditorgui");
	}

} // END class.ilObjQuestionPoolGUI
?>
