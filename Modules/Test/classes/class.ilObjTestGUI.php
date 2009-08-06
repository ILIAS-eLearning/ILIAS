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
* Class ilObjTestGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
*
* @ilCtrl_Calls ilObjTestGUI: ilObjCourseGUI, ilMDEditorGUI, ilTestOutputGUI
* @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjTestGUI: ilCertificateGUI
* @ilCtrl_Calls ilObjTestGUI: ilTestScoringGUI, ilShopPurchaseGUI
*
* @extends ilObjectGUI
* @ingroup ModulesTest
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjTestGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjTestGUI()
	{
		global $lng, $ilCtrl;
		$lng->loadLanguageModule("assessment");
		$this->type = "tst";
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id");
		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
		// Added parameter if called from crs_objectives
		if((int) $_GET['crs_show_result'])
		{
			$this->ctrl->saveParameter($this,'crs_show_result',(int) $_GET['crs_show_result']);
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilAccess, $ilNavigationHistory;

		if ((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) && (!$ilAccess->checkAccess("visible", "", $_GET["ref_id"])))
		{
			global $ilias;
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}		
		$cmd = $this->ctrl->getCmd("properties");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");
		if (method_exists($this->object, "getTestStyleLocation")) $this->tpl->addCss($this->object->getTestStyleLocation("output"), "screen");
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=".$_GET["ref_id"], "tst");
		}
		
		if(!$this->getCreationMode())
		{
			include_once 'payment/classes/class.ilPaymentObject.php';				
			if(ilPaymentObject::_isBuyable($this->object->getRefId()) && 
			   !ilPaymentObject::_hasAccess($this->object->getRefId()))
			{
				$this->setLocator();
				$this->tpl->getStandardTemplate();
				
				include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$pp = new ilShopPurchaseGUI((int)$_GET['ref_id']);				
				$ret = $this->ctrl->forwardCommand($pp);
				$this->tpl->show();
				exit();			
			}
		}
		
		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreen();	// forwards command
				break;
			case 'ilmdeditorgui':
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$this->prepareOutput();
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
			case "iltestoutputgui":
				include_once "./Modules/Test/classes/class.ilTestOutputGUI.php";

				if (!$this->object->getKioskMode()) $this->prepareOutput();
				$output_gui =& new ilTestOutputGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;

			case "iltestevaluationgui":
				include_once "./Modules/Test/classes/class.ilTestEvaluationGUI.php";
				$this->prepareOutput();
				$evaluation_gui =& new ilTestEvaluationGUI($this->object);
				$this->ctrl->forwardCommand($evaluation_gui);
				break;
				
			case "iltestservicegui":
				include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
				$this->prepareOutput();
				$serviceGUI =& new ilTestServiceGUI($this->object);
				$this->ctrl->forwardCommand($serviceGUI);
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->prepareOutput();
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$this->prepareOutput();
				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,$this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);

				break;

			case "ilcertificategui":
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				$this->prepareOutput();
				include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;

			case "iltestscoringgui":
				include_once "./Modules/Test/classes/class.ilTestScoringGUI.php";
				$this->prepareOutput();
				$output_gui = new ilTestScoringGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;

			default:
				$this->prepareOutput();
				if (preg_match("/deleteqpl_\d+/", $cmd))
				{
					$cmd = "randomQuestions";
				}
				if ((strcmp($cmd, "properties") == 0) && ($_GET["browse"]))
				{
					$this->questionBrowser();
					return;
				}
				if ((strcmp($cmd, "properties") == 0) && ($_GET["up"] || $_GET["down"]))
				{
					$this->questionsObject();
					return;
				}
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

	function runObject()
	{
		$this->ctrl->redirect($this, "infoScreen");
	}
	
	function outEvaluationObject()
	{
		$this->ctrl->redirectByClass("iltestevaluationgui", "outEvaluation");
	}

	/**
	* form for new test object import
	*/
	function importFileObject()
	{
		if ($_POST["qpl"] < 1)
		{
			ilUtil::sendInfo($this->lng->txt("tst_select_questionpools"));
			$this->createObject();
			return;
		}
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("tst_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->ctrl->setParameter($this, "new_type", $this->type);
		$this->uploadTstObject();
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
		if ($_POST["defaults"] > 0) 
		{
			$newObj->applyDefaults($_POST["defaults"]);
		}

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&ref_id=".$newObj->getRefId()."&cmd=properties");
	}

	function backToRepositoryObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
	}
	
	function backToCourseObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?ref_id=".(int) $_GET['crs_show_result']));
	}
	
	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if(count($export_files) > 0)
		{
			foreach($export_files as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Test/classes/tables/class.ilTestExportTableGUI.php";
		$table_gui = new ilTestExportTableGUI($this, 'export');
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
	
	/**
	* create test export file
	*/
	function createTestExportObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once("./Modules/Test/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, 'xml');
			$test_exp->buildExportFile();
		}
		else
		{
			ilUtil::sendInfo("cannot_export_test", TRUE);
		}
		$this->ctrl->redirect($this, "export");
	}
	
	/**
	* create results export file
	*/
	function createTestResultsExportObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once("./Modules/Test/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, 'results');
			$test_exp->buildExportFile();
		}
		else
		{
			ilUtil::sendInfo("cannot_export_test", TRUE);
		}
		$this->ctrl->redirect($this, "export");
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
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if (!isset($_POST["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if (count($_POST["file"]) > 0)
		{
			foreach ($_POST["file"] as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Test/classes/tables/class.ilTestExportTableGUI.php";
		$table_gui = new ilTestExportTableGUI($this, 'export', true);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "export");
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach ($_POST["file"] as $file)
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
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_export_files'), true);
		$this->ctrl->redirect($this, "export");
	}

	/**
	* display dialogue for importing tests
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import", "tst");
		$this->tpl->setCurrentBlock("option_qpl");
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$questionpools =& $tst->getAvailableQuestionpools(TRUE, FALSE, FALSE, TRUE);
		if (count($questionpools) == 0)
		{
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option_qpl");
				$this->tpl->setVariable("OPTION_VALUE", $key);
				$this->tpl->setVariable("TXT_OPTION", $value["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
		$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_NAME", "uploadTst");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("NEW_TYPE", $this->type);
		$this->tpl->setVariable("TXT_IMPORT_TST", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

	}

	/**
	* imports test and question(s)
	*/
	function uploadTstObject()
	{
		if ($_POST["qpl"] < 1)
		{
			ilUtil::sendInfo($this->lng->txt("tst_select_questionpools"));
			$this->importObject();
			return;
		}

		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("error_upload"));
			$this->importObject();
			return;
		}
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		// create import directory
		ilObjTest::_createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = ilObjTest::_getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		// determine filenames of xml files
		$subdir = basename($file["basename"],".".$file["extension"]);
		$xml_file = ilObjTest::_getImportDirectory()."/".$subdir."/".$subdir.".xml";
		$qti_file = ilObjTest::_getImportDirectory()."/".$subdir."/". str_replace("test", "qti", $subdir).".xml";
		// start verification of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir(ilObjTest::_getImportDirectory());

			ilUtil::sendInfo($this->lng->txt("tst_import_no_items"));
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
			ilUtil::delDir(ilObjTest::_getImportDirectory());

			ilUtil::sendInfo($this->lng->txt("qpl_import_non_ilias_files"));
			$this->importObject();
			return;
		}
		
		$_SESSION["tst_import_xml_file"] = $xml_file;
		$_SESSION["tst_import_qti_file"] = $qti_file;
		$_SESSION["tst_import_subdir"] = $subdir;
		// display of found questions
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tst_import_verification.html");
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
				case "MULTIPLE CHOICE QUESTION":
				case QT_MULTIPLE_CHOICE_MR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_multiple_choice"));
					break;
				case "SINGLE CHOICE QUESTION":
				case QT_MULTIPLE_CHOICE_SR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assSingleChoice"));
					break;
				case "NUMERIC QUESTION":
				case QT_NUMERIC:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assNumeric"));
					break;
				case "TEXTSUBSET QUESTION":
				case QT_TEXTSUBSET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextSubset"));
					break;
				case "CLOZE QUESTION":
				case QT_CLOZE:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assClozeTest"));
					break;
				case "IMAGE MAP QUESTION":
				case QT_IMAGEMAP:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assImagemapQuestion"));
					break;
				case "JAVA APPLET QUESTION":
				case QT_JAVAAPPLET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assJavaApplet"));
					break;
				case "MATCHING QUESTION":
				case QT_MATCHING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
					break;
				case "ORDERING QUESTION":
				case QT_ORDERING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingQuestion"));
					break;
				case "TEXT QUESTION":
				case QT_TEXT:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextQuestion"));
					break;
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("question_title"));
		$this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("tst_import_verify_found_questions"));
		$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("QUESTIONPOOL_ID", $_POST["qpl"]);
		$this->tpl->setVariable("VALUE_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* imports question(s) into the questionpool (after verification)
	*/
	function importVerifiedFileObject()
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		// create new questionpool object
		$newObj = new ilObjTest(0, true);
		// set type of questionpool object
		$newObj->setType($_GET["new_type"]);
		// set title of questionpool object to "dummy"
		$newObj->setTitle("dummy");
		// set description of questionpool object
		$newObj->setDescription("test import");
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
		// empty mark schema
		$newObj->mark_schema->flush();

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($_SESSION["tst_import_qti_file"], IL_MO_PARSE_QTI, $_POST["qpl_id"], $_POST["ident"]);
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();
		$newObj->saveToDb();


		
		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $_SESSION["tst_import_xml_file"], $_SESSION["tst_import_subdir"]);
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		// delete import directory
		ilUtil::delDir(ilObjTest::_getImportDirectory());
		ilUtil::sendSuccess($this->lng->txt("object_imported"),true);
		ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjTestGUI");
	}
	
	function cancelImportObject()
	{
		$this->ctrl->redirect($this, "cancel");
//		$this->backToRepositoryObject();
	}
	
	
	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject($redirect = true)
	{
		$this->uploadTstObject();
	}

	/**
	* Displays a save confirmation dialog for test properties
	*
	* Displays a save confirmation dialog for test properties when
	* already defined questions or question pools get lost after saving
	*
	* @param int $direction Direction of the change (0 = from random test to standard, anything else = from standard to random test)
	* @access	private
	*/
	function confirmChangeProperties($direction = 0)
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_properties_save_confirmation.html", "Modules/Test");
		$information = "";
		switch ($direction)
		{
			case 0:
				$information = $this->lng->txt("change_properties_from_random_to_standard");
				break;
			default:
				$information = $this->lng->txt("change_properties_from_standard_to_random");
				break;
		}
		foreach ($_POST as $key => $value)
		{
			if (strcmp($key, "cmd") != 0)
			{
				if (is_array($value))
				{
					foreach ($value as $k => $v)
					{
						$this->tpl->setCurrentBlock("hidden_variable");
						$this->tpl->setVariable("HIDDEN_KEY", $key . "[" . $k . "]");
						$this->tpl->setVariable("HIDDEN_VALUE", $v);
						$this->tpl->parseCurrentBlock();
					}
				}
				else
				{
					$this->tpl->setCurrentBlock("hidden_variable");
					$this->tpl->setVariable("HIDDEN_KEY", $key);
					$this->tpl->setVariable("HIDDEN_VALUE", $value);
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		$this->tpl->setCurrentBlock("hidden_variable");
		$this->tpl->setVariable("HIDDEN_KEY", "tst_properties_confirmation");
		$this->tpl->setVariable("HIDDEN_VALUE", "1");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_CONFIRMATION", $this->lng->txt("confirmation"));
		$this->tpl->setVariable("TXT_INFORMATION", $information);
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save the form input of the scoring form
	*
	* @access	public
	*/
	function saveScoringObject()
	{
		$hasErrors = $this->scoringObject(true);
		if (!$hasErrors)
		{
			$total = $this->object->evalTotalPersons();
			// Check the values the user entered in the form
			if (!$total)
			{
				$this->object->setCountSystem($_POST["count_system"]);
				$this->object->setMCScoring($_POST["mc_scoring"]);
				$this->object->setScoreCutting($_POST["score_cutting"]);
				$this->object->setPassScoring($_POST["pass_scoring"]);
			}

			$this->object->setAnswerFeedback(in_array('instant_feedback_answer', $_POST['instant_feedback']) ? 1 : 0);
			$this->object->setAnswerFeedbackPoints(in_array('instant_feedback_points', $_POST['instant_feedback']) ? 1 : 0);
			$this->object->setInstantFeedbackSolution(in_array('instant_feedback_solution', $_POST['instant_feedback']) ? 1 : 0);

			$this->object->setScoreReporting($_POST["results_access"]);
			if ($this->object->getScoreReporting() == REPORT_AFTER_DATE)
			{
				$this->object->setReportingDate(sprintf("%04d%02d%02d%02d%02d%02d",
					$_POST["reporting_date"]["YY"],
					$_POST["reporting_date"]["MM"],
					$_POST["reporting_date"]["DD"],
					$_POST["reporting_date"]["hh"],
					$_POST["reporting_date"]["mm"],
					$_POST["reporting_date"]["ss"]
				));
			}
			else
			{
				$this->object->setReportingDate('');
			}

			$this->object->setShowPassDetails(in_array('pass_details', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionDetails(in_array('solution_details', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionPrintview(in_array('solution_printview', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionFeedback(in_array('solution_feedback', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionAnswersOnly(in_array('solution_answers_only', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionSignature(in_array('solution_signature', $_POST['results_presentation']) ? 1 : 0);
			$this->object->setShowSolutionSuggested(in_array('solution_suggested', $_POST['results_presentation']) ? 1 : 0);
			$this->object->saveToDb(true);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), TRUE);
			$this->ctrl->redirect($this, "scoring");
		}
	}
	
	/**
	* Display and fill the scoring settings form of the test
	*
	* @access	public
	*/
	function scoringObject($checkonly = FALSE)
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$save = (strcmp($this->ctrl->getCmd(), "saveScoring") == 0) ? TRUE : FALSE;
		$total = $this->object->evalTotalPersons();
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("test_properties_scoring");

		// scoring properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("scoring"));
		$form->addItem($header);
		
		// scoring system
		$count_system = new ilRadioGroupInputGUI($this->lng->txt("tst_text_count_system"), "count_system");
		$count_system->addOption(new ilRadioOption($this->lng->txt("tst_count_partial_solutions"), 0, ''));
		$count_system->addOption(new ilRadioOption($this->lng->txt("tst_count_correct_solutions"), 1, ''));
		$count_system->setValue($this->object->getCountSystem());
		$count_system->setInfo($this->lng->txt("tst_count_system_description"));
		if ($total)
		{
			$count_system->setDisabled(true);
		}
		$form->addItem($count_system);

		// mc questions
		$mc_scoring = new ilRadioGroupInputGUI($this->lng->txt("tst_score_mcmr_questions"), "mc_scoring");
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt("tst_score_mcmr_zero_points_when_unanswered"), 0, ''));
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt("tst_score_mcmr_use_scoring_system"), 1, ''));
		$mc_scoring->setValue($this->object->getMCScoring());
		$mc_scoring->setInfo($this->lng->txt("tst_score_mcmr_questions_description"));
		if ($total)
		{
			$mc_scoring->setDisabled(true);
		}
		$form->addItem($mc_scoring);
		
		// score cutting
		$score_cutting = new ilRadioGroupInputGUI($this->lng->txt("tst_score_cutting"), "score_cutting");
		$score_cutting->addOption(new ilRadioOption($this->lng->txt("tst_score_cut_question"), 0, ''));
		$score_cutting->addOption(new ilRadioOption($this->lng->txt("tst_score_cut_test"), 1, ''));
		$score_cutting->setValue($this->object->getScoreCutting());
		$score_cutting->setInfo($this->lng->txt("tst_score_cutting_description"));
		if ($total)
		{
			$score_cutting->setDisabled(true);
		}
		$form->addItem($score_cutting);
		
		// pass scoring
		$pass_scoring = new ilRadioGroupInputGUI($this->lng->txt("tst_pass_scoring"), "pass_scoring");
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt("tst_pass_last_pass"), 0, ''));
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt("tst_pass_best_pass"), 1, ''));
		$pass_scoring->setValue($this->object->getPassScoring());
		$pass_scoring->setInfo($this->lng->txt("tst_pass_scoring_description"));
		if ($total)
		{
			$pass_scoring->setDisabled(true);
		}
		$form->addItem($pass_scoring);

		// instant feedback
		$instant_feedback = new ilCheckboxGroupInputGUI($this->lng->txt("tst_instant_feedback"), "instant_feedback");
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_answer_specific"), 'instant_feedback_answer', ''));
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_results"), 'instant_feedback_points', ''));
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_solution"), 'instant_feedback_solution', ''));
		$values = array();
		if ($this->object->getAnswerFeedback()) array_push($values, 'instant_feedback_answer');
		if ($this->object->getAnswerFeedbackPoints()) array_push($values, 'instant_feedback_points');
		if ($this->object->getInstantFeedbackSolution()) array_push($values, 'instant_feedback_solution');
		$instant_feedback->setValue($values);
		$instant_feedback->setInfo($this->lng->txt("tst_instant_feedback_description"));
		$form->addItem($instant_feedback);

		// access to test results
		$results_access = new ilRadioGroupInputGUI($this->lng->txt("tst_results_access"), "results_access");
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_finished"), 1, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_always"), 2, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_date"), 3, ''));
		$results_access->setValue($this->object->getScoreReporting());
		$results_access->setInfo($this->lng->txt("tst_results_access_description"));

		// access date
		$reporting_date = new ilDateTimeInputGUI('', 'reporting_date');
		$reporting_date->setShowDate(true);
		$reporting_date->setShowTime(true);
		if (strlen($this->object->getReportingDate()))
		{
			$reporting_date->setDate(new ilDateTime($this->object->getReportingDate(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$reporting_date->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$results_access->addSubItem($reporting_date);
		$form->addItem($results_access);

		// results presentation
		$results_presentation = new ilCheckboxGroupInputGUI($this->lng->txt("tst_results_access"), "results_presentation");
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_pass_details"), 'pass_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_details"), 'solution_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_printview"), 'solution_printview', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_feedback"), 'solution_feedback', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_answers_only"), 'solution_answers_only', ''));
		$signatureOption = new ilCheckboxOption($this->lng->txt("tst_show_solution_signature"), 'solution_signature', '');
		$results_presentation->addOption($signatureOption);
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_suggested"), 'solution_suggested', ''));
		$values = array();
		if ($this->object->getShowPassDetails()) array_push($values, 'pass_details');
		if ($this->object->getShowSolutionDetails()) array_push($values, 'solution_details');
		if ($this->object->getShowSolutionPrintview()) array_push($values, 'solution_printview');
		if ($this->object->getShowSolutionFeedback()) array_push($values, 'solution_feedback');
		if ($this->object->getShowSolutionAnswersOnly()) array_push($values, 'solution_answers_only');
		if ($this->object->getShowSolutionSignature()) array_push($values, 'solution_signature');
		if ($this->object->getShowSolutionSuggested()) array_push($values, 'solution_suggested');
		$results_presentation->setValue($values);
		$results_presentation->setInfo($this->lng->txt("tst_results_access_description"));
		if ($this->object->getAnonymity())
		{
			$signatureOption->setDisabled(true);
		}
		$form->addItem($results_presentation);
		
		$form->addCommandButton("saveScoring", $this->lng->txt("save"));
		$errors = false;

		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if ($errors) $checkonly = false;
		}
		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	/**
	* Display and fill the properties form of the test
	*
	* @access	public
	*/
	function propertiesObject($checkonly = FALSE)
	{
		$save = (strcmp($this->ctrl->getCmd(), "saveProperties") == 0) ? TRUE : FALSE;
		$total = $this->object->evalTotalPersons();
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("test_properties");

		// general properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("tst_general_properties"));
		$form->addItem($header);
		
		// anonymity
		$anonymity = new ilCheckboxInputGUI($this->lng->txt("tst_anonymity"), "anonymity");
		$anonymity->setValue(1);
		if ($total) $anonymity->setDisabled(true);
		$anonymity->setChecked($this->object->getAnonymity());
		$anonymity->setInfo($this->lng->txt("tst_anonymity_description"));
		$form->addItem($anonymity);

		// random selection of questions
		$random = new ilCheckboxInputGUI($this->lng->txt("tst_random_selection"), "random_test");
		$random->setValue(1);
		if ($total) $random->setDisabled(true);
		$random->setChecked($this->object->isRandomTest());
		$random->setInfo($this->lng->txt("tst_random_test_description"));
		$form->addItem($random);

		// introduction
		$intro = new ilTextAreaInputGUI($this->lng->txt("tst_introduction"), "introduction");
		$intro->setValue(ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getIntroduction())));
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->addPlugin("latex");
		$intro->addButton("latex");
		$intro->setRTESupport($this->object->getId(), "tst", "assessment");
		$intro->setRteTagSet('full');
		// showinfo
		$showinfo = new ilCheckboxInputGUI('', "showinfo");
		$showinfo->setValue(1);
		$showinfo->setChecked($this->object->getShowInfo());
		$showinfo->setOptionTitle($this->lng->txt("showinfo"));
		$showinfo->setInfo($this->lng->txt("showinfo_desc"));
		$intro->addSubItem($showinfo);
		$form->addItem($intro);

		// final statement
		$finalstatement = new ilTextAreaInputGUI($this->lng->txt("final_statement"), "finalstatement");
		$finalstatement->setValue(ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFinalStatement())));
		$finalstatement->setRows(10);
		$finalstatement->setCols(80);
		$finalstatement->setUseRte(TRUE);
		$finalstatement->addPlugin("latex");
		$finalstatement->addButton("latex");
		$finalstatement->setRTESupport($this->object->getId(), "tst", "assessment");
		$finalstatement->setRteTagSet('full');
		// show final statement
		$showfinal = new ilCheckboxInputGUI('', "showfinalstatement");
		$showfinal->setValue(1);
		$showfinal->setChecked($this->object->getShowFinalStatement());
		$showfinal->setChecked($this->object->getShowInfo());
		$showfinal->setOptionTitle($this->lng->txt("final_statement_show"));
		$showfinal->setInfo($this->lng->txt("final_statement_show_desc"));
		$finalstatement->addSubItem($showfinal);
		$form->addItem($finalstatement);

		// sequence properties
		$seqheader = new ilFormSectionHeaderGUI();
		$seqheader->setTitle($this->lng->txt("tst_sequence_properties"));
		$form->addItem($seqheader);

		// postpone questions
		$postpone = new ilCheckboxInputGUI($this->lng->txt("tst_postpone"), "chb_postpone");
		$postpone->setValue(1);
		$postpone->setChecked($this->object->getSequenceSettings());
		$postpone->setInfo($this->lng->txt("tst_postpone_description"));
		$form->addItem($postpone);
		
		// shuffle questions
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("tst_shuffle_questions"), "chb_shuffle_questions");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getShuffleQuestions());
		$shuffle->setInfo($this->lng->txt("tst_shuffle_questions_description"));
		$form->addItem($shuffle);

		// show list of questions
		$list_of_questions = new ilCheckboxInputGUI($this->lng->txt("tst_show_summary"), "list_of_questions");
		$list_of_questions->setOptionTitle($this->lng->txt("tst_show_summary"));
		$list_of_questions->setValue(1);
		$list_of_questions->setChecked($this->object->getListOfQuestions());
		$list_of_questions->setInfo($this->lng->txt("tst_show_summary_description"));

		$list_of_questions_options = new ilCheckboxGroupInputGUI('', "list_of_questions_options");
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_start"), 'chb_list_of_questions_start', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_end"), 'chb_list_of_questions_end', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_with_description"), 'chb_list_of_questions_with_description', ''));
		$values = array();
		if ($this->object->getListOfQuestionsStart()) array_push($values, 'chb_list_of_questions_start');
		if ($this->object->getListOfQuestionsEnd()) array_push($values, 'chb_list_of_questions_end');
		if ($this->object->getListOfQuestionsDescription()) array_push($values, 'chb_list_of_questions_with_description');
		$list_of_questions_options->setValue($values);

		$list_of_questions->addSubItem($list_of_questions_options);
		$form->addItem($list_of_questions);

		// show question marking
		$marking = new ilCheckboxInputGUI($this->lng->txt("question_marking"), "chb_show_marker");
		$marking->setValue(1);
		$marking->setChecked($this->object->getShowMarker());
		$marking->setInfo($this->lng->txt("question_marking_description"));
		$form->addItem($marking);

		// show suspend test
		$cancel = new ilCheckboxInputGUI($this->lng->txt("tst_show_cancel"), "chb_show_cancel");
		$cancel->setValue(1);
		$cancel->setChecked($this->object->getShowCancel());
		$cancel->setInfo($this->lng->txt("tst_show_cancel_description"));
		$form->addItem($cancel);

		// kiosk mode properties
		$kioskheader = new ilFormSectionHeaderGUI();
		$kioskheader->setTitle($this->lng->txt("kiosk"));
		$form->addItem($kioskheader);

		// kiosk mode
		$kiosk = new ilCheckboxInputGUI($this->lng->txt("kiosk"), "kiosk");
		$kiosk->setValue(1);
		$kiosk->setChecked($this->object->getKioskMode());
		$kiosk->setInfo($this->lng->txt("kiosk_description"));

		// kiosk mode options
		$kiosktitle = new ilCheckboxGroupInputGUI($this->lng->txt("kiosk_options"), "kiosk_options");
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_title"), 'kiosk_title', ''));
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_participant"), 'kiosk_participant', ''));
		$values = array();
		if ($this->object->getShowKioskModeTitle()) array_push($values, 'kiosk_title');
		if ($this->object->getShowKioskModeParticipant()) array_push($values, 'kiosk_participant');
		$kiosktitle->setValue($values);
		$kiosktitle->setInfo($this->lng->txt("kiosk_options_desc"));
		$kiosk->addSubItem($kiosktitle);

		$form->addItem($kiosk);

		// session properties
		$sessionheader = new ilFormSectionHeaderGUI();
		$sessionheader->setTitle($this->lng->txt("tst_session_settings"));
		$form->addItem($sessionheader);

		// max. number of passes
		$nr_of_tries = new ilTextInputGUI($this->lng->txt("tst_nr_of_tries"), "nr_of_tries");
		$nr_of_tries->setSize(3);
		$nr_of_tries->setValue($this->object->getNrOfTries());
		$nr_of_tries->setRequired(true);
		$nr_of_tries->setSuffix($this->lng->txt("0_unlimited"));
		$form->addItem($nr_of_tries);

		// enable max. processing time
		$processing = new ilCheckboxInputGUI($this->lng->txt("tst_processing_time"), "chb_processing_time");
		$processing->setValue(1);
		$processing->setOptionTitle($this->lng->txt("enabled"));
		$processing->setChecked($this->object->getEnableProcessingTime());
		
		// max. processing time
		$processingtime = new ilDurationInputGUI('', 'processing_time');
		$ptime = $this->object->getProcessingTimeAsArray();
		$processingtime->setHours($ptime['hh']);
		$processingtime->setMinutes($ptime['mm']);
		$processingtime->setSeconds($ptime['ss']);
		$processingtime->setShowMonths(false);
		$processingtime->setShowDays(false);
		$processingtime->setShowHours(true);
		$processingtime->setShowMinutes(true);
		$processingtime->setShowSeconds(true);
		$processingtime->setInfo($this->lng->txt("tst_processing_time_desc"));
		$processing->addSubItem($processingtime);
		
		// reset max. processing time
		$resetprocessing = new ilCheckboxInputGUI('', "chb_reset_processing_time");
		$resetprocessing->setValue(1);
		$resetprocessing->setOptionTitle($this->lng->txt("tst_reset_processing_time"));
		$resetprocessing->setChecked($this->object->getResetProcessingTime());
		$resetprocessing->setInfo($this->lng->txt("tst_reset_processing_time_desc"));
		$processing->addSubItem($resetprocessing);
		$form->addItem($processing);

		// enable starting time
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("tst_starting_time"), "chb_starting_time");
		$enablestartingtime->setValue(1);
		$enablestartingtime->setOptionTitle($this->lng->txt("enabled"));
		$enablestartingtime->setChecked(strlen($this->object->getStartingTime()));
		// starting time
		$startingtime = new ilDateTimeInputGUI('', 'starting_time');
		$startingtime->setShowDate(true);
		$startingtime->setShowTime(true);
		if (strlen($this->object->getStartingTime()))
		{
			$startingtime->setDate(new ilDateTime($this->object->getStartingTime(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$startingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enablestartingtime->addSubItem($startingtime);
		$form->addItem($enablestartingtime);

		// enable ending time
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("tst_ending_time"), "chb_ending_time");
		$enableendingtime->setValue(1);
		$enableendingtime->setOptionTitle($this->lng->txt("enabled"));
		$enableendingtime->setChecked(strlen($this->object->getEndingTime()));
		// ending time
		$endingtime = new ilDateTimeInputGUI('', 'ending_time');
		$endingtime->setShowDate(true);
		$endingtime->setShowTime(true);
		if (strlen($this->object->getEndingTime()))
		{
			$endingtime->setDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$endingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enableendingtime->addSubItem($endingtime);
		$form->addItem($enableendingtime);

		// use previous answers
		$prevanswers = new ilCheckboxInputGUI($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers");
		$prevanswers->setValue(1);
		$prevanswers->setChecked($this->object->getUsePreviousAnswers());
		$prevanswers->setInfo($this->lng->txt("tst_use_previous_answers_description"));
		$form->addItem($prevanswers);

		// force js
		$forcejs = new ilCheckboxInputGUI($this->lng->txt("forcejs_short"), "forcejs");
		$forcejs->setValue(1);
		$forcejs->setChecked($this->object->getUsePreviousAnswers());
		$forcejs->setOptionTitle($this->lng->txt("forcejs"));
		$forcejs->setInfo($this->lng->txt("forcejs_desc"));
		$form->addItem($forcejs);

		// question title output
		$title_output = new ilRadioGroupInputGUI($this->lng->txt("tst_title_output"), "title_output");
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_full"), 0, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_hide_points"), 1, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_no_title"), 2, ''));
		$title_output->setValue($this->object->getTitleOutput());
		$title_output->setInfo($this->lng->txt("tst_title_output_description"));
		$form->addItem($title_output);

		// test password
		$password = new ilTextInputGUI($this->lng->txt("tst_password"), "password");
		$password->setSize(20);
		$password->setValue($this->object->getPassword());
		$password->setInfo($this->lng->txt("tst_password_details"));
		$form->addItem($password);

		// participants properties
		$restrictions = new ilFormSectionHeaderGUI();
		$restrictions->setTitle($this->lng->txt("tst_max_allowed_users"));
		$form->addItem($restrictions);

		// simultaneous users
		$simul = new ilTextInputGUI($this->lng->txt("tst_allowed_users"), "allowedUsers");
		$simul->setSize(3);
		$simul->setValue(($this->object->getAllowedUsers()) ? $this->object->getAllowedUsers() : '');
		$form->addItem($simul);

		// idle time
		$idle = new ilTextInputGUI($this->lng->txt("tst_allowed_users_time_gap"), "allowedUsersTimeGap");
		$idle->setSize(4);
		$idle->setSuffix($this->lng->txt("seconds"));
		$idle->setValue(($this->object->getAllowedUsersTimeGap()) ? $this->object->getAllowedUsersTimeGap() : '');
		$form->addItem($idle);

		// notifications
		$notifications = new ilFormSectionHeaderGUI();
		$notifications->setTitle($this->lng->txt("notifications"));
		$form->addItem($notifications);

		// mail notification
		$mailnotification = new ilRadioGroupInputGUI($this->lng->txt("tst_finish_notification"), "mailnotification");
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_no"), 0, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_simple"), 1, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_advanced"), 2, ''));
		$mailnotification->setValue($this->object->getMailNotification());
		$form->addItem($mailnotification);

		$form->addCommandButton("saveProperties", $this->lng->txt("save"));
		$errors = false;
		
		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if ($errors) $checkonly = false;
		}
		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	/**
	* Save the form input of the properties form
	*
	* @access	public
	*/
	function savePropertiesObject()
	{
		if (!array_key_exists("tst_properties_confirmation", $_POST))
		{
			$hasErrors = $this->propertiesObject(true);
		}
		else
		{
			$hasErrors = false;
		}
		if (!$hasErrors)
		{
			$total = $this->object->evalTotalPersons();
			$randomtest_switch = false;
			// Check the values the user entered in the form
			if (!$total)
			{
				if (!array_key_exists("tst_properties_confirmation", $_POST))
				{
					if (($this->object->isRandomTest()) && (count($this->object->getRandomQuestionpools()) > 0))
					{
						if (!$_POST["random_test"])
						{
							// user tries to change from a random test with existing random question pools to a non random test
							$this->confirmChangeProperties(0);
							return;
						}
					}
					if ((!$this->object->isRandomTest()) && (count($this->object->questions) > 0))
					{
						if ($_POST["random_test"])
						{
							// user tries to change from a non random test with existing questions to a random test
							$this->confirmChangeProperties(1);
							return;
						}
					}
				}

				if (!strlen($_POST["random_test"]))
				{
					$random_test = 0;
				}
				else
				{
					$random_test = ilUtil::stripSlashes($_POST["random_test"]);
				}
			}
			else
			{
				$random_test = $this->object->isRandomTest();
			}
			if ($random_test != $this->object->isRandomTest())
			{
				$randomtest_switch = true;
			}
			
			if (!$total)
			{
				$this->object->setAnonymity($_POST["anonymity"]);
				$this->object->setRandomTest($random_test);
			}
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$this->object->setIntroduction(ilUtil::stripSlashes($_POST["introduction"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
			$this->object->setShowInfo(($_POST["showinfo"]) ? 1 : 0);
			$this->object->setFinalStatement(ilUtil::stripSlashes($_POST["finalstatement"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
			$this->object->setShowFinalStatement(($_POST["showfinalstatement"]) ? 1 : 0);
			$this->object->setSequenceSettings(($_POST["chb_postpone"]) ? 1 : 0);
			$this->object->setShuffleQuestions(($_POST["chb_shuffle_questions"]) ? 1 : 0);
			$this->object->setListOfQuestions($_POST["list_of_questions"]);
			if (is_array($_POST["list_of_questions_options"]))
			{
				$this->object->setListOfQuestionsStart((in_array('chb_list_of_questions_start', $_POST["list_of_questions_options"])) ? 1 : 0);
				$this->object->setListOfQuestionsEnd((in_array('chb_list_of_questions_end', $_POST["list_of_questions_options"])) ? 1 : 0);
				$this->object->setListOfQuestionsDescription((in_array('chb_list_of_questions_with_description', $_POST["list_of_questions_options"])) ? 1 : 0);
			}
			else
			{
				$this->object->setListOfQuestionsStart(0);
				$this->object->setListOfQuestionsEnd(0);
				$this->object->setListOfQuestionsDescription(0);
			}
			$this->object->setMailNotification($_POST["mailnotification"]);
			$this->object->setShowMarker(($_POST["chb_show_marker"]) ? 1 : 0);
			$this->object->setShowCancel(($_POST["chb_show_cancel"]) ? 1 : 0);
			$this->object->setKioskMode(($_POST["kiosk"]) ? 1 : 0);
			$this->object->setShowKioskModeTitle((is_array($_POST["kiosk_options"]) && in_array('kiosk_title', $_POST["kiosk_options"])) ? 1 : 0);
			$this->object->setShowKioskModeParticipant((is_array($_POST["kiosk_options"]) && in_array('kiosk_participant', $_POST["kiosk_options"])) ? 1 : 0);
			$this->object->setNrOfTries($_POST["nr_of_tries"]);
			$this->object->setEnableProcessingTime(($_POST["chb_processing_time"]) ? 1 : 0);
			if ($this->object->getEnableProcessingTime())
			{
				$this->object->setProcessingTime(sprintf("%02d:%02d:%02d",
					$_POST["processing_time"]["hh"],
					$_POST["processing_time"]["mm"],
					$_POST["processing_time"]["ss"]
				));
			}
			else
			{
				$this->object->setProcessingTime('');
			}
			$this->object->setResetProcessingTime(($_POST["chb_reset_processing_time"]) ? 1 : 0);
			if ($_POST['chb_starting_time'])
			{
				$this->object->setStartingTime(ilFormat::dateDB2timestamp($_POST['starting_time']['date'] . ' ' . $_POST['starting_time']['time']));
			}
			else
			{
				$this->object->setStartingTime('');
			}
			if ($_POST['chb_ending_time'])
			{
				$this->object->setEndingTime(ilFormat::dateDB2timestamp($_POST['ending_time']['date'] . ' ' . $_POST['ending_time']['time']));
			}
			else
			{
				$this->object->setEndingTime('');
			}
			$this->object->setUsePreviousAnswers(($_POST["chb_use_previous_answers"]) ? 1 : 0);
			$this->object->setForceJS(($_POST["forcejs"]) ? 1 : 0);
			$this->object->setTitleOutput($_POST["title_output"]);
			$this->object->setPassword(ilUtil::stripSlashes($_POST["password"]));
			$this->object->setAllowedUsers(ilUtil::stripSlashes($_POST["allowedUsers"]));
			$this->object->setAllowedUsersTimeGap(ilUtil::stripSlashes($_POST["allowedUsersTimeGap"]));

			if ($this->object->isRandomTest())
			{
				$this->object->setUsePreviousAnswers(0);
			}

			$this->object->saveToDb(true);

			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			if ($randomtest_switch)
			{
				if ($this->object->isRandomTest())
				{
					$this->object->removeNonRandomTestData();
				}
				else
				{
					$this->object->removeRandomTestData();
				}
			}
			$this->ctrl->redirect($this, 'properties');
		}
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
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		//$page =& new ilPageObject("qpl", $_GET["pg_id"]);
		$page_gui =& new ilPageObjectGUI("qpl", $_GET["pg_id"]);
		$page_gui->showMediaFullscreen();
		
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
	* Sets the filter for the question browser 
	*
	* Sets the filter for the question browser 
	*
	* @access	public
	*/
	function filterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Resets the filter for the question browser 
	*
	* Resets the filter for the question browser 
	*
	* @access	public
	*/
	function resetFilterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Called when the back button in the question browser was pressed 
	*
	* Called when the back button in the question browser was pressed 
	*
	* @access	public
	*/
	function backObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Creates a new questionpool and returns the reference id
	*
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	function createQuestionPool($name = "dummy")
	{
		global $tree;
		$parent_ref = $tree->getParentId($this->object->getRefId());
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		return $qpl->getRefId();
	}

	/**
	* Creates a form for random selection of questions
	*
	* Creates a form for random selection of questions
	*
	* @access	public
	*/
	function randomselectObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE);
		$this->tpl->setCurrentBlock("option");
		$this->tpl->setVariable("VALUE_OPTION", "0");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("all_available_question_pools"));
		$this->tpl->parseCurrentBlock();
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_random_select_questionpool"));
		$this->tpl->setVariable("TXT_NR_OF_QUESTIONS", $this->lng->txt("tst_random_nr_of_questions"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Cancels the form for random selection of questions
	*
	* Cancels the form for random selection of questions
	*
	* @access	public
	*/
	function cancelRandomSelectObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Offers a random selection for insertion in the test
	*
	* Offers a random selection for insertion in the test
	*
	* @access	public
	*/
	function createRandomSelectionObject()
	{
		$this->getQuestionsSubTabs();
		$question_array = $this->object->randomSelectQuestions($_POST["nr_of_questions"], $_POST["sel_qpl"]);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_question_offer.html", "Modules/Test");
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->getAvailableQuestionpools(true);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		foreach ($question_array as $question_id)
		{
			$dataset = $this->object->getQuestionDataset($question_id);
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $dataset->title);
			$this->tpl->setVariable("QUESTION_COMMENT", $dataset->description);
			$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($dataset->type_tag));
			$this->tpl->setVariable("QUESTION_AUTHOR", $dataset->author);
			$this->tpl->setVariable("QUESTION_POOL", $questionpools[$dataset->obj_fi]["title"]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($question_array) == 0)
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_NO_QUESTIONS_AVAILABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
			else
		{
			$this->tpl->setCurrentBlock("Selectionbuttons");
			$this->tpl->setVariable("BTN_YES", $this->lng->txt("random_accept_sample"));
			$this->tpl->setVariable("BTN_NO", $this->lng->txt("random_another_sample"));
			$this->tpl->parseCurrentBlock();
		}
		$chosen_questions = join($question_array, ",");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));
		$this->tpl->setVariable("VALUE_CHOSEN_QUESTIONS", $chosen_questions);
		$this->tpl->setVariable("VALUE_QUESTIONPOOL_SELECTION", $_POST["sel_qpl"]);
		$this->tpl->setVariable("VALUE_NR_OF_QUESTIONS", $_POST["nr_of_questions"]);
		$this->tpl->setVariable("TEXT_QUESTION_OFFER", $this->lng->txt("tst_question_offer"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Inserts a random selection into the test
	*
	* Inserts a random selection into the test
	*
	* @access	public
	*/
	function insertRandomSelectionObject()
	{
		$selected_array = split(",", $_POST["chosen_questions"]);
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"));
		}
		else
		{
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion($value);
			}
			$this->object->saveCompleteStatus();
			ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), true);
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}
	
	function addQuestionpoolObject()
	{
		$this->randomQuestionsObject();
	}
	
	/**
	* Evaluates a posted random question form and saves the form data
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writeRandomQuestionInput($always = false)
	{
		$hasErrors = (!$always) ? $this->randomQuestionsObject(true) : false;
		if (!$hasErrors)
		{
			global $ilUser;
			$ilUser->setPref("tst_question_selection_mode_equal", ($_POST['chbQuestionSelectionMode']) ? 1 : 0);
			$ilUser->writePref("tst_question_selection_mode_equal", ($_POST['chbQuestionSelectionMode']) ? 1 : 0);
			$this->object->setRandomQuestionCount($_POST['total_questions']);
			if (is_array($_POST['source']['qpl']))
			{
				$data = array();
				include_once "./Modules/Test/classes/class.ilRandomTestData.php";
				foreach ($_POST['source']['qpl'] as $idx => $qpl)
				{
					array_push($data, new ilRandomTestData($_POST['source']['count'][$idx], $qpl));
				}
				$this->object->setRandomQuestionpoolData($data);
			}
			return 0;
		}
		return 1;
	}

	function saveRandomQuestionsObject()
	{
		if ($this->writeRandomQuestionInput() == 0)
		{
			$this->object->saveRandomQuestionCount($this->object->getRandomQuestionCount());
			$this->object->saveRandomQuestionpools();
			$this->object->saveCompleteStatus();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, 'randomQuestions');
		}
	}
		
	function addsourceObject()
	{
		$this->writeRandomQuestionInput(true);
		$position = key($_POST['cmd']['addsource']);
		$this->object->addRandomQuestionpoolData(0, 0, $position+1);
		$this->randomQuestionsObject();
	}
	
	function removesourceObject()
	{
		$this->writeRandomQuestionInput(true);
		$position = key($_POST['cmd']['removesource']);
		$this->object->removeRandomQuestionpoolData($position);
		$this->randomQuestionsObject();
	}

	function randomQuestionsObject()
	{
		global $ilUser;

		$total = $this->object->evalTotalPersons();
		$save = (strcmp($this->ctrl->getCmd(), "saveRandomQuestions") == 0) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'randomQuestions'));
		$form->setTitle($this->lng->txt('random_selection'));
		$form->setDescription($this->lng->txt('tst_select_random_questions'));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("randomSelectionForm");

		// question selection
		$selection_mode = $ilUser->getPref("tst_question_selection_mode_equal");
		$question_selection = new ilCheckboxInputGUI($this->lng->txt("tst_question_selection"), "chbQuestionSelectionMode");
		$question_selection->setValue(1);
		$question_selection->setChecked($selection_mode);
		$question_selection->setOptionTitle($this->lng->txt('tst_question_selection_equal'));
		$question_selection->setInfo($this->lng->txt('tst_question_selection_description'));
		$question_selection->setRequired(false);
		$form->addItem($question_selection);
		
		// total amount of questions
		$total_questions = new ilNumberInputGUI($this->lng->txt('tst_total_questions'), 'total_questions');
		$total_questions->setValue($this->object->getRandomQuestionCount());
		$total_questions->setSize(3);
		$total_questions->setInfo($this->lng->txt('tst_total_questions_description'));
		$total_questions->setRequired(false);
		$form->addItem($total_questions);

		if ($total == 0)
		{
			$found_qpls = $this->object->getRandomQuestionpoolData();
			include_once "./Modules/Test/classes/class.ilRandomTestData.php";
			if (count($found_qpls) == 0)
			{
				array_push($found_qpls, new ilRandomTestData());
			}
			$available_qpl =& $this->object->getAvailableQuestionpools(TRUE, $selection_mode, FALSE, TRUE, TRUE);
			include_once './Modules/Test/classes/class.ilRandomTestInputGUI.php';
			$source = new ilRandomTestInputGUI($this->lng->txt('tst_random_questionpools'), 'source');
			$source->setUseEqualPointsOnly($selection_mode);
			$source->setRandomQuestionPools($available_qpl);
			$source->setUseQuestionCount((array_key_exists('total_questions', $_POST)) ? ($_POST['total_questions'] < 1) : ($this->object->getRandomQuestionCount() < 1));
			$source->setValues($found_qpls);
			$form->addItem($source);
		}
		else
		{
			$qpls = $this->object->getUsedRandomQuestionpools();
			include_once './Modules/Test/classes/class.ilRandomTestROInputGUI.php';
			$source = new ilRandomTestROInputGUI($this->lng->txt('tst_random_questionpools'), 'source');
			$source->setValues($qpls);
			$form->addItem($source);
		}

		if ($total == 0) $form->addCommandButton("saveRandomQuestions", $this->lng->txt("save"));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if (!$errors)
			{
				// check total amount of questions
				if ($_POST['total_questions'] > 0)
				{
					$totalcount = 0;
					foreach ($_POST['source']['qpl'] as $idx => $qpl)
					{
						$totalcount += $available_qpl[$qpl]['count'];
					}
					if ($_POST['total_questions'] > $totalcount)
					{
						$total_questions->setAlert($this->lng->txt('msg_total_questions_too_high'));
						$errors = true;
					}
				}
			}
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	function saveQuestionSelectionModeObject()
	{
		global $ilUser;
		if ($_POST["chbQuestionSelectionMode"])
		{
			$ilUser->setPref("tst_question_selection_mode_equal", 1);
			$ilUser->writePref("tst_question_selection_mode_equal", 1);
		}
		else
		{
			$ilUser->setPref("tst_question_selection_mode_equal", 0);
			$ilUser->writePref("tst_question_selection_mode_equal", 0);
		}
		$this->randomQuestionsObject();
	}

	function browseForQuestionsObject()
	{
		$this->questionBrowser();
	}
	
	/**
	* Called when a new question should be created from a test after confirmation
	*
	* Called when a new question should be created from a test after confirmation
	*
	* @access	public
	*/
	function executeCreateQuestionObject()
	{
		$qpl_ref_id = $_POST["sel_qpl"];
		if ((strcmp($_POST["txt_qpl"], "") == 0) && (strcmp($qpl_ref_id, "") == 0))
		{
			ilUtil::sendInfo($this->lng->txt("questionpool_not_entered"));
			$this->createQuestionObject();
			return;
		}
		else
		{
			$_SESSION["test_id"] = $this->object->getRefId();
			if (strcmp($_POST["txt_qpl"], "") != 0)
			{
				// create a new question pool and return the reference id
				$qpl_ref_id = $this->createQuestionPool($_POST["txt_qpl"]);
			}
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolGUI.php";
			ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=".$_GET["ref_id"]."&sel_question_types=" . $_POST["sel_question_types"]);
			exit();
		}
	}

	/**
	* Called when the creation of a new question is cancelled
	*
	* Called when the creation of a new question is cancelled
	*
	* @access	public
	*/
	function cancelCreateQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* Called when a new question should be created from a test
	*
	* Called when a new question should be created from a test
	*
	* @access	public
	*/
	function createQuestionObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE, FALSE, "write");
		if (count($questionpools) == 0)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_QPL", "");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option");
				$this->tpl->setVariable("VALUE_OPTION", $key);
				$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));

		if (count($questionpools) == 0)
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_enter_questionpool"));
		}
		else
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_select_questionpool"));
		}
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Remove questions from the test after confirmation
	*
	* Remove questions from the test after confirmation
	*
	* @access	public
	*/
	function confirmRemoveQuestionsObject()
	{
		ilUtil::sendSuccess($this->lng->txt("tst_questions_removed"));
		$checked_questions = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}
		foreach ($checked_questions as $key => $value) {
			$this->object->removeQuestion($value);
		}
		$this->object->saveCompleteStatus();
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Cancels the removal of questions from the test
	*
	* Cancels the removal of questions from the test
	*
	* @access	public
	*/
	function cancelRemoveQuestionsObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Displays a form to confirm the removal of questions from the test
	*
	* Displays a form to confirm the removal of questions from the test
	*
	* @access	public
	*/
	function removeQuestionsForm($checked_questions)
	{
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_remove_questions.html", "Modules/Test");
		$removablequestions =& $this->object->getTestQuestions();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if (count($removablequestions))
		{
			foreach ($removablequestions as $data)
			{
				if (in_array($data["question_id"], $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("TXT_TITLE", $data["title"]);
					$this->tpl->setVariable("TXT_DESCRIPTION", $data["description"]);
					$this->tpl->setVariable("TXT_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
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
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Called when a selection of questions should be removed from the test
	*
	* Called when a selection of questions should be removed from the test
	*
	* @access	public
	*/
	function removeQuestionsObject()
	{
		$this->getQuestionsSubTabs();
		$checked_questions = $_POST["q_id"];
		if (count($checked_questions) > 0) 
		{
			$total = $this->object->evalTotalPersons();
			if ($total) 
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_remove_questions_and_results"), $total));
			} 
			else 
			{
				ilUtil::sendInfo($this->lng->txt("tst_remove_questions"));
			}
			$this->removeQuestionsForm($checked_questions);
			return;
		} 
		elseif (count($checked_questions) == 0) 
		{
			ilUtil::sendInfo($this->lng->txt("tst_no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Marks selected questions for moving
	*/
	function moveQuestionsObject()
	{
		$_SESSION['tst_qst_move_' . $this->object->getTestId()] = $_POST['q_id'];
		ilUtil::sendSuccess($this->lng->txt("msg_selected_for_move"), true);
		$this->ctrl->redirect($this, 'questions');
	}
	
	/**
	* Insert checked questions before the actual selection
	*/
	public function insertQuestionsBeforeObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];

		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 0;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert checked questions after the actual selection
	*/
	public function insertQuestionsAfterObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];
		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 1;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert questions from the questionbrowser into the test 
	*
	* @access	public
	*/
	function insertQuestionsObject()
	{
		$selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : array();
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"), true);
			$this->ctrl->redirect($this, "browseForQuestions");
		}
		else
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$manscoring = FALSE;
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion($value);
				if (!$manscoring)
				{
					$manscoring = $manscoring | assQuestion::_needsManualScoring($value);
				}
			}
			$this->object->saveCompleteStatus();
			if ($manscoring)
			{
				ilUtil::sendInfo($this->lng->txt("manscoring_hint"), TRUE);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), TRUE);
			}
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}

	function filterAvailableQuestionsObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions');
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, "browseForQuestions");
	}
	
	/**
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* @access	public
	*/
	function questionBrowser()
	{
		global $ilAccess;

		$this->ctrl->setParameterByClass(get_class($this), "browse", "1");

		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getAvailableQuestions($arrFilter, 1);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	function questionsObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		if ($_GET['browse'])
		{
			return $this->questionbrowser();
		}

		$this->getQuestionsSubTabs();
		if ($this->object->isRandomTest())
		{
			$this->randomQuestionsObject();
			return;
		}
		
		if ($_GET["eqid"] and $_GET["eqpl"])
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForTest&calling_test=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}
		
		if ($_GET["up"] > 0)
		{
			$this->object->questionMoveUp($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->questionMoveDown($_GET["down"]);
		}

		if ($_GET["add"])
		{
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test");

		$total = $this->object->evalTotalPersons();
		if (($ilAccess->checkAccess("write", "", $this->ref_id) and ($total == 0))) 
		{
			global $ilUser;
			$lastquestiontype = $ilUser->getPref("tst_lastquestiontype");
			$this->tpl->setCurrentBlock("QTypes");
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			$question_types =& ilObjQuestionPool::_getQuestionTypes();
			foreach ($question_types as $trans => $data)
			{
				if ($data["type_tag"] == $lastquestiontype)
				{
					$this->tpl->setVariable("QUESTION_TYPE_SELECTED", " selected=\"selected\"");
				}
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data["type_tag"]);
				$this->tpl->setVariable("QUESTION_TYPE", $trans);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}

		if (($ilAccess->checkAccess("write", "", $this->ref_id) and ($total == 0))) 
		{
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("tst_browse_for_questions"));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
			$this->tpl->setVariable("TXT_OR", $this->lng->txt("or"));
			$this->tpl->setVariable("TEXT_RANDOM_SELECT", $this->lng->txt("random_selection"));
		}

		$this->tpl->setCurrentBlock("adm_content");
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionsTableGUI.php";
		$checked_move = is_array($_SESSION['tst_qst_move_' . $this->object->getTestId()]) && (count($_SESSION['tst_qst_move_' . $this->object->getTestId()]));
		$table_gui = new ilTestQuestionsTableGUI($this, 'questions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)), $checked_move);
		$data = $this->object->getTestQuestions();
		$table_gui->setData($data);
		$table_gui->setTotal($total);
		$this->tpl->setVariable('QUESTIONBROWSER', $table_gui->getHTML());	
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	function takenObject() {
	}
	
	/**
	* Add a new mark step to the tests marks
	*
	* Add a new mark step to the tests marks
	*
	* @access	public
	*/
	function addMarkStepObject()
	{
		$this->saveMarkSchemaFormData();
		$this->object->mark_schema->addMarkStep();
		$this->marksObject();
	}

	/**
	* Save the mark schema POST data when the form was submitted
	*
	* Save the mark schema POST data when the form was submitted
	*
	* @access	public
	*/
	function saveMarkSchemaFormData()
	{
		$this->object->mark_schema->flush();
		foreach ($_POST as $key => $value) {
			if (preg_match("/mark_short_(\d+)/", $key, $matches)) 
			{
				$this->object->mark_schema->addMarkStep($_POST["mark_short_$matches[1]"], $_POST["mark_official_$matches[1]"], $_POST["mark_percentage_$matches[1]"], $_POST["passed_$matches[1]"]);
			}
		}
		$this->object->ects_grades["A"] = $_POST["ects_grade_a"];
		$this->object->ects_grades["B"] = $_POST["ects_grade_b"];
		$this->object->ects_grades["C"] = $_POST["ects_grade_c"];
		$this->object->ects_grades["D"] = $_POST["ects_grade_d"];
		$this->object->ects_grades["E"] = $_POST["ects_grade_e"];
		if ($_POST["chbUseFX"])
		{
			$this->object->ects_fx = $_POST["percentFX"];
		}
		else
		{
			$this->object->ects_fx = "";
		}
		$this->object->ects_output = $_POST["chbECTS"];
	}
	
	/**
	* Add a simple mark schema to the tests marks
	*
	* Add a simple mark schema to the tests marks
	*
	* @access	public
	*/
	function addSimpleMarkSchemaObject()
	{
		$this->object->mark_schema->createSimpleSchema($this->lng->txt("failed_short"), $this->lng->txt("failed_official"), 0, 0, $this->lng->txt("passed_short"), $this->lng->txt("passed_official"), 50, 1);
		$this->marksObject();
	}
	
	/**
	* Delete selected mark steps
	*
	* Delete selected mark steps
	*
	* @access	public
	*/
	function deleteMarkStepsObject()
	{
		$this->saveMarkSchemaFormData();
		$delete_mark_steps = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/cb_(\d+)/", $key, $matches)) {
				array_push($delete_mark_steps, $matches[1]);
			}
		}
		if (count($delete_mark_steps)) {
			$this->object->mark_schema->deleteMarkSteps($delete_mark_steps);
		} else {
			ilUtil::sendInfo($this->lng->txt("tst_delete_missing_mark"));
		}
		$this->marksObject();
	}

	/**
	* Cancel the mark schema form and return to the properties form
	*
	* Cancel the mark schema form and return to the properties form
	*
	* @access	public
	*/
	function cancelMarksObject()
	{
		$this->ctrl->redirect($this, "marks");
	}
	
	/**
	* Save the mark schema
	*
	* Save the mark schema
	*
	* @access	public
	*/
	function saveMarksObject()
	{
		$this->saveMarkSchemaFormData();
		
		$mark_check = $this->object->checkMarks();
		if ($mark_check !== true)
		{
			ilUtil::sendInfo($this->lng->txt($mark_check));
		}
		elseif ($_POST["chbECTS"] && ((strcmp($_POST["ects_grade_a"], "") == 0) or (strcmp($_POST["ects_grade_b"], "") == 0) or (strcmp($_POST["ects_grade_c"], "") == 0) or (strcmp($_POST["ects_grade_d"], "") == 0) or (strcmp($_POST["ects_grade_e"], "") == 0)))
		{
			ilUtil::sendInfo($this->lng->txt("ects_fill_out_all_values"), true);
		}
		elseif (($_POST["ects_grade_a"] > 100) or ($_POST["ects_grade_a"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_a"), true);
		}
		elseif (($_POST["ects_grade_b"] > 100) or ($_POST["ects_grade_b"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_b"), true);
		}
		elseif (($_POST["ects_grade_c"] > 100) or ($_POST["ects_grade_c"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_c"), true);
		}
		elseif (($_POST["ects_grade_d"] > 100) or ($_POST["ects_grade_d"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_d"), true);
		}
		elseif (($_POST["ects_grade_e"] > 100) or ($_POST["ects_grade_e"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_e"), true);
		}
		else 
		{
			$this->object->mark_schema->saveToDb($this->object->getTestId());
			$this->object->saveCompleteStatus();
			if ($this->object->getReportingDate())
			{
				$fxpercent = "";
				if ($_POST["chbUseFX"])
				{
					$fxpercent = ilUtil::stripSlashes($_POST["percentFX"]);
				}
				$this->object->saveECTSStatus($_POST["chbECTS"], $fxpercent, $this->object->ects_grades["A"], $this->object->ects_grades["B"], $this->object->ects_grades["C"], $this->object->ects_grades["D"], $this->object->ects_grades["E"]);
			}
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->marksObject();
	}
	
	function marksObject() 
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if (!$this->object->canEditMarks())
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_marks"));
		}
		
		$this->object->mark_schema->sort();
	
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_marks.html", "Modules/Test");
		$marks = $this->object->mark_schema->mark_steps;
		$rows = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($marks as $key => $value) {
			$this->tpl->setCurrentBlock("markrow");
			$this->tpl->setVariable("MARK_SHORT", $value->getShortName());
			$this->tpl->setVariable("MARK_OFFICIAL", $value->getOfficialName());
			$this->tpl->setVariable("MARK_PERCENTAGE", sprintf("%.2f", $value->getMinimumLevel()));
			$this->tpl->setVariable("MARK_PASSED", strtolower($this->lng->txt("tst_mark_passed")));
			$this->tpl->setVariable("MARK_ID", "$key");
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			if ($value->getPassed()) {
				$this->tpl->setVariable("MARK_PASSED_CHECKED", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($marks) == 0) 
		{
			$this->tpl->setCurrentBlock("Emptyrow");
			$this->tpl->setVariable("EMPTY_ROW", $this->lng->txt("tst_no_marks_defined"));
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		} 
		else 
		{
			if ($ilAccess->checkAccess("write", "", $this->ref_id) && $this->object->canEditMarks()) 
			{
				$this->tpl->setCurrentBlock("selectall");
				$counter++;
				$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				$this->tpl->setVariable("BUTTON_EDIT", $this->lng->txt("edit"));
				$this->tpl->setVariable("BUTTON_DELETE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->getReportingDate())
		{
			$this->tpl->setCurrentBlock("ects");
			if ($this->object->ects_output)
			{
				$this->tpl->setVariable("CHECKED_ECTS", " checked=\"checked\"");
			}
			$this->tpl->setVariable("TEXT_OUTPUT_ECTS_GRADES", $this->lng->txt("ects_output_of_ects_grades"));
			$this->tpl->setVariable("TEXT_ALLOW_ECTS_GRADES", $this->lng->txt("ects_allow_ects_grades"));
			$this->tpl->setVariable("TEXT_USE_FX", $this->lng->txt("ects_use_fx_grade"));
			if (preg_match("/\d+/", $this->object->ects_fx))
			{
				$this->tpl->setVariable("CHECKED_FX", " checked=\"checked\"");
				$this->tpl->setVariable("VALUE_PERCENT_FX", sprintf("value=\"%s\" ", $this->object->ects_fx));
			}
			$this->tpl->setVariable("TEXT_PERCENT", $this->lng->txt("ects_use_fx_grade_part2"));
			$this->tpl->setVariable("ECTS_GRADE", $this->lng->txt("ects_grade"));
			$this->tpl->setVariable("PERCENTILE", $this->lng->txt("percentile"));
			$this->tpl->setVariable("ECTS_GRADE_A", "A - " . $this->lng->txt("ects_grade_a_short"));
			$this->tpl->setVariable("VALUE_GRADE_A", $this->object->ects_grades["A"]);
			$this->tpl->setVariable("ECTS_GRADE_B", "B - " . $this->lng->txt("ects_grade_b_short"));
			$this->tpl->setVariable("VALUE_GRADE_B", $this->object->ects_grades["B"]);
			$this->tpl->setVariable("ECTS_GRADE_C", "C - " . $this->lng->txt("ects_grade_c_short"));
			$this->tpl->setVariable("VALUE_GRADE_C", $this->object->ects_grades["C"]);
			$this->tpl->setVariable("ECTS_GRADE_D", "D - " . $this->lng->txt("ects_grade_d_short"));
			$this->tpl->setVariable("VALUE_GRADE_D", $this->object->ects_grades["D"]);
			$this->tpl->setVariable("ECTS_GRADE_E", "E - " . $this->lng->txt("ects_grade_e_short"));
			$this->tpl->setVariable("VALUE_GRADE_E", $this->object->ects_grades["E"]);
			
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_MARKS", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_SHORT", $this->lng->txt("tst_mark_short_form"));
		$this->tpl->setVariable("HEADER_OFFICIAL", $this->lng->txt("tst_mark_official_form"));
		$this->tpl->setVariable("HEADER_PERCENTAGE", $this->lng->txt("tst_mark_minimum_level"));
		$this->tpl->setVariable("HEADER_PASSED", $this->lng->txt("tst_mark_passed"));
		if ($ilAccess->checkAccess("write", "", $this->ref_id) && $this->object->canEditMarks()) 
		{
			$this->tpl->setVariable("BUTTON_NEW", $this->lng->txt("tst_mark_create_new_mark_step"));
			$this->tpl->setVariable("BUTTON_NEW_SIMPLE", $this->lng->txt("tst_mark_create_simple_mark_schema"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Deletes all user data for the test object
	*
	* Deletes all user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteAllUserResultsObject()
	{
		$this->object->removeAllTestEditings();
		ilUtil::sendSuccess($this->lng->txt("tst_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Deletes the selected user data for the test object
	*
	* Deletes the selected user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteSelectedUserDataObject()
	{
		$active_ids = array();
		foreach ($_POST["chbUser"] as $active_id)
		{
			if ($this->object->getFixedParticipants())
			{
				array_push($active_ids, $this->object->getActiveIdOfUser($active_id));
			}
			else
			{
				array_push($active_ids, $active_id);
			}
		}
		$this->object->removeSelectedTestResults($active_ids);
		ilUtil::sendSuccess($this->lng->txt("tst_selected_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
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
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*
	* Asks for a confirmation to delete all user data of the test object
	*
	* @access	public
	*/
	function deleteAllUserDataObject()
	{
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", "Modules/Test");

		$this->tpl->setCurrentBlock("confirm_delete");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*/
	public function deleteAllUserResultsObject()
	{
		ilUtil::sendQuestion($this->lng->txt("delete_all_user_data_confirmation"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "participants"));

		// cancel/confirm button
		$buttons = array( "confirmDeleteAllUserResults"  => $this->lng->txt("proceed"),
			"participants"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
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
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_single_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", "Modules/Test");

		foreach ($_POST["chbUser"] as $key => $value)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("USER_ID", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		include_once './Services/User/classes/class.ilObjUser.php';
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($_POST["chbUser"] as $key => $active_id)
		{
			if ($this->object->getFixedParticipants())
			{
				$user_id = $active_id;
			}
			else
			{
				$user_id = $this->object->_getUserIdFromActiveId($active_id);
			}
			$user = ilObjUser::_lookupName($user_id);
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("USER_ICON", ilUtil::getImagePath("icon_usr.gif"));
			$this->tpl->setVariable("USER_ALT", $this->lng->txt("usr"));
			$this->tpl->setVariable("USER_TITLE", $this->lng->txt("usr"));
			if ($this->object->getAnonymity())
			{
				$this->tpl->setVariable("TXT_FIRSTNAME", "");
				$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("unknown"));
				$this->tpl->setVariable("TXT_LOGIN", "");
			}
			else
			{
				$this->tpl->setVariable("TXT_FIRSTNAME", $user["firstname"]);
				if (strlen($user["lastname"]))
				{
					$this->tpl->setVariable("TXT_LASTNAME", $user["lastname"]);
				}
				else
				{
					$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("deleted_user"));
				}
				$this->tpl->setVariable("TXT_LOGIN", ilObjUser::_lookupLogin($user_id));
			}
			$this->tpl->setVariable("ROW_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		$this->tpl->setCurrentBlock("selectedusers");
		$this->tpl->setVariable("HEADER_TXT_FIRSTNAME", $this->lng->txt("firstname"));
		$this->tpl->setVariable("HEADER_TXT_LASTNAME", $this->lng->txt("lastname"));
		$this->tpl->setVariable("HEADER_TXT_LOGIN", $this->lng->txt("login"));
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_SELECTED", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_SELECTED", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the change history for a test
	*
	* Creates the change history for a test
	*
	* @access	public
	*/
	function historyObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestHistoryTableGUI.php";
		$table_gui = new ilTestHistoryTableGUI($this, 'history');
		$table_gui->setTestObject($this->object);
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$log =& ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), TRUE);
		$table_gui->setData($log);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
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

			include_once("./Modules/Test/classes/class.ilObjTest.php");
			$tst = new ilObjTest();
			$questionpools =& $tst->getAvailableQuestionpools(TRUE, FALSE, TRUE, TRUE);
			if (count($questionpools) == 0)
			{
			}
			else
			{
				foreach ($questionpools as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_qpl");
					$this->tpl->setVariable("OPTION_VALUE", $key);
					$this->tpl->setVariable("TXT_OPTION", $value["title"]);
					if ($_POST["qpl"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}

			$defaults =& $tst->getAvailableDefaults();
			if (count($defaults))
			{
				foreach ($defaults as $row)
				{
					$this->tpl->setCurrentBlock("defaults_row");
					$this->tpl->setVariable("DEFAULTS_VALUE", $row["test_defaults_id"]);
					$this->tpl->setVariable("DEFAULTS_NAME", ilUtil::prepareFormOutput($row["name"]));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("defaults");
				$this->tpl->setVariable("TXT_DEFAULTS", $this->lng->txt("defaults"));
				$this->tpl->setVariable("TEXT_NO_DEFAULTS", $this->lng->txt("tst_defaults_dont_use"));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->fillCloneTemplate('DUPLICATE','tst');
			$this->tpl->setCurrentBlock("adm_content");
			
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
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
			$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
			$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", ' target="'. ilFrameTargetInfo::_getFrame("MainContent").'" ');
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_TST", $this->lng->txt("import_tst"));
			$this->tpl->setVariable("TXT_TST_FILE", $this->lng->txt("tst_upload_file"));
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TYPE_IMG", ilUtil::getImagePath('icon_tst.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_tst"));
			$this->tpl->setVariable("TYPE_IMG2", ilUtil::getImagePath('icon_tst.gif'));
			$this->tpl->setVariable("ALT_IMG2",$this->lng->txt("obj_tst"));
			$this->tpl->setVariable("NEW_TYPE", $this->type);
			$this->tpl->parseCurrentBlock();

		}
	}

 /**
	* Cancels the change of the fixed participants status when fixed participants already exist
	*/
	public function cancelFixedParticipantsStatusChangeObject()
	{
		$this->ctrl->redirect($this, "inviteParticipants");
	}
	
 /**
	* Confirms the change of the fixed participants status when fixed participants already exist
	*/
	public function confirmFixedParticipantsStatusChangeObject()
	{
		$fixed_participants = 0;
		$invited_users = $this->object->getInvitedUsers();
		foreach ($invited_users as $user_object)
		{
			$this->object->disinviteUser($user_object["usr_id"]);
		}
		$this->object->setFixedParticipants($fixed_participants);
		$this->object->saveToDb();
		$this->ctrl->redirect($this, "inviteParticipants");
	}
	
 /**
	* Shows a confirmation dialog to remove fixed participants from the text
	*/
	public function confirmFixedParticipantsStatusChange()
	{
		ilUtil::sendQuestion($this->lng->txt("tst_fixed_participants_disable_description"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, 'confirmFixedParticipantsStatusChange'));

		// cancel/confirm button
		$buttons = array( "confirmFixedParticipantsStatusChange"  => $this->lng->txt("proceed"),
			"cancelFixedParticipantsStatusChange"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
 /**
	* Saves the status change of the fixed participants status
	*/
	public function saveFixedParticipantsStatusObject()
	{
		$fixed_participants = 0;
		if (array_key_exists("chb_fixed_participants", $_POST))
		{
			if ($_POST["chb_fixed_participants"])
			{
				$fixed_participants = 1;
			}
		}
		$invited_users = $this->object->getInvitedUsers();
		if ($this->object->getFixedParticipants() && !$fixed_participants && count($invited_users))
		{
			$this->confirmFixedParticipantsStatusChange();
		}
		else
		{
			$this->object->setFixedParticipants($fixed_participants);
			$this->object->saveToDb();
			$this->ctrl->redirect($this, "inviteParticipants");
		}
	}
	
 /**
	* Creates the output for user/group invitation to a test
	*/
	public function inviteParticipantsObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$total = $this->object->evalTotalPersons();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_fixed_users.html", "Modules/Test");

		if ($_POST["cmd"]["cancel"])
		{
			$this->backToRepositoryObject();
		}

		if (strcmp($this->ctrl->getCmd(), "searchParticipants") == 0)
		{
			if (is_array($_POST["search_for"]))
			{
				$this->tpl->setCurrentBlock("search_results_title");
				$this->tpl->setVariable("TEXT_SEARCH_RESULTS", $this->lng->txt("search_results"));
				$this->tpl->parseCurrentBlock();
				if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]) or in_array("role", $_POST["search_for"]))
				{
					include_once './classes/class.ilSearch.php';
					$search =& new ilSearch($ilUser->id);
					$search->setSearchString($_POST["search_term"]);
					$search->setCombination($_POST["concatenation"]);
					$search->setSearchFor($_POST["search_for"]);
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
	
					$invited_users =& $this->object->getInvitedUsers();
				
					if ($searchresult = $search->getResultByType("usr"))
					{
						$users = array();
						foreach ($searchresult as $result_array)
						{
							if (!array_key_exists($result_array["id"], $invited_users))
							{
								array_push($users, $result_array["id"]);
							}
						}
						
						$users = $this->object->getUserData($users);
						
						if (count ($users))
						{
							include_once "./Modules/Test/classes/tables/class.ilTestInviteUsersTableGUI.php";
							$table_gui = new ilTestInviteUsersTableGUI($this, 'inviteParticipants');
							$table_gui->setData($users);
							$this->tpl->setVariable('TBL_USER_RESULT', $table_gui->getHTML());	
						}
					}
	
					$searchresult = array();
					
					if ($searchresult = $search->getResultByType("grp"))
					{
						$groups = array();
						
						foreach ($searchresult as $result_array)
						{							
							array_push($groups, $result_array["id"]);
						}
						$groups = $this->object->getGroupData ($groups);
						
						if (count ($groups))
						{
							include_once "./Modules/Test/classes/tables/class.ilTestInviteGroupsTableGUI.php";
							$table_gui = new ilTestInviteGroupsTableGUI($this, 'inviteParticipants');
							$table_gui->setData($groups);
							$this->tpl->setVariable('TBL_GROUP_RESULT', $table_gui->getHTML());	
						}
					}
					
					$searchresult = array();
					
					if ($searchresult = $search->getResultByType("role"))
					{
						$roles = array();
						
						foreach ($searchresult as $result_array)
						{							
							array_push($roles, $result_array["id"]);
						}
						
						$roles = $this->object->getRoleData($roles);
								
						if (count ($roles))
						{
							include_once "./Modules/Test/classes/tables/class.ilTestInviteRolesTableGUI.php";
							$table_gui = new ilTestInviteRolesTableGUI($this, 'inviteParticipants');
							$table_gui->setData($roles);
							$this->tpl->setVariable('TBL_ROLE_RESULT', $table_gui->getHTML());	
						}
					}
					
				}
				
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("no_user_or_group_selected"));
			}
		}
		
		if ($_POST["cmd"]["save"])
		{
			$this->object->saveToDb();
		}
		$invited_users = $this->object->getInvitedUsers();

		$buttons = array("save","remove");
		
		if ($this->object->getFixedParticipants())
		{
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$this->tpl->setCurrentBlock("invitation");
				$this->tpl->setVariable("FORM_ACTION_INVITATION", $this->ctrl->getFormAction($this));
				$this->tpl->setVariable("SEARCH_INVITATION", $this->lng->txt("search"));
				$this->tpl->setVariable("SEARCH_TERM", $this->lng->txt("search_term"));
				$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("search_for"));
				$this->tpl->setVariable("SEARCH_USERS", $this->lng->txt("search_users"));
				$this->tpl->setVariable("SEARCH_GROUPS", $this->lng->txt("search_groups"));
				$this->tpl->setVariable("SEARCH_ROLES", $this->lng->txt("search_roles"));
				$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("concatenation"));
				$this->tpl->setVariable("TEXT_AND", $this->lng->txt("and"));
				$this->tpl->setVariable("TEXT_OR", $this->lng->txt("or"));
				$this->tpl->setVariable("VALUE_SEARCH_TERM", $_POST["search_term"]);
				if (is_array($_POST["search_for"]))
				{
					if (in_array("usr", $_POST["search_for"]))
					{
						$this->tpl->setVariable("CHECKED_USERS", " checked=\"checked\"");
					}
					if (in_array("grp", $_POST["search_for"]))
					{
						$this->tpl->setVariable("CHECKED_GROUPS", " checked=\"checked\"");
					}
					if (in_array("role", $_POST["search_for"]))
					{
						$this->tpl->setVariable("CHECKED_ROLES", " checked=\"checked\"");
					}
				}
				else
				{
					$this->tpl->setVariable("CHECKED_USERS", " checked=\"checked\"");
				}
				if (strcmp($_POST["concatenation"], "and") == 0)
				{
					$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
				}
				else
				{
					$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
				}
				$this->tpl->setVariable("SEARCH", $this->lng->txt("search"));
				$this->tpl->setVariable("SEARCH_INTRODUCTION", $this->lng->txt("participants_invitation_search_introduction"));
				$this->tpl->setVariable("TEXT_INVITATION", $this->lng->txt("invitation"));
				$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
				$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
				$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_ALLOW_FIXED_PARTICIPANTS", $this->lng->txt("tst_allow_fixed_participants"));
		$this->tpl->setVariable("BUTTON_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TEXT_FIXED_PARTICIPANTS", $this->lng->txt("participants_invitation"));
		$this->tpl->setVariable("TEXT_FIXED_PARTICIPANTS_DESCRIPTION", $this->lng->txt("participants_invitation_description"));
		if ($this->object->getFixedParticipants())
		{
			$this->tpl->setVariable("CHECKED_FIXED_PARTICIPANTS", " checked=\"checked\"");
		}
		if ($total && (count($invited_users) == 0))
		{
			ilUtil::sendInfo($this->lng->txt("tst_fixed_participants_data_exists"));
			$this->tpl->setVariable("DISABLED_FIXED_PARTICIPANTS", " disabled=\"disabled\"");
		}

		if ($ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}
	
 /**
	* Evaluates the actions on the participants page
	*
	* @access	public
	*/
	function participantsActionObject()
	{
		$command = $_POST["command"];
		if (strlen($command))
		{
			$method = $command . "Object";
			if (method_exists($this, $method))
			{
				$this->$method();
				return;
			}
		}
		$this->ctrl->redirect($this, "participants");
	}

 /**
	* Creates the output of the test participants
	*
	* @access	public
	*/
	function participantsObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if ($this->object->getFixedParticipants())
		{
			$participants =& $this->object->getInvitedUsers();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME));					
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
				array_push($rows, array(
					'usr_id' => $data["usr_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'clientip' => $data["clientip"],
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestFixedParticipantsTableGUI.php";
			$table_gui = new ilTestFixedParticipantsTableGUI($this, 'participants', $this->object->getAnonymity(), count($rows));
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
		else
		{
			$participants =& $this->object->getTestParticipants();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME));
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
				array_push($rows, array(
					'usr_id' => $data["active_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php";
			$table_gui = new ilTestParticipantsTableGUI($this, 'participants', $this->object->getAnonymity(), count($rows));
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
	}

 /**
	* Shows the pass overview and the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showDetailedResultsObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = TRUE, $show_reached_points = TRUE);
	}

 /**
	* Shows the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showUserAnswersObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = FALSE, $show_answers = TRUE);
	}

 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showPassOverviewObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = FALSE);
	}
	
 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showUserResults($show_pass_details, $show_answers, $show_reached_points = FALSE)
	{
		$template = new ilTemplate("tpl.il_as_tst_participants_result_output.html", TRUE, TRUE, "Modules/Test");
		
		if (count($_SESSION["show_user_results"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}

		include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
		$serviceGUI =& new ilTestServiceGUI($this->object);
		$count = 0;
		foreach ($_SESSION["show_user_results"] as $key => $active_id)
		{
			$count++;
			$results = "";
			if ($this->object->getFixedParticipants())
			{
				$active_id = $this->object->getActiveIdOfUser($active_id);
			}
			if ($active_id > 0)
			{
				$results = $serviceGUI->getResultsOfUserOutput($active_id, $this->object->_getResultPass($active_id), $show_pass_details, $show_answers, FALSE, $show_reached_points);
			}
			if ($count < count($_SESSION["show_user_results"]))
			{
				$template->touchBlock("break");
			}
			$template->setCurrentBlock("user_result");
			$template->setVariable("USER_RESULT", $results);
			$template->parseCurrentBlock();
		}
		$template->setVariable("BACK_TEXT", $this->lng->txt("back"));
		$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");
		
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
	}

	function removeParticipantObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->disinviteUser($user_id);				
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	function saveClientIPObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->setClientIP($user_id, $_POST["clientip_".$user_id]);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Print tab to create a print of all questions with points and solutions
	*
	* Print tab to create a print of all questions with points and solutions
	*
	* @access	public
	*/
	function printobject() 
	{
		global $ilAccess, $ilias;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		$this->getQuestionsSubTabs();
		$template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", TRUE, TRUE, "Modules/Test");

		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(ilRPCServerSettings::getInstance()->isEnabled())
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "print"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		
		global $ilUser;		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		$max_points= 0;
		$counter = 1;
					
		foreach ($this->object->questions as $question) 
		{		
			$template->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);
			$template->setVariable("COUNTER_QUESTION", $counter.".");
			$template->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($question_gui->object->getTitle()));
			if ($question_gui->object->getMaximumPoints() == 1)
			{
				$template->setVariable("QUESTION_POINTS", $question_gui->object->getMaximumPoints() . " " . $this->lng->txt("point"));
			}
			else
			{
				$template->setVariable("QUESTION_POINTS", $question_gui->object->getMaximumPoints() . " " . $this->lng->txt("points"));
			}
			$result_output = $question_gui->getSolutionOutput("", NULL, FALSE, TRUE, FALSE, $this->object->getShowSolutionFeedback());
			if (strlen($result_output) == 0) $result_output = $question_gui->getPreview(FALSE);
			$template->setVariable("SOLUTION_OUTPUT", $result_output);
			$template->parseCurrentBlock("question");
			$counter ++;
			$max_points += $question_gui->object->getMaximumPoints();
		}

		$template->setCurrentBlock("navigation_buttons");
		$template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
		$template->parseCurrentBlock();
		
		$template->setVariable("TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$template->setVariable("PRINT_TEST", ilUtil::prepareFormOutput($this->lng->txt("tst_print")));
		$template->setVariable("TXT_PRINT_DATE", ilUtil::prepareFormOutput($this->lng->txt("date")));
		$template->setVariable("VALUE_PRINT_DATE", ilUtil::prepareFormOutput(strftime("%c",$print_date)));
		$template->setVariable("TXT_MAXIMUM_POINTS", ilUtil::prepareFormOutput($this->lng->txt("tst_maximum_points")));
		$template->setVariable("VALUE_MAXIMUM_POINTS", ilUtil::prepareFormOutput($max_points));
		
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("PRINT_CONTENT", $template->get());
		}
	}
	
	function addParticipantsObject()
	{
		$countusers = 0;
		$countgroups = 0;
		$countroles = 0;
		// add users 
		if (is_array($_POST["user_select"]))
		{
			$i = 0;
			foreach ($_POST["user_select"] as $user_id)
			{
				$client_ip = $_POST["client_ip"][$i];
				$this->object->inviteUser($user_id, $client_ip);
				$countusers++;
				$i++;
			}
		}
		// add groups members
		if (is_array($_POST["group_select"]))
		{
			foreach ($_POST["group_select"] as $group_id)
			{
				$this->object->inviteGroup($group_id);
				$countgroups++;
			}
		}
		// add role members
		if (is_array($_POST["role_select"]))
		{
			foreach ($_POST["role_select"] as $role_id)
			{
				$this->object->inviteRole($role_id);
				$countroles++;
			}
		}
		$message = "";
		if ($countusers)
		{
			$message = $this->lng->txt("tst_invited_selected_users");
		}
		if ($countgroups)
		{
			if (strlen($message)) $message .= "<br />";
			$message = $this->lng->txt("tst_invited_selected_groups");
		}
		if ($countroles)
		{
			if (strlen($message)) $message .= "<br />";
			$message = $this->lng->txt("tst_invited_selected_roles");
		}
		if (strlen($message))
		{
			ilUtil::sendInfo($message, TRUE);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_invited_nobody"), TRUE);
		}
		
		$this->ctrl->redirect($this, "inviteParticipants");
	}
	
	function searchParticipantsObject()
	{
		$this->inviteParticipantsObject();
	}
	
	/**
	* Displays the settings page for test defaults
	*
	* Displays the settings page for test defaults
	*
	* @access public
	*/
	function defaultsObject()
	{
		global $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_defaults.html", "Modules/Test");
		
		$maxentries = $ilUser->getPref("hits_per_page");
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}

		$offset = $_GET["offset"] ? $_GET["offset"] : 0;
		$sortby = $_GET["sort_by"] ? $_GET["sort_by"] : "name";
		$sortorder = $_GET["sort_order"] ? $_GET["sort_order"] : "asc";
		
		$defaults =& $this->object->getAvailableDefaults($sortby, $sortorder);
		if (count($defaults) > 0)
		{
			$tablerows = array();
			foreach ($defaults as $row)
			{
				array_push($tablerows, array("checkbox" => "<input type=\"checkbox\" name=\"chb_defaults[]\" value=\"" . $row["test_defaults_id"] . "\"/>", "name" => $row["name"]));
			}
			$headervars = array("", "name");

			include_once "./Services/Table/classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI(0, FALSE);
			$tbl->setTitle($this->lng->txt("tst_defaults_available"));
			$header_names = array(
				"",
				$this->lng->txt("title")
			);
			$tbl->setHeaderNames($header_names);

			$tbl->disable("sort");
			$tbl->disable("auto_sort");
			$tbl->enable("title");
			$tbl->enable("action");
			$tbl->enable("select_all");
			$tbl->setLimit($maxentries);
			$tbl->setOffset($offset);
			$tbl->setData($tablerows);
			$tbl->setMaxCount(count($tablerows));
			$tbl->setOrderDirection($sortorder);
			$tbl->setSelectAllCheckbox("chb_defaults");
			$tbl->setFormName("formDefaults");
			$tbl->addActionButton("deleteDefaults", $this->lng->txt("delete"));
			$tbl->addActionButton("applyDefaults", $this->lng->txt("apply"));

			$header_params = $this->ctrl->getParameterArray($this, "defaults");
			$tbl->setHeaderVars($headervars, $header_params);

			// footer
			$tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
			// render table
			$tableoutput = $tbl->render();
			$this->tpl->setVariable("TEST_DEFAULTS_TABLE", $tableoutput);
		}
		else
		{
			$this->tpl->setVariable("TEST_DEFAULTS_TABLE", $this->lng->txt("tst_defaults_not_defined"));
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "addDefaults"));
		$this->tpl->setVariable("BUTTON_ADD", $this->lng->txt("add"));
		$this->tpl->setVariable("TEXT_DEFAULTS_OF_TEST", $this->lng->txt("tst_defaults_defaults_of_test"));
	}
	
	/**
	* Deletes selected test defaults
	*/
	function deleteDefaultsObject()
	{
		if (count($_POST["chb_defaults"]))
		{
			foreach ($_POST["chb_defaults"] as $test_default_id)
			{
				$this->object->deleteDefaults($test_default_id);
			}
		}
		$this->defaultsObject();
	}
	
	/**
	* Applies the selected test defaults
	*/
	function applyDefaultsObject()
	{
		if (count($_POST["chb_defaults"]) == 1)
		{
			foreach ($_POST["chb_defaults"] as $test_default_id)
			{
				$result = $this->object->applyDefaults($test_default_id);
				if (!$result)
				{
					ilUtil::sendInfo($this->lng->txt("tst_defaults_apply_not_possible"));
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("tst_defaults_applied"));
				}
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_defaults_apply_select_one"));
		}
		$this->defaultsObject();
	}
	
	/**
	* Adds the defaults of this test to the defaults
	*/
	function addDefaultsObject()
	{
		if (strlen($_POST["name"]) > 0)
		{
			$this->object->addDefaults($_POST['name']);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_defaults_enter_name"));
		}
		$this->defaultsObject();
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
	
	function redirectToInfoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen($_SESSION["lock"]);
	}
	
	/**
	* show information screen
	*/
	function infoScreen($session_lock = "")
	{
		global $ilAccess;
		global $ilUser;

		// Disabled
		/*
		if ($_GET['crs_show_result'])
		{
			$this->object->hideCorrectAnsweredQuestions();
		}
		else
		*/
		{
			if ($this->object->getTestSequence()->hasHiddenQuestions())
			{
				$this->object->getTestSequence()->clearHiddenQuestions();
				$this->object->getTestSequence()->saveToDb();
			}
		}
		
		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$seq = $this->object->getTestSession()->getLastSequence();

		include_once "./Modules/Test/classes/class.ilTestOutputGUI.php";
		$output_gui =& new ilTestOutputGUI($this->object);
		$this->ctrl->setParameter($output_gui, "sequence", $seq);
		$info->setFormAction($this->ctrl->getFormAction($output_gui));
		if (strlen($session_lock))
		{
			$info->addHiddenElement("lock", $session_lock);
		}
		else
		{
			$info->addHiddenElement("lock", md5($_COOKIE['PHPSESSID'] . time()));
		}
		$online_access = false;
		if ($this->object->getFixedParticipants())
		{
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
			if ($online_access_result === true)
			{
				$online_access = true;
			}
			else
			{
				ilUtil::sendInfo($online_access_result);
			}
		}
		if ($this->object->isComplete())
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				$executable = $this->object->isExecutable($ilUser->getId(), $allowPassIncrease = TRUE);
				if ($executable["executable"])
				{
					if ($this->object->getTestSession()->getActiveId() > 0)
					{
						// resume test
						$resume_text = $this->lng->txt("tst_resume_test");
						if (($seq < 1) || ($seq == $this->object->getTestSequence()->getFirstSequence()))
						{
							$resume_text = $this->object->getStartTestLabel($this->object->getTestSession()->getActiveId());
						}
						// Disabled
						#if(!$_GET['crs_show_result'] or $this->object->getTestSequence()->getFirstSequence())
						{
							$info->addFormButton("resume", $resume_text);
						}
					}
					else
					{
						// start new test
						$info->addFormButton("start", $this->object->getStartTestLabel($this->object->getTestSession()->getActiveId()));
					}
				}
				else
				{
					ilUtil::sendInfo($executable["errormessage"]);
				}
				if ($this->object->getTestSession()->getActiveId() > 0)
				{
					// test results button
					if ($this->object->canShowTestResults($ilUser->getId())) 
					{
						$info->addFormButton("outUserResultsOverview", $this->lng->txt("tst_show_results"));
					}
				}
			}
			if ($this->object->getTestSession()->getActiveId() > 0)
			{
				if ($this->object->canShowSolutionPrintview($ilUser->getId()))
				{
					$info->addFormButton("outUserListOfAnswerPasses", $this->lng->txt("tst_list_of_answers_show"));
				}
			}
		}
		
		if ($this->object->getShowInfo())
		{
			$info->enablePrivateNotes();

		}
		if (strlen($this->object->getIntroduction()))
		{
			$info->addSection($this->lng->txt("tst_introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($this->object->getIntroduction()));
		}

		$info->addSection($this->lng->txt("tst_general_properties"));
		if ($this->object->getShowInfo())
		{
			$info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
			$info->addProperty($this->lng->txt("title"), $this->object->getTitle());
		}
		if ($this->object->isComplete())
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				if ($this->object->getShowInfo() || !$this->object->getForceJS())
				{
					// use javascript
					$checked_javascript = false;
					if ($this->object->getJavaScriptOutput())
					{
						$checked_javascript = true;
					}
					if ($this->object->getForceJS())
					{
						$info->addProperty($this->lng->txt("tst_test_output"), $this->lng->txt("tst_use_javascript"));
					}
					else
					{
						$info->addPropertyCheckbox($this->lng->txt("tst_test_output"), "chb_javascript", 1, $this->lng->txt("tst_use_javascript"), $checked_javascript);
					}
				}
				// hide previous results
				if (!$this->object->isRandomTest())
				{
					if ($this->object->getNrOfTries() != 1)
					{
						if ($this->object->getUsePreviousAnswers() == 0)
						{
							if ($this->object->getShowInfo())
							{
								$info->addProperty($this->lng->txt("tst_use_previous_answers"), $this->lng->txt("tst_dont_use_previous_answers"));
							}
						}
						else
						{
							$use_previous_answers = FALSE;
							if ($ilUser->prefs["tst_use_previous_answers"])
							{
								$checked_previous_answers = TRUE;
							}
							$info->addPropertyCheckbox($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers", 1, $this->lng->txt("tst_use_previous_answers_user"), $checked_previous_answers);
						}
					}
				}
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					$info->addPropertyTextinput($this->lng->txt("enter_anonymous_code"), "anonymous_id", "", 8, "setAnonymousId", $this->lng->txt("submit"));
				}
			}
		}
		                                 
		if ($this->object->getShowInfo())
		{
			$info->addSection($this->lng->txt("tst_sequence_properties"));
			$info->addProperty($this->lng->txt("tst_sequence"), $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)? "tst_sequence_fixed":"tst_sequence_postpone"));
		
			$info->addSection($this->lng->txt("tst_heading_scoring"));
			$info->addProperty($this->lng->txt("tst_text_count_system"), $this->lng->txt(($this->object->getCountSystem() == COUNT_PARTIAL_SOLUTIONS)? "tst_count_partial_solutions":"tst_count_correct_solutions"));
			$info->addProperty($this->lng->txt("tst_score_mcmr_questions"), $this->lng->txt(($this->object->getMCScoring() == SCORE_ZERO_POINTS_WHEN_UNANSWERED)? "tst_score_mcmr_zero_points_when_unanswered":"tst_score_mcmr_use_scoring_system"));
			if ($this->object->isRandomTest())
			{
				$info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->object->getPassScoring() == SCORE_BEST_PASS)? "tst_pass_best_pass":"tst_pass_last_pass"));
			}

			$info->addSection($this->lng->txt("tst_score_reporting"));
			$score_reporting_text = "";
			switch ($this->object->getScoreReporting())
			{
				case REPORT_AFTER_TEST:
					$score_reporting_text = $this->lng->txt("tst_report_after_test");
					break;
				case REPORT_ALWAYS:
					$score_reporting_text = $this->lng->txt("tst_report_after_first_question");
					break;
				case REPORT_AFTER_DATE:
					$score_reporting_text = $this->lng->txt("tst_report_after_date");
					break;
			}
			$info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text); 
			$reporting_date = $this->object->getReportingDate();
			if ($reporting_date)
			{
				#preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $reporting_date, $matches);
				#$txt_reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
				#$info->addProperty($this->lng->txt("tst_score_reporting_date"), $txt_reporting_date);
				$info->addProperty($this->lng->txt('tst_score_reporting_date'),
					ilDatePresentation::formatDate(new ilDateTime($reporting_date,IL_CAL_TIMESTAMP)));
			}
	
			$info->addSection($this->lng->txt("tst_session_settings"));
			$info->addProperty($this->lng->txt("tst_nr_of_tries"), ($this->object->getNrOfTries() == 0)?$this->lng->txt("unlimited"):$this->object->getNrOfTries());
			if ($this->object->getNrOfTries() != 1)
			{
				$info->addProperty($this->lng->txt("tst_nr_of_tries_of_user"), ($this->object->getTestSession()->getPass() == false)?$this->lng->txt("tst_no_tries"):$this->object->getTestSession()->getPass());
			}

			if ($this->object->getEnableProcessingTime())
			{
				$info->addProperty($this->lng->txt("tst_processing_time"), $this->object->getProcessingTime());
			}
			if (strlen($this->object->getAllowedUsers()) && ($this->object->getAllowedUsersTimeGap()))
			{
				$info->addProperty($this->lng->txt("tst_allowed_users"), $this->object->getAllowedUsers());
			}
		
			$starting_time = $this->object->getStartingTime();
			if ($starting_time)
			{
				$info->addProperty($this->lng->txt("tst_starting_time"),
					ilDatePresentation::formatDate(new ilDateTime($starting_time,IL_CAL_TIMESTAMP)));
			}
			$ending_time = $this->object->getEndingTime();
			if ($ending_time)
			{
				$info->addProperty($this->lng->txt("tst_ending_time"),
					ilDatePresentation::formatDate(new ilDateTime($ending_time,IL_CAL_TIMESTAMP)));
			}
			$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
			// forward the command

			if($_GET['crs_show_result'] and !$this->object->getTestSequence()->getFirstSequence())
			{
				#ilUtil::sendInfo($this->lng->txt('crs_all_questions_answered_successfully'));
			}			
		}
		
		$this->ctrl->forwardCommand($info);
	}

	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "run":
			case "infoScreen":
			case "redirectToInfoScreen":
			case "start":
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "redirectQuestion":
			case "outUserPassDetails":
			case "checkPassword":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
			case "eval_stat":
			case "evalAllUsers":
			case "evalUserDetail":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "eval_stat"), "", $_GET["ref_id"]);
				break;
			case "create":
			case "save":
			case "cancel":
			case "importFile":
			case "cloneAll":
			case "importVerifiedFile":
			case "cancelImport":
				break;
		default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
	}
	
	function getBrowseForQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTarget($this, "questions"));
			$tabs_gui->addTarget("tst_browse_for_questions",
				$this->ctrl->getLinkTarget($this, "browseForQuestions"),
				array("browseForQuestions", "filter", "resetFilter", "resetTextFilter", "insertQuestions"),
				"", "", TRUE
			);
		}
	}
	
	function getRandomQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTarget($this, "questions"));
			$tabs_gui->addTarget("random_selection",
				$this->ctrl->getLinkTarget($this, "randomQuestions"),
				array("randomQuestions"),
				"", ""
			);
		}
	}

	function statisticsObject()
	{
	}

	/**
	* Shows the certificate editor
	*/
	function certificateObject()
	{
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
		$output_gui->certificateEditor();
	}

	function getQuestionsSubTabs()
	{
		global $ilTabs;
		
		// questions subtab
		$ilTabs->addSubTabTarget("edit_test_questions",
			 $this->ctrl->getLinkTarget($this,'questions'),
			 array("questions", "browseForQuestions", "questionBrowser", "createQuestion", 
			 "randomselect", "filter", "resetFilter", "insertQuestions",
			 "back", "createRandomSelection", "cancelRandomSelect",
			 "insertRandomSelection", "removeQuestions", "moveQuestions",
			 "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
			 "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
			 "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode"), 
			 "");
			 
		// print view subtab
		if (!$this->object->isRandomTest())
		{
			$ilTabs->addSubTabTarget("print_view",
				 $this->ctrl->getLinkTarget($this,'print'),
				 "print", "");
		}
			
	}
	
	function getStatisticsSubTabs()
	{
		global $ilTabs;
		
		// user results subtab
		$ilTabs->addSubTabTarget("eval_all_users",
			 $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
			 array("outEvaluation", "detailedEvaluation", "exportEvaluation", "evalUserDetail", "passDetails",
			 	"outStatisticsResultsOverview", "statisticsPassDetails")
			 , "");
	
		// aggregated results subtab
		$ilTabs->addSubTabTarget("tst_results_aggregated",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "eval_a"),
			array("eval_a"),
			"", "");
	
		// question export
		$ilTabs->addSubTabTarget("tst_single_results",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
			array("singleResults"),
			"", "");

		// settings
		$ilTabs->addSubTabTarget("settings",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "evalSettings"),
			array("evalSettings", "saveEvalSettings"),
			"", "");
	
	}
	
	function getSettingsSubTabs()
	{
		global $ilTabs, $ilias;
		
		// general subtab
		$force_active = ($this->ctrl->getCmd() == "")
			? true
			: false;
		$ilTabs->addSubTabTarget("general",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 array("properties", "saveProperties", "cancelProperties"),
			 array("", "ilobjtestgui", "ilcertificategui"),
			 "", $force_active);
	
		// scoring subtab
		$ilTabs->addSubTabTarget(
			"scoring",
			$this->ctrl->getLinkTarget($this,'scoring'),
			array("scoring"),
			array("", "ilobjtestgui", "ilcertificategui")
		);
	
		// mark schema subtab
		$ilTabs->addSubTabTarget(
			"mark_schema",
			$this->ctrl->getLinkTarget($this,'marks'),
			array("marks", "addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema",
				"saveMarks", "cancelMarks"),
			array("", "ilobjtestgui", "ilcertificategui")
		);
	
		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(ilRPCServerSettings::getInstance()->isEnabled())
		{
			// certificate subtab
			$ilTabs->addSubTabTarget(
				"certificate",
				$this->ctrl->getLinkTarget($this,'certificate'),
				array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
					"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport"),
				array("", "ilobjtestgui", "ilcertificategui")
			);
		}

		// aggregated results subtab
		$ilTabs->addSubTabTarget("participants_invitation",
			$this->ctrl->getLinkTarget($this, "inviteParticipants"),
			array("inviteParticipants", "searchParticipants"),
			"", "");
	
		// defaults subtab
		$ilTabs->addSubTabTarget(
			"defaults",
			$this->ctrl->getLinkTarget($this, "defaults"),
			array("defaults", "deleteDefaults", "addDefaults", "applyDefaults"),
			array("", "ilobjtestgui", "ilcertificategui")
		);
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess,$ilUser;

		switch ($this->ctrl->getCmd())
		{
			case "start":
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "directfeedback":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "confirmFinish":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "redirectQuestion":
			case "outUserPassDetails":
			case "checkPassword":
			case "exportCertificate":
			case "finishListOfAnswers":
			case "backConfirmFinish":
			case "showFinalStatement":
				return;
				break;
			case "browseForQuestions":
			case "filter":
			case "resetFilter":
			case "resetTextFilter":
			case "insertQuestions":
				return $this->getBrowseForQuestionsTab($tabs_gui);
				break;
			case "scoring":
			case "properties":
			case "marks":
			case "saveMarks":
			case "cancelMarks":
			case "addMarkStep":
			case "deleteMarkSteps":
			case "addSimpleMarkSchema":
			case "certificate":
			case "certificateservice":
			case "certificateImport":
			case "certificateUpload":
			case "certificateEditor":
			case "certificateDelete":
			case "certificateSave":
			case "defaults":
			case "deleteDefaults":
			case "addDefaults":
			case "applyDefaults":
			case "inviteParticipants":
			case "searchParticipants":
			case "":
				if (($ilAccess->checkAccess("write", "", $this->ref_id)) && ((strcmp($this->ctrl->getCmdClass(), "ilobjtestgui") == 0) || (strcmp($this->ctrl->getCmdClass(), "ilcertificategui") == 0) || (strlen($this->ctrl->getCmdClass()) == 0)))
				{
					$this->getSettingsSubTabs();
				}
				break;
			case "export":
			case "print":
				break;
			case "statistics":
			case "eval_a":
			case "evalSettings":
			case "saveEvalSettings":
			case "detailedEvaluation":
			case "outEvaluation":
			case "singleResults":
			case "exportEvaluation":
			case "evalUserDetail":
			case "passDetails":
			case "outStatisticsResultsOverview":
			case "statisticsPassDetails":
				$this->getStatisticsSubTabs();
				break;
		}
		
		if (strcmp(strtolower(get_class($this->object)), "ilobjtest") == 0)
		{
			// questions tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
					? true
					: false;
				if (!$force_active)
				{
					if ($_GET["browse"] == 1) $force_active = true;
					if (preg_match("/deleteqpl_\d+/", $this->ctrl->getCmd()))
					{
						$force_active = true;
					}
				}
				$tabs_gui->addTarget("assQuestions",
					 $this->ctrl->getLinkTarget($this,'questions'),
					 array("questions", "browseForQuestions", "questionBrowser", "createQuestion", 
					 "randomselect", "filter", "resetFilter", "insertQuestions",
					 "back", "createRandomSelection", "cancelRandomSelect",
					 "insertRandomSelection", "removeQuestions", "moveQuestions",
					 "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
					 "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
					 "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode", "print",
					"addsource", "removesource", "randomQuestions"), 
					 "", "", $force_active);
			}

			// info tab
			if ($ilAccess->checkAccess("visible", "", $this->ref_id))
			{
				$tabs_gui->addTarget("info_short",
					 $this->ctrl->getLinkTarget($this,'infoScreen'),
					 array("infoScreen", "outIntroductionPage", "showSummary", 
					 "setAnonymousId", "outUserListOfAnswerPasses", "redirectToInfoScreen"));
			}
			
			// settings tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$tabs_gui->addTarget("settings",
					$this->ctrl->getLinkTarget($this,'properties'),
						array("properties", "saveProperties", "cancelProperties",
							"marks", "addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema",
							"saveMarks", "cancelMarks", 
							"certificate", "certificateEditor", "certificateRemoveBackground",
							"certificateSave", "certificatePreview", "certificateDelete", "certificateUpload",
							"certificateImport", "scoring", "defaults", "addDefaults", "deleteDefaults", "applyDefaults",
							"inviteParticipants", "saveFixedParticipantsStatus", "searchParticipants", "addParticipants", 
							""
					),
					 array("", "ilobjtestgui", "ilcertificategui")
				);
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				// meta data
				$tabs_gui->addTarget("meta_data",
					 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
					 "", "ilmdeditorgui");
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				// participants
				$tabs_gui->addTarget("participants",
					 $this->ctrl->getLinkTarget($this,'participants'),
					 array("participants", "saveClientIP",
					 "removeParticipant", 
					 "showParticipantAnswersForAuthor",
					 "deleteAllUserResults",
					 "cancelDeleteAllUserData", "deleteSingleUserResults",
					 "outParticipantsResultsOverview", "outParticipantsPassDetails",
					 "showPassOverview", "showUserAnswers", "participantsAction",
					"showDetailedResults"), 
					 "");

				// export tab
				$tabs_gui->addTarget("export",
					 $this->ctrl->getLinkTarget($this,'export'),
					 array("export", "createExportFile", "confirmDeleteExportFile",
					 "downloadExportFile", "deleteExportFile", "cancelDeleteExportFile"),
					 "");

				include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
				$scoring = ilObjAssessmentFolder::_getManualScoring();
				if (count($scoring))
				{
					// scoring tab
					$tabs_gui->addTarget("manscoring",
						 $this->ctrl->getLinkTargetByClass("iltestscoringgui", "manscoring"),
						 array("manscoring", "scoringfilter", "scoringfilterreset", "setPointsManual", "setFeedbackManual", "setManscoringDone"),
						 "");
				}
			}

			if (($ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) || ($ilAccess->checkAccess("write", "", $this->ref_id)))
			{
				// statistics tab
				$tabs_gui->addTarget("statistics",
					 $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
					 array("statistics", "outEvaluation", "exportEvaluation", "detailedEvaluation", "eval_a", "evalSettings", "saveEvalSettings", "evalUserDetail",
					 	"passDetails", "outStatisticsResultsOverview", "statisticsPassDetails", "singleResults")
					 , "");
			}

			include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
			if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
			{
				$tabs_gui->addTarget('learning_progress',
									 $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
									 '',
									 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
			}
			
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				// history
				$tabs_gui->addTarget("history",
					 $this->ctrl->getLinkTarget($this,'history'),
					 "history", "");

				if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
				{
					$tabs_gui->addTarget("perm_settings",
					$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
				}
			}
		}
	}
	
	/**
	* Redirect script to call a test with the test reference id
	* 
	* Redirect script to call a test with the test reference id
	*
	* @param integer $a_target The reference id of the test
	* @access	public
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			//include_once "./Services/Utilities/classes/class.ilUtil.php";
			$_GET["baseClass"] = "ilObjTestGUI";
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include_once("ilias.php");
			exit;
			//ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=$a_target");
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

} // END class.ilObjTestGUI
?>
