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
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
*
* @ilCtrl_Calls ilObjQuestionPoolGUI: ilPageObjectGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assNumericGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assTextSubsetGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assSingleChoiceGUI
* @ilCtrl_Calls ilObjQuestionPoolGUI: assTextQuestionGUI, ilMDEditorGUI, ilPermissionGUI
*
* @extends ilObjectGUI
* @ingroup ModulesTestQuestionPool
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjQuestionPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjQuestionPoolGUI()
	{
		global $lng, $ilCtrl, $rbacsystem;
		$lng->loadLanguageModule("assessment");
		$this->type = "qpl";
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "test_ref_id", "calling_test"));

		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilLocator, $ilAccess, $ilNavigationHistory, $tpl;
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjQuestionPoolGUI&cmd=questions&ref_id=".$_GET["ref_id"], "qpl");
		}
		$this->prepareOutput();
		$cmd = $this->ctrl->getCmd("questions");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "questions");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "ta.css", "Modules/Test"), "screen");
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
			}
		}
		switch($next_class)
		{
			case 'ilmdeditorgui':
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				$this->ctrl->forwardCommand($md_gui);
				break;
			case "ilpageobjectgui":
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();
		
				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
				$q_gui->setQuestionTabs();
				$q_gui->outAdditionalOutput();
				$q_gui->object->setObjId($this->object->getId());
				$question =& $q_gui->object;
				$this->ctrl->saveParameter($this, "q_id");
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
				$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilPageObjectGUI", "view");
				$this->ctrl->setReturn($this, "questions");
				//$page =& new ilPageObject("qpl", $_GET["q_id"]);
				$page_gui =& new ilPageObjectGUI("qpl", $_GET["q_id"]);
				$page_gui->setEditPreview(true);
				$page_gui->setEnabledTabs(false);
				$page_gui->setEnabledInternalLinks(false);
				if (strlen($this->ctrl->getCmd()) == 0)
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}
				//$page_gui->setQuestionXML($question->toXML(false, false, true));
				$page_gui->setQuestionHTML($q_gui->getPreview(TRUE));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode("edit");
				$page_gui->setHeader($question->getTitle());
				$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
				$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
				$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
				$page_gui->setPresentationTitle($question->getTitle());
				$ret =& $this->ctrl->forwardCommand($page_gui);
				$tpl->setContent($ret);
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			case "ilobjquestionpoolgui":
			case "":
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
			default:
				$this->ctrl->setReturn($this, "questions");
				include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui =& assQuestionGUI::_getQuestionGUI($q_type, $_GET["q_id"]);
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
	function propertiesObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_properties.html", "Modules/TestQuestionPool");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("qpl_general_properties"));
		$this->tpl->setVariable("PROPERTY_ONLINE", $this->lng->txt("qpl_online_property"));
		$this->tpl->setVariable("PROPERTY_ONLINE_DESCRIPTION", $this->lng->txt("qpl_online_property_description"));
		if ($this->object->getOnline() == 1)
		{
			$this->tpl->setVariable("PROPERTY_ONLINE_CHECKED", " checked=\"checked\"");
		}
		global $rbacsystem;
		if ($rbacsystem->checkAccess("write", $this->ref_id))
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
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* show fullscreen view
	*/
	function fullscreenObject()
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		//$page =& new ilPageObject("qpl", $_GET["pg_id"]);
		$page_gui =& new ilPageObjectGUI("qpl", $_GET["pg_id"]);
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
		include_once("./Services/COPage/classes/class.ilPageObject.php");
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
			ilUtil::sendInfo($this->lng->txt("error_upload"));
			$this->importObject();
			return;
		}
		// create import directory
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = ilObjQuestionPool::_getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);
		if (strcmp($_FILES["xmldoc"]["type"], "text/xml") == 0)
		{
			$qti_file = $full_path;
		}
		else
		{
			// unzip file
			ilUtil::unzip($full_path);
	
			// determine filenames of xml files
			$subdir = basename($file["basename"],".".$file["extension"]);
			$xml_file = ilObjQuestionPool::_getImportDirectory()."/".$subdir."/".$subdir.".xml";
			$qti_file = ilObjQuestionPool::_getImportDirectory()."/".$subdir."/". str_replace("qpl", "qti", $subdir).".xml";
		}

		// start verification of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir(ilObjQuestionPool::_getImportDirectory());

			ilUtil::sendInfo($this->lng->txt("qpl_import_no_items"));
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

			ilUtil::sendInfo($this->lng->txt("qpl_import_non_ilias_files"));
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
			include_once "./Services/QTI/classes/class.ilQTIItem.php";
			switch ($item["type"])
			{
				case CLOZE_TEST_IDENTIFIER:
					$type = $this->lng->txt("assClozeTest");
					break;
				case IMAGEMAP_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assImagemapQuestion");
					break;
				case JAVAAPPLET_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assJavaApplet");
					break;
				case MATCHING_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assMatchingQuestion");
					break;
				case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assMultipleChoice");
					break;
				case SINGLE_CHOICE_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assSingleChoice");
					break;
				case ORDERING_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assOrderingQuestion");
					break;
				case TEXT_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assTextQuestion");
					break;
				case NUMERIC_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assNumeric");
					break;
				case TEXTSUBSET_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assTextSubset");
					break;
				default:
					$type = $this->lng->txt($item["type"]);
					break;
			}
			
			if (strcmp($type, "-" . $item["type"] . "-") == 0)
			{
				global $ilPluginAdmin;
				$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
				foreach ($pl_names as $pl_name)
				{
					$pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
					if (strcmp($pl->getQuestionType(), $item["type"]) == 0)
					{
						$type = $pl->getQuestionTypeTranslation();
					}
				}
			}
			$this->tpl->setVariable("QUESTION_TYPE", $type);
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
			
			$this->ctrl->setParameter($this, "new_type", $this->type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

			//$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$this->type));
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
			include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
			// create new questionpool object
			$newObj = new ilObjQuestionPool(0, true);
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
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($_SESSION["qpl_import_qti_file"], IL_MO_PARSE_QTI, $newObj->getId(), $_POST["ident"]);
		$result = $qtiParser->startParsing();

		// import page data
		if (strlen($_SESSION["qpl_import_xml_file"]))
		{
			include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
			$contParser = new ilContObjParser($newObj, $_SESSION["qpl_import_xml_file"], $_SESSION["qpl_import_subdir"]);
			$contParser->setQuestionMapping($qtiParser->getImportMapping());
			$contParser->startParsing();
		}

		// set another question pool name (if possible)
		$qpl_name = $_POST["qpl_new"];
		if ((strcmp($qpl_name, $newObj->getTitle()) != 0) && (strlen($qpl_name) > 0))
		{
			$newObj->setTitle($qpl_name);
			$newObj->update();
		}
		
		// delete import directory
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::delDir(ilObjQuestionPool::_getImportDirectory());

		if ($_POST["questions_only"] == 1)
		{
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("object_imported"),true);
			ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjQuestionPoolGUI");
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
			$this->ctrl->redirect($this, "cancel");
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_import_question.html", "Modules/TestQuestionPool");
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
		global $rbacsystem;
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		$this->getTemplateFile("import", "qpl");
		$this->ctrl->setParameter($this, "new_type", $this->type);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		//$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=".$this->type);
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
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_gui =& assQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$q_gui->object->createNewQuestion();
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_POST["sel_question_types"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create new question
	*/
	function &createQuestionForTestObject()
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_gui =& assQuestionGUI::_getQuestionGUI($_GET["sel_question_types"]);
		$q_gui->object->setObjId($this->object->getId());
		$q_gui->object->createNewQuestion();
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_GET["sel_question_types"]); 
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
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
			"&baseClass=ilObjQuestionPoolGUI");

