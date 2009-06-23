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

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if ($this->object->isRandomTest())
		{
			ilUtil::sendInfo($this->lng->txt("tst_no_export_randomtest"));
		}
		else
		{
			// create export file button
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "createExportFile")."&mode=xml");
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("ass_create_export_file"));
			$this->tpl->parseCurrentBlock();
		}
		
		// create export test results button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "createExportfile")."&mode=results");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("ass_create_export_test_results"));
		$this->tpl->parseCurrentBlock();
		
		$export_dir = $this->object->getExportDirectory();

		$export_files = $this->object->getExportFiles($export_dir);

		// create table
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/Test");

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
		global $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once("./Modules/Test/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, $_GET["mode"]);
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
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("TEXT_CONTENT", $file);
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDeleteExportFile"  => $this->lng->txt("cancel"),
			"deleteExportFile"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
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
				ilUtil::delDir($exp_dir);
			}
		}
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
	* Save the form input of the properties form
	*
	* Save the form input of the properties form
	*
	* @access	public
	*/
	function savePropertiesObject()
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
					if (!$_POST["chb_random"])
					{
						// user tries to change from a random test with existing random question pools to a non random test
						$this->confirmChangeProperties(0);
						return;
					}
				}
				if ((!$this->object->isRandomTest()) && (count($this->object->questions) > 0))
				{
					if ($_POST["chb_random"])
					{
						// user tries to change from a non random test with existing questions to a random test
						$this->confirmChangeProperties(1);
						return;
					}
				}
			}

			if (!strlen($_POST["chb_random"]))
			{
				$data["random_test"] = 0;
			}
			else
			{
				$data["random_test"] = ilUtil::stripSlashes($_POST["chb_random"]);
			}
		}
		else
		{
			$data["random_test"] = $this->object->random_test;
		}
		if ($data["random_test"] != $this->object->random_test)
		{
			$randomtest_switch = true;
		}
		$data["anonymity"] = $_POST["anonymity"];
		if ($total)
		{
			$data["anonymity"] = $this->object->getAnonymity();
		}
		$data["show_cancel"] = $_POST["show_cancel"];
		$data["password"] = $_POST["password"];
		$data["allowedUsers"] = $_POST["allowedUsers"];
		$data["show_cancel"] = $_POST["chb_show_cancel"];
		$data["show_marker"] = ($_POST["chb_show_marker"] ? 1 : 0);
		$data["allowedUsersTimeGap"] = $_POST["allowedUsersTimeGap"];
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$introduction = ilUtil::stripSlashes($_POST["introduction"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$data["introduction"] = $introduction;
		$finalstatement = ilUtil::stripSlashes($_POST["finalstatement"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$data["finalstatement"] = $finalstatement;
		$data["showfinalstatement"] = ($_POST["showfinalstatement"]) ? 1 : 0;
		$data["showinfo"] = ($_POST["showinfo"]) ? 1 : 0;
		$data["forcejs"] = ($_POST["forcejs"]) ? 1 : 0;
		$data["customstyle"] = (strcmp($_POST["customstyle"], "0") == 0) ? "" : $_POST["customstyle"];
		$data["sequence_settings"] = ilUtil::stripSlashes($_POST["chb_postpone"]);
		$data["shuffle_questions"] = 0;
		if ($_POST["chb_shuffle_questions"])
		{
			$data["shuffle_questions"] = $_POST["chb_shuffle_questions"];
		}
		$data["list_of_questions"] = 0;
		if ($_POST["list_of_questions"] == 1)
		{
			$data["list_of_questions"] = 1;
		}
		$data["list_of_questions_start"] = 0;
		if ($_POST["chb_list_of_questions_start"] == 1)
		{
			$data["list_of_questions_start"] = 1;
		}
		$data["list_of_questions_end"] = 0;
		if ($_POST["chb_list_of_questions_end"] == 1)
		{
			$data["list_of_questions_end"] = 1;
		}
		$data["list_of_questions_with_description"] = 0;
		if ($_POST["chb_list_of_questions_with_description"] == 1)
		{
			$data["list_of_questions_with_description"] = 1;
		}
		$data["nr_of_tries"] = ilUtil::stripSlashes($_POST["nr_of_tries"]);
		$data["kiosk"] = ilUtil::stripSlashes($_POST["kiosk"]);
		$data["kiosk_title"] = ilUtil::stripSlashes($_POST["kiosk_title"]);
		$data["kiosk_participant"] = ilUtil::stripSlashes($_POST["kiosk_participant"]);
		$data["processing_time"] = ilUtil::stripSlashes($_POST["processing_time"]);
		if (!$_POST["chb_starting_time"])
		{
			$data["starting_time"] = "";
		}
		else
		{
			$data["starting_time"] = sprintf("%04d%02d%02d%02d%02d%02d",
				$_POST["starting_date"]["y"],
				$_POST["starting_date"]["m"],
				$_POST["starting_date"]["d"],
				$_POST["starting_time"]["h"],
				$_POST["starting_time"]["m"],
				0
			);
		}
		if (!$_POST["chb_ending_time"])
		{
			$data["ending_time"] = "";
		}
		else
		{
			$data["ending_time"] = sprintf("%04d%02d%02d%02d%02d%02d",
				$_POST["ending_date"]["y"],
				$_POST["ending_date"]["m"],
				$_POST["ending_date"]["d"],
				$_POST["ending_time"]["h"],
				$_POST["ending_time"]["m"],
				0
			);
		}

		if ($_POST["chb_processing_time"])
		{
			$data["enable_processing_time"] = "1";
		}
		else
		{
			$data["enable_processing_time"] = "0";
		}
		$data["reset_processing_time"] = "0";
		if ($_POST["chb_processing_time"])
		{
			if ($_POST["chb_reset_processing_time"])
			{
				$data["reset_processing_time"] = "1";
			}
		}
		if ($_POST["chb_use_previous_answers"])
		{
			$data["use_previous_answers"] = "1";
		}
		else
		{
			$data["use_previous_answers"] = "0";
		}

		$data["title_output"] = $_POST["title_output"];

		if ($data["enable_processing_time"])
		{
			$data["processing_time"] = sprintf("%02d:%02d:%02d",
				$_POST["processing_time"]["h"],
				$_POST["processing_time"]["m"],
				$_POST["processing_time"]["s"]
			);
		}
		else
		{
			$proc_time = $this->object->getEstimatedWorkingTime();
			$data["processing_time"] = sprintf("%02d:%02d:%02d",
				$proc_time["h"],
				$proc_time["m"],
				$proc_time["s"]
			);
		}

		if ($data["nr_of_tries"] == 1)
		{
			$data["pass_scoring"] = SCORE_LAST_PASS;
		}
		$this->object->setIntroduction($data["introduction"]);
		$this->object->setFinalStatement($data["finalstatement"]);
		$this->object->setShowFinalStatement($data["showfinalstatement"]);
		$this->object->setShowInfo($data["showinfo"]);
		$this->object->setForceJS($data["forcejs"]);
		$this->object->setCustomStyle($data["customstyle"]);
		$this->object->setSequenceSettings($data["sequence_settings"]);
		$this->object->setAnonymity($data["anonymity"]);
		$this->object->setShowCancel($data["show_cancel"]);
		$this->object->setShowMarker($data["show_marker"]);
		$this->object->setPassword($data["password"]);
		$this->object->setAllowedUsers($data["allowedUsers"]);
		$this->object->setAllowedUsersTimeGap($data["allowedUsersTimeGap"]);
		$this->object->setKioskMode($data["kiosk"]);
		$this->object->setShowKioskModeTitle($data["kiosk_title"]);
		$this->object->setShowKioskModeParticipant($data["kiosk_participant"]);
		$this->object->setNrOfTries($data["nr_of_tries"]);
		$this->object->setStartingTime($data["starting_time"]);
		$this->object->setEndingTime($data["ending_time"]);
		$this->object->setProcessingTime($data["processing_time"]);
		$this->object->setRandomTest($data["random_test"]);
		$this->object->setEnableProcessingTime($data["enable_processing_time"]);
		$this->object->setResetProcessingTime($data["reset_processing_time"]);
		$this->object->setUsePreviousAnswers($data["use_previous_answers"]);
		$this->object->setTitleOutput($data["title_output"]);
		
		if ($this->object->isRandomTest())
		{
			$this->object->setUsePreviousAnswers(0);
			$this->object->setRandomTest(1);
		}
		if ($data["shuffle_questions"])
		{
			$this->object->setShuffleQuestions(TRUE);
		}
		else
		{
			$this->object->setShuffleQuestions(FALSE);
		}
		$this->object->setListOfQuestions($data["list_of_questions"]);
		$this->object->setListOfQuestionsStart($data["list_of_questions_start"]);
		$this->object->setListOfQuestionsEnd($data["list_of_questions_end"]);
		$this->object->setListOfQuestionsDescription($data["list_of_questions_with_description"]);

		$this->object->saveToDb(true);

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
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
		$this->ctrl->redirect($this, "properties");
	}
	
	/**
	* Save the form input of the scoring form
	*
	* Save the form input of the scoring form
	*
	* @access	public
	*/
	function saveScoringObject()
	{
		$total = $this->object->evalTotalPersons();
		// Check the values the user entered in the form
		if (!$total)
		{
			$data["count_system"] = $_POST["count_system"];
			$data["mc_scoring"] = $_POST["mc_scoring"];
			$data["score_cutting"] = $_POST["score_cutting"];
			$data["pass_scoring"] = $_POST["pass_scoring"];
		}
		else
		{
			$data["count_system"] = $this->object->getCountSystem();
			$data["mc_scoring"] = $this->object->getMCScoring();
			$data["score_cutting"] = $this->object->getScoreCutting();
			$data["pass_scoring"] = $this->object->getPassScoring();
		}

		$data["instant_feedback_solution"] = $_POST["chb_instant_feedback_solution"];
		$data["answer_feedback"] = ($_POST["chb_instant_feedback_answer"]) ? 1 : 0;
		$data["answer_feedback_points"] = ($_POST["chb_instant_feedback_results"]) ? 1 : 0;

		$data["show_solution_printview"] = ($_POST["chb_show_solution_printview"] == 1) ? 1 : 0;
		$data["show_solution_feedback"] = ($_POST["chb_show_solution_feedback"] == 1) ? 1 : 0;
		$data["show_solution_suggested"] = ($_POST["chb_show_solution_suggested"] == 1) ? 1 : 0;
		$data["show_solution_details"] = $_POST["chb_show_solution_details"];
		$data["show_solution_answers_only"] = $_POST["chb_show_solution_answers_only"];
		$data["show_solution_signature"] = $_POST["chb_show_solution_signature"];
		$data["show_pass_details"] = $_POST["chb_show_pass_details"];
		$data["results_access"] = $_POST["results_access"];
		
		$this->object->setCountSystem($data["count_system"]);
		$this->object->setMCScoring($data["mc_scoring"]);
		$this->object->setScoreCutting($data["score_cutting"]);
		$this->object->setPassScoring($data["pass_scoring"]);
		$this->object->setInstantFeedbackSolution($data["instant_feedback_solution"]);
		$this->object->setAnswerFeedback($data["answer_feedback"]);
		$this->object->setAnswerFeedbackPoints($data["answer_feedback_points"]);
		$this->object->setShowSolutionDetails($data["show_solution_details"]);
		$this->object->setShowSolutionAnswersOnly($data["show_solution_answers_only"]);
		$this->object->setShowSolutionSignature($data["show_solution_signature"]);
		$this->object->setShowPassDetails($data["show_pass_details"]);
		$this->object->setScoreReporting($data["results_access"]);
		$this->object->setShowSolutionPrintview($data["show_solution_printview"]);
		$this->object->setShowSolutionFeedback($data["show_solution_feedback"]);
		$this->object->setShowSolutionSuggested($data["show_solution_suggested"]);
		if ($data["results_access"] == REPORT_AFTER_DATE)
		{
			$data["reporting_date"] = sprintf("%04d%02d%02d%02d%02d%02d",
				$_POST["reporting_date"]["y"],
				$_POST["reporting_date"]["m"],
				$_POST["reporting_date"]["d"],
				$_POST["reporting_time"]["h"],
				$_POST["reporting_time"]["m"],
				0
			);
			$this->object->setReportingDate($data["reporting_date"]);
		}
		else
		{
			$this->object->setReportingDate("");
		}
		$this->object->saveToDb(true);
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), TRUE);

		$this->ctrl->redirect($this, "scoring");
	}
	
	/**
	* Display and fill the scoring settings form of the test
	*
	* Display and fill the scoring settings form of the test
	*
	* @access	public
	*/
	function scoringObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$data["count_system"] = $this->object->getCountSystem();
		$data["mc_scoring"] = $this->object->getMCScoring();
		$data["score_cutting"] = $this->object->getScoreCutting();
		$data["pass_scoring"] = $this->object->getPassScoring();
		$data["instant_feedback_solution"] = $this->object->getInstantFeedbackSolution();
		$data["answer_feedback"] = $this->object->getAnswerFeedback();
		$data["answer_feedback_points"] = $this->object->getAnswerFeedbackPoints();
		$data["show_solution_printview"] = $this->object->getShowSolutionPrintview();
		$data["show_solution_feedback"] = $this->object->getShowSolutionFeedback();
		$data["show_solution_suggested"] = $this->object->getShowSolutionSuggested();
		$data["show_solution_details"] = $this->object->getShowSolutionDetails();
		$data["show_solution_answers_only"] = $this->object->getShowSolutionAnswersOnly();
		$data["show_solution_signature"] = $this->object->getShowSolutionSignature();
		$data["show_pass_details"] = $this->object->getShowPassDetails();
		$data["results_access"] = $this->object->getScoreReporting();

		$total = $this->object->evalTotalPersons();
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_scoring.html", "Modules/Test");

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
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./Modules/Test/js/calendar/calendar.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./Modules/Test/js/calendar/calendar-setup.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./Modules/Test/js/calendar/calendar.css");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("javascript_call_calendar");
		$this->tpl->setVariable("INPUT_FIELDS_REPORTING_DATE", "reporting_date");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("HEADING_SCORING_AND_RESULTS", $this->lng->txt("scoring"));
		$this->tpl->setVariable("DEFAULT", "(" . $this->lng->txt("default") . ")");
		$this->tpl->setVariable("TEXT_COUNT_SYSTEM", $this->lng->txt("tst_text_count_system"));
		$this->tpl->setVariable("COUNT_PARTIAL_SOLUTIONS", $this->lng->txt("tst_count_partial_solutions"));
		$this->tpl->setVariable("COUNT_CORRECT_SOLUTIONS", $this->lng->txt("tst_count_correct_solutions"));
		$this->tpl->setVariable("COUNT_SYSTEM_DESCRIPTION", $this->lng->txt("tst_count_system_description"));
		switch ($data["count_system"])
		{
			case COUNT_CORRECT_SOLUTIONS:
				$this->tpl->setVariable("CHECKED_COUNT_CORRECT_SOLUTIONS", " checked=\"checked\"");
				break;
			case COUNT_PARTIAL_SOLUTIONS:
			default:
				$this->tpl->setVariable("CHECKED_COUNT_PARTIAL_SOLUTIONS", " checked=\"checked\"");
				break;
		}
		if ($total)
		{
			$this->tpl->setVariable("DISABLED_COUNT_CORRECT_SOLUTIONS", " disabled=\"disabled\"");
			$this->tpl->setVariable("DISABLED_COUNT_PARTIAL_SOLUTIONS", " disabled=\"disabled\"");
		}

		$this->tpl->setVariable("TEXT_SCORE_MCMR", $this->lng->txt("tst_score_mcmr_questions"));
		$this->tpl->setVariable("ZERO_POINTS_WHEN_UNANSWERED", $this->lng->txt("tst_score_mcmr_zero_points_when_unanswered"));
		$this->tpl->setVariable("USE_SCORING_SYSTEM", $this->lng->txt("tst_score_mcmr_use_scoring_system"));
		$this->tpl->setVariable("TEXT_SCORE_MCMR_DESCRIPTION", $this->lng->txt("tst_score_mcmr_questions_description"));
		switch ($data["mc_scoring"])
		{
			case SCORE_ZERO_POINTS_WHEN_UNANSWERED:
				$this->tpl->setVariable("CHECKED_ZERO_POINTS_WHEN_UNANSWERED", " checked=\"checked\"");
				break;
			case SCORE_STANDARD_SCORE_SYSTEM:
			default:
				$this->tpl->setVariable("CHECKED_USE_SCORING_SYSTEM", " checked=\"checked\"");
				break;
		}
		if ($total)
		{
			$this->tpl->setVariable("DISABLED_ZERO_POINTS_WHEN_UNANSWERED", " disabled=\"disabled\"");
			$this->tpl->setVariable("DISABLED_USE_SCORING_SYSTEM", " disabled=\"disabled\"");
		}

		$this->tpl->setVariable("TEXT_SCORE_CUTTING", $this->lng->txt("tst_score_cutting"));
		$this->tpl->setVariable("TEXT_CUT_QUESTION", $this->lng->txt("tst_score_cut_question"));
		$this->tpl->setVariable("TEXT_CUT_TEST", $this->lng->txt("tst_score_cut_test"));
		$this->tpl->setVariable("TEXT_SCORE_CUTTING_DESCRIPTION", $this->lng->txt("tst_score_cutting_description"));
		switch ($data["score_cutting"])
		{
			case SCORE_CUT_QUESTION:
				$this->tpl->setVariable("CHECKED_CUT_QUESTION", " checked=\"checked\"");
				break;
			case SCORE_CUT_TEST:
			default:
				$this->tpl->setVariable("CHECKED_CUT_TEST", " checked=\"checked\"");
				break;
		}
		if ($total)
		{
			$this->tpl->setVariable("DISABLED_CUT_QUESTION", " disabled=\"disabled\"");
			$this->tpl->setVariable("DISABLED_CUT_TEST", " disabled=\"disabled\"");
		}
		
		$this->tpl->setVariable("TEXT_PASS_SCORING", $this->lng->txt("tst_pass_scoring"));
		$this->tpl->setVariable("TEXT_LASTPASS", $this->lng->txt("tst_pass_last_pass"));
		$this->tpl->setVariable("TEXT_BESTPASS", $this->lng->txt("tst_pass_best_pass"));
		$this->tpl->setVariable("TEXT_PASS_SCORING_DESCRIPTION", $this->lng->txt("tst_pass_scoring_description"));
		switch ($data["pass_scoring"])
		{
			case SCORE_BEST_PASS:
				$this->tpl->setVariable("CHECKED_BESTPASS", " checked=\"checked\"");
				break;
			case SCORE_LAST_PASS:
			default:
				$this->tpl->setVariable("CHECKED_LASTPASS", " checked=\"checked\"");
				break;
		}
		if ($total)
		{
			$this->tpl->setVariable("DISABLED_BESTPASS", " disabled=\"disabled\"");
			$this->tpl->setVariable("DISABLED_LASTPASS", " disabled=\"disabled\"");
		}

		$this->tpl->setVariable("TEXT_INSTANT_FEEDBACK", $this->lng->txt("tst_instant_feedback"));
		$this->tpl->setVariable("TEXT_ANSWER_SPECIFIC_FEEDBACK", $this->lng->txt("tst_instant_feedback_answer_specific"));
		$this->tpl->setVariable("TEXT_SHOW_RESULTS", $this->lng->txt("tst_instant_feedback_results"));
		if ($data["answer_feedback_points"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_RESULTS", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SHOW_SOLUTION", $this->lng->txt("tst_instant_feedback_solution"));
		$this->tpl->setVariable("TEXT_INSTANT_FEEDBACK_DESCRIPTION", $this->lng->txt("tst_instant_feedback_description"));
		if ($data["instant_feedback_solution"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION", " checked=\"checked\"");
		}
		if ($data["answer_feedback"])
		{
			$this->tpl->setVariable("CHECKED_ANSWER_SPECIFIC_FEEDBACK", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_RESULTS_PRESENTATION", $this->lng->txt("tst_results_presentation"));
		$this->tpl->setVariable("TEXT_SHOW_PASS_DETAILS", $this->lng->txt("tst_show_pass_details"));
		if ($data["show_pass_details"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_PASS_DETAILS", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_DETAILS", $this->lng->txt("tst_show_solution_details"));
		if ($data["show_solution_details"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_DETAILS", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_ANSWERS_ONLY", $this->lng->txt("tst_show_solution_answers_only"));
		if ($data["show_solution_answers_only"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_ANSWERS_ONLY", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_SIGNATURE", $this->lng->txt("tst_show_solution_signature"));
		if ($this->object->getAnonymity())
		{
			$this->tpl->setVariable("DISABLED_SHOW_SOLUTION_SIGNATURE", " disabled=\"disabled\"");
		}
		else
		{
			if ($data["show_solution_signature"])
			{
				$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_SIGNATURE", " checked=\"checked\"");
			}
		}
		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_SUGGESTED", $this->lng->txt("tst_show_solution_suggested"));
		if ($data["show_solution_suggested"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_SUGGESTED", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_FEEDBACK", $this->lng->txt("tst_show_solution_feedback"));
		if ($data["show_solution_feedback"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_FEEDBACK", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_SHOW_SOLUTION_PRINTVIEW", $this->lng->txt("tst_show_solution_printview"));
		$this->tpl->setVariable("TEXT_RESULTS_PRESENTATION_DESCRIPTION", $this->lng->txt("tst_results_presentation_description"));
		if ($data["show_solution_printview"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_SOLUTION_PRINTVIEW", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_RESULTS_ACCESS", $this->lng->txt("tst_results_access"));
		$this->tpl->setVariable("TEXT_RESULTS_FINISHED", $this->lng->txt("tst_results_access_finished"));
		$this->tpl->setVariable("TEXT_RESULTS_DATE", $this->lng->txt("tst_results_access_date"));
		if ($data["results_access"] != REPORT_AFTER_DATE)
		{
			$report = getdate(time()+60*60*24*7);
			$date_input = ilUtil::makeDateSelect("reporting_date", $report["year"], $report["mon"], $report["mday"]);
			$time_input = ilUtil::makeTimeSelect("reporting_time", true, "12", "0", "0");
		} else {
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getReportingDate(), $matches);
			$date_input = ilUtil::makeDateSelect("reporting_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
			$time_input = ilUtil::makeTimeSelect("reporting_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
		}
		switch ($data["results_access"])
		{
			case REPORT_ALWAYS:
				$this->tpl->setVariable("CHECKED_RESULTS_ALWAYS", " checked=\"checked\"");
				break;
			case REPORT_AFTER_DATE:
				$this->tpl->setVariable("CHECKED_RESULTS_DATE", " checked=\"checked\"");
				break;
			case REPORT_AFTER_TEST:
			default:
				$this->tpl->setVariable("CHECKED_RESULTS_FINISHED", " checked=\"checked\"");
				break;
		}
		$this->tpl->setVariable("INPUT_REPORTING_DATE", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
		$this->tpl->setVariable("IMG_REPORTING_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_REPORTING_DATE_CALENDAR", $this->lng->txt("open_calendar"));
		$this->tpl->setVariable("TEXT_RESULTS_ALWAYS", $this->lng->txt("tst_results_access_always"));
		$this->tpl->setVariable("TEXT_RESULTS_ACCESS_DESCRIPTION", $this->lng->txt("tst_results_access_description"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		if ($ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		}
		
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Display and fill the properties form of the test
	*
	* Display and fill the properties form of the test
	*
	* @access	public
	*/
	function propertiesObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		// to set the command class for the default command after object creation to make the RTE editor switch work
		if (strlen($this->ctrl->getCmdClass()) == 0) $this->ctrl->setCmdClass("ilobjtestgui");
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		include_once "./classes/class.ilObject.php";
		$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_properties.html", "Modules/Test");
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
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./Modules/Test/js/calendar/calendar.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./Modules/Test/js/calendar/calendar-setup.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./Modules/Test/js/calendar/calendar.css");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("javascript_call_calendar");
		$this->tpl->setVariable("INPUT_FIELDS_STARTING_DATE", "starting_date");
		$this->tpl->setVariable("INPUT_FIELDS_ENDING_DATE", "ending_date");
		$this->tpl->parseCurrentBlock();

		$customstyles = $this->object->getCustomStyles();
		if (is_array($customstyles) && count($customstyles) > 0)
		{
			foreach ($customstyles as $customstyle)
			{
				$this->tpl->setCurrentBlock("customstyle_option");
				$this->tpl->setVariable("VALUE_OPTION_CUSTOMSTYLE", $customstyle);
				$this->tpl->setVariable("TEXT_OPTION_CUSTOMSTYLE", $customstyle);
				if (strcmp($this->object->getCustomStyle(), $customstyle) == 0)
				{
					$this->tpl->setVariable("SELECTION_OPTION_CUSTOMSTYLE", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("customtyle");
			$this->tpl->setVariable("TEXT_CUSTOMSTYLE", $this->lng->txt("customstyle"));
			$this->tpl->setVariable("TEXT_NO_CUSTOMSTYLE", $this->lng->txt("no_selection"));
			$this->tpl->setVariable("TEXT_DESCRIPTION_CUSTOMSTYLE", $this->lng->txt("customstyle_description"));
			$this->tpl->parseCurrentBlock();
		}

		$total = $this->object->evalTotalPersons();
		$data["anonymity"] = $this->object->getAnonymity();
		$data["show_cancel"] = $this->object->getShowCancel();
		$data["show_marker"] = $this->object->getShowMarker();
		$data["introduction"] = $this->object->getIntroduction();
		$data["sequence_settings"] = $this->object->getSequenceSettings();
		$data["nr_of_tries"] = $this->object->getNrOfTries();
		$data["kiosk"] = $this->object->getKioskMode();
		$data["kiosk_title"] = $this->object->getShowKioskModeTitle();
		$data["kiosk_participant"] = $this->object->getShowKioskModeParticipant();
		$data["use_previous_answers"] = $this->object->getUsePreviousAnswers();
		$data["title_output"] = $this->object->getTitleOutput();
		$data["enable_processing_time"] = $this->object->getEnableProcessingTime();
		$data["reset_processing_time"] = $this->object->getResetProcessingTime();
		$data["processing_time"] = $this->object->getProcessingTime();
		$data["random_test"] = $this->object->isRandomTest();
		$data["password"] = $this->object->getPassword();
		$data["allowedUsers"] = $this->object->getAllowedUsers();
		$data["allowedUsersTimeGap"] = $this->object->getAllowedUsersTimeGap();
		if (!$this->object->getEnableProcessingTime())
		{
			$proc_time = $this->object->getEstimatedWorkingTime();
			$data["processing_time"] = sprintf("%02d:%02d:%02d",
				$proc_time["h"],
				$proc_time["m"],
				$proc_time["s"]
			);
		}
		$data["starting_time"] = $this->object->getStartingTime();
		$data["ending_time"] = $this->object->getEndingTime();
		
		$this->tpl->setCurrentBlock("starting_time");
		$this->tpl->setVariable("TEXT_STARTING_TIME", $this->lng->txt("tst_starting_time"));
		if (!$data["starting_time"])
		{
			$date_input = ilUtil::makeDateSelect("starting_date");
			$time_input = ilUtil::makeTimeSelect("starting_time");
		}
		else
		{
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["starting_time"], $matches);
			$date_input = ilUtil::makeDateSelect("starting_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
			$time_input = ilUtil::makeTimeSelect("starting_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
		}
		$this->tpl->setVariable("IMG_STARTING_TIME_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_STARTING_TIME_CALENDAR", $this->lng->txt("open_calendar"));
		$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
		if ($data["starting_time"])
		{
			$this->tpl->setVariable("CHECKED_STARTING_TIME", " checked=\"checked\"");
		}
		$this->tpl->setVariable("INPUT_STARTING_TIME", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("ending_time");
		$this->tpl->setVariable("TEXT_ENDING_TIME", $this->lng->txt("tst_ending_time"));
		if (!$data["ending_time"])
		{
			$date_input = ilUtil::makeDateSelect("ending_date");
			$time_input = ilUtil::makeTimeSelect("ending_time");
		}
		else
		{
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["ending_time"], $matches);
			$date_input = ilUtil::makeDateSelect("ending_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
			$time_input = ilUtil::makeTimeSelect("ending_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
		}
		$this->tpl->setVariable("IMG_ENDING_TIME_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_ENDING_TIME_CALENDAR", $this->lng->txt("open_calendar"));
		$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
		if ($data["ending_time"])
		{
			$this->tpl->setVariable("CHECKED_ENDING_TIME", " checked=\"checked\"");
		}
		$this->tpl->setVariable("INPUT_ENDING_TIME", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_PROPERTIES", $this->ctrl->getFormAction($this));
		if ($ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			$this->tpl->setVariable("SUBMIT_TYPE", $this->lng->txt("change"));
		}
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("tst_general_properties"));
		$this->tpl->setVariable("TEXT_ANONYMITY", $this->lng->txt("tst_anonymity"));
		$this->tpl->setVariable("DESCRIPTION_ANONYMITY", $this->lng->txt("tst_anonymity_description"));
		if ($data["anonymity"])
		{
			$this->tpl->setVariable("CHECKED_ANONYMITY", " checked=\"checked\"");
		}
		if ($total)
		{
			$this->tpl->setVariable("DISABLED_ANONYMITY", " disabled=\"disabled\"");
		}

		$this->tpl->setVariable("TEXT_SHOW_MARKER", $this->lng->txt("question_marking"));
		$this->tpl->setVariable("TEXT_SHOW_MARKER_DESCRIPTION", $this->lng->txt("question_marking_description"));
		if ($data["show_marker"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_MARKER", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_SHOW_CANCEL", $this->lng->txt("tst_show_cancel"));
		$this->tpl->setVariable("TEXT_SHOW_CANCEL_DESCRIPTION", $this->lng->txt("tst_show_cancel_description"));
		if ($data["show_cancel"])
		{
			$this->tpl->setVariable("CHECKED_SHOW_CANCEL", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("tst_introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($data["introduction"])));
		$this->tpl->setVariable("SHOWINFO", $this->lng->txt("showinfo"));
		$this->tpl->setVariable("SHOWINFO_DESC", $this->lng->txt("showinfo_desc"));
		if ($this->object->getShowInfo())
		{
			$this->tpl->setVariable("CHECKED_SHOWINFO", " checked=\"checked\"");
		}
		$this->tpl->setVariable("FINAL_STATEMENT", $this->lng->txt("final_statement"));
		$this->tpl->setVariable("VALUE_FINAL_STATEMENT", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFinalStatement())));
		$this->tpl->setVariable("FINAL_STATEMENT_SHOW", $this->lng->txt("final_statement_show"));
		$this->tpl->setVariable("FINAL_STATEMENT_SHOW_DESC", $this->lng->txt("final_statement_show_desc"));
		if ($this->object->getShowFinalStatement())
		{
			$this->tpl->setVariable("CHECKED_FINAL_STATEMENT_SHOW", " checked=\"checked\"");
		}
		$this->tpl->setVariable("HEADING_SEQUENCE", $this->lng->txt("tst_sequence_properties"));
		$this->tpl->setVariable("TEXT_POSTPONE", $this->lng->txt("tst_postpone"));
		$this->tpl->setVariable("TEXT_POSTPONE_DESCRIPTION", $this->lng->txt("tst_postpone_description"));
		if ($data["sequence_settings"] == 1) 
		{
			$this->tpl->setVariable("CHECKED_POSTPONE", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SHUFFLE_QUESTIONS", $this->lng->txt("tst_shuffle_questions"));
		$this->tpl->setVariable("TEXT_SHUFFLE_QUESTIONS_DESCRIPTION", $this->lng->txt("tst_shuffle_questions_description"));
		if ($this->object->getShuffleQuestions())
		{
			$this->tpl->setVariable("CHECKED_SHUFFLE_QUESTIONS", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_SHOW_SUMMARY", $this->lng->txt("tst_show_summary"));
		$this->tpl->setVariable("TEXT_SHOW_SUMMARY_DESCRIPTION", $this->lng->txt("tst_show_summary_description"));
		$this->tpl->setVariable("TEXT_NO", $this->lng->txt("no"));
		$this->tpl->setVariable("TEXT_YES", $this->lng->txt("tst_list_of_questions_yes"));
		$this->tpl->setVariable("TEXT_LIST_OF_QUESTIONS_START", $this->lng->txt("tst_list_of_questions_start"));
		$this->tpl->setVariable("TEXT_LIST_OF_QUESTIONS_END", $this->lng->txt("tst_list_of_questions_end"));
		$this->tpl->setVariable("TEXT_LIST_OF_QUESTIONS_WITH_DESCRIPTION", $this->lng->txt("tst_list_of_questions_with_description"));
		if ($this->object->getListOfQuestions())
		{
			$this->tpl->setVariable("CHECKED_LIST_OF_QUESTIONS_YES", " checked=\"checked\"");
			if ($this->object->getListOfQuestionsStart())
			{
				$this->tpl->setVariable("CHECKED_LIST_OF_QUESTIONS_START", " checked=\"checked\"");
			}
			if ($this->object->getListOfQuestionsEnd())
			{
				$this->tpl->setVariable("CHECKED_LIST_OF_QUESTIONS_END", " checked=\"checked\"");
			}
			if ($this->object->getListOfQuestionsDescription())
			{
				$this->tpl->setVariable("CHECKED_LIST_OF_QUESTIONS_WITH_DESCRIPTION", " checked=\"checked\"");
			}
		}
		else
		{
			$this->tpl->setVariable("CHECKED_LIST_OF_QUESTIONS_NO", " checked=\"checked\"");
		}
		
		$this->tpl->setVariable("TEXT_USE_PREVIOUS_ANSWERS", $this->lng->txt("tst_use_previous_answers"));
		$this->tpl->setVariable("TEXT_USE_PREVIOUS_ANSWERS_DESCRIPTION", $this->lng->txt("tst_use_previous_answers_description"));

		$this->tpl->setVariable("FORCEJS", $this->lng->txt("forcejs"));
		$this->tpl->setVariable("FORCEJS_SHORT", $this->lng->txt("forcejs_short"));
		$this->tpl->setVariable("FORCEJS_DESC", $this->lng->txt("forcejs_desc"));
		if ($this->object->getForceJS())
		{
			$this->tpl->setVariable("CHECKED_FORCEJS", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_TITLE_OUTPUT", $this->lng->txt("tst_title_output"));
		$this->tpl->setVariable("TEXT_TITLE_OUTPUT_FULL", $this->lng->txt("tst_title_output_full"));
		$this->tpl->setVariable("TEXT_TITLE_OUTPUT_HIDE_POINTS", $this->lng->txt("tst_title_output_hide_points"));
		$this->tpl->setVariable("TEXT_TITLE_OUTPUT_NO_TITLE", $this->lng->txt("tst_title_output_no_title"));
		$this->tpl->setVariable("TEXT_TITLE_OUTPUT_DESCRIPTION", $this->lng->txt("tst_title_output_description"));
		switch ($data["title_output"])
		{
			case 1:
				$this->tpl->setVariable("CHECKED_TITLE_OUTPUT_HIDE_POINTS", " checked=\"checked\"");
				break;
			case 2:
				$this->tpl->setVariable("CHECKED_TITLE_OUTPUT_NO_TITLE", " checked=\"checked\"");
				break;
			case 0:
			default:
				$this->tpl->setVariable("CHECKED_TITLE_OUTPUT_FULL", " checked=\"checked\"");
				break;
		}
		if ($data["random_test"])
		{
			$data["use_previous_answers"] = 0;
		}
		if ($data["use_previous_answers"])
		{
			$this->tpl->setVariable("CHECKED_USE_PREVIOUS_ANSWERS",  " checked=\"checked\"");
		}
		if ($data["random_test"])
		{
			$this->tpl->setVariable("DISABLE_USE_PREVIOUS_ANSWERS",  " disabled=\"disabled\"");
		}
		$this->tpl->setVariable("HEADING_KIOSK", $this->lng->txt("kiosk"));
		$this->tpl->setVariable("TEXT_KIOSK", $this->lng->txt("kiosk"));
		if ($data["kiosk"]) 
		{
			$this->tpl->setVariable("CHECKED_KIOSK", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_KIOSK_DESCRIPTION", $this->lng->txt("kiosk_description"));
		$this->tpl->setVariable("TEXT_KIOSK_OPTIONS", $this->lng->txt("kiosk_options"));
		$this->tpl->setVariable("TEXT_KIOSK_OPTIONS_DESCRIPTION", $this->lng->txt("kiosk_options_desc"));
		$this->tpl->setVariable("TEXT_KIOSK_TITLE", $this->lng->txt("kiosk_show_title"));
		if ($data["kiosk_title"]) 
		{
			$this->tpl->setVariable("CHECKED_KIOSK_TITLE", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_KIOSK_PARTICIPANT", $this->lng->txt("kiosk_show_participant"));
		if ($data["kiosk_participant"]) 
		{
			$this->tpl->setVariable("CHECKED_KIOSK_PARTICIPANT", " checked=\"checked\"");
		}
		$this->tpl->setVariable("HEADING_SESSION", $this->lng->txt("tst_session_settings"));
		$this->tpl->setVariable("TEXT_NR_OF_TRIES", $this->lng->txt("tst_nr_of_tries"));
		$this->tpl->setVariable("VALUE_NR_OF_TRIES", $data["nr_of_tries"]);
		$this->tpl->setVariable("COMMENT_NR_OF_TRIES", $this->lng->txt("0_unlimited"));
		$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
		$this->tpl->setVariable("TXT_RESET_PROCESSING_TIME", $this->lng->txt("tst_reset_processing_time"));
		$this->tpl->setVariable("TEXT_RESET_PROCESSING_TIME_DESC", $this->lng->txt("tst_reset_processing_time_desc"));
		$this->tpl->setVariable("TEXT_PROCESSING_TIME", $this->lng->txt("tst_processing_time"));
		$this->tpl->setVariable("TEXT_PROCESSING_TIME_DESC", $this->lng->txt("tst_processing_time_desc"));
		$time_input = ilUtil::makeTimeSelect("processing_time", false, substr($data["processing_time"], 0, 2), substr($data["processing_time"], 3, 2), substr($data["processing_time"], 6, 2));
		$this->tpl->setVariable("MAX_PROCESSING_TIME", $time_input . " (hh:mm:ss)");
		if ($data["enable_processing_time"]) {
			$this->tpl->setVariable("CHECKED_PROCESSING_TIME", " checked=\"checked\"");
		}
		if ($data["reset_processing_time"]) 
		{
			$this->tpl->setVariable("CHECKED_RESET_PROCESSING_TIME", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_RANDOM_TEST", $this->lng->txt("tst_random_selection"));
		$this->tpl->setVariable("TEXT_RANDOM_TEST_DESCRIPTION", $this->lng->txt("tst_random_test_description"));
		if ($data["random_test"]) 
		{
			$this->tpl->setVariable("CHECKED_RANDOM_TEST", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TEXT_MAX_ALLOWED_USERS", $this->lng->txt("tst_max_allowed_users"));
		$this->tpl->setVariable("TEXT_ALLOWED_USERS", $this->lng->txt("tst_allowed_users"));
		$this->tpl->setVariable("TEXT_ALLOWED_USERS_TIME_GAP", $this->lng->txt("tst_allowed_users_time_gap"));
		if ($data["allowedUsers"] > 0)
		{
			$this->tpl->setVariable("VALUE_ALLOWED_USERS", " value=\"" . $data["allowedUsers"] . "\"");
		}
		$this->tpl->setVariable("TEXT_ALLOWED_USERS_TIME_GAP", $this->lng->txt("tst_allowed_users_time_gap"));
		if ($data["allowedUsersTimeGap"] > 0)
		{
			$this->tpl->setVariable("VALUE_ALLOWED_USERS_TIME_GAP", " value=\"" . $data["allowedUsersTimeGap"] . "\"");
		}
		$this->tpl->setVariable("SECONDS", $this->lng->txt("seconds"));
		$this->tpl->setVariable("TEXT_PASSWORD", $this->lng->txt("tst_password"));
		$this->tpl->setVariable("TEXT_PASSWORD_DETAILS", $this->lng->txt("tst_password_details"));
		if (strlen($data["password"]))
		{
			$this->tpl->setVariable("VALUE_PASSWORD", " value=\"". ilUtil::prepareFormOutput($data["password"])."\"");
		}
		if ($ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		}
		if ($total > 0)
		{
			$this->tpl->setVariable("ENABLED_RANDOM_TEST", " disabled=\"disabled\"");
		}
		$this->tpl->parseCurrentBlock();
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
	* Insert questions from the questionbrowser into the test 
	*
	* Insert questions from the questionbrowser into the test 
	*
	* @access	public
	*/
	function insertQuestionsObject()
	{
		// insert selected questions into test
		$selected_array = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				array_push($selected_array, $matches[1]);
			}
		}
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"), true);
			$this->ctrl->setParameterByClass(get_class($this), "sel_filter_type", $_POST["sel_filter_type"]);
			$this->ctrl->setParameterByClass(get_class($this), "sel_question_type", $_POST["sel_question_type"]);
			$this->ctrl->setParameterByClass(get_class($this), "sel_questionpool", $_POST["sel_questionpool"]);
			$this->ctrl->setParameterByClass(get_class($this), "filter_text", $_POST["filter_text"]);
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

	/**
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* @access	public
	*/
	function questionBrowser()
	{
		global $ilAccess;

		$this->ctrl->setParameterByClass(get_class($this), "browse", "1");
		$textfilters = array();
		if (strcmp($this->ctrl->getCmd(), "resetFilter") == 0)
		{
			$filter_type = "";
			$filter_question_type = "";
			$filter_questionpool = "";
			$filter_text = "";
		}
		else
		{
			$filter_question_type = (array_key_exists("sel_question_type", $_POST)) ? $_POST["sel_question_type"] : $_GET["sel_question_type"];
			$filter_type = (array_key_exists("sel_filter_type", $_POST)) ? $_POST["sel_filter_type"] : $_GET["sel_filter_type"];
			$filter_questionpool = (array_key_exists("sel_questionpool", $_POST)) ? $_POST["sel_questionpool"] : $_GET["sel_questionpool"];
			$filter_text = (array_key_exists("filter_text", $_POST)) ? $_POST["filter_text"] : $_GET["filter_text"];
		}
		
		$filter_title = (array_key_exists("filter_title", $_POST)) ? $_POST["filter_title"] : $_GET["filter_title"];
		if (strlen($filter_title)) $textfilters["title"] = $filter_title;
		$filter_qpl = (array_key_exists("filter_qpl", $_POST)) ? $_POST["filter_qpl"] : $_GET["filter_qpl"];
		if (strlen($filter_qpl)) $textfilters["qpl"] = $filter_qpl;
		$filter_comment = (array_key_exists("filter_comment", $_POST)) ? $_POST["filter_comment"] : $_GET["filter_comment"];
		if (strlen($filter_comment)) $textfilters["comment"] = $filter_comment;
		$filter_author = (array_key_exists("filter_author", $_POST)) ? $_POST["filter_author"] : $_GET["filter_author"];
		if (strlen($filter_author)) $textfilters["author"] = $filter_author;

		$this->ctrl->setParameterByClass(get_class($this), "sel_filter_type", $filter_type);
		$this->ctrl->setParameterByClass(get_class($this), "sel_question_type", $filter_question_type);
		$this->ctrl->setParameterByClass(get_class($this), "sel_questionpool", $filter_questionpool);
		$this->ctrl->setParameterByClass(get_class($this), "filter_text", $filter_text);
		$this->ctrl->setParameterByClass(get_class($this), "filter_title", $filter_title);
		$this->ctrl->setParameterByClass(get_class($this), "filter_qpl", $filter_qpl);
		$this->ctrl->setParameterByClass(get_class($this), "filter_comment", $filter_comment);
		$this->ctrl->setParameterByClass(get_class($this), "filter_author", $filter_author);
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questionbrowser.html", "Modules/Test");
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", "Modules/Test");
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_tst_filter_questions.html", "Modules/Test");

		$questionpools =& $this->object->getAvailableQuestionpools(true);
		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) {
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (strcmp($this->ctrl->getCmd(), "resetFilter") != 0) 
			{
				if (strcmp($filter_type, $key) == 0) 
				{
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$questiontypes =& ilObjQuestionPool::_getQuestionTypes();
		foreach ($questiontypes as $key => $value)
		{
			$this->tpl->setCurrentBlock("questiontype_row");
			$this->tpl->setVariable("VALUE_QUESTION_TYPE", $value["type_tag"]);
			$this->tpl->setVariable("TEXT_QUESTION_TYPE", $key);
			if (strcmp($filter_question_type, $value["type_tag"]) == 0)
			{
				$this->tpl->setVariable("SELECTED_QUESTION_TYPE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("questionpool_row");
			$this->tpl->setVariable("VALUE_QUESTIONPOOL", $key);
			$this->tpl->setVariable("TEXT_QUESTIONPOOL", $value["title"]);
			if (strcmp($filter_questionpool, $key) == 0)
			{
				$this->tpl->setVariable("SELECTED_QUESTIONPOOL", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("SHOW_QUESTION_TYPES", $this->lng->txt("filter_show_question_types"));
		$this->tpl->setVariable("TEXT_ALL_QUESTION_TYPES", $this->lng->txt("filter_all_question_types"));
		$this->tpl->setVariable("SHOW_QUESTIONPOOLS", $this->lng->txt("filter_show_questionpools"));
		$this->tpl->setVariable("TEXT_ALL_QUESTIONPOOLS", $this->lng->txt("filter_all_questionpools"));
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		if (strcmp($this->ctrl->getCmd(), "resetFilter") != 0) 
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
		$sort = ($_GET["sort"]) ? $_GET["sort"] : "title";
		$sortorder = ($_GET["sortorder"]) ? $_GET["sortorder"] : "ASC";
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		if (strlen($filter_text) && strlen($filter_type)) $textfilters[$filter_type] = $filter_text;
		$table = $this->object->getQuestionsTable($sort, $sortorder, $textfilters, $startrow, 1, $filter_question_type, $filter_questionpool);
		// display all questions in accessable question pools
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$existing_questions =& $this->object->getExistingQuestions();
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if ((is_array($table["rows"])) && (count($table["rows"])))
		{
			foreach ($table["rows"] as $data)
			{
				if (!in_array($data["question_id"], $existing_questions))
				{
					if ($data["complete"])
					{
						// make only complete questions selectable
						$this->tpl->setCurrentBlock("checkable");
						$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
					$this->tpl->setVariable("PREVIEW", "[<a href=\"" . $this->ctrl->getLinkTarget($this, "questions") . "&preview=" . $data["question_id"] . "\">" . $this->lng->txt("preview") . "</a>]");
					$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
					$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
					$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
					$this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
					$this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data['tstamp'],IL_CAL_UNIX)));
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data["obj_fi"]]["title"]);
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
					$this->tpl->setCurrentBlock("pages");
					if ($table["startrow"] == $i)
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<span class=\"inactivepage\">$counter</span>");
					}
					else
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "&nextrow=$i" . "\">$counter</a>");
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
				$this->tpl->setVariable("HREF_PREV_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . "&prevrow=" . $table["prevrow"]);
				$this->tpl->setVariable("HREF_NEXT_ROWS", $this->ctrl->getLinkTarget($this, "browseForQuestions") . "&nextrow=" . $table["nextrow"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		// if there are no questions, display a message
		if (!((is_array($table["rows"])) && (count($table["rows"]))))
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// create edit buttons & table footer
			$this->tpl->setCurrentBlock("selection");
			$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
			$this->tpl->parseCurrentBlock();
	
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$counter++;
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
	
			$this->tpl->setCurrentBlock("Footer");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
			$this->tpl->parseCurrentBlock();
		}
		// define the sort column parameters
		$sortarray = array(
			"title" => (strcmp($sort, "title") == 0) ? $sortorder : "",
			"comment" => (strcmp($sort, "comment") == 0) ? $sortorder : "",
			"type" => (strcmp($sort, "type") == 0) ? $sortorder : "",
			"author" => (strcmp($sort, "author") == 0) ? $sortorder : "",
			"created" => (strcmp($sort, "created") == 0) ? $sortorder : "",
			"updated" => (strcmp($sort, "updated") == 0) ? $sortorder : "",
			"qpl" => (strcmp($sort, "qpl") == 0) ? $sortorder : ""
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

		// add imports for YUI menu
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initMenu();
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "tpl.text_filter.css", "Modules/TestQuestionPool"));

		// add title text filter
		$titlefilter = new ilTemplate("tpl.text_filter.js", TRUE, TRUE, "Modules/TestQuestionPool");
		$titlefilter->setVariable("FILTERELEMENTID", "titlefilter");
		$titlefilter->setVariable("OVERLAY_WIDTH", "500px");
		$titlefilter->setVariable("OVERLAY_HEIGHT", "5em");
		$titlefilter->setVariable("TEXTFIELD_NAME", "filter_title");
		$titlefilter->setVariable("IMAGE_CLOSE", ilUtil::getImagePath("icon_close2_s.gif"));
		$titlefilter->setVariable("ALT_CLOSE", $this->lng->txt("close"));
		$titlefilter->setVariable("TITLE_CLOSE", $this->lng->txt("close"));
		$titlefilter->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "filter"));
		$titlefilter->setVariable("VALUE_FILTER_TEXT", $filter_title);
		$titlefilter->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$titlefilter->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $titlefilter->get());
		$this->tpl->parseCurrentBlock();

		// add questiontype filter
		$filtermenu = new ilTemplate("tpl.question_type_menu.js", TRUE, TRUE, "Modules/TestQuestionPool");
		if (strcmp($filter_question_type, "") == 0)
		{
			$filtermenu->setCurrentBlock("selected");
			$filtermenu->touchBlock("selected");
			$filtermenu->parseCurrentBlock();
		}
		$filtermenu->setCurrentBlock("menuitem");
		$filtermenu->setVariable("ITEM_TEXT", $this->lng->txt("filter_all_question_types"));
		$this->ctrl->setParameter($this, "sel_question_type", "");
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$filtermenu->setVariable("ITEM_URL", $this->ctrl->getLinkTarget($this, "browseForQuestions"));
		$filtermenu->parseCurrentBlock();
		foreach ($questiontypes as $key => $value)
		{
			if (strcmp($filter_question_type, $value["type_tag"]) == 0)
			{
				$filtermenu->setCurrentBlock("selected");
				$filtermenu->touchBlock("selected");
				$filtermenu->parseCurrentBlock();
			}
			$filtermenu->setCurrentBlock("menuitem");
			$filtermenu->setVariable("VALUE_QUESTION_TYPE", $value["type_tag"]);
			$filtermenu->setVariable("ITEM_TEXT", $key);
			$this->ctrl->setParameter($this, "sel_question_type", $value["type_tag"]);
			$filtermenu->setVariable("ITEM_URL", $this->ctrl->getLinkTarget($this, "browseForQuestions"));
			$filtermenu->parseCurrentBlock();
		}
		$this->ctrl->setParameter($this, "sel_question_type", $filter_question_type);
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $filtermenu->get());
		$this->tpl->parseCurrentBlock();

		// add description text filter
		$commenttextfilter = new ilTemplate("tpl.text_filter.js", TRUE, TRUE, "Modules/TestQuestionPool");
		$commenttextfilter->setVariable("FILTERELEMENTID", "commenttextfilter");
		$commenttextfilter->setVariable("OVERLAY_WIDTH", "500px");
		$commenttextfilter->setVariable("OVERLAY_HEIGHT", "8em");
		$commenttextfilter->setVariable("TEXTFIELD_NAME", "filter_comment");
		$commenttextfilter->setVariable("IMAGE_CLOSE", ilUtil::getImagePath("icon_close2_s.gif"));
		$commenttextfilter->setVariable("ALT_CLOSE", $this->lng->txt("close"));
		$commenttextfilter->setVariable("TITLE_CLOSE", $this->lng->txt("close"));
		$commenttextfilter->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "filter"));
		$commenttextfilter->setVariable("VALUE_FILTER_TEXT", $filter_comment);
		$commenttextfilter->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$commenttextfilter->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $commenttextfilter->get());
		$this->tpl->parseCurrentBlock();

		// add author text filter
		$authortextfilter = new ilTemplate("tpl.text_filter.js", TRUE, TRUE, "Modules/TestQuestionPool");
		$authortextfilter->setVariable("FILTERELEMENTID", "authortextfilter");
		$authortextfilter->setVariable("OVERLAY_WIDTH", "500px");
		$authortextfilter->setVariable("OVERLAY_HEIGHT", "5em");
		$authortextfilter->setVariable("TEXTFIELD_NAME", "filter_author");
		$authortextfilter->setVariable("IMAGE_CLOSE", ilUtil::getImagePath("icon_close2_s.gif"));
		$authortextfilter->setVariable("ALT_CLOSE", $this->lng->txt("close"));
		$authortextfilter->setVariable("TITLE_CLOSE", $this->lng->txt("close"));
		$authortextfilter->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "filter"));
		$authortextfilter->setVariable("VALUE_FILTER_TEXT", $filter_author);
		$authortextfilter->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$authortextfilter->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $authortextfilter->get());
		$this->tpl->parseCurrentBlock();

		// add question pool text filter
		$qpltextfilter = new ilTemplate("tpl.text_filter.js", TRUE, TRUE, "Modules/TestQuestionPool");
		$qpltextfilter->setVariable("FILTERELEMENTID", "qpltextfilter");
		$qpltextfilter->setVariable("OVERLAY_WIDTH", "500px");
		$qpltextfilter->setVariable("OVERLAY_HEIGHT", "5em");
		$qpltextfilter->setVariable("TEXTFIELD_NAME", "filter_qpl");
		$qpltextfilter->setVariable("IMAGE_CLOSE", ilUtil::getImagePath("icon_close2_s.gif"));
		$qpltextfilter->setVariable("ALT_CLOSE", $this->lng->txt("close"));
		$qpltextfilter->setVariable("TITLE_CLOSE", $this->lng->txt("close"));
		$qpltextfilter->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "filter"));
		$qpltextfilter->setVariable("VALUE_FILTER_TEXT", $filter_qpl);
		$qpltextfilter->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$qpltextfilter->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $qpltextfilter->get());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->ctrl->setCmd("questionBrowser");
		$this->ctrl->setParameterByClass(get_class($this), "startrow", $table["startrow"]);
		$template = new ilTemplate("tpl.image.html", true, true);
		if (strlen($filter_title))
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter-locked.png"));
		}
		else
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter.png"));
		}
		$template->setVariable("IMAGE_TITLE", $this->lng->txt("filter"));
		$template->setVariable("IMAGE_ALT", $this->lng->txt("filter"));
		$template->setVariable("ID", "titlefilter");
		$template->setVariable("STYLE", "visibility: hidden; cursor: pointer");
		$this->ctrl->setParameter($this, "sort", "title");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["title"]);
		$questiontitle = "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("title") . "</a>";
		$questiontitle .= $template->get();
		$questiontitle .= $table["images"]["title"];
		$this->tpl->setVariable("QUESTION_TITLE", $questiontitle);
		$this->ctrl->setParameter($this, "sort", "comment");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["comment"]);
		$template = new ilTemplate("tpl.image.html", true, true);
		if (strlen($filter_comment))
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter-locked.png"));
		}
		else
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter.png"));
		}
		$template->setVariable("IMAGE_TITLE", $this->lng->txt("filter"));
		$template->setVariable("IMAGE_ALT", $this->lng->txt("filter"));
		$template->setVariable("ID", "commenttextfilter");
		$template->setVariable("STYLE", "visibility: hidden; cursor: pointer");
		$this->ctrl->setParameter($this, "sort", "comment");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["comment"]);
		$questiontype = "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("description") . "</a>";
		$questiontype .= $template->get();
		$questiontype .= $table["images"]["comment"];
		$this->tpl->setVariable("QUESTION_COMMENT", $questiontype);
		$template = new ilTemplate("tpl.image.html", true, true);
		if (strlen($filter_question_type))
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter-locked.png"));
		}
		else
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter.png"));
		}
		$template->setVariable("IMAGE_TITLE", $this->lng->txt("filter"));
		$template->setVariable("IMAGE_ALT", $this->lng->txt("filter"));
		$template->setVariable("ID", "filter");
		$template->setVariable("STYLE", "visibility: hidden; cursor: pointer");
		$this->ctrl->setParameter($this, "sort", "type");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["type"]);
		$questiontype = "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("question_type") . "</a>";
		$questiontype .= $template->get();
		$questiontype .= $table["images"]["type"];
		$this->tpl->setVariable("QUESTION_TYPE", $questiontype);
		$template = new ilTemplate("tpl.image.html", true, true);
		if (strlen($filter_author))
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter-locked.png"));
		}
		else
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter.png"));
		}
		$template->setVariable("IMAGE_TITLE", $this->lng->txt("filter"));
		$template->setVariable("IMAGE_ALT", $this->lng->txt("filter"));
		$template->setVariable("ID", "authortextfilter");
		$template->setVariable("STYLE", "visibility: hidden; cursor: pointer;");
		$this->ctrl->setParameter($this, "sort", "author");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["author"]);
		$questiontype = "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("author") . "</a>";
		$questiontype .= $template->get();
		$questiontype .= $table["images"]["author"];
		$this->tpl->setVariable("QUESTION_AUTHOR", $questiontype);
		$this->ctrl->setParameter($this, "sort", "created");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["created"]);
		$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
		$this->ctrl->setParameter($this, "sort", "updated");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["updated"]);
		$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
		$template = new ilTemplate("tpl.image.html", true, true);
		if (strlen($filter_qpl))
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter-locked.png"));
		}
		else
		{
			$template->setVariable("IMAGE_SOURCE", ilUtil::getImagePath("search-filter.png"));
		}
		$template->setVariable("IMAGE_TITLE", $this->lng->txt("filter"));
		$template->setVariable("IMAGE_ALT", $this->lng->txt("filter"));
		$template->setVariable("ID", "qpltextfilter");
		$template->setVariable("STYLE", "visibility: hidden; cursor: pointer");
		$this->ctrl->setParameter($this, "sort", "qpl");
		$this->ctrl->setParameter($this, "sortorder", $sortarray["qpl"]);
		$qpfilter = "<a href=\"" . $this->ctrl->getLinkTarget($this, "browseForQuestions") . "\">" . $this->lng->txt("obj_qpl") . "</a>";
		$qpfilter .= $template->get();
		$qpfilter .= $table["images"]["qpl"];
		$this->tpl->setVariable("QUESTION_POOL", $qpfilter);
		$this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
		$this->ctrl->setParameter($this, "sort", $sort);
		$this->ctrl->setParameter($this, "sortorder", $sortorder);
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
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
	
	function saveRandomQuestionsObject()
	{
		$this->randomQuestionsObject();
	}
	
	function addQuestionpoolObject()
	{
		$this->randomQuestionsObject();
	}

	function randomQuestionsObject()
	{
		global $ilUser;
		$selection_mode = $ilUser->getPref("tst_question_selection_mode_equal");
		$total = $this->object->evalTotalPersons();
		$available_qpl =& $this->object->getAvailableQuestionpools(TRUE, $selection_mode, FALSE, TRUE, TRUE);
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$qpl_question_count = array();
		foreach ($available_qpl as $key => $value)
		{
			if ($value["count"] > 0)
			{
				$qpl_question_count[$key] = $value["count"];
			}
			else
			{
				unset($available_qpl[$key]);
			}
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_questions.html", "Modules/Test");
		$found_qpls = array();
		if (count($_POST) == 0)
		{
			$found_qpls = $this->object->getRandomQuestionpools();
		}
		$qpl_unselected = 0;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/countqpl_(\d+)/", $key, $matches))
			{
				$questioncount = $qpl_question_count[$_POST["qpl_" . $matches[1]]];
				if ((strlen($questioncount) > 0) && ($value > $questioncount))
				{
					$value = $questioncount;
					ilUtil::sendInfo($this->lng->txt("tst_random_selection_question_count_too_high"));
				}
				$found_qpls[$matches[1]] = array(
					"index" => $matches[1],
					"count" => sprintf("%d", $value),
					"qpl"   => $_POST["qpl_" . $matches[1]],
					"title" => $available_qpl[$_POST["qpl_" . $matches[1]]]["title"]
				);
				if ($_POST["qpl_" . $matches[1]] == -1)
				{
					$qpl_unselected = 1;
				}
			}
		}
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/deleteqpl_(\d+)/", $key, $matches))
				{
					unset($found_qpls[$matches[1]]);
				}
			}
		}
		sort($found_qpls);
		$found_qpls = array_values($found_qpls);
		if (count($found_qpls) == 0)
		{
			foreach ($available_qpl as $key => $value)
			{
				$this->tpl->setCurrentBlock("qpl_value");
				$this->tpl->setVariable("QPL_ID", $key);
				$this->tpl->setVariable("QPL_TEXT", $value["title"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("questionpool_row");
			$this->tpl->setVariable("COUNTQPL", "0");
			$this->tpl->setVariable("VALUE_COUNTQPL", $_POST["countqpl_0"]);
			$this->tpl->setVariable("TEXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TEXT_QUESTIONS_FROM", $this->lng->txt("questions_from"));
			$this->tpl->setVariable("BTNCOUNTQPL", 0);
			$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}
		$counter = 0;
		foreach ($found_qpls as $key => $value)
		{
			$pools = $available_qpl;
			foreach ($found_qpls as $pkey => $pvalue)
			{
				if ($pvalue["qpl"] != $value["qpl"])
				{
					unset($pools[$pvalue["qpl"]]);
				}
			}
			// create first questionpool row automatically
			foreach ($pools as $pkey => $pvalue)
			{
				$this->tpl->setCurrentBlock("qpl_value");
				$this->tpl->setVariable("QPL_ID", $pkey);
				$this->tpl->setVariable("QPL_TEXT", $pvalue["title"]);
				if ($pkey == $value["qpl"])
				{
					$this->tpl->setVariable("SELECTED_QPL", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("questionpool_row");
			$this->tpl->setVariable("COUNTQPL", $counter);
			$this->tpl->setVariable("VALUE_COUNTQPL", $value["count"]);
			$this->tpl->setVariable("TEXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TEXT_QUESTIONS_FROM", $this->lng->txt("questions_from"));
			$this->tpl->setVariable("BTNCOUNTQPL", $counter);
			$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if ($_POST["cmd"]["addQuestionpool"])
		{
			if ($qpl_unselected)
			{
				ilUtil::sendInfo($this->lng->txt("tst_random_qpl_unselected"));
			}
			else
			{
				$pools = $available_qpl;
				foreach ($found_qpls as $pkey => $pvalue)
				{
					unset($pools[$pvalue["qpl"]]);
				}
				if (count($pools) == 0)
				{
					ilUtil::sendInfo($this->lng->txt("tst_no_more_available_questionpools"));
				}
				else
				{
					foreach ($pools as $key => $value)
					{
						$this->tpl->setCurrentBlock("qpl_value");
						$this->tpl->setVariable("QPL_ID", $key);
						$this->tpl->setVariable("QPL_TEXT", $value["title"]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("questionpool_row");
					$this->tpl->setVariable("COUNTQPL", "$counter");
					$this->tpl->setVariable("TEXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
					$this->tpl->setVariable("TEXT_QUESTIONS_FROM", $this->lng->txt("questions_from"));
					$this->tpl->setVariable("BTNCOUNTQPL", $counter);
					$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		if ($_POST["cmd"]["saveRandomQuestions"])
		{
			$this->object->saveRandomQuestionCount($_POST["total_questions"]);
			$this->object->saveRandomQuestionpools($found_qpls);
			$this->object->saveCompleteStatus();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_SELECT_RANDOM_QUESTIONS", $this->lng->txt("tst_select_random_questions"));
		$this->tpl->setVariable("TEXT_TOTAL_QUESTIONS", $this->lng->txt("tst_total_questions"));
		$this->tpl->setVariable("TEXT_TOTAL_QUESTIONS_DESCRIPTION", $this->lng->txt("tst_total_questions_description"));
		$total_questions = $this->object->getRandomQuestionCount();
		if (array_key_exists("total_questions", $_POST))
		{
			$total_questions = $_POST["total_questions"];
		}
		if ($total_questions > 0)
		{
			$sum = 0;
			foreach ($found_qpls as $key => $value)
			{
				$sum += $qpl_question_count[$value["qpl"]];
			}
			if ($total_questions > $sum)
			{
				$total_questions = $sum;
				if ($_POST["cmd"]["saveRandomQuestions"])
				{
					$this->object->saveRandomQuestionCount($total_questions);
				}
				ilUtil::sendInfo($this->lng->txt("tst_random_selection_question_total_count_too_high"));
			}
		}
		$this->tpl->setVariable("VALUE_TOTAL_QUESTIONS", $total_questions);
		$this->tpl->setVariable("TEXT_QUESTIONPOOLS", $this->lng->txt("tst_random_questionpools"));
		if (!$total)
		{
			$this->tpl->setVariable("BTN_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("BTN_ADD_QUESTIONPOOL", $this->lng->txt("add_questionpool"));
		}
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TEXT_QUESTION_SELECTION", $this->lng->txt("tst_question_selection"));
		$this->tpl->setVariable("VALUE_QUESTION_SELECTION", $this->lng->txt("tst_question_selection_equal"));
		$this->tpl->setVariable("CMD_QUESTION_SELECTION", "setEqualQplSelection");
		$this->tpl->setVariable("TEXT_QUESTION_SELECTION_DESCRIPTION", $this->lng->txt("tst_question_selection_description"));
		$this->tpl->setVariable("BUTTON_SAVE", $this->lng->txt("change"));
		if ($selection_mode == 1)
		{
			$this->tpl->setVariable("CHECKED_QUESTION_SELECTION_MODE", " checked=\"checked\"");
		}
		$this->tpl->parseCurrentBlock();
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
	*
	* Marks selected questions for moving
	*
	* @access	public
	*/
	function moveQuestionsObject()
	{
		$this->questionsObject();
	}
	
	/**
	* Insert checked questions before the actual selection
	*
	* Insert checked questions before the actual selection
	*
	* @access	public
	*/
	function insertQuestionsBeforeObject()
	{
		// get all questions to move
		$move_questions = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^move_(\d+)$/", $key, $matches))
			{
				array_push($move_questions, $value);
			}
		}
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
		}
		if ($insert_id <= 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_target_selected_for_move"), true);
		}
		else
		{
			$insert_mode = 0;
			$this->object->moveQuestions($move_questions, $insert_id, $insert_mode);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert checked questions after the actual selection
	*
	* Insert checked questions after the actual selection
	*
	* @access	public
	*/
	function insertQuestionsAfterObject()
	{
		// get all questions to move
		$move_questions = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^move_(\d+)$/", $key, $matches))
			{
				array_push($move_questions, $value);
			}
		}
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
		}
		if ($insert_id <= 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_target_selected_for_move"), true);
		}
		else
		{
			$insert_mode = 1;
			$this->object->moveQuestions($move_questions, $insert_id, $insert_mode);
		}
		$this->ctrl->redirect($this, "questions");
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

		$checked_move = 0;
		if (strcmp($this->ctrl->getCmd(), "moveQuestions") == 0)
		{
			if (is_array($_POST['q_id']))
			{
				foreach ($_POST['q_id'] as $value)
				{
					$checked_move++;
					$this->tpl->setCurrentBlock("move");
					$this->tpl->setVariable("MOVE_COUNTER", $value);
					$this->tpl->setVariable("MOVE_VALUE", $value);
					$this->tpl->parseCurrentBlock();
				}
			}
			if (!$checked_move)
			{
				ilUtil::sendInfo($this->lng->txt("no_question_selected_for_move"));
			}
		}

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
		include_once "./Modules/Test/classes/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'questions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)), $checked_move);
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
	*
	* Asks for a confirmation to delete all user data of the test object
	*
	* @access	public
	*/
	function deleteAllUserResultsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "participants"));
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT", $this->lng->txt("delete_all_user_data"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("table_row");
		$this->tpl->setVariable("CSS_ROW", "tblrow1");
		$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("delete_all_user_data_confirmation"));
		$this->tpl->parseCurrentBlock();

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
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_status.html", "Modules/Test");
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
			$log =& ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), TRUE);
			if (count($log))
			{
				$tblrow = array("tblrow1", "tblrow2");
				$counter = 0;
				include_once './Services/User/classes/class.ilObjUser.php';
				foreach ($log as $entry)
				{
					$this->tpl->setCurrentBlock("changelog_row");
					$this->tpl->setVariable("ROW_CLASS", $tblrow[$counter % 2]);
					$username = $this->object->userLookupFullName($entry["user_fi"], TRUE);
					$this->tpl->setVariable("TXT_USER", $username);
					$this->tpl->setVariable("TXT_DATETIME", ilDatePresentation::formatDate(new ilDateTime($entry["tstamp"],IL_CAL_UNIX)));
					if (strlen($entry["ref_id"]) && strlen($entry["href"]))
					{
						$this->tpl->setVariable("TXT_TEST_REFERENCE", $this->lng->txt("perma_link"));
						$this->tpl->setVariable("HREF_REFERENCE", $entry["href"]);
					}
					$this->tpl->setVariable("TXT_LOGTEXT", trim(ilUtil::prepareFormOutput($entry["logtext"])));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("changelog");
				$this->tpl->setVariable("HEADER_DATETIME", $this->lng->txt("assessment_log_datetime"));
				$this->tpl->setVariable("HEADER_USER", $this->lng->txt("user"));
				$this->tpl->setVariable("HEADER_LOGTEXT", $this->lng->txt("assessment_log_text"));
				$this->tpl->setVariable("HEADER_TEST_REFERENCE", $this->lng->txt("location"));
				$this->tpl->setVariable("HEADING_CHANGELOG", $this->lng->txt("changelog_heading"));
				$this->tpl->setVariable("DESCRIPTION_CHANGELOG", $this->lng->txt("changelog_description"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->parseCurrentBlock();
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
	*
	* Cancels the change of the fixed participants status when fixed participants already exist
	*
	* @access	public
	*/
	function cancelFixedParticipantsStatusChangeObject()
	{
		$this->ctrl->redirect($this, "inviteParticipants");
	}
	
 /**
	* Confirms the change of the fixed participants status when fixed participants already exist
	*
	* Confirms the change of the fixed participants status when fixed participants already exist
	*
	* @access	public
	*/
	function confirmFixedParticipantsStatusChangeObject()
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
	*
	* Shows a confirmation dialog to remove fixed participants from the text
	*
	* @access	public
	*/
	function confirmFixedParticipantsStatusChange()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT", $this->lng->txt("tst_fixed_participants_disable"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("table_row");
		$this->tpl->setVariable("CSS_ROW", "tblrow1");
		$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("tst_fixed_participants_disable_description"));
		$this->tpl->parseCurrentBlock();

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
	*
	* Saves the status change of the fixed participants status
	*
	* @access	public
	*/
	function saveFixedParticipantsStatusObject()
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
	*
	* Creates the output for user/group invitation to a test
	*
	* @access	public
	*/
	function inviteParticipantsObject()
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
							$this->outUserGroupTable("usr", $users, "user_result", "user_row", $this->lng->txt("search_user"),"TEXT_USER_TITLE", $buttons);
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
							$this->outUserGroupTable("grp", $groups, "group_result", "group_row", $this->lng->txt("search_group"), "TEXT_GROUP_TITLE", $buttons);
					}
					
					$searchresult = array();
					
					if ($searchresult = $search->getResultByType("role"))
					{
						$roles = array();
						
						foreach ($searchresult as $result_array)
						{							
							array_push($roles, $result_array["id"]);
						}
						
						$roles = $this->object->getRoleData ($roles);			
								
						if (count ($roles))
							$this->outUserGroupTable("role", $roles, "role_result", "role_row", $this->lng->txt("role"), "TEXT_ROLE_TITLE", $buttons);
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
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_invite.html", "Modules/Test");
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_participants.html", "Modules/Test");
		}
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::addYesNoDialog(
			"deleteAllUserResults", 
			$this->lng->txt("delete_all_user_data"), 
			$this->lng->txt("confirm_delete_all_user_data"), 
			"location.href='" . $this->ctrl->getLinkTarget($this, "confirmDeleteAllUserResults") . "';", 
			"", 
			TRUE, 
			$icon = "warn"
		);
		if ($_POST["cmd"]["cancel"])
		{
			$this->backToRepositoryObject();
		}
		
		if ($_POST["cmd"]["save"])
		{
			$this->object->saveToDb();
		}
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			if ($this->tpl->blockExists("invitation"))
			{
				$this->tpl->setCurrentBlock("invitation");
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
				if (strcmp($_POST["concatenation"], "and") == 0)
				{
					$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
				}
				else if (strcmp($_POST["concatenation"], "or") == 0)
				{
					$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
				}
				$this->tpl->setVariable("SEARCH", $this->lng->txt("search"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($this->object->getFixedParticipants())
		{
			$invited_users =& $this->object->getInvitedUsers();
			if (count($invited_users) == 0)
			{
				ilUtil::sendInfo($this->lng->txt("tst_participants_no_fixed"));
			}
			else
			{
				$this->tpl->setCurrentBlock("delete_all");
				$this->tpl->setVariable("VALUE_DELETE_ALL_USER_DATA", $this->lng->txt("delete_all_user_data"));
				$this->tpl->setVariable("FORMACTION_DELETEALL", $this->ctrl->getFormAction($this, "deleteAllUserResults"));
				$this->tpl->parseCurrentBlock();
			}
			$buttons = array(array("saveClientIP" => "save"),array("removeParticipant" => "remove_as_participant"));
			if (!$this->object->getAnonymity())
			{
				array_push($buttons, array("showPassOverview" => "show_pass_overview"));
				array_push($buttons, array("showUserAnswers" => "show_user_answers"));
				array_push($buttons, array("showDetailedResults" => "show_detailed_results"));
			}
			array_push($buttons, array("deleteSingleUserResults" => "delete_user_data"));
			if (count($invited_users))
			{
				$this->outUserGroupTable("iv_usr", $invited_users, "invited_user_result", "invited_user_row", $this->lng->txt("tst_fixed_participating_users"), "TEXT_INVITED_USER_TITLE",$buttons);
			}
		}
		else
		{
			$invited_users =& $this->object->getTestParticipants();
			if (count($invited_users) == 0)	
			{
				ilUtil::sendInfo($this->lng->txt("tst_participants_no"));
			}
			else
			{
				$this->tpl->setCurrentBlock("delete_all");
				$this->tpl->setVariable("VALUE_DELETE_ALL_USER_DATA", $this->lng->txt("delete_all_user_data"));
				$this->tpl->setVariable("FORMACTION_DELETEALL", $this->ctrl->getFormAction($this, "deleteAllUserResults"));
				$this->tpl->parseCurrentBlock();
			}
			$buttons = array();
			if (!$this->object->getAnonymity())
			{
				array_push($buttons, array("showPassOverview" => "show_pass_overview"));
				array_push($buttons, array("showUserAnswers" => "show_user_answers"));
				array_push($buttons, array("showDetailedResults" => "show_detailed_results"));
			}
			array_push($buttons, array("deleteSingleUserResults" => "delete_user_data"));
			if (count($invited_users))
			{
				$this->outUserGroupTable("iv_participants", $invited_users, "invited_user_result", "invited_user_row", $this->lng->txt("tst_participating_users"), "TEXT_INVITED_USER_TITLE",$buttons);
			}
		}

		if ($this->object->getFixedParticipants())
		{
			$this->tpl->setCurrentBlock("fixed_participants_hint");
			$this->tpl->setVariable("FIXED_PARTICIPANTS_HINT", sprintf($this->lng->txt("fixed_participants_hint"), $this->lng->txt("participants_invitation")));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_INVITATION", $this->lng->txt("invitation"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		if ($ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

 /**
	* Shows the pass overview and the answers of one ore more users for the scored pass
	*
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

		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
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
	
	/**
	* Output of the table structures for selected users and selected groups
	*
	* Output of the table structures for selected users and selected groups
	* for the invite participants tab
	*
	* @access	private
	*/
	function outUserGroupTable($a_type, $data_array, $block_result, $block_row, $title_text, $title_label, $buttons)
	{
		global $ilAccess;
		$rowclass = array("tblrow1", "tblrow2");
		switch($a_type)
		{
			case "iv_usr":
				if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					foreach ($buttons as $arr)
					{
						foreach ($arr as $val => $cat)
						{
							$this->tpl->setCurrentBlock("commandoption");
							$this->tpl->setVariable("OPTION_NAME", $this->lng->txt($cat));
							$this->tpl->setVariable("OPTION_VALUE", $val);
							$this->tpl->parseCurrentBlock();
						}
					}
					$this->tpl->setCurrentBlock("user_action_buttons");
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
					$this->tpl->setVariable("VALUE_SUBMIT", $this->lng->txt("submit"));
					$this->tpl->parseCurrentBlock();
				}

				$finished = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("checkbox_checked")."\" />";
				$started  = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("checkbox_checked")."\" />" ;
				$counter = 0;
				foreach ($data_array as $data)
				{
					$maxpass = $this->object->_getMaxPass($data["active_id"]);
					if (!is_null($maxpass))
					{
						$maxpass += 1;
					}
					$passes = ($maxpass) ? (($maxpass == 1) ? sprintf($this->lng->txt("pass_finished"), $maxpass) : sprintf($this->lng->txt("passes_finished"), $maxpass)) : "&nbsp;";
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $data["usr_id"]);
					$this->tpl->setVariable("VALUE_IV_USR_ID", $data["usr_id"]);
					$this->tpl->setVariable("VALUE_IV_LOGIN", $data["login"]);
					$this->tpl->setVariable("VALUE_IV_FIRSTNAME", $data["firstname"]);
					$this->tpl->setVariable("VALUE_IV_LASTNAME", $data["lastname"]);
					$this->tpl->setVariable("VALUE_IV_CLIENT_IP", $data["clientip"]);
					$this->tpl->setVariable("VALUE_IV_TEST_FINISHED", ($data["test_finished"]==1)?$finished.$passes:$passes);
					$this->tpl->setVariable("VALUE_IV_TEST_STARTED", ($data["active_id"] > 0)?$started:"&nbsp;");
					if (strlen($data["usr_id"]))
					{
						$last_access = $this->object->_getLastAccess($data["active_id"]);
						if (!strlen($last_access))
						{
							$this->tpl->setVariable("VALUE_IV_LAST_ACCESS", $this->lng->txt("not_yet_accessed"));
						}
						else
						{
							$this->tpl->setVariable("VALUE_IV_LAST_ACCESS", ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME)));
						}
					}
					else
					{
						$last_access = $this->lng->txt("not_yet_accessed");
						$this->tpl->setVariable("VALUE_IV_LAST_ACCESS", $last_access);
					}
					$this->ctrl->setParameter($this, "active_id", $data["active_id"]);
					if ($data["active_id"] > 0)
					{
						$this->tpl->setVariable("VALUE_TST_SHOW_RESULTS", $this->lng->txt("tst_show_results"));
						$this->ctrl->setParameterByClass("iltestevaluationgui", "active_id", $data["active_id"]);
						$this->tpl->setVariable("URL_TST_SHOW_RESULTS", $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outParticipantsResultsOverview"));
					}
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($data_array))
				{
					$this->tpl->setCurrentBlock("selectall");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"".$this->lng->txt("objs_usr")."\" align=\"middle\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_IV_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_IV_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_IV_LASTNAME", $this->lng->txt("lastname"));
				$this->tpl->setVariable("TEXT_IV_CLIENT_IP", $this->lng->txt("clientip"));
				$this->tpl->setVariable("TEXT_IV_TEST_FINISHED", $this->lng->txt("tst_finished"));
				$this->tpl->setVariable("TEXT_IV_TEST_STARTED", $this->lng->txt("tst_started"));
				$this->tpl->setVariable("TEXT_IV_LAST_ACCESS", $this->lng->txt("last_access"));
				$this->tpl->parseCurrentBlock();
				break;
			case "iv_participants":
				if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					foreach ($buttons as $arr)
					{
						foreach ($arr as $val => $cat)
						{
							$this->tpl->setCurrentBlock("commandoption");
							$this->tpl->setVariable("OPTION_NAME", $this->lng->txt($cat));
							$this->tpl->setVariable("OPTION_VALUE", $val);
							$this->tpl->parseCurrentBlock();
						}
					}
					$this->tpl->setCurrentBlock("user_action_buttons");
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
					$this->tpl->setVariable("VALUE_SUBMIT", $this->lng->txt("submit"));
					$this->tpl->parseCurrentBlock();
				}
				$finished = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("checkbox_checked")."\" />";
				$started  = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("checkbox_checked")."\" />" ;
				$counter = 0;
				foreach ($data_array as $data)
				{
					$maxpass = $this->object->_getMaxPass($data["active_id"]);
					if (!is_null($maxpass))
					{
						$maxpass += 1;
					}
					$passes = ($maxpass) ? (($maxpass == 1) ? sprintf($this->lng->txt("pass_finished"), $maxpass) : sprintf($this->lng->txt("passes_finished"), $maxpass)) : "&nbsp;";
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $data["active_id"]);
					$this->tpl->setVariable("VALUE_IV_USR_ID", $data["active_id"]);
					$this->tpl->setVariable("VALUE_IV_LOGIN", $data["login"]);
					$this->tpl->setVariable("VALUE_IV_FIRSTNAME", $data["firstname"]);
					$this->tpl->setVariable("VALUE_IV_LASTNAME", $data["lastname"]);
					$this->tpl->setVariable("VALUE_IV_TEST_FINISHED", ($data["test_finished"]==1)?$finished.$passes:$passes);
					$this->tpl->setVariable("VALUE_IV_TEST_STARTED", ($data["active_id"] > 0)?$started:"&nbsp;");
					if (strlen($data["active_id"]))
					{
						$last_access = $this->object->_getLastAccess($data["active_id"]);
						$this->tpl->setVariable("VALUE_IV_LAST_ACCESS", ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME)));
						
					}
					else
					{
						$last_access = $this->lng->txt("not_yet_accessed");
						$this->tpl->setVariable("VALUE_IV_LAST_ACCESS", $last_access);
					}
					$this->ctrl->setParameter($this, "active_id", $data["active_id"]);
					if ($data["active_id"] > 0)
					{
						$this->tpl->setVariable("VALUE_TST_SHOW_RESULTS", $this->lng->txt("tst_show_results"));
						$this->ctrl->setParameterByClass("iltestevaluationgui", "active_id", $data["active_id"]);
						$this->tpl->setVariable("URL_TST_SHOW_RESULTS", $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outParticipantsResultsOverview"));
					}
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($data_array))
				{
					$this->tpl->setCurrentBlock("selectall");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"".$this->lng->txt("objs_usr")."\" align=\"middle\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_IV_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_IV_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_IV_LASTNAME", $this->lng->txt("lastname"));
				$this->tpl->setVariable("TEXT_IV_TEST_FINISHED", $this->lng->txt("tst_finished"));
				$this->tpl->setVariable("TEXT_IV_TEST_STARTED", $this->lng->txt("tst_started"));
				$this->tpl->setVariable("TEXT_IV_LAST_ACCESS", $this->lng->txt("last_access"));
				$this->tpl->parseCurrentBlock();
				break;
			case "usr":
				$counter = 0;
				foreach ($data_array as $data)
				{
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $data["usr_id"]);
					$this->tpl->setVariable("VALUE_LOGIN", $data["login"]);
					$this->tpl->setVariable("VALUE_FIRSTNAME", $data["firstname"]);
					$this->tpl->setVariable("VALUE_LASTNAME", $data["lastname"]);
					$this->tpl->setVariable("VALUE_CLIENT_IP", $data["clientip"]);
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($data_array))
				{
					$this->tpl->setCurrentBlock("selectall_user_row");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"".$this->lng->txt("objs_usr")."\" align=\"middle\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				$this->tpl->setVariable("TEXT_CLIENT_IP", $this->lng->txt("clientip"));
					
				if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				}
				$this->tpl->parseCurrentBlock();
				break;
				
			case "role":
			case "grp":
				$counter = 0;
				foreach ($data_array as $key => $data)
				{
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $key);
					$this->tpl->setVariable("VALUE_TITLE", $data["title"]);
					$this->tpl->setVariable("VALUE_DESCRIPTION", $data["description"]);
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				if (count($data_array))
				{
					$this->tpl->setCurrentBlock("selectall_" . $a_type . "_row");
					$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
					$counter++;
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_".$a_type."_b.gif") . "\" align=\"middle\" alt=\"".$this->lng->txt("objs_".$a_type)."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				}
				$this->tpl->parseCurrentBlock();
				break;
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
	
		if ($this->object->hasPDFProcessing())
		{
			// question export
			$ilTabs->addSubTabTarget("tst_single_results",
				$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
				array("singleResults"),
				"", "");
		}

		// settings
		$ilTabs->addSubTabTarget("settings",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "evalSettings"),
			array("evalSettings", "saveEvalSettings"),
			"", "");
	
	}
	
	function getParticipantsSubTabs()
	{
		global $ilTabs;
		
		// user results subtab
		$ilTabs->addSubTabTarget("participants_data",
			$this->ctrl->getLinkTarget($this,'participants'),
			array("participants", "saveFixedParticipantsStatus",
				"showParticipantAnswersForAuthor", "showResults",
				"confirmDeleteAllUserData",
				"deleteAllUserResults",
				"cancelDeleteAllUserData", "deleteSingleUserResults",
				"outParticipantsResultsOverview", "outParticipantsPassDetails",
				"showPassOverview", "showUserAnswers", "participantsAction"
			),
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
	
		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
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
			case "showParticipantAnswersForAuthor":
			case "participants":
			case "outParticipantsPassDetails":
			case "outParticipantsResultsOverview":
			case "deleteAllUserResults":
			case "confirmDeleteAllUserData":
			case "cancelDeleteAllUserData":
			case "deleteSingleUserResults":
			case "showPassOverview":
			case "showUserAnswers":
					 $this->getParticipantsSubTabs();
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
					 "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode", "print"), 
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
					 "showPassOverview", "showUserAnswers", "participantsAction"), 
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