/*		if (strlen($this->ctrl->getModuleDir()) == 0)
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("save","adm_object.php?ref_id=".$_GET["ref_id"]));
		}
		else
		{
			$this->ctrl->redirect($this, "questions");
		}*/
	}

	/**
	* show assessment data of object
	*/
	function assessmentObject()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", "Modules/TestQuestionPool");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		ilUtil::sendInfo();

		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$question_title = assQuestion::_getTitle($_GET["q_id"]);
		$title = $this->lng->txt("statistics") . " - $question_title";
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$total_of_answers = assQuestion::_getTotalAnswers($_GET["q_id"]);
		$counter = 0;
		$color_class = array("tblrow1", "tblrow2");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_assessment_of_questions.html", "Modules/TestQuestionPool");
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
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", assQuestion::_getTotalRightAnswers($_GET["edit"]) * 100.0) . " %");
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
	}

	/**
	* delete questions confirmation screen
	*/
	function deleteQuestionsObject()
	{
		if (count($_POST["q_id"]) < 1)
		{
			ilUtil::sendInfo($this->lng->txt("qpl_delete_select_none"), true);
			$this->ctrl->redirect($this, "questions");
		}
		global $ilLog;
		$ilLog->write("getQuestionDetails");
		$checked_questions =& $this->object->getQuestionDetails($_POST["q_id"]);
		$ilLog->write("getDeleteableQuestionDetails");
		$deleteable_questions =& $this->object->getDeleteableQuestionDetails($_POST["q_id"]);
		$ilLog->write("getUsedQuestionDetails");
		$used_questions =& $this->object->getUsedQuestionDetails($_POST["q_id"]);
		$ilLog->write("done");
		$_SESSION["ass_q_id"] = $deleteable_questions;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_confirm_delete_questions.html", "Modules/TestQuestionPool");

		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if (count($deleteable_questions) > 0)
		{
			foreach ($deleteable_questions as $question)
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TXT_TITLE", $question["title"]);
				$this->tpl->setVariable("TXT_DESCRIPTION", $question["comment"]);
				$this->tpl->setVariable("TXT_TYPE", assQuestion::_getQuestionTypeName($question["type_tag"]));
				$this->tpl->parseCurrentBlock();
				$counter++;
				
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "id_" . $question["question_id"]);
				$this->tpl->setVariable("HIDDEN_VALUE", "1");
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TEXT_EMPTY_ROW", $this->lng->txt("qpl_delete_no_deleteable_questions"));
			$this->tpl->parseCurrentBlock();
		}
		
		if (count($used_questions))
		{
			foreach ($used_questions as $question)
			{
				$this->tpl->setCurrentBlock("undeleteable_row");
				$this->tpl->setVariable("QUESTION_TITLE", $question["title"]); 
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("undeleteable_questions");
			$this->tpl->setVariable("TEXT_UNDELETEABLE_QUESTIONS", $this->lng->txt("qpl_delete_describe_undeleteable_questions"));
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
		$this->tpl->setVariable("DELETE_QUESTION", $this->lng->txt("qpl_confirm_delete_questions"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* delete questions
	*/
	function confirmDeleteQuestionsObject()
	{
		// delete questions after confirmation
		if (count($_SESSION["ass_q_id"])) ilUtil::sendInfo($this->lng->txt("qpl_questions_deleted"), true);
		foreach ($_SESSION["ass_q_id"] as $key => $value)
		{
			$this->object->deleteQuestion($value["question_id"]);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Cancel question deletion
	*/
	function cancelDeleteQuestionsObject()
	{
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
			include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
			$qpl_exp = new ilQuestionpoolExport($this->object, "xml", $_POST["q_id"]);
			$export_file = $qpl_exp->buildExportFile();
			$filename = $export_file;
			$filename = preg_replace("/.*\//", "", $filename);
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::deliverFile($export_file, $filename);
			exit();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_export_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* list questions of question pool
	*/
	function questionsObject()
	{
		global $rbacsystem;
		global $ilUser;

		$this->object->purgeQuestions();
		$lastquestiontype = $ilUser->getPref("tst_lastquestiontype");
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_questions.html", "Modules/TestQuestionPool");
		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			$this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_as_create_new_question.html", "Modules/TestQuestionPool");
		}
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", "Modules/TestQuestionPool");
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_qpl_filter_questions.html", "Modules/TestQuestionPool");

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
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
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
		}
		else
		{
			$this->tpl->setVariable("EXPORT", $this->lng->txt("export"));
			$this->tpl->setVariable("COPY", $this->lng->txt("copy"));
		}

		$this->tpl->setCurrentBlock("Footer");
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
		$this->tpl->parseCurrentBlock();
		
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
		$sort = ($_GET["sort"]) ? $_GET["sort"] : (($_SESSION["qpl_sort"]) ? $_SESSION["qpl_sort"] : "title");
		$sortorder = ($_GET["sortorder"]) ? $_GET["sortorder"] : (($_SESSION["qpl_sortorder"]) ? $_SESSION["qpl_sortorder"] : "ASC");
		$_SESSION["qpl_sort"] = $sort;
		$_SESSION["qpl_sortorder"] = $sortorder;
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$table = $this->object->getQuestionsTable($sort, $sortorder, $_POST["filter_text"], $_POST["sel_filter_type"], $startrow);
		$colors = array("tblrow1", "tblrow2");
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$counter = 0;
		$sumPoints = 0;
		$editable = $rbacsystem->checkAccess('write', $this->ref_id);
		foreach ($table["rows"] as $data)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$class = strtolower(assQuestionGUI::_getGUIClassNameForId($data["question_id"]));
			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $data["question_id"]);
			$this->ctrl->setParameterByClass($class, "q_id", $data["question_id"]);

			if ($data["complete"] == 0)
			{
				$this->tpl->setCurrentBlock("qpl_warning");
				$this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("warning.gif"));
				$this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->parseCurrentBlock();
				$points = 0;
			} else
			{
			    $points = assQuestion::_getMaximumPoints($data["question_id"]);
			}
			$sumPoints += $points;

			$this->tpl->setCurrentBlock("checkable");
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			$this->tpl->parseCurrentBlock();
			if ($editable)
			{
				$this->tpl->setCurrentBlock("edit_link");
				$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
				$this->tpl->setVariable("LINK_EDIT", $this->ctrl->getLinkTargetByClass("ilpageobjectgui", "edit"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			$this->tpl->setVariable("QUESTION_TITLE", "<strong>" .$data["title"] . "</strong>");

			$this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
			$this->tpl->setVariable("LINK_PREVIEW", $this->ctrl->getLinkTargetByClass("ilpageobjectgui", "preview"));

			$this->tpl->setVariable("QUESTION_COMMENT", $data["comment"]);
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
			$this->tpl->setVariable("LINK_ASSESSMENT", $this->ctrl->getLinkTargetByClass($class, "assessment"));
			$this->tpl->setVariable("TXT_ASSESSMENT", $this->lng->txt("statistics"));
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$this->tpl->setVariable("IMG_ASSESSMENT", ilUtil::getImagePath("assessment.gif", "Modules/TestQuestionPool"));
			$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
			include_once "./classes/class.ilFormat.php";
			$this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
			$this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"],IL_CAL_UNIX)));
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->setVariable("QUESTION_POINTS", $points);
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
					$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getFormAction($this) . "&nextrow=$i" . "\">$counter</a>");
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
			$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getFormAction($this) . "&prevrow=" . $table["prevrow"]);
			$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getFormAction($this) . "&nextrow=" . $table["nextrow"]);
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
			$counter++;
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("SUM_POINTS", $sumPoints);
			$this->tpl->parseCurrentBlock();
		}

		if ($rbacsystem->checkAccess('write', $this->ref_id))
		{
			// "create question" form
			$this->tpl->setCurrentBlock("QTypes");
			$types =& $this->object->getQuestionTypes();
			foreach ($types as $translation => $data)
			{
					if ($data["type_tag"] == $lastquestiontype)
					{
						$this->tpl->setVariable("QUESTION_TYPE_SELECTED", " selected=\"selected\"");
					}
					$this->tpl->setVariable("QUESTION_TYPE_ID", $data["type_tag"]);
					$this->tpl->setVariable("QUESTION_TYPE", $translation);
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
		$sortarray = array(
			"title" => (strcmp($sort, "title") == 0) ? $sortorder : "",
			"comment" => (strcmp($sort, "comment") == 0) ? $sortorder : "",
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
		$this->ctrl->setParameter($this, "sort", "comment");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["comment"]);
		$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["comment"]);
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
		$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("points"));
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates a print view for a question pool
	*
	* Creates a print view for a question pool
	*
	* @access	public
	*/
	function printObject()
	{
		$sort = "title";
		if (strlen($_POST["sortorder"]))
		{
			$sort = $_POST["sortorder"];
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_printview.html", "Modules/TestQuestionPool");
		$sortorder = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"type" => $this->lng->txt("question_type"),
			"author" => $this->lng->txt("author"),
			"created" => $this->lng->txt("create_date"),
			"updated" => $this->lng->txt("last_update")
		);
		foreach ($sortorder as $value => $text)
		{
			$this->tpl->setCurrentBlock("sortorder");
			$this->tpl->setVariable("VALUE_SORTORDER", $value);
			$this->tpl->setVariable("TEXT_SORTORDER", $text);
			if (strcmp($sort, $value) == 0)
			{
				$this->tpl->setVariable("SELECTED_SORTORDER", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		$table =& $this->object->getPrintviewQuestions($sort);
		$colors = array("tblrow1top", "tblrow2top");
		$counter = 1;
		include_once "./classes/class.ilFormat.php";
		foreach ($table as $row)
		{
			if ((strcmp($_POST["output"], "detailed") == 0) || (strcmp($_POST["output"], "detailed_printview") == 0))
			{
				$this->tpl->setCurrentBlock("overview_row_detail");
				$this->tpl->setVariable("ROW_CLASS", $colors[$counter % 2]);
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$question_gui = assQuestion::_instanciateQuestionGUI($row["question_id"]);
				if (strcmp($_POST["output"], "detailed") == 0)
				{
					$solutionoutput = $question_gui->getSolutionOutput($active_id = "", $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = FALSE, $show_feedback = FALSE);
					if (strlen($solutionoutput) == 0) $solutionoutput = $question_gui->getPreview();
					$this->tpl->setVariable("PREVIEW", $solutionoutput);
				}
				else
				{
					$this->tpl->setVariable("PREVIEW", $question_gui->getPreview());
				}
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("overview_row_detail");
				$this->tpl->setVariable("ROW_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
			}
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$this->tpl->setCurrentBlock("overview_row");
			$this->tpl->setVariable("ROW_CLASS", $colors[$counter % 2]);
			$this->tpl->setVariable("TEXT_COUNTER", $counter);
			$this->tpl->setVariable("TEXT_TITLE", ilUtil::prepareFormOutput($row["title"]));
			$this->tpl->setVariable("TEXT_DESCRIPTION", ilUtil::prepareFormOutput($row["comment"]));
			$this->tpl->setVariable("TEXT_QUESTIONTYPE", assQuestion::_getQuestionTypeName($row["type_tag"]));
			$this->tpl->setVariable("TEXT_AUTHOR", $row["author"]);
			$this->tpl->setVariable("TEXT_CREATED", ilDatePresentation::formatDate(new ilDate($row["created"],IL_CAL_UNIX)));
			$this->tpl->setVariable("TEXT_UPDATED", ilDatePresentation::formatDate(new ilDate($row["tstamp"],IL_CAL_UNIX)));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		$this->tpl->setCurrentBlock("overview");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTIONTYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_CREATED", $this->lng->txt("create_date"));
		$this->tpl->setVariable("TEXT_UPDATED", $this->lng->txt("last_update"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		if (strcmp($_POST["output"], "detailed") == 0)
		{
			$this->tpl->setVariable("SELECTED_DETAILED", " selected=\"selected\"");
		}
		else if (strcmp($_POST["output"], "detailed_printview") == 0)
		{
			$this->tpl->setVariable("SELECTED_DETAILED_PRINTVIEW", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_DETAILED", $this->lng->txt("detailed_output_solutions"));
		$this->tpl->setVariable("TEXT_DETAILED_PRINTVIEW", $this->lng->txt("detailed_output_printview"));
		$this->tpl->setVariable("TEXT_OVERVIEW", $this->lng->txt("overview"));
		$this->tpl->setVariable("OUTPUT_MODE", $this->lng->txt("output_mode"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SORT_TEXT", $this->lng->txt("sort_by_this_column"));
		$this->tpl->setVariable("TEXT_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("PRINT", $this->lng->txt("print"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "ta.css", "Modules/Test"), "screen");
		$this->tpl->setVariable("PAGETITLE", " - " . ilUtil::prepareFormOutput(ilObjQuestionPool::_getFullPathToQpl($this->object->getRefId()) . " > " . $this->object->getTitle()));
	}

	function updateObject()
	{
//		$this->update = $this->object->updateMetaData();
		$this->update = $this->object->update();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
	}

	/**
	* paste questios from the clipboard into the question pool
	*/
	function pasteObject()
	{
		if (array_key_exists("qpl_clipboard", $_SESSION))
		{
			$this->object->pasteFromClipboard();
			ilUtil::sendInfo($this->lng->txt("qpl_paste_success"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_paste_no_objects"), true);
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
			ilUtil::sendInfo($this->lng->txt("qpl_copy_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_copy_select_none"), true);
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
			ilUtil::sendInfo($this->lng->txt("qpl_move_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_move_select_none"), true);
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
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.il_as_qpl_export.html", "Modules/TestQuestionPool");

		// create export file button
		$this->tpl->setCurrentBlock("exporttype");
		$this->tpl->setVariable("VALUE_EXPORTTYPE", "xml");
		$this->tpl->setVariable("TEXT_EXPORTTYPE", $this->lng->txt("qpl_export_xml"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("exporttype");
		$this->tpl->setVariable("VALUE_EXPORTTYPE", "xls");
		$this->tpl->setVariable("TEXT_EXPORTTYPE", $this->lng->txt("qpl_export_excel"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("buttons");
		$this->tpl->setVariable("FORMACTION_BUTTONS", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_CREATE", $this->lng->txt("create"));
		$this->tpl->parseCurrentBlock();

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);

		// create table
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/TestQuestionPool");

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
		$header_params = $this->ctrl->getParameterArray($this, "export");
		$tbl->setHeaderVars(array("", "file", "size", "date"), $header_params);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
			include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
			$question_ids =& $this->object->getAllQuestionIds();
			$qpl_exp = new ilQuestionpoolExport($this->object, $_POST["exporttype"], $question_ids);
			$qpl_exp->buildExportFile();
			$this->ctrl->redirect($this, "export");
		}
		else
		{
			ilUtil::sendInfo("cannot_export_qpl", TRUE);
			$this->ctrl->redirect($this, "export");
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
			ilUtil::sendInfo($this->lng->txt("cont_select_max_one_item"), true);
			$this->ctrl->redirect($this, "export");
		}


		$export_dir = $this->object->getExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/TestQuestionPool");

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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export_dir = $this->object->getExportDirectory();
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$exp_file = $export_dir."/".$file;
			include_once "./Services/Utilities/classes/class.ilStr.php";
			$exp_dir = $export_dir."/".ilStr::subStr($file, 0, ilStr::strLen($file) - 4);
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
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
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


			$this->fillCloneTemplate('DUPLICATE','qpl');
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

			$this->tpl->setVariable("TXT_IMPORT_QPL", $this->lng->txt("import_qpl"));
			$this->tpl->setVariable("TXT_QPL_FILE", $this->lng->txt("qpl_upload_file"));
			$this->tpl->setVariable("NEW_TYPE", $this->type);
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TYPE_IMG", ilUtil::getImagePath('icon_qpl.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_qpl"));
			$this->tpl->setVariable("TYPE_IMG2", ilUtil::getImagePath('icon_qpl.gif'));
			$this->tpl->setVariable("ALT_IMG2",$this->lng->txt("obj_qpl"));

			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* form for new questionpool object import
	*/
	function importFileObject()
	{
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("qpl_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->uploadQplObject();
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
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$q_gui->object->setObjId($this->object->getId());
			if ($_GET["q_id"] > 0)
			{
				$ilLocator->addItem($q_gui->object->getTitle(), $this->ctrl->getLinkTargetByClass(get_class($q_gui), "editQuestion"));
			}
		}
	}
	
	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		if ($_GET["q_id"] > 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$q_gui->object->setObjId($this->object->getId());
			$title = $q_gui->object->getTitle();
			if (strcmp($this->ctrl->getCmd(), "assessment") == 0)
			{
				$title .= " - " . $this->lng->txt("statistics");
			}
			$this->tpl->setTitle($title);
			$this->tpl->setDescription($q_gui->object->getComment());
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_qpl"));
		}
		else
		{
			$this->tpl->setTitle($this->object->getTitle());
			$this->tpl->setDescription($this->object->getLongDescription());
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_qpl"));
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class)
		{
			case "":
			case "ilpermissiongui":
			case "ilmdeditorgui":
				break;
			default:
				return;
				break;
		}
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
					preg_match("/^deleteImage_.*/", $key, $matches) ||
					preg_match("/^upload_.*/", $key, $matches) ||
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
			$force_active = ((strtolower($this->ctrl->getCmdClass()) == strtolower(get_class($this)) || strlen($this->ctrl->getCmdClass()) == 0) &&
				$this->ctrl->getCmd() == "")
				? true
				: false;
		}
		$tabs_gui->addTarget("assQuestions",
			 $this->ctrl->getLinkTarget($this, "questions"),
			 array("questions", "filter", "resetFilter", "createQuestion", 
			 	"importQuestions", "deleteQuestions",  
				"view", "preview", "editQuestion", "exec_pg",
				"addItem", "upload", "save", "cancel", "addSuggestedSolution",
				"cancelExplorer", "linkChilds", "removeSuggestedSolution",
				"add", "addYesNo", "addTrueFalse", "createGaps", "saveEdit",
				"setMediaMode", "uploadingImage", "uploadingImagemap", "addArea",
				"deletearea", "saveShape", "back", "addPair", "uploadingJavaapplet",
				"addParameter", "assessment", "addGIT", "addST", "addPG", "delete",
				"toggleGraphicalAnswers", "deleteAnswer", "deleteImage", "removeJavaapplet"),
			 "", "", $force_active);

	// properties
		$tabs_gui->addTarget("properties",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 "properties", "",
			 "");

		global $rbacsystem;
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			// meta data
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", "ilmdeditorgui");
		}

		// print view
		$tabs_gui->addTarget("print_view",
			 $this->ctrl->getLinkTarget($this,'print'),
			 array("print"),
			 "", "");

		// export
		$tabs_gui->addTarget("export",
			 $this->ctrl->getLinkTarget($this,'export'),
			 array("export", "createExportFile", "confirmDeleteExportFile", "downloadExportFile"),
			 "", "");

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* Redirect script to call a test with the question pool reference id
	* 
	* Redirect script to call a test with the question pool reference id
	*
	* @param integer $a_target The reference id of the question pool
	* @access	public
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("write", "", $a_target))
		{
			$_GET["baseClass"] = "ilObjQuestionPoolGUI";
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

} // END class.ilObjQuestionPoolGUI
?>
