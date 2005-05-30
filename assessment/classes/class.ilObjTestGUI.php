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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
*
* @ilCtrl_Calls ilObjTestGUI: ilObjCourseGUI, ilMDEditorGUI
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

include_once "./assessment/classes/class.ilObjQuestionPool.php";
include_once "./classes/class.ilObjectGUI.php";
//include_once "./classes/class.ilMetaDataGUI.php";
include_once "./assessment/classes/class.assQuestionGUI.php";
include_once './classes/Spreadsheet/Excel/Writer.php';
require_once "./classes/class.ilSearch.php";
require_once "./classes/class.ilObjUser.php";
require_once "./classes/class.ilObjGroup.php";

define ("TYPE_XLS_PC", "latin1");
define ("TYPE_XLS_MAC", "macos");
define ("TYPE_SPSS", "csv");

class ilObjTestGUI extends ilObjectGUI
{
	var $sequence;

	var $cmdCtrl;

	var $maxProcessingTimeReached;

	var $endingTimeReached;

	var $saveResult;

	/**
	* Constructor
	* @access public
	*/
	function ilObjTestGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng, $ilCtrl;
		$lng->loadLanguageModule("assessment");
		$this->type = "tst";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		if (!defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("adm_object.php");
		}
		else
		{
			$this->setTabTargetScript("test.php");
		}
		if ($a_prepare_output) {
			$this->prepareOutput();
		}

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id");

		// Added parameter if called from crs_objectives
		if((int) $_GET['crs_show_result'])
		{
			$this->ctrl->saveParameter($this,'crs_show_result',(int) $_GET['crs_show_result']);
		}
	}
	
	function createCommandControlObject() {
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("read", $this->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		require_once "./assessment/classes/class.ilCommandControl.php";;
		if ($this->object->isOnlineTest()) {
			require_once "./assessment/classes/class.ilOnlineTestCommandControl.php";;
			$this->cmdCtrl = new OnlineTestCommandControl ($this, $this->object);
		} else 
			$this->cmdCtrl = new DefaultTestCommandControl ($this, $this->object);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd("properties");
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");

		#echo "<br>nextclass:$next_class:cmd:$cmd:qtype=$q_type";
		switch($next_class)
		{
			case 'ilmdeditorgui':
				$this->setAdminTabs();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			default:
				switch ($cmd)
				{
					case "run":
					case "eval_a":
					case "eval_stat":
					case "evalStatSelected":
					case "searchForEvaluation":
					case "addFoundGroupsToEval":
					case "removeSelectedGroup":
					case "removeSelectedUser":
					case "addFoundUsersToEval":
					case "evalSelectedUsers":
					case "evalAllUsers":
					case "printAnswers":
						break;
					default:
						$this->setAdminTabs();
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
	}

	/**
	* Returns the calling script of the GUI class
	*
	* @access	public
	*/
	function getCallingScript()
	{
		return "test.php";
	}

	/**
	* form for new test object import
	*/
	function importFileObject()
	{
		if ($_POST["qpl"] < 1)
		{
			sendInfo($this->lng->txt("tst_select_questionpools"));
			$this->createObject();
			return;
		}
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			sendInfo($this->lng->txt("tst_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->uploadObject(false);
		ilUtil::redirect($this->getReturnLocation("post","$returnlocation?".$this->link_params));
//		ilUtil::redirect($this->getCallingScript() . "?".$this->link_params);
	}
	
	/**
	* form for new test object duplication
	*/
	function cloneAllObject()
	{
		if ($_POST["tst"] < 1)
		{
			sendInfo($this->lng->txt("tst_select_tsts"));
			$this->createObject();
			return;
		}
		include_once "./assessment/classes/class.ilObjTest.php";
		ilObjTest::_clone($_POST["tst"]);
		ilUtil::redirect($this->getReturnLocation("post","$returnlocation?".$this->link_params));
//		ilUtil::redirect($this->getCallingScript() . "?".$this->link_params);
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

		$returnlocation = "test.php";
		if (!defined("ILIAS_MODULE"))
		{
			$returnlocation = "adm_object.php";
		}
		ilUtil::redirect($this->getReturnLocation("save","$returnlocation?".$this->link_params));
		exit();
	}

	function getAddParameter()
	{
		return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"] . '&crs_show_result='. (int) $_GET['crs_show_result'];
	}

	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}

		//$this->setTabs();

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", "test.php?ref_id=".$_GET["ref_id"]."&cmd=createExportFile&mode=xml");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("ass_create_export_file"));
		$this->tpl->parseCurrentBlock();
		
		// create export file button
		if ($this->object->isOnlineTest()) {
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", "test.php?ref_id=".$_GET["ref_id"]."&cmd=createExportFile&mode=results");
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("ass_create_export_test_results"));
			$this->tpl->parseCurrentBlock();
		}
		

		// view last export log button
		/*
		if (is_file($this->object->getExportDirectory()."/export.log"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "viewExportLog"));
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_view_last_export_log"));
			$this->tpl->parseCurrentBlock();
		}*/

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

		$tbl->setHeaderNames(array("<input type=\"checkbox\" name=\"chb_check_all\" value=\"1\" onclick=\"setCheckboxes('ObjectItems', 'file', document.ObjectItems.chb_check_all.checked);\" />", $this->lng->txt("ass_file"),
			$this->lng->txt("ass_size"), $this->lng->txt("date") ));

		$tbl->enabled["sort"] = false;
		$tbl->setColumnWidth(array("1%", "49%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???


		$this->tpl->setVariable("COLUMN_COUNTS", 4);

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

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
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
			include_once("assessment/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, $_GET["mode"]);
			$test_exp->buildExportFile();
		}
		else
		{
			sendInfo("cannot_export_test");
		}
		$this->exportObject();
	}
	
	
	/**
	* download export file
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("select_max_one_item"),$this->ilias->error_obj->MESSAGE);
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
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
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
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		session_unregister("ilExportFiles");
		ilUtil::redirect("test.php?cmd=export&ref_id=".$_GET["ref_id"]);
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
		ilUtil::redirect("test.php?cmd=export&ref_id=".$_GET["ref_id"]);
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
		include_once("./assessment/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$questionpools =& $tst->getAvailableQuestionpools(true);
		if (count($questionpools) == 0)
		{
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option_qpl");
				$this->tpl->setVariable("OPTION_VALUE", $key);
				$this->tpl->setVariable("TXT_OPTION", $value);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
		$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=".$this->type);
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
		if ($_POST["qpl"] < 1)
		{
			sendInfo($this->lng->txt("tst_select_questionpools"));
			$this->importObject();
			return;
		}
		
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			sendInfo($this->lng->txt("tst_select_questionpools"));
			$this->importObject();
			return;
		}
		
		include_once("./assessment/classes/class.ilObjTest.php");
		$newObj = new ilObjTest();
		$newObj->setType($_GET["new_type"]);
		$newObj->setTitle("dummy");
		$newObj->setDescription("dummy");
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create import directory
		$newObj->createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = $newObj->getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);
		//move_uploaded_file($_FILES["xmldoc"]["tmp_name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		// determine filename of xml file
		$subdir = basename($file["basename"],".".$file["extension"]);
		$xml_file = $newObj->getImportDirectory()."/".$subdir."/".$subdir.".xml";
		$qti_file = $newObj->getImportDirectory()."/".$subdir."/".
			str_replace("test", "qti", $subdir).".xml";
		
		// import qti data
		$qtiresult = $newObj->importObject($qti_file, $_POST["qpl"]);
//		$tmp_title = $newObj->getTitle();
//		$tmp_descr = $newObj->getDescription();
		// import page data
		include_once ("content/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $xml_file, $subdir);
		$contParser->setQuestionMapping($newObj->getImportMapping());
		$contParser->startParsing();

		/* update title and description in object data */
/*
		if (is_object($newObj->meta_data))
		{
			// read the object metadata from the nested set tables
			//$meta_data =& new ilMetaData($newObj->getType(), $newObj->getId());
			//$newObj->meta_data = $meta_data;
			//$newObj->setTitle($newObj->meta_data->getTitle());
			//$newObj->setDescription($newObj->meta_data->getDescription());
			ilObject::_writeTitle($newObj->getID(), $newObj->getTitle());
			ilObject::_writeDescription($newObj->getID(), $newObj->getDescription());
		}
*/

		$newObj->saveToDb();
		if ($redirect)
		{
			ilUtil::redirect("adm_object.php?".$this->link_params);
		}
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
		$deleteuserdata = false;
		$randomtest_switch = false;
		// Check the values the user entered in the form
		if (!$total)
		{
			$data["count_system"] = $_POST["count_system"];
			$data["sel_test_types"] = ilUtil::stripSlashes($_POST["sel_test_types"]);
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
			$data["sel_test_types"] = $this->object->getTestType();
			$data["random_test"] = $this->object->random_test;
			$data["count_system"] = $this->object->getCountSystem();
		}
		if ($data["sel_test_types"] != $this->object->getTestType())
		{
			$deleteuserdata = true;
		}
		if ($data["random_test"] != $this->object->random_test)
		{
			$randomtest_switch = true;
		}
		$data["title"] = ilUtil::stripSlashes($_POST["title"]);
		$data["description"] = ilUtil::stripSlashes($_POST["description"]);
		$data["author"] = ilUtil::stripSlashes($_POST["author"]);
		$data["introduction"] = ilUtil::stripSlashes($_POST["introduction"]);
		$data["sequence_settings"] = ilUtil::stripSlashes($_POST["sequence_settings"]);
		if ($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			$data["score_reporting"] = REPORT_AFTER_TEST;
		}
		else
		{
			$data["score_reporting"] = ilUtil::stripSlashes($_POST["score_reporting"]);
		}
		$data["nr_of_tries"] = ilUtil::stripSlashes($_POST["nr_of_tries"]);
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

		if (!$_POST["chb_reporting_date"] && !$this->object->isOnlineTest())
		{
			$data["reporting_date"] = "";
		}
		else
		{
			$data["reporting_date"] = sprintf("%04d%02d%02d%02d%02d%02d",
				$_POST["reporting_date"]["y"],
				$_POST["reporting_date"]["m"],
				$_POST["reporting_date"]["d"],
				$_POST["reporting_time"]["h"],
				$_POST["reporting_time"]["m"],
				0
			);
		}
		$this->object->setTestType($data["sel_test_types"]);
		$this->object->setTitle($data["title"]);
		$this->object->setDescription($data["description"]);
		$this->object->setAuthor($data["author"]);
		$this->object->setIntroduction($data["introduction"]);
		$this->object->setSequenceSettings($data["sequence_settings"]);
		$this->object->setCountSystem($data["count_system"]);
		if ($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->getTestType() == TYPE_ONLINE_TEST )
		{
			$this->object->setScoreReporting(REPORT_AFTER_TEST);
		}
		else
		{
			$this->object->setScoreReporting($data["score_reporting"]);
		}
		
		$this->object->setReportingDate($data["reporting_date"]);
		$this->object->setNrOfTries($data["nr_of_tries"]);
		$this->object->setStartingTime($data["starting_time"]);
		$this->object->setEndingTime($data["ending_time"]);
		$this->object->setProcessingTime($data["processing_time"]);
		$this->object->setRandomTest($data["random_test"]);
		$this->object->setEnableProcessingTime($data["enable_processing_time"]);
		
		if ($this->object->getTestType() == TYPE_ONLINE_TEST) 
		{
			$this->object->setScoreReporting(1);
    		$this->object->setSequenceSettings(0);
    		$this->object->setNrOfTries(1);
    		$this->object->setRandomTest(0);
		}

//		$this->object->updateTitleAndDescription();
		$this->update = $this->object->update();
		$this->object->saveToDb(true);

		if ($deleteuserdata)
		{
			$this->object->removeAllTestEditings();
		}
		sendInfo($this->lng->txt("msg_obj_modified"));
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
	* Cancels the properties form
	*
	* Cancels the properties form and goes back to the parent object
	*
	* @access	public
	*/
	function cancelPropertiesObject()
	{
		sendInfo($this->lng->txt("msg_cancel"), true);
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
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
		global $rbacsystem;
		$total = $this->object->evalTotalPersons();
		if ($this->object->getTestType() == TYPE_ONLINE_TEST  || $data["sel_test_types"] == TYPE_ONLINE_TEST)
		{
    		// fixed settings
    		$this->object->setScoreReporting(1);
    		$this->object->setSequenceSettings(0);
    		$this->object->setNrOfTries(1);
    		$this->object->setRandomTest(0);
    	}
		
		if (($data["sel_test_types"] == TYPE_ONLINE_TEST) || ($data["sel_test_types"] == TYPE_ASSESSMENT) || (($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->getTestType() == TYPE_ONLINE_TEST) && strlen($data["sel_test_types"]) == 0)) 
		{
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
			$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", ilUtil::getJSPath("calendar.js"));
			$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", ilUtil::getJSPath("calendar-setup.js"));
			$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", ilUtil::getJSPath("calendar.css"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("javascript_call_calendar");
			$this->tpl->setVariable("INPUT_FIELDS_STARTING_DATE", "starting_date");
			$this->tpl->setVariable("INPUT_FIELDS_ENDING_DATE", "ending_date");
			$this->tpl->setVariable("INPUT_FIELDS_REPORTING_DATE", "reporting_date");
			$this->tpl->parseCurrentBlock();
		}
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		$data["sel_test_types"] = $this->object->getTestType();
		$data["author"] = $this->object->getAuthor();
		$data["introduction"] = $this->object->getIntroduction();
		$data["sequence_settings"] = $this->object->getSequenceSettings();
		$data["score_reporting"] = $this->object->getScoreReporting();
		$data["reporting_date"] = $this->object->getReportingDate();
		$data["nr_of_tries"] = $this->object->getNrOfTries();

		$data["enable_processing_time"] = $this->object->getEnableProcessingTime();
		$data["processing_time"] = $this->object->getProcessingTime();
		$data["random_test"] = $this->object->isRandomTest();
		$data["count_system"] = $this->object->getCountSystem();
		if ((int)substr($data["processing_time"], 0, 2) + (int)substr($data["processing_time"], 3, 2) + (int)substr($data["processing_time"], 6, 2) == 0)
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
		$data["title"] = $this->object->getTitle();
		$data["description"] = $this->object->getDescription();
		
		if ($data["sel_test_types"] == TYPE_ASSESSMENT || ($data["sel_test_types"] == TYPE_ONLINE_TEST))
		{
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

			$this->tpl->setCurrentBlock("reporting_date");
			$this->tpl->setVariable("TEXT_SCORE_DATE", $this->lng->txt("tst_score_reporting_date"));
			if (!$data["reporting_date"])
			{
				$date_input = ilUtil::makeDateSelect("reporting_date");
				$time_input = ilUtil::makeTimeSelect("reporting_time");
			} else {
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["reporting_date"], $matches);
				$date_input = ilUtil::makeDateSelect("reporting_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
				$time_input = ilUtil::makeTimeSelect("reporting_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
			}
			$this->tpl->setVariable("IMG_REPORTING_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
			$this->tpl->setVariable("TXT_REPORTING_DATE_CALENDAR", $this->lng->txt("open_calendar"));
			$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
			if ($data["reporting_date"] || ($data["sel_test_types"] == TYPE_ONLINE_TEST)) {
				$this->tpl->setVariable("CHECKED_REPORTING_DATE", " checked=\"checked\"");
			}
			$this->tpl->setVariable("INPUT_REPORTING_DATE", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_properties.html", true);
		$this->tpl->setCurrentBlock("test_types");
		foreach ($this->object->test_types as $key => $value) {
			$this->tpl->setVariable("VALUE_TEST_TYPE", $key);
			$this->tpl->setVariable("TEXT_TEST_TYPE", $this->lng->txt($value));
			if ($data["sel_test_types"] == $key) {
				$this->tpl->setVariable("SELECTED_TEST_TYPE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_PROPERTIES", $this->ctrl->getFormAction($this));
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SUBMIT_TYPE", $this->lng->txt("change"));
		}
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("tst_general_properties"));
		$this->tpl->setVariable("TEXT_TEST_TYPES", $this->lng->txt("tst_types"));
		$this->tpl->setVariable("TEST_TYPE_COMMENT", $this->lng->txt("tst_type_comment"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TITLE", ilUtil::prepareFormOutput($data["title"]));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("VALUE_AUTHOR", ilUtil::prepareFormOutput($data["author"]));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("VALUE_DESCRIPTION", ilUtil::prepareFormOutput($data["description"]));
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("tst_introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", $data["introduction"]);
		$this->tpl->setVariable("HEADING_SEQUENCE", $this->lng->txt("tst_sequence_properties"));
		$this->tpl->setVariable("TEXT_SEQUENCE", $this->lng->txt("tst_sequence"));
		$this->tpl->setVariable("SEQUENCE_FIXED", $this->lng->txt("tst_sequence_fixed"));
		$this->tpl->setVariable("SEQUENCE_POSTPONE", $this->lng->txt("tst_sequence_postpone"));
		if ($data["sequence_settings"] == 0) {
			$this->tpl->setVariable("SELECTED_FIXED", " selected=\"selected\"");
		} elseif ($data["sequence_settings"] == 1) {
			$this->tpl->setVariable("SELECTED_POSTPONE", " selected=\"selected\"");
		}
		$this->tpl->setVariable("HEADING_SCORE", $this->lng->txt("tst_score_reporting"));
		$this->tpl->setVariable("TEXT_SCORE_TYPE", $this->lng->txt("tst_score_type"));
		$this->tpl->setVariable("REPORT_AFTER_QUESTION", $this->lng->txt("tst_report_after_question"));
		$this->tpl->setVariable("REPORT_AFTER_TEST", $this->lng->txt("tst_report_after_test"));
		if ($data["sel_test_types"] == TYPE_ASSESSMENT || ($data["sel_test_types"] == TYPE_ONLINE_TEST || $this->object->getTestType() == TYPE_ONLINE_TEST)) {
			$this->tpl->setVariable("SELECTED_TEST", " selected=\"selected\"");
			$this->tpl->setVariable("DISABLE_SCORE_REPORTING", " disabled=\"disabled\"");
			if ($this->object->getTestType() == TYPE_ONLINE_TEST || $data["sel_test_types"] == TYPE_ONLINE_TEST) {
				$this->tpl->setVariable("DISABLE_SCORE_REPORTING_DATE_CHECKBOX", " disabled=\"disabled\"");
				$this->tpl->setVariable("DISABLE_SEQUENCE", " disabled=\"disabled\"");
				$this->tpl->setVariable("DISABLE_NR_OF_TRIES", " disabled=\"disabled\"");
				$this->tpl->setVariable("ENABLED_RANDOM_TEST", " disabled=\"disabled\"");
			}
		} else {
			if ($data["score_reporting"] == 0) {
				$this->tpl->setVariable("SELECTED_QUESTION", " selected=\"selected\"");
			} elseif ($data["score_reporting"] == 1) {
				$this->tpl->setVariable("SELECTED_TEST", " selected=\"selected\"");
			}
		}
		$this->tpl->setVariable("HEADING_SESSION", $this->lng->txt("tst_session_settings"));
		$this->tpl->setVariable("TEXT_NR_OF_TRIES", $this->lng->txt("tst_nr_of_tries"));
		$this->tpl->setVariable("VALUE_NR_OF_TRIES", $data["nr_of_tries"]);
		$this->tpl->setVariable("COMMENT_NR_OF_TRIES", $this->lng->txt("0_unlimited"));
		$this->tpl->setVariable("TEXT_PROCESSING_TIME", $this->lng->txt("tst_processing_time"));
		$time_input = ilUtil::makeTimeSelect("processing_time", false, substr($data["processing_time"], 0, 2), substr($data["processing_time"], 3, 2), substr($data["processing_time"], 6, 2));
		$this->tpl->setVariable("MAX_PROCESSING_TIME", $time_input . " (hh:mm:ss)");
		if ($data["enable_processing_time"]) {
			$this->tpl->setVariable("CHECKED_PROCESSING_TIME", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_RANDOM_TEST", $this->lng->txt("tst_random_test"));
		$this->tpl->setVariable("TEXT_RANDOM_TEST_DESCRIPTION", $this->lng->txt("tst_random_test_description"));
		if ($data["random_test"]) {
			$this->tpl->setVariable("CHECKED_RANDOM_TEST", " checked=\"checked\"");
		}

		$this->tpl->setVariable("HEADING_SCORING", $this->lng->txt("tst_heading_scoring"));
		$this->tpl->setVariable("TEXT_COUNT_SYSTEM", $this->lng->txt("tst_text_count_system"));
		$this->tpl->setVariable("COUNT_PARTIAL_SOLUTIONS", $this->lng->txt("tst_count_partial_solutions"));
		if ($data["count_system"] == COUNT_PARTIAL_SOLUTIONS)
		{
			$this->tpl->setVariable("SELECTED_PARTIAL", " selected=\"selected\"");
		}
		$this->tpl->setVariable("COUNT_CORRECT_SOLUTIONS", $this->lng->txt("tst_count_correct_solutions"));
		if ($data["count_system"] == COUNT_CORRECT_SOLUTIONS)
		{
			$this->tpl->setVariable("SELECTED_CORRECT", " selected=\"selected\"");
		}

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		if ($total > 0)
		{
			$this->tpl->setVariable("DISABLE_COUNT_SYSTEM", " disabled=\"disabled\"");
			$this->tpl->setVariable("ENABLED_TEST_TYPES", " disabled=\"disabled\"");
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
		include_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$page =& new ilPageObject("qpl", $_GET["pg_id"]);
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->showMediaFullscreen();
		
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

	
	function filterObject()
	{
		$filter_type = $_GET["sel_filter_type"];
		if (!$filter_type)
		{
			$filter_type = $_POST["sel_filter_type"];
		}
		$filter_question_type = $_GET["sel_question_type"];
		if (!$filter_question_type)
		{
			$filter_question_type = $_POST["sel_question_type"];
		}
		$filter_questionpool = $_GET["sel_questionpool"];
		if (!$filter_questionpool)
		{
			$filter_questionpool = $_POST["sel_questionpool"];
		}
		$filter_text = $_GET["filter_text"];
		if (!$filter_text)
		{
			$filter_text = $_POST["filter_text"];
		}

		$this->questionBrowser($filter_type, $filter_question_type, $filter_questionpool);
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
		$_GET["sel_filter_type"] = "";
		$_GET["sel_question_type"] = "";
		$_GET["sel_questionpool"] = "";
		$_GET["filter_text"] = "";
		$this->questionBrowser();
	}

	/**
	* Called when the insert of questions to the test was confirmed 
	*
	* Called when the insert of questions to the test was confirmed 
	*
	* @access	public
	*/
	function confirmInsertQuestionsObject()
	{
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				$this->object->insertQuestion($matches[1]);
			}
		}
		$this->object->saveCompleteStatus();
		sendInfo($this->lng->txt("tst_questions_inserted"), true);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Called when the insert of questions to the test was cancelled 
	*
	* Called when the insert of questions to the test was cancelled 
	*
	* @access	public
	*/
	function cancelInsertQuestionsObject()
	{
		$this->ctrl->redirect($this, "questions");
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
	* Confirmation for for inserting questions into the test 
	*
	* Confirmation for for inserting questions into the test 
	*
	* @access	public
	*/
	function confirmInsertQuestionsForm($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_insert_questions.html", true);
		$where = "";
		foreach ($checked_questions as $id)
		{
			$where .= sprintf(" OR qpl_questions.question_id = %s", $this->ilias->db->quote($id));
		}
		$where = preg_replace("/^ OR /", "", $where);
		$where = "($where)";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND $where";
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if (in_array($data->question_id, $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
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
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
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
			sendInfo($this->lng->txt("tst_insert_missing_question"));
		}
		else
		{
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->confirmInsertQuestionsForm($selected_array);
		}
	}

	/**
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* @access	public
	*/
	function questionBrowser($filter_type = "", $filter_question_type = "", $filter_questionpool = "", $filter_text = "")
	{
		global $rbacsystem;

		$this->ctrl->setParameterByClass(get_class($this), "browse", "1");

		if (!$filter_type)
		{
			$filter_type = $_GET["sel_filter_type"];
		}
		$this->ctrl->setParameterByClass(get_class($this), "sel_filter_type", $filter_type);
		if (!$filter_question_type)
		{
			$filter_question_type = $_GET["sel_question_type"];
		}
		$this->ctrl->setParameterByClass(get_class($this), "sel_question_type", $filter_question_type);
		if (!$filter_questionpool)
		{
			$filter_questionpool = $_GET["sel_questionpool"];
		}
		$this->ctrl->setParameterByClass(get_class($this), "sel_questionpool", $filter_questionpool);
		if (!$filter_text)
		{
			$filter_text = $_GET["filter_text"];
		}
		$this->ctrl->setParameterByClass(get_class($this), "filter_text", $filter_text);
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questionbrowser.html", true);
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", true);
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_tst_filter_questions.html", true);

		$questionpools =& $this->object->get_qpl_titles();

		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) {
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (strcmp($_POST["cmd"]["resetFilter"], "") == 0) {
				if (strcmp($filter_type, $key) == 0) {
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

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

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("SHOW_QUESTION_TYPES", $this->lng->txt("filter_show_question_types"));
		$this->tpl->setVariable("TEXT_ALL_QUESTION_TYPES", $this->lng->txt("filter_all_question_types"));
		$this->tpl->setVariable("SHOW_QUESTIONPOOLS", $this->lng->txt("filter_show_questionpools"));
		$this->tpl->setVariable("TEXT_ALL_QUESTIONPOOLS", $this->lng->txt("filter_all_questionpools"));
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		if (strcmp($_POST["cmd"]["resetFilter"], "") == 0) 
		{
			$this->tpl->setVariable("VALUE_FILTER_TEXT", $filter_text);
		}
		$this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->parseCurrentBlock();

		// create edit buttons & table footer
		$this->tpl->setCurrentBlock("selection");
		$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("Footer");
		$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("QTab");

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
		$table = $this->object->getQuestionsTable($_GET["sort"], $filter_text, $filter_type, $startrow, 1, $filter_question_type, $filter_questionpool);
		// display all questions in accessable question pools
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$existing_questions =& $this->object->getExistingQuestions();
		foreach ($table["rows"] as $data)
		{
			if (($rbacsystem->checkAccess("write", $data["ref_id"])) and (!in_array($data["question_id"], $existing_questions)))
			{
				if ($data["complete"])
				{
					// make only complete questions selectable
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
				}
				$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
				$this->tpl->setVariable("PREVIEW", "[<a href=\"" . $this->getCallingScript() . "$add_parameter&preview=" . $data["question_id"] . "\">" . $this->lng->txt("preview") . "</a>]");
				$this->tpl->setVariable("QUESTION_COMMENT", $data["comment"]);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
				$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
				$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP"]), "date"));
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
		if ($counter == 0) {
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}

		// define the sort column parameters
		$sort = array(
			"title" => $_GET["sort"]["title"],
			"comment" => $_GET["sort"]["comment"],
			"type" => $_GET["sort"]["type"],
			"author" => $_GET["sort"]["author"],
			"created" => $_GET["sort"]["created"],
			"updated" => $_GET["sort"]["updated"],
			"qpl" => $_GET["sort"]["qpl"]
		);
		foreach ($sort as $key => $value) {
			if (strcmp($value, "ASC") == 0) {
				$sort[$key] = "DESC";
			} else {
				$sort[$key] = "ASC";
			}
		}

		$this->tpl->setCurrentBlock("adm_content");
		// create table header
		$this->ctrl->setCmd("questionBrowser");
		$this->ctrl->setParameterByClass(get_class($this), "startrow", $table["startrow"]);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
		$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[comment]=" . $sort["comment"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["comment"]);
		$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
		$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
		$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
		$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
		$this->tpl->setVariable("QUESTION_POOL", "<a href=\"" . $this->ctrl->getFormAction($this) . "&sort[qpl]=" . $sort["qpl"] . "\">" . $this->lng->txt("obj_qpl") . "</a>" . $table["images"]["qpl"]);
		$this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
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
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
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
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_select.html", true);
		$questionpools =& $this->object->getAvailableQuestionpools();
		$this->tpl->setCurrentBlock("option");
		$this->tpl->setVariable("VALUE_OPTION", "0");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("all_available_question_pools"));
		$this->tpl->parseCurrentBlock();
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
		$question_array = $this->object->randomSelectQuestions($_POST["nr_of_questions"], $_POST["sel_qpl"]);
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_question_offer.html", true);
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->get_qpl_titles();
		foreach ($question_array as $question_id)
		{
			$dataset = $this->object->getQuestionDataset($question_id);
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $dataset->title);
			$this->tpl->setVariable("QUESTION_COMMENT", $dataset->comment);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($dataset->type_tag));
			$this->tpl->setVariable("QUESTION_AUTHOR", $dataset->author);
			$this->tpl->setVariable("QUESTION_POOL", $questionpools[$dataset->obj_fi]);
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
			sendInfo($this->lng->txt("tst_insert_missing_question"));
		}
		else
		{
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->confirmInsertQuestionsForm($selected_array);
			return;
		}
	}

	function randomQuestionsObject()
	{
		$total = $this->object->evalTotalPersons();
		$add_parameter = $this->getAddParameter();
		$available_qpl =& $this->object->getAvailableQuestionpools(true);
		foreach ($available_qpl as $key => $value)
		{
			$count = ilObjQuestionPool::_getQuestionCount($key);
			if ($count == 1)
			{
				$available_qpl[$key] = $value . " ($count " . $this->lng->txt("ass_question") . ")";
			}
			else
			{
				$available_qpl[$key] = $value . " ($count " . $this->lng->txt("ass_questions") . ")";
			}
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_questions.html", true);
		$found_qpls = array();
		if (count($_POST) == 0)
		{
			$found_qpls = $this->object->getRandomQuestionpools();
		}
		if (count($found_qpls) == 0)
		{
			if (!array_key_exists("countqpl_0", $_POST))
			{
				// create first questionpool row automatically
				foreach ($available_qpl as $key => $value)
				{
					$this->tpl->setCurrentBlock("qpl_value");
					$this->tpl->setVariable("QPL_ID", $key);
					$this->tpl->setVariable("QPL_TEXT", $value);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("questionpool_row");
				$this->tpl->setVariable("COUNTQPL", "0");
				$this->tpl->setVariable("VALUE_COUNTQPL", $_POST["countqpl_0"]);
				$this->tpl->setVariable("TEXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
				$this->tpl->setVariable("TEXT_QUESTIONS_FROM", $this->lng->txt("questions_from"));
				$this->tpl->parseCurrentBlock();
			}
		}
		$qpl_unselected = 0;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/countqpl_(\d+)/", $key, $matches))
			{
				$found_qpls[$matches[1]] = array(
					"index" => $matches[1],
					"count" => sprintf("%d", $value),
					"qpl"   => $_POST["qpl_" . $matches[1]]
				);
				if ($_POST["qpl_" . $matches[1]] == -1)
				{
					$qpl_unselected = 1;
				}
			}
		}
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/deleteqpl_(\d+)/", $key, $matches))
			{
				unset($found_qpls[$matches[1]]);
			}
		}
		sort($found_qpls);
		$found_qpls = array_values($found_qpls);
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
				$this->tpl->setVariable("QPL_TEXT", $pvalue);
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
			if (!$total)
			{
				if ($counter > 0)
				{
					$this->tpl->setVariable("BTNCOUNTQPL", $counter);
					$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
				}
			}
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if ($_POST["cmd"]["addQuestionpool"])
		{
			if ($qpl_unselected)
			{
				sendInfo($this->lng->txt("tst_random_qpl_unselected"));
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
					sendInfo($this->lng->txt("tst_no_more_available_questionpools"));
				}
				else
				{
					foreach ($pools as $key => $value)
					{
						$this->tpl->setCurrentBlock("qpl_value");
						$this->tpl->setVariable("QPL_ID", $key);
						$this->tpl->setVariable("QPL_TEXT", $value);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("questionpool_row");
					$this->tpl->setVariable("COUNTQPL", "$counter");
					$this->tpl->setVariable("TEXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
					$this->tpl->setVariable("TEXT_QUESTIONS_FROM", $this->lng->txt("questions_from"));
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		if ($_POST["cmd"]["save"])
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
		$this->tpl->setVariable("VALUE_TOTAL_QUESTIONS", $total_questions);
		$this->tpl->setVariable("TEXT_QUESTIONPOOLS", $this->lng->txt("tst_random_questionpools"));
		if (!$total)
		{
			$this->tpl->setVariable("BTN_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("BTN_ADD_QUESTIONPOOL", $this->lng->txt("add_questionpool"));
		}
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->parseCurrentBlock();
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
			sendInfo($this->lng->txt("questionpool_not_entered"));
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
			ilUtil::redirect("questionpool.php?ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=".$_GET["ref_id"]."&sel_question_types=" . $_POST["sel_question_types"]);
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
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", true);
		$questionpools =& $this->object->getAvailableQuestionpools();
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
				$this->tpl->setVariable("TEXT_OPTION", $value);
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
		sendInfo($this->lng->txt("tst_questions_removed"));
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
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_remove_questions.html", true);
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$this->ilias->db->quote($this->object->getTestId())
		);
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if (in_array($data->question_id, $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
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
		$checked_questions = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/cb_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}
		if (count($checked_questions) > 0) {
			$total = $this->object->evalTotalPersons();
			if ($total) {
				// the test was executed previously
				sendInfo(sprintf($this->lng->txt("tst_remove_questions_and_results"), $total));
			} else {
				sendInfo($this->lng->txt("tst_remove_questions"));
			}
			$this->removeQuestionsForm($checked_questions);
			return;
		} elseif (count($checked_questions) == 0) {
			sendInfo($this->lng->txt("tst_no_question_selected_for_removal"), true);
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
			sendInfo($this->lng->txt("no_target_selected_for_move"), true);
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
			sendInfo($this->lng->txt("no_target_selected_for_move"), true);
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
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}

		if ($this->object->isRandomTest())
		{
			$this->randomQuestionsObject();
			return;
		}
		
		$add_parameter = $this->getAddParameter();

		if ($_GET["eqid"] and $_GET["eqpl"])
		{
			ilUtil::redirect("questionpool.php?ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForTest&calling_test=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
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
				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", true);
		$this->tpl->addBlockFile("A_BUTTONS", "question_buttons", "tpl.il_as_tst_question_buttons.html", true);

		if (strcmp($this->ctrl->getCmd(), "moveQuestions") == 0)
		{
			$checked_move = 0;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_(\d+)/", $key, $matches))
				{
					$checked_move++;
					$this->tpl->setCurrentBlock("move");
					$this->tpl->setVariable("MOVE_COUNTER", $matches[1]);
					$this->tpl->setVariable("MOVE_VALUE", $matches[1]);
					$this->tpl->parseCurrentBlock();
				}
			}
			if ($checked_move)
			{
				sendInfo($this->lng->txt("select_target_position_for_move_question"));
				$this->tpl->setCurrentBlock("move_buttons");
				$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
				$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				sendInfo($this->lng->txt("no_question_selected_for_move"));
			}
		}
		
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$this->ilias->db->quote($this->object->getTestId())
		);
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->get_qpl_titles();
		$total = $this->object->evalTotalPersons();
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setCurrentBlock("QTab");
				$this->tpl->setVariable("QUESTION_ID", $data->question_id);
				if (($rbacsystem->checkAccess("write", $this->ref_id) and ($total == 0))) {
					$q_id = $data->question_id;
					$qpl_ref_id = $this->object->_getRefIdFromObjId($data->obj_fi);
					$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->getCallingScript() . $add_parameter . "&eqid=$q_id&eqpl=$qpl_ref_id" . "\">" . $data->title . "</a>");
				} else {
					$this->tpl->setVariable("QUESTION_TITLE", $data->title);
				}
				$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("tst_sequence"));

				if (($rbacsystem->checkAccess("write", $this->ref_id) and ($total == 0))) {
					if ($data->question_id != $this->object->questions[1])
					{
						$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getFormAction($this) . "&up=$data->question_id\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"" . $this->lng->txt("up") . "\" border=\"0\" /></a>");
					}
					if ($data->question_id != $this->object->questions[count($this->object->questions)])
					{
						$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getFormAction($this) . "&down=$data->question_id\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"" . $this->lng->txt("down") . "\" border=\"0\" /></a>");
					}
				}
				$this->tpl->setVariable("QUESTION_COMMENT", $data->comment);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data->author);
				$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data->obj_fi]);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		if ($counter == 0) {
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("tst_no_questions_available"));
			$this->tpl->parseCurrentBlock();
		} else {
			if (($rbacsystem->checkAccess("write", $this->ref_id) and ($total == 0))) {
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
				$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if (($rbacsystem->checkAccess("write", $this->ref_id) and ($total == 0))) {
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM qpl_question_type";
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
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));

		if (($rbacsystem->checkAccess("write", $this->ref_id) and ($total == 0))) {
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("tst_browse_for_questions"));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
			$this->tpl->setVariable("TXT_OR", $this->lng->txt("or"));
			$this->tpl->setVariable("TEXT_RANDOM_SELECT", $this->lng->txt("random_selection"));
		}

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
		$this->object->mark_schema->add_mark_step();
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
			if (preg_match("/mark_short_(\d+)/", $key, $matches)) {
				$this->object->mark_schema->add_mark_step($_POST["mark_short_$matches[1]"], $_POST["mark_official_$matches[1]"], $_POST["mark_percentage_$matches[1]"], $_POST["passed_$matches[1]"]);
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
		$this->object->mark_schema->create_simple_schema($this->lng->txt("failed_short"), $this->lng->txt("failed_official"), 0, 0, $this->lng->txt("passed_short"), $this->lng->txt("passed_official"), 50, 1);
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
			$this->object->mark_schema->delete_mark_steps($delete_mark_steps);
		} else {
			sendInfo($this->lng->txt("tst_delete_missing_mark"));
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
		sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "properties");
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
			sendInfo($this->lng->txt($mark_check));
		}
		elseif ($_POST["chbECTS"] && ((strcmp($_POST["ects_grade_a"], "") == 0) or (strcmp($_POST["ects_grade_b"], "") == 0) or (strcmp($_POST["ects_grade_c"], "") == 0) or (strcmp($_POST["ects_grade_d"], "") == 0) or (strcmp($_POST["ects_grade_e"], "") == 0)))
		{
			sendInfo($this->lng->txt("ects_fill_out_all_values"), true);
		}
		elseif (($_POST["ects_grade_a"] > 100) or ($_POST["ects_grade_a"] < 0))
		{
			sendInfo($this->lng->txt("ects_range_error_a"), true);
		}
		elseif (($_POST["ects_grade_b"] > 100) or ($_POST["ects_grade_b"] < 0))
		{
			sendInfo($this->lng->txt("ects_range_error_b"), true);
		}
		elseif (($_POST["ects_grade_c"] > 100) or ($_POST["ects_grade_c"] < 0))
		{
			sendInfo($this->lng->txt("ects_range_error_c"), true);
		}
		elseif (($_POST["ects_grade_d"] > 100) or ($_POST["ects_grade_d"] < 0))
		{
			sendInfo($this->lng->txt("ects_range_error_d"), true);
		}
		elseif (($_POST["ects_grade_e"] > 100) or ($_POST["ects_grade_e"] < 0))
		{
			sendInfo($this->lng->txt("ects_range_error_e"), true);
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
			sendInfo($this->lng->txt("msg_obj_modified"), true);
		}
		$this->marksObject();
	}
	
	function marksObject() {
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}

		$this->object->mark_schema->sort();
	
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_marks.html", true);
		$marks = $this->object->mark_schema->mark_steps;
		$rows = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($marks as $key => $value) {
			$this->tpl->setCurrentBlock("markrow");
			$this->tpl->setVariable("MARK_SHORT", $value->get_short_name());
			$this->tpl->setVariable("MARK_OFFICIAL", $value->get_official_name());
			$this->tpl->setVariable("MARK_PERCENTAGE", sprintf("%.2f", $value->get_minimum_level()));
			$this->tpl->setVariable("MARK_PASSED", strtolower($this->lng->txt("tst_mark_passed")));
			$this->tpl->setVariable("MARK_ID", "$key");
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			if ($value->get_passed()) {
				$this->tpl->setVariable("MARK_PASSED_CHECKED", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($marks) == 0) {
			$this->tpl->setCurrentBlock("Emptyrow");
			$this->tpl->setVariable("EMPTY_ROW", $this->lng->txt("tst_no_marks_defined"));
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		} else {
			if ($rbacsystem->checkAccess("write", $this->ref_id)) {
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
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
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("BUTTON_NEW", $this->lng->txt("tst_mark_create_new_mark_step"));
			$this->tpl->setVariable("BUTTON_NEW_SIMPLE", $this->lng->txt("tst_mark_create_simple_mark_schema"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	function runObject()
	{
		global $ilUser;
		global $rbacsystem;
/*
		global $ilDB;
		
		// update code
		$idx = 1;
		$query = "SELECT question_id, question_type_fi FROM qpl_questions";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$queryanswers = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
				$ilDB->quote($row["question_id"] . "")
			);
			$resultanswers = $ilDB->query($queryanswers);
			$answers = array();
			while ($rowanswer = $resultanswers->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($answers, $rowanswer);
			}
			$querytests = sprintf("SELECT DISTINCT test_fi FROM tst_solutions WHERE question_fi = %s",
				$ilDB->quote($row["question_id"] . "")
			);
			$resulttests = $ilDB->query($querytests);
			$tests = array();
			while ($rowtest = $resulttests->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($tests, $rowtest["test_fi"]);
			}
			foreach ($tests as $test_id)
			{
				$queryusers = sprintf("SELECT DISTINCT user_fi FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
					$ilDB->quote($test_id . ""),
					$ilDB->quote($row["question_id"])
				);
				$resultusers = $ilDB->query($queryusers);
				$users = array();
				while ($rowuser = $resultusers->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($users, $rowuser["user_fi"]);
				}
				// now begin the conversion
				foreach ($users as $user_id)
				{
					$querysolutions = sprintf("SELECT * FROM tst_solutions WHERE test_fi = %s AND user_fi = %s AND question_fi = %s",
						$ilDB->quote($test_id . ""),
						$ilDB->quote($user_id . ""),
						$ilDB->quote($row["question_id"] . "")
					);
					$resultsolutions = $ilDB->query($querysolutions);
					switch ($row["question_type_fi"])
					{
						case 1:
						case 2:
							// multiple choice questions
							$found_values = array();
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								if (strcmp($data["value1"], "") != 0)
								{
									array_push($found_values, $data["value1"]);
								}
							}
							$points = 0;
							if (count($found_values) > 0)
							{
								foreach ($answers as $key => $answer)
								{
									if ($answer["correctness"])
									{
										if (in_array($key, $found_values))
										{
											$points += $answer["points"];
										}
									}
									else
									{
										if (!in_array($key, $found_values))
										{
											$points += $answer["points"];
										}
									}
								}
							}
							// save $points
							break;
						case 3:
							// close questions
							$found_value1 = array();
							$found_value2 = array();
							$user_result = array();
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								if (strcmp($data["value2"], "") != 0)
								{
									$user_result[$data["value1"]] = array(
										"gap_id" => $data["value1"],
										"value" => $data["value2"]
									);
								}
							}
							$points = 0;
							$counter = 0;
							$gaps = array();
							foreach ($answers as $key => $value)
							{
								if (!array_key_exists($value["gap_id"], $gaps))
								{
									$gaps[$value["gap_id"]] = array();
								}
								array_push($gaps[$value["gap_id"]], $value);
							}
							foreach ($user_result as $gap_id => $value) 
							{
								if ($gaps[$gap_id][0]["cloze_type"] == 0) 
								{
									$foundsolution = 0;
									foreach ($gaps[$gap_id] as $k => $v) 
									{
										if ((strcmp(strtolower($v["answertext"]), strtolower($value["value"])) == 0) && (!$foundsolution)) 
										{
											$points += $v["points"];
											$foundsolution = 1;
										}
									}
								} 
								else 
								{
									if ($value["value"] >= 0)
									{
										foreach ($gaps[$gap_id] as $answerkey => $answer)
										{
											if ($value["value"] == $answerkey)
											{
												$points += $answer["points"];
											}
										}
									}
								}
							}
							// save $points;
							break;
						case 4:
							// matching questions
							$found_value1 = array();
							$found_value2 = array();
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								if (strcmp($data["value1"], "") != 0)
								{
									array_push($found_value1, $data["value1"]);
									array_push($found_value2, $data["value2"]);
								}
							}
							$points = 0;
							foreach ($found_value2 as $key => $value)
							{
								foreach ($answers as $answer_value)
								{
									if (($answer_value["matching_order"] == $value) and ($answer_value["aorder"] == $found_value1[$key]))
									{
										$points += $answer_value["points"];
									}
								}
							}
							// save $points;
							break;
						case 5:
							// ordering questions
							$found_value1 = array();
							$found_value2 = array();
							$user_order = array();
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								if ((strcmp($data["value1"], "") != 0) && (strcmp($data["value2"], "") != 0))
								{
									$user_order[$data["value2"]] = $data["value1"];
								}
							}
							ksort($user_order);
							$user_order = array_values($user_order);
							$answer_order = array();
							foreach ($answers as $key => $answer)
							{
								$answer_order[$answer["solution_order"]] = $key;
							}
							ksort($answer_order);
							$answer_order = array_values($answer_order);
							$points = 0;
							foreach ($answer_order as $index => $answer_id)
							{
								if (strcmp($user_order[$index], "") != 0)
								{
									if ($answer_id == $user_order[$index])
									{
										$points += $answers[$answer_id]["points"];
									}
								}
							}
							// save $points;
							break;
						case 6:
							// imagemap questions
							$found_values = array();
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								if (strcmp($data["value1"], "") != 0)
								{
									array_push($found_values, $data["value1"]);
								}
							}
							$points = 0;
							if (count($found_values) > 0)
							{
								foreach ($answers as $key => $answer)
								{
									if ($answer["correctness"])
									{
										if (in_array($key, $found_values))
										{
											$points += $answer["points"];
										}
									}
								}
							}
							// save $points;
							break;
						case 7:
							// java applet questions
							$found_values = array();
							$points = 0;
							while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
							{
								$points += $data["points"];
							}
							// save $points;
							break;
						case 8:
							// text questions
							$points = 0;
							if ($resultsolutions->numRows() == 1)
							{
								$data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC);
								if ($data["points"])
								{
									$points = $data["points"];
								}
							}
							// save $points;
							break;
					}
					$insertquery = sprintf("REPLACE tst_test_result (user_fi, test_fi, question_fi, points) VALUES (%s, %s, %s, %s)",
						$ilDB->quote($user_id . ""),
						$ilDB->quote($test_id . ""),
						$ilDB->quote($row["question_id"] . ""),
						$ilDB->quote($points . "")
					);
					echo $idx++ . ". " . $insertquery . "<br>";
					//$ilDB->query($insertquery);
				}
			}
		}
		exit;*/
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");		
		$title = $this->object->getTitle();
		
		$this->createCommandControlObject();
		
		$this->cmdCtrl->prepareRequestVariables();
		
		$this->cmdCtrl->onRunObjectEnter();
		
		// update working time and set saveResult state
		$this->updateWorkingTime();
					
		
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		
		if ($_POST["cmd"]["start"] or $_POST["cmd"]["resume"])
			$this->cmdCtrl->handleStartCommands ();
				

		$this->setLocator();
				
		// catch feedback message
		sendInfo();
		
		$this->sequence = $this->cmdCtrl->getSequence();	
		
		if ($this->cmdCtrl->handleCommands())
			return;

		// sequence not defined
		if (!$this->sequence)
		{
			// show introduction page
			$this->outIntroductionPage();
			return;
		}
							
		if ($this->isMaxProcessingTimeReached())
		{
			$this->maxProcessingTimeReached();
			return;
		}
		
		if ($this->isEndingTimeReached())
		{
			$this->endingTimeReached();
			return;
		}
			
		$user_question_order =& $this->object->getAllQuestionsForActiveUser();
			
		if ($this->sequence <= $this->object->getQuestionCount())
		{
			if ($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)
			{
				$this->tpl->setCurrentBlock("direct_feedback");
				$this->tpl->setVariable("TEXT_DIRECT_FEEDBACK", $this->lng->txt("direct_feedback"));
				$this->tpl->parseCurrentBlock();
			}
			
			// show next/previous question
			$postpone = "";
			if ($_POST["cmd"]["postpone"])
			{
				$postpone = $this->sequence;
			}
		
			$this->object->setActiveTestUser($this->sequence, $postpone);
		
			if ($this->sequence == $this->object->getQuestionCount())
			{
				$finish = true;
			}
			else
			{
				$finish = false;
			}

			$postpone = false;

			if ($this->object->getSequenceSettings() == TEST_POSTPONE)
			{
				$postpone = true;
			}

			$active = $this->object->getActiveTestUser();

			if(!$_GET['crs_show_result'])
			{
				$this->outShortResult($user_question_order);
			}
				
			if ($this->object->getEnableProcessingTime())
			{
				$this->outProcessingTime();
			}

			$this->outWorkingForm($this->sequence, $finish, $this->object->getTestId(), $active, $postpone, $user_question_order, $_POST["cmd"]["directfeedback"], $show_summary);

		}
		else
		{
			// finish test
			
			if ($this->object->isOnlineTest() && !$this->object->isActiveTestSubmitted($ilUser->getId())) {
				$this->outTestSummary();
				return;
			}
				
				
			$this->object->setActiveTestUser(1, "", true);
				
			if (!$this->object->canViewResults()) 
			{
				$this->outIntroductionPage($maxprocessingtimereached);
			}
			else
			{					
				$this->outTestResults();
			}
			// Update objectives
			include_once './course/classes/class.ilCourseObjectiveResult.php';

			$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
			$tmp_obj_res->updateResults($this->object->getTestResult($ilUser->getId()));
			unset($tmp_obj_res);

			if($_GET['crs_show_result'])
			{
				ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?ref_id=".(int) $_GET['crs_show_result']));
			}
				
		}
	}

	/**
	* Creates the introduction page for a test
	*
	* Creates the introduction page for a test
	*
	* @access public
	*/
	function outIntroductionPage()
	{
		global $ilUser;
		// todo: max_processing_reached
		
		$maxprocessingtimereached = $this->isMaxProcessingTimeReached();

		$add_parameter = $this->getAddParameter();
		$active = $this->object->getActiveTestUser();
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_introduction.html", true);
		$this->tpl->setCurrentBlock("info_row");
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_type") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($this->object->test_types[$this->object->getTestType()]));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("description") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->object->getDescription());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_sequence") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)? "tst_sequence_fixed":"tst_sequence_postpone"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_score_reporting") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt(($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)?"tst_report_after_question":"tst_report_after_test"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries") . ":");

		$num_of = $this->object->getNrOfTries();
		if (!$num_of) {
			$num_of = $this->lng->txt("unlimited");
		}
		$this->tpl->setVariable("TEXT_INFO_COL2", $num_of);
		$this->tpl->parseCurrentBlock();

		if ($num_of != 1)
		{
			// display number of tries of the user
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries_of_user") . ":");
			$tries = $active->tries;
			if (!$tries)
			{
				$tries = $this->lng->txt("tst_no_tries");
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $tries);
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getEnableProcessingTime())
		{
	 		$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
			$processing_time = $this->object->getProcessingTimeInSeconds();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_processing_time") . ":");
			$time_seconds = $processing_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_time_already_spent") . ":");
			$time_seconds = $working_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getStartingTime())
		{
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_starting_time") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($this->object->getStartingTime())));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->object->getEndingTime())
		{
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_ending_time") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("info");
		$this->tpl->setVariable("TEXT_USE_JAVASCRIPT", $this->lng->txt("tst_use_javascript"));
		if ($ilUser->prefs["tst_javascript"])
		{
			$this->tpl->setVariable("CHECKED_JAVASCRIPT", "checked=\"checked\" ");
		}
		$this->tpl->parseCurrentBlock();
		$seq = 1;
		if ($active) {
			$seq = $active->lastindex;
		}
		$add_sequence = "&sequence=$seq";

		if($this->cmdCtrl->showTestResults())
		{
			$first_seq = $this->object->getFirstSequence();
			$add_sequence = "&sequence=".$first_seq;

			if(!$first_seq)
			{
				sendInfo($this->lng->txt('crs_all_questions_answered_successfully'));
			}
		}
				
		// from here we have test type specific handling
		
		$test_disabled = !$this->cmdCtrl->isTestAccessible();
		
		if ($test_disabled) {
			$add_sequence = "";
		}
		
		if ($this->cmdCtrl->isTestResumable() && $this->cmdCtrl->isTestAccessible()){
			// RESUME BLOCK 
			$this->tpl->setCurrentBlock("resume");
			if ($seq == 1)
			{
				if(!$this->cmdCtrl->showTestResults() or $first_seq)
				{
					$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("tst_start_test"));
				}
			}
			else
			{
				$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("tst_resume_test"));
			}
			
			// disable resume button
			if ($test_disabled) {
				$this->tpl->setVariable("DISABLED", " disabled");
			}
			$this->tpl->parseCurrentBlock();
		} else {
		// Start a new Test
			if ($this->cmdCtrl->isTestAccessible()// ($this->object->startingTimeReached() and !$this->object->endingTimeReached()) 
						//or ($this->object->getTestType() != TYPE_ASSESSMENT and !$this->object->isOnlineTest())
					)
			{
				$this->tpl->setCurrentBlock("start");
				$this->tpl->setVariable("BTN_START", $this->lng->txt("tst_start_test"));
				$this->tpl->parseCurrentBlock();
			}							
		}
						
		// we have results
		if ($active && $active->tries > 0) {
			// DELETE RESULTS only available for non Online Exams
			if (!$this->object->isOnlineTest())
			{
				// if resume is active it is possible to reset the test
				$this->tpl->setCurrentBlock("delete_results");
				$this->tpl->setVariable("BTN_DELETERESULTS", $this->lng->txt("tst_delete_results"));
				$this->tpl->parseCurrentBlock();
			}			
						
			// RESULT BLOCK if we can show result because we have data
			if ($this->cmdCtrl->canShowTestResults()) {
				$this->tpl->setCurrentBlock("results");
				$this->tpl->setVariable("BTN_RESULTS", $this->lng->txt("tst_show_results"));				
				$this->tpl->parseCurrentBlock();
			}
			
			if (!$this->cmdCtrl->canShowTestResults() && $this->object->isActiveTestSubmitted()) {
				$this->tpl->setCurrentBlock("show_answers");
				$this->tpl->setVariable("BTN_ANSWERS", $this->lng->txt("tst_show_answer_sheet"));				
				$this->tpl->parseCurrentBlock();				
			}			
						
			// Result Date not reached
			if (!$this->cmdCtrl->canShowTestResults()) {
					$this->tpl->setCurrentBlock("report_date_not_reached");
					preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getReportingDate(), $matches);
					$reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
					$this->tpl->setVariable("RESULT_DATE_NOT_REACHED", sprintf($this->lng->txt("report_date_not_reached"), $reporting_date));
					$this->tpl->parseCurrentBlock();
				}
			
			if ($this->object->isOnlineTest() and $test_disabled) {
				if (!$this->object->isActiveTestSubmitted($ilUser->getId())) {
					$this->tpl->setCurrentBlock("show_summary");				
					$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("save_finish"));
					$this->tpl->parseCurrentBlock();
				} else {
					sendInfo($this->lng->txt("tst_already_submitted"));					
				}
			} 			
		}
		

		$this->tpl->setCurrentBlock("adm_content");

		// test is disabled
		if ($test_disabled)
		{
			if (!$this->object->startingTimeReached() or $this->object->endingTimeReached())
			{
				$this->tpl->setCurrentBlock("startingtime");
				$this->tpl->setVariable("IMAGE_STARTING_TIME", ilUtil::getImagePath("time.gif", true));
			
				if (!$this->object->startingTimeReached())
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("starting_time_not_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_starting_time_not_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getStartingTime())));
				}
				else
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("ending_time_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
				}
				$this->tpl->parseCurrentBlock();
			}
			
			if ($this->cmdCtrl->isNrOfTriesReached())				
			{
				$this->tpl->setVariable("MAXIMUM_NUMBER_OF_TRIES_REACHED", $this->lng->txt("maximum_nr_of_tries_reached"));
			}
			if ($this->isMaxProcessingTimeReached())
			{
				sendInfo($this->lng->txt("detail_max_processing_time_reached"));					
			}				
		}		
		$introduction = $this->object->getIntroduction();
		$introduction = preg_replace("/\n/i", "<br />", $introduction);
		$this->tpl->setVariable("TEXT_INTRODUCTION", $introduction);
		$this->tpl->setVariable("FORMACTION", $this->getCallingScript() . "$add_parameter$add_sequence");
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the learners output of a question
	*
	* Creates the learners output of a question
	*
	* @access public
	*/
	function outWorkingForm($sequence = 1, $finish = false, $test_id, $active, $postpone_allowed, $user_question_order, $directfeedback = 0)
	{
		global $ilUser;
		
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
		
		$question_gui = $this->object->createQuestionGUI("", $this->object->getQuestionIdFromActiveUserSequence($sequence));
		if ($ilUser->prefs["tst_javascript"])
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_preview.html", true);

		$is_postponed = false;
		if (is_object($active))
		{			
			if (!preg_match("/(^|\D)" . $question_gui->object->getId() . "($|\D)/", $active->postponed) and 
				!($active->postponed == $question_gui->object->getId()))
			{
				$is_postponed = false;
			}
			else
			{
				$is_postponed = true;
			}
		}

		$formaction = $this->getCallingScript() . $this->getAddParameter() . "&sequence=$sequence";
				
		// output question
		switch ($question_gui->getQuestionType())
		{
			case "qt_imagemap":
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback, $formaction, true);
				$info =& $question_gui->object->getReachedInformation($ilUser->id, $test_id);
				if (strcmp($info[0]["value"], "") != 0)
				{
					$formaction .= "&selImage=" . $info[0]["value"];
				}
				break;

			default:
				$question_gui->setSequenceNumber ($sequence);
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback);
				break;
		}

		if(!$_GET['crs_show_result'])
		{
			if ($sequence == 1)
			{
				$this->tpl->setCurrentBlock("prev");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("prev_bottom");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("prev");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("prev_bottom");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($postpone_allowed)
		{
			if (!$is_postponed)
			{
				$this->tpl->setCurrentBlock("postpone");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("postpone_bottom");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->isOnlineTest()) {
			$this->tpl->setCurrentBlock("summary");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("summary_bottom");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
		}

		if (!$this->object->isOnlineTest()) {
			$this->tpl->setCurrentBlock("cancel_test");
			$this->tpl->setVariable("TEXT_CANCELTEST", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_ALTCANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_TITLECANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("HREF_IMGCANCELTEST", $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&cancelTest=true");
			$this->tpl->setVariable("HREF_CANCELTEXT", $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&cancelTest=true");
			$this->tpl->setVariable("IMAGE_CANCEL", ilUtil::getImagePath("cancel.png"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("cancel_test_bottom");
			$this->tpl->setVariable("TEXT_CANCELTEST", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_ALTCANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_TITLECANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("HREF_IMGCANCELTEST", $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&cancelTest=true");
			$this->tpl->setVariable("HREF_CANCELTEXT", $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&cancelTest=true");
			$this->tpl->setVariable("IMAGE_CANCEL", ilUtil::getImagePath("cancel.png"));
			$this->tpl->parseCurrentBlock();			
		}		

		if ($finish)
		{
			if (!$this->object->isOnlineTest()) {
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
			} else {
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();				
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("next");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("next_bottom");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
		}

		
		
		if ($this->object->isOnlineTest()) {
			$solved_array = ilObjTest::_getSolvedQuestions($this->object->test_id, $ilUser->getId(), $question_gui->object->getId());
			$solved = 0;
			
			if (count ($solved_array) > 0) {
				$solved = array_pop($solved_array);
				$solved = $solved->solved;
			}			
			
			if ($solved==1) 
			{
			 	$solved = ilUtil::getImagePath("solved.png", true);
			 	$solved_cmd = "resetsolved";
			 	$solved_txt = $this->lng->txt("tst_qst_resetsolved");
			} else 
			{				 
				$solved = ilUtil::getImagePath("not_solved.png", true);
				$solved_cmd = "setsolved";
				$solved_txt = $this->lng->txt("tst_qst_setsolved");
			}			
			$solved = "<input align=\"middle\" border=\"0\" alt=\"".$this->lng->txt("tst_qst_solved_state_click_to_change")."\" name=\"cmd[$solved_cmd]\" type=\"image\" src=\"$solved\">&nbsp;<small>$solved_txt</small>";
			
			$this->tpl->setCurrentBlock("question_status");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS_LABEL", $this->lng->txt("tst_question_solved_state").":");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS", $solved);
			$this->tpl->parseCurrentBlock();			
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $formaction);

		$this->tpl->parseCurrentBlock();
	}

	function outEvaluationForm()
	{
		global $ilUser;

		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$test_id = $this->object->getTestId();
		$question_gui = $this->object->createQuestionGUI("", $_GET["evaluation"]);
//		$this->tpl->addBlockFile("RESULT_DESCRIPTION", "result_description", "tpl.il_as_tst_result_table.html", true);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_evaluation.html", true);
		$formaction = $this->getCallingScript() . $this->getAddParameter() . "&sequence=$sequence";
		
		switch ($question_gui->getQuestionType())
		{
			case "qt_imagemap":
				$question_gui->outWorkingForm($test_id, "", 1, $formaction);
				break;
			case "qt_javaapplet":
				$question_gui->outWorkingForm("", "", 0);
				break;
			default:
				$question_gui->outWorkingForm($test_id, "", 1);
		}
//		$this->tpl->setCurrentBlock("result_description");
//		$question_gui->outUserSolution($ilUser->id, $this->object->getTestId());
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getCallingScript() . $this->getAddParameter());
		$this->tpl->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates the output for the search results when trying to add users/groups to a test evaluation
	*
	* Creates the output for the search results when trying to add users/groups to a test evaluation
	*
	* @access public
	*/
	function outStatSelectedSearchResults()
	{
		include_once ("./classes/class.ilSearch.php");
		global $ilUser;
		
		if (is_array($_POST["search_for"]))
		{
			if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]))
			{
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
					//sendInfo($message);
				}
				if(!$search->getNumberOfResults() && $search->getSearchFor())
				{
					sendInfo($this->lng->txt("search_no_match"));
					return;
				}
				$buttons = array("add");
				$participants =& $this->object->evalTotalPersonsArray();
				$eval_users = $this->object->getEvaluationUsers($ilUser->id);
				if ($searchresult = $search->getResultByType("usr"))
				{
					$users = array();
					foreach ($searchresult as $result_array)
					{
						if (!array_key_exists($result_array["id"], $eval_users))
						{
							if (array_key_exists($result_array["id"], $participants))
							{
								$users[$result_array["id"]] = $eval_users[$result_array["id"]];
							}
						}
					}
					$this->outEvalSearchResultTable("usr", $users, "user_result", "user_row", $this->lng->txt("search_found_users"), $buttons);
				}
				$searchresult = array();
				$eval_groups = $this->object->getEvaluationGroups($ilUser->id);
				if ($searchresult = $search->getResultByType("grp"))
				{
					$groups = array();
					foreach ($searchresult as $result_array)
					{
						if (!in_array($result_array["id"], $eval_groups))
						{
							include_once("./classes/class.ilObjGroup.php");
							$grp = new ilObjGroup($result_array["id"], true);
							$members = $grp->getGroupMemberIds();
							$found_member = 0;
							foreach ($members as $member_id)
							{
								if (array_key_exists($member_id, $participants))
								{
									$found_member = 1;
								}
							}
							if ($found_member)
							{
								array_push($groups, $result_array["id"]);
							}
						}
					}
					$this->outEvalSearchResultTable("grp", $groups, "group_result", "group_row", $this->lng->txt("search_found_groups"), $buttons);
				}
			}
		}
		else
		{
			sendInfo($this->lng->txt("no_user_or_group_selected"));
		}
	}
	
	/**
	* Adds found users to the selected users table
	*
	* Adds found users to the selected users table
	*
	* @access public
	*/
	function addFoundUsersToEvalObject()
	{
		global $ilUser;
		if (is_array($_POST["user_select"]))
		{
			foreach ($_POST["user_select"] as $user_id)
			{
				$this->object->addSelectedUser($user_id, $ilUser->id);
			}
		}
		$this->evalStatSelectedObject();
	}
	
	/**
	* Removes selected users from the selected users table
	*
	* Removes selected users from the selected users table
	*
	* @access public
	*/
	function removeSelectedUserObject()
	{
		global $ilUser;
		if (is_array($_POST["selected_users"]))
		{
			foreach ($_POST["selected_users"] as $user_id)
			{
				$this->object->removeSelectedUser($user_id, $ilUser->id);
			}
		}
		$this->evalStatSelectedObject();
	}
	
	/**
	* Removes selected users from the selected users table
	*
	* Removes selected users from the selected users table
	*
	* @access public
	*/
	function removeSelectedGroupObject()
	{
		global $ilUser;
		if (is_array($_POST["selected_groups"]))
		{
			foreach ($_POST["selected_groups"] as $group_id)
			{
				$this->object->removeSelectedGroup($group_id, $ilUser->id);
			}
		}
		$this->evalStatSelectedObject();
	}
	
	/**
	* Removes selected groups from the selected groups table
	*
	* Removes selected groups from the selected groups table
	*
	* @access public
	*/
	function addFoundGroupsToEvalObject()
	{
		global $ilUser;
		if (is_array($_POST["group_select"]))
		{
			foreach ($_POST["group_select"] as $group_id)
			{
				$this->object->addSelectedGroup($group_id, $ilUser->id);
			}
		}
		$this->evalStatSelectedObject();
	}
	
	/**
	* Called when the search button is pressed in the evaluation user selection
	*
	* Called when the search button is pressed in the evaluation user selection
	*
	* @access public
	*/
	function searchForEvaluationObject()
	{
		$this->evalStatSelectedObject(1);
	}
	
	/**
	* Creates the ouput of the selected users/groups for the test evaluation
	*
	* Creates the ouput of the selected users/groups for the test evaluation
	*
	* @access public
	*/
	function evalStatSelectedObject($search = 0)
	{
		global $ilUser;
		
		$this->ctrl->setCmd("evalStatSelected");
		$this->setEvaluationSettingsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation_selection.html", true);
		if ($search)
		{
			$this->outStatSelectedSearchResults();
		}
		$this->tpl->setCurrentBlock("userselection");
		$this->tpl->setVariable("SEARCH_USERSELECTION", $this->lng->txt("eval_search_userselection"));
		$this->tpl->setVariable("SEARCH_TERM", $this->lng->txt("eval_search_term"));
		$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("search_for"));
		$this->tpl->setVariable("SEARCH_USERS", $this->lng->txt("eval_search_users"));
		$this->tpl->setVariable("SEARCH_GROUPS", $this->lng->txt("eval_search_groups"));
		$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("eval_concatenation"));
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

		// output of alread found users and groups
		$eval_users = $this->object->getEvaluationUsers($ilUser->id);
		$eval_groups = $this->object->getEvaluationGroups($ilUser->id);
		$buttons = array("remove");
		if (count($eval_users))
		{
			$this->outEvalSearchResultTable("usr", $eval_users, "selected_user_result", "selected_user_row", $this->lng->txt("eval_found_selected_users"), $buttons);
		}
		if (count($eval_groups))
		{
			$this->outEvalSearchResultTable("grp", $eval_groups, "selected_group_result", "selected_group_row", $this->lng->txt("eval_found_selected_groups"), $buttons);
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CMD_EVAL", "evalSelectedUsers");
		$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
		$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_selected_users"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the search output for the user/group search form
	*
	* Creates the search output for the user/group search form
	*
	* @access	public
	*/
	function outEvalSearchResultTable($a_type, $id_array, $block_result, $block_row, $title_text, $buttons)
	{
		include_once("./classes/class.ilObjGroup.php");
		global $rbacsystem;
		
		$rowclass = array("tblrow1", "tblrow2");
		switch($a_type)
		{
			case "usr":
				foreach ($id_array as $user_id => $username)
				{
					$counter = 0;
					$user = new ilObjUser($user_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $user->getId());
					$this->tpl->setVariable("VALUE_LOGIN", $user->getLogin());
					$this->tpl->setVariable("VALUE_FIRSTNAME", $user->getFirstname());
					$this->tpl->setVariable("VALUE_LASTNAME", $user->getLastname());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_USER_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "grp":
				foreach ($id_array as $group_id)
				{
					$counter = 0;
					$group = new ilObjGroup($group_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $group->getRefId());
					$this->tpl->setVariable("VALUE_TITLE", $group->getTitle());
					$this->tpl->setVariable("VALUE_DESCRIPTION", $group->getDescription());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_GROUP_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_grp_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
		}
	}

	/**
	* Creates the output of a users text answer
	*
	* Creates the output of a users text answer
	*
	* @access	public
	*/
	function evaluationDetailObject()
	{
		$answertext = $this->object->getTextAnswer($_GET["userdetail"], $_GET["answer"]);
		$questiontext = $this->object->getQuestiontext($_GET["answer"]);
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_eval_user_answer.html", true, true);
		$this->tpl->setVariable("TITLE_USER_ANSWER", $this->lng->txt("tst_eval_user_answer"));
		$this->tpl->setVariable("TEXT_USER", $this->lng->txt("user"));
		$user = new ilObjUser($_GET["userdetail"]);
		$this->tpl->setVariable("TEXT_USERNAME", trim($user->getFirstname() . " " . $user->getLastname()));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_QUESTIONTEXT", ilUtil::prepareFormOutput($questiontext));
		$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$this->tpl->setVariable("TEXT_USER_ANSWER", str_replace("\n", "<br />", ilUtil::prepareFormOutput($answertext)));
	}
	
	function eval_statObject()
	{
		$this->ctrl->setCmdClass(get_class($this));
		$this->ctrl->setCmd("eval_stat");
		$this->setEvaluationSettingsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation_selection.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CMD_EVAL", "evalAllUsers");
		$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
		$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_all_users"));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();
	}

	function saveEvaluationSettings()
	{
		$eval_statistical_settings = array(
			"qworkedthrough" => $_POST["chb_result_qworkedthrough"],
			"pworkedthrough" => $_POST["chb_result_pworkedthrough"],
			"timeofwork" => $_POST["chb_result_timeofwork"],
			"atimeofwork" => $_POST["chb_result_atimeofwork"],
			"firstvisit" => $_POST["chb_result_firstvisit"],
			"lastvisit" => $_POST["chb_result_lastvisit"],
			"resultspoints" => $_POST["chb_result_resultspoints"],
			"resultsmarks" => $_POST["chb_result_resultsmarks"],
			"distancemedian" => $_POST["chb_result_distancemedian"]
		);
		$this->object->evalSaveStatisticalSettings($eval_statistical_settings, $ilUser->id);
		return $eval_statistical_settings;
	}
	
	function evalSelectedUsersObject($all_users = 0)
	{
		global $ilUser;

		$savetextanswers = 0;
		$textanswers = 0;
		$print = 0;
		$export = 0;
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("print")) == 0)
		{
			$print = 1;
		}
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("export")) == 0)
		{
			$export = 1;
		}
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("save_text_answer_points")) == 0)
		{
			$savetextanswers = 1;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/(\d+)_(\d+)_(\d+)/", $key, $matches))
				{
					ASS_TextQuestion::_setReachedPoints($matches[1], $this->object->getTestId(), $matches[2], $value, $matches[3]);
				}
			}
			sendInfo($this->lng->txt("text_answers_saved"));
		}
		if ((count($_POST) == 0) || ($print) || ($export) || ($savetextanswers))
		{
			$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
			$eval_statistical_settings = array(
				"qworkedthrough" => $user_settings["qworkedthrough"],
				"pworkedthrough" => $user_settings["pworkedthrough"],
				"timeofwork" => $user_settings["timeofwork"],
				"atimeofwork" => $user_settings["atimeofwork"],
				"firstvisit" => $user_settings["firstvisit"],
				"lastvisit" => $user_settings["lastvisit"],
				"resultspoints" => $user_settings["resultspoints"],
				"resultsmarks" => $user_settings["resultsmarks"],
				"distancemedian" => $user_settings["distancemedian"]
			);
		}
		else
		{
			$eval_statistical_settings = $this->saveEvaluationSettings();
		}
//		$this->ctrl->setCmd("evalSelectedUsers");
		$this->setEvaluationTabs($all_users);
		$legend = array();
		$titlerow = array();
		// build title columns
		$name_column = $this->lng->txt("name");
		if ($this->object->getTestType() == TYPE_SELF_ASSESSMENT)
		{
			$name_column = $this->lng->txt("counter");
		}
		array_push($titlerow, $name_column);
		
		$char = "A";
		if ($eval_statistical_settings["qworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_qworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["pworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_pworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["timeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_timeofwork");
			$char++;
		}
		if ($eval_statistical_settings["atimeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_atimeofwork");
			$char++;
		}
		if ($eval_statistical_settings["firstvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_firstvisit");
			$char++;
		}
		if ($eval_statistical_settings["lastvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_lastvisit");
			$char++;
		}
		if ($eval_statistical_settings["resultspoints"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultspoints");
			$char++;
		}
		if ($eval_statistical_settings["resultsmarks"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultsmarks");
			$char++;
			
			if ($this->object->ects_output)
			{
				array_push($titlerow, $char);
				$legend[$char] = $this->lng->txt("ects_grade");
				$char++;
			}
		}
		if ($eval_statistical_settings["distancemedian"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_mark_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_participant");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_total_participants");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_median");
			$char++;
		}
		
		$titlerow_without_questions = $titlerow;
		if (!$this->object->isRandomTest())
		{
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				array_push($titlerow, $this->lng->txt("question_short") . " " . $i);
				$legend[$this->lng->txt("question_short") . " " . $i] = $this->object->getQuestionTitle($i);
			}
		}
		else
		{
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				array_push($titlerow, "&nbsp;");
			}
		}
		$total_users =& $this->object->evalTotalPersonsArray();
		$selected_users = array();
		if ($all_users == 1) {
			$selected_users = $total_users;
		} else {
			$selected_users =& $this->object->getEvaluationUsers($ilUser->id);
			$selected_groups =& $this->object->getEvaluationGroups($ilUser->id);
			include_once("./classes/class.ilObjGroup.php");
			foreach ($selected_groups as $group_id)
			{
				$grp = new ilObjGroup($group_id, true);
				$members = $grp->getGroupMemberIds();
				foreach ($members as $member_id)
				{
					if (array_key_exists($member_id, $total_users))
					{
						$usr = new ilObjUser($member_id); 
						$selected_users[$member_id] = trim($usr->firstname . " " . $usr->lastname);
					}
				}
			}
		}
//			$ilBench->stop("Test_Statistical_evaluation", "getAllParticipants");
		$row = 0;
		$question_legend = false;
		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) {
			// receive array with statistical information on the test for a specific user
//				$ilBench->start("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($qt, $question_stat))
					{
						$question_stat[$qt] = array("max" => 0, "reached" => 0);
					}
					$question_stat[$qt]["single_max"] = $sarray["max"];
					$question_stat[$qt]["max"] += $sarray["max"];
					$question_stat[$qt]["reached"] += $sarray["reached"];
				}
			}
//				$ilBench->stop("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$evaluation_array[$key] = $stat_eval;
		}

		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$median_array = array();
		foreach ($evaluation_array as $key => $value)
		{
			array_push($median_array, $value["resultspoints"]);
		}
		//$median_array =& $this->object->getTotalPointsArray();
		$statistics = new ilStatistics();
		$statistics->setData($median_array);
		$median = $statistics->median();
//			$ilBench->stop("Test_Statistical_evaluation", "calculate all statistical data");
//			$ilBench->save();
		$evalcounter = 1;
		$question_titles = array();
		$question_title_counter = 1;
		$eval_complete = array();
		foreach ($selected_users as $key => $name)
		{
			$stat_eval = $evaluation_array[$key];
			
			$titlerow_user = array();
			if ($this->object->isRandomTest())
			{
				$this->object->loadQuestions($key);
				$titlerow_user = $titlerow_without_questions;
				$i = 1;
				foreach ($stat_eval as $key1 => $value1)
				{
					if (preg_match("/\d+/", $key1))
					{
						$qt = $value1["title"];
						$qt = preg_replace("/<.*?>/", "", $qt);
						$arraykey = array_search($qt, $legend);
						if (!$arraykey)
						{
							array_push($titlerow_user, $this->lng->txt("question_short") . " " . $question_title_counter);
							$legend[$this->lng->txt("question_short") . " " . $question_title_counter] = $qt;
							$question_title_counter++;
						}
						else
						{
							array_push($titlerow_user, $arraykey);
						}
					}
				}
			}

			$evalrow = array();
			$username = $evalcounter++; 
			if ($this->object->getTestType() != TYPE_SELF_ASSESSMENT)
			{
				$username = $selected_users[$key];
			}
			array_push($evalrow, array(
				"html" => $username,
				"xls"  => $username,
				"csv"  => $username
			));
			if ($eval_statistical_settings["qworkedthrough"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["qworkedthrough"],
					"xls"  => $stat_eval["qworkedthrough"],
					"csv"  => $stat_eval["qworkedthrough"]
				));
			}
			if ($eval_statistical_settings["pworkedthrough"]) {
				array_push($evalrow, array(
					"html" => sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %",
					"xls"  => $stat_eval["pworkedthrough"],
					"csv"  => $stat_eval["pworkedthrough"],
					"format" => "%"
				));
			}
			if ($eval_statistical_settings["timeofwork"]) {
				$time = $stat_eval["timeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => $stat_eval["timeofwork"],
					"csv"  => $stat_eval["timeofwork"]
				));
			}
			if ($eval_statistical_settings["atimeofwork"]) {
				$time = $stat_eval["atimeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => $stat_eval["atimeofwork"],
					"csv"  => $stat_eval["atimeofwork"]
				));
			}
			if ($eval_statistical_settings["firstvisit"]) {
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["firstvisit"]["year"],$stat_eval["firstvisit"]["mon"],$stat_eval["firstvisit"]["mday"],$stat_eval["firstvisit"]["hours"],$stat_eval["firstvisit"]["minutes"],$stat_eval["firstvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"format" => "t"
				));
			}
			if ($eval_statistical_settings["lastvisit"]) {
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["lastvisit"]["year"],$stat_eval["lastvisit"]["mon"],$stat_eval["lastvisit"]["mday"],$stat_eval["lastvisit"]["hours"],$stat_eval["lastvisit"]["minutes"],$stat_eval["lastvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"format" => "t"
				));
			}
			if ($eval_statistical_settings["resultspoints"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultspoints"]." ".strtolower($this->lng->txt("of"))." ". $stat_eval["maxpoints"],
					"xls"  => $stat_eval["resultspoints"],
					"csv"  => $stat_eval["resultspoints"]
				));
			}
			if ($eval_statistical_settings["resultsmarks"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultsmarks"],
					"xls"  => $stat_eval["resultsmarks"],
					"csv"  => $stat_eval["resultsmarks"]
				));

				if ($this->object->ects_output)
				{
					$mark_ects = $this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"]);
					array_push($evalrow, array(
						"html" => $mark_ects,
						"xls"  => $mark_ects,
						"csv"  => $mark_ects
					));
				}
			}
			
			if ($eval_statistical_settings["distancemedian"]) {
				if ($stat_eval["maxpoints"] == 0)
				{
					$pct = 0;
				}
				else
				{
					$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
				}
				$mark = $this->object->mark_schema->get_matching_mark($pct);
				$mark_short_name = "";
				if ($mark)
				{
					$mark_short_name = $mark->get_short_name();
				}
				array_push($evalrow, array(
					"html" => $mark_short_name,
					"xls"  => $mark_short_name,
					"csv"  => $mark_short_name
				));
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				array_push($evalrow, array(
					"html" => $rank_participant,
					"xls"  => $rank_participant,
					"csv"  => $rank_participant
				));
				$rank_median = $statistics->rank_median();
				array_push($evalrow, array(
					"html" => $rank_median,
					"xls"  => $rank_median,
					"csv"  => $rank_median
				));
				$total_participants = count($median_array);
				array_push($evalrow, array(
					"html" => $total_participants,
					"xls"  => $total_participants,
					"csv"  => $total_participants
				));
				array_push($evalrow, array(
					"html" => $median,
					"xls"  => $median,
					"csv"  => $median
				));
			}
			
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				$qshort = "";
				$qt = "";
				if ($this->object->isRandomTest())
				{
					$qt = $stat_eval[$i-1]["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					$arrkey = array_search($qt, $legend);
					if ($arrkey)
					{
						$qshort = "<span title=\"" . ilUtil::prepareFormOutput($qt) . "\">" . $arrkey . "</span>: ";
					}
				}

				$htmloutput = "";
				if ($stat_eval[$i-1]["type"] == 8)
				{
					// Text question
					$name = $key."_".$stat_eval[$i-1]["qid"]."_".$stat_eval[$i-1]["max"];
					$htmloutput = $qshort . "<input type=\"text\" name=\"".$name."\" size=\"3\" value=\"".$stat_eval[$i-1]["reached"]."\" />".strtolower($this->lng->txt("of"))." ". $stat_eval[$i-1]["max"];
					// Solution
					$htmloutput .= " [<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "evaluationDetail") . "&userdetail=$key&answer=".$stat_eval[$i-1]["qid"]."\" target=\"popup\" onclick=\"";
					$htmloutput .= "window.open('', 'popup', 'width=600, height=200, scrollbars=no, toolbar=no, status=no, resizable=yes, menubar=no, location=no, directories=no')";
					$htmloutput .= "\">".$this->lng->txt("tst_eval_show_answer")."</a>]";
					$textanswers++;
				}
					else
				{
					$htmloutput = $qshort . $stat_eval[$i-1]["reached"] . " " . strtolower($this->lng->txt("of")) . " " .  $stat_eval[$i-1]["max"];
				}

				array_push($evalrow, array(
					"html" => $htmloutput,
					"xls"  => $stat_eval[$i-1]["reached"],
					"csv"  => $stat_eval[$i-1]["reached"]
				));
			}
			array_push($eval_complete, array("title" => $titlerow_user, "data" => $evalrow));
		}

		$noqcount = count($titlerow_without_questions);
		if ($export)
		{
			$testname = preg_replace("/\s/", "_", $this->object->getTitle());
			switch ($_POST["export_type"])
			{
				case TYPE_XLS_PC:
				case TYPE_XLS_MAC:
					// Creating a workbook
					$workbook = new Spreadsheet_Excel_Writer();
	
					// sending HTTP headers
					$workbook->send("$testname.xls");
	
					// Creating a worksheet
					$format_bold =& $workbook->addFormat();
					$format_bold->setBold();
					$format_percent =& $workbook->addFormat();
					$format_percent->setNumFormat("0.00%");
					$format_datetime =& $workbook->addFormat();
					$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
					$format_title =& $workbook->addFormat();
					$format_title->setBold();
					$format_title->setColor('black');
					$format_title->setPattern(1);
					$format_title->setFgColor('silver');
					$worksheet =& $workbook->addWorksheet();
					$row = 0;
					$col = 0;
					include_once ("./classes/class.ilExcelUtils.php");
					if (!$this->object->isRandomTest())
					{
						foreach ($titlerow as $title)
						{
							$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$title], $_POST["export_type"]), $format_title);
							$col++;
						}
						$row++;
					}
					foreach ($eval_complete as $evalrow)
					{
						$col = 0;
						if ($this->object->isRandomTest())
						{
							foreach ($evalrow["title"] as $key => $value)
							{
								if ($key == 0)
								{
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $_POST["export_type"]), $format_title);
								}
								else
								{
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$value], $_POST["export_type"]), $format_title);
								}
								$col++;
							}
							$row++;
						}
						$col = 0;
						foreach ($evalrow["data"] as $key => $value)
						{
							switch ($value["format"])
							{
								case "%":
									$worksheet->write($row, $col, $value["xls"], $format_percent);
									break;
								case "t":
									$worksheet->write($row, $col, $value["xls"], $format_datetime);
									break;
								default:
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value["xls"], $_POST["export_type"]));
									break;
							}
							$col++;
						}
						$row++;
					}
					$workbook->close();
					exit;
				case TYPE_SPSS:
					$csv = "";
					$separator = ";";
					if (!$this->object->isRandomTest())
					{
						$titlerow =& $this->object->processCSVRow($titlerow, TRUE, $separator);
						$csv .= join($titlerow, $separator) . "\n";
					}
					foreach ($eval_complete as $evalrow)
					{
						if ($this->object->isRandomTest())
						{
							$evalrow["title"] =& $this->object->processCSVRow($evalrow["title"], TRUE, $separator);
							$csv .= join($evalrow["title"], $separator) . "\n";
						}
						$csvarr = array();
						$evalrow["data"] =& $this->object->processCSVRow($evalrow["data"], TRUE, $separator);
						$csv .= join($evalrow["data"], $separator) . "\n";
					}
					ilUtil::deliverData($csv, "$testname.csv");
					break;
			}
			exit;
		}
		if ($print)
		{
			$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_eval_statistical_evaluation_preview.html", true, true);
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation.html", true);
		}
		$color_class = array("tblrow1", "tblrow2");
		foreach ($legend as $short => $long)
		{
			$this->tpl->setCurrentBlock("legendrow");
			$this->tpl->setVariable("TXT_SYMBOL", $short);
			$this->tpl->setVariable("TXT_MEANING", $long);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("legend");
		$this->tpl->setVariable("TXT_LEGEND", $this->lng->txt("legend"));
		$this->tpl->setVariable("TXT_LEGEND_LINK", $this->lng->txt("eval_legend_link"));
		$this->tpl->setVariable("TXT_SYMBOL", $this->lng->txt("symbol"));
		$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("meaning"));
		$this->tpl->parseCurrentBlock();

		$counter = 0;
		foreach ($question_stat as $title => $values)
		{
			$this->tpl->setCurrentBlock("meanrow");
			$this->tpl->setVariable("TXT_QUESTION", ilUtil::prepareFormOutput($title));
			$percent = 0;
			if ($values["max"] > 0)
			{
				$percent = $values["reached"] / $values["max"];
			}
			$this->tpl->setVariable("TXT_MEAN", sprintf("%.2f", $values["single_max"]*$percent) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $values["single_max"]) . " (" . sprintf("%.2f", $percent*100) . " %)");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("question_mean_points");
		$this->tpl->setVariable("TXT_AVERAGE_POINTS", $this->lng->txt("average_reached_points"));
		$this->tpl->setVariable("TXT_QUESTION", $this->lng->txt("question_title"));
		$this->tpl->setVariable("TXT_MEAN", $this->lng->txt("average_reached_points"));
		$this->tpl->parseCurrentBlock();
		
		$noq = $noqcount;		
		foreach ($titlerow as $title)
		{
			if ($noq > 0)
			{
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . ilUtil::prepareFormOutput($legend[$title]) . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
				if ($noq == $noqcount)
				{
					$this->tpl->setCurrentBlock("questions_titlecol");
					$this->tpl->setVariable("TXT_TITLE", $title);
					$this->tpl->parseCurrentBlock();
				}
				$noq--;
			}
			else
			{
				$this->tpl->setCurrentBlock("questions_titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $legend[$title] . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
			}
		}
		$counter = 0;
		foreach ($eval_complete as $row)
		{
			$noq = $noqcount;
			foreach ($row["data"] as $key => $value)
			{
				if ($noq > 0)
				{
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
					if ($noq == $noqcount)
					{
						$this->tpl->setCurrentBlock("questions_datacol");
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->setVariable("TXT_DATA", $value["html"]);
						$this->tpl->parseCurrentBlock();
					}
					$noq--;
				}
				else
				{
					$this->tpl->setCurrentBlock("questions_datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("questions_row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}

		if ($textanswers)
		{
			$this->tpl->setCurrentBlock("questions_output_button");
			$this->tpl->setVariable("BUTTON_SAVE", $this->lng->txt("save_text_answer_points"));
			$this->tpl->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("questions_output");
		$this->tpl->setVariable("TXT_QUESTIONS",  $this->lng->txt("ass_questions"));
		$this->tpl->setVariable("FORM_ACTION_RESULTS", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("export_btn");
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_EXCEL_MAC", $this->lng->txt("exp_type_excel_mac"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->parseCurrentBlock();
		if ($print)
		{
			$this->tpl->setCurrentBlock("__global__");
			$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation") . " " . strtolower($this->lng->txt("of")) . " &quot;" . ilUtil::prepareFormOutput($this->object->getTitle()) . "&quot;");
			$this->tpl->setVariable("PRINT_CSS", "./templates/default/print.css");
			$this->tpl->setVariable("PRINT_TYPE", "summary");
			$this->tpl->show();
			exit;
		}
	}
	
	function evalAllUsersObject()
	{
		$this->evalSelectedUsersObject(1);
	}
	
	function eval_aObject()
	{
		$this->setAggregatedResultsTabs();
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", true);
		$total_persons = $this->object->evalTotalPersons();
		if ($total_persons) {
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", $total_persons);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished"));
			$total_finished = $this->object->evalTotalFinished();
			$this->tpl->setVariable("TXT_VALUE", $total_finished);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$average_time = $this->object->evalTotalFinishedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished_average_time"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$passed_tests = $this->object->evalTotalFinishedPassed();
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed"));
			$this->tpl->setVariable("TXT_VALUE", $passed_tests["total_passed"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_points"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $passed_tests["average_points"]) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $passed_tests["maximum_points"]));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ANONYMOUS_AGGREGATION", $this->lng->txt("tst_eval_no_anonymous_aggregation"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function confirmDeleteResults() 
	{
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_delete_results_confirm.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_CONFIRM_DELETE_RESULTS", $this->lng->txt("tst_confirm_delete_results"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_delete_results"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->parseCurrentBlock();
	}

/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outTestResults($print = false) {
		global $ilUser;

		function sort_percent($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["percent"] == $b["percent"]) {
				if ($a["nr"] == $b["nr"]) return 0;
		 	 	return ($a["nr"] < $b["nr"]) ? -1 : 1;
			}
			return ($a["percent"] < $b["percent"]) ? $smaller : $greater;
		}

		function sort_nr($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}

		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$user_id = $ilUser->id;
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_results.html", true);
		$result_array =& $this->object->getTestResult($user_id);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];
		$img_title_percent = "";
		$img_title_nr = "";
		switch ($_GET["sortres"]) {
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortpercent = "DESC";
				} else {
					$sortpercent = "ASC";
				}
				break;
			case "nr":
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortnr = "DESC";
				} else {
					$sortnr = "ASC";
				}
				break;
		}
		if (!$sortpercent) {
			$sortpercent = "ASC";
		}
		if (!$sortnr) {
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				if ($this->object->isOnlineTest())
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", preg_replace("/<a[^>]*>(.*?)<\/a>/i","\\1", $value["title"]));
				else
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				if (preg_match("/http/", $value["solution"]))
				{
					$this->tpl->setVariable("SOLUTION_HINT", "<a href=\"".$value["solution"]."\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a>");
				}
				else
				{
					if ($value["solution"])
					{
						$this->tpl->setVariable("SOLUTION_HINT", $this->lng->txt($value["solution"]));
					}
					else
					{
						$this->tpl->setVariable("SOLUTION_HINT", "");
					}
				}
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("question");
		$this->tpl->setVariable("COLOR_CLASS", "std");
		$this->tpl->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$this->tpl->setVariable("VALUE_QUESTION_TITLE", "");
		$this->tpl->setVariable("SOLUTION_HINT", "");
		$this->tpl->setVariable("VALUE_MAX_POINTS", "<strong>" . sprintf("%d", $total_max) . "</strong>");
		$this->tpl->setVariable("VALUE_REACHED_POINTS", "<strong>" . sprintf("%d", $total_reached) . "</strong>");
		$this->tpl->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_COUNTER", "<a href=\"" . $this->getCallingScript() . "$add_parameter&sortres=nr&order=$sortnr\">" . $this->lng->txt("tst_question_no") . "</a>$img_title_nr");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", "<a href=\"" . $this->getCallingScript() . "$add_parameter&sortres=percent&order=$sortpercent\">" . $this->lng->txt("tst_percent_solved") . "</a>$img_title_percent");
		$mark_obj = $this->object->mark_schema->get_matching_mark($percentage);
		if ($mark_obj)
		{
			if ($mark_obj->get_passed()) {
				$mark = $this->lng->txt("tst_result_congratulations");
			} else {
				$mark = $this->lng->txt("tst_result_sorry");
			}
			$mark .= "<br />" . $this->lng->txt("tst_your_mark_is") . ": &quot;" . $mark_obj->get_official_name() . "&quot;";
		}
		if ($this->object->ects_output)
		{
			$ects_mark = $this->object->getECTSGrade($total_reached, $total_max);
			$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
		}
		$this->tpl->setVariable("USER_FEEDBACK", $mark);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
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
		sendInfo($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", true);

		$this->tpl->setCurrentBlock("confirm_delete");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Deletes all user data for the test object
	*
	* Deletes all user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteAllUserDataObject()
	{
		$this->object->removeAllTestEditings();
		sendInfo($this->lng->txt("tst_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Cancels the deletion of all user data for the test object
	*
	* Cancels the deletion of all user data for the test object
	*
	* @access	public
	*/
	function cancelDeleteAllUserDataObject()
	{
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Create random solutions for the test object for every registered user
	*
	* Create random solutions for the test object for every registered user
	* NOTE: This method is only for debug and performance test reasons. Don't use it
	* in your productive system
	*
	* @access	public
	*/
	function createSolutionsObject()
	{
		$this->object->createRandomSolutionsForAllUsers();
		$this->ctrl->redirect($this, "maintenance");
	}

	/**
	* Creates the maintenance form for a test
	*
	* Creates the maintenance form for a test
	*
	* @access	public
	*/
	function maintenanceObject()
	{
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", true);
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("BTN_DELETE_ALL", $this->lng->txt("tst_delete_all_user_data"));
//			$this->tpl->setVariable("BTN_CREATE_SOLUTIONS", $this->lng->txt("tst_create_solutions"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo($this->lng->txt("cannot_maintain_test"));
		}
	}	

	/**
	* Creates the status output for a test
	*
	* Creates the status output for a test
	*
	* @access	public
	*/
	function statusObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_status.html", true);
		if (!$this->object->isComplete())
		{
			if (!$this->object->isRandomTest())
			{
				if (count($this->object->questions) == 0)
				{
					$this->tpl->setCurrentBlock("list_element");
					$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_questions"));
					$this->tpl->parseCurrentBlock();
				}
			}
			if (count($this->object->mark_schema->mark_steps) == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_marks"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->author, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->title, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			
			if ($this->object->isRandomTest())
			{
				$arr = $this->object->getRandomQuestionpools();
				if (count($arr) == 0)
				{
					$this->tpl->setCurrentBlock("list_element");
					$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_no_questionpools_for_random_test"));
					$this->tpl->parseCurrentBlock();
				}
				$count = 0;
				foreach ($arr as $array)
				{
					$count += $array["count"];
				}
				if (($count == 0) && ($this->object->getRandomQuestionCount() == 0))
				{
					$this->tpl->setCurrentBlock("list_element");
					$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_no_questions_for_random_test"));
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setCurrentBlock("status_list");
			$this->tpl->setVariable("TEXT_MISSING_ELEMENTS", $this->lng->txt("tst_status_missing_elements"));
			$this->tpl->parseCurrentBlock();
		}
		$total = $this->object->evalTotalPersons();
		if ($total > 0)
		{
			$this->tpl->setCurrentBlock("list_element");
			$this->tpl->setVariable("TEXT_ELEMENT", sprintf($this->lng->txt("tst_in_use_edit_questions_disabled"), $total));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isComplete())
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("tst_status_ok"));
			$this->tpl->setVariable("STATUS_CLASS", "bold");
		}
		else
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("tst_status_missing"));
			$this->tpl->setVariable("STATUS_CLASS", "warning");
		}
		$this->tpl->parseCurrentBlock();
	}	

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php")
	{
//		global $ilias_locator;
		$ilias_locator = new ilLocatorGUI(false);
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);
		//check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;
		if (!defined("ILIAS_MODULE")) {
			foreach ($path as $key => $row)
			{
				$ilias_locator->navigate($i++, $row["title"], 
										 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH).
										 "/adm_object.php?ref_id=".$row["child"],"");
			}
		} else {
			
			// Workaround for crs_objectives
			$frameset = $_GET['crs_show_result'] ? '' : 'cmd=frameset&';

			foreach ($path as $key => $row)
			{
				if (strcmp($row["title"], "ILIAS") == 0) {
					$row["title"] = $this->lng->txt("repository");
				}
				if ($this->ref_id == $row["child"]) {
					if ($_GET["cmd"]) {
						$param = "&cmd=" . $_GET["cmd"];
					} else {
						$param = "";
					}
					$ilias_locator->navigate($i++, $row["title"], 
											 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
											 "/assessment/test.php" . "?crs_show_result=".$_GET['crs_show_result'].
											 "&ref_id=".$row["child"] . $param,"");

					if ($this->sequence) {
						if (($this->sequence <= $this->object->getQuestionCount()) and (!$_POST["cmd"]["showresults"])) {
							$ilias_locator->navigate($i++, $this->object->getQuestionTitle($this->sequence), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=".$_GET['crs_show_result'].
													 "&ref_id=".$row["child"] . $param . 
													 "&sequence=" . $this->sequence,"");
						} else {
						}
						/*if ($_POST["cmd"]["summary"] or isset($_GET["sort_summary"]))
						{
						$ilias_locator->navigate($i++, $this->lng->txt("summary"), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=0".
													 "&ref_id=".$row["child"] . $param . 
												 "&sequence=" . $_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"],"");
						}*/
					} else {
						if ($_POST["cmd"]["summary"] or isset($_GET["sort_summary"]))
						{
							$ilias_locator->navigate($i++, $this->lng->txt("summary"), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=0".
													 "&ref_id=".$row["child"] . $param . 
												 "&sequence=" . $_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"],"");
						}/* elseif ($_POST["cmd"]["show_answers"])
						{
							$ilias_locator->navigate($i++, $this->lng->txt("preview"), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=0".
													 "&ref_id=".$row["child"] . $param . 
												 "&sequence=" . $_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"],"");
							
						}						
						elseif ($_POST["cmd"]["submit_answers"])
						{
							$ilias_locator->navigate($i++, $this->lng->txt("submit"), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=0".
													 "&ref_id=".$row["child"] . $param . 
												 "&sequence=" . $_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"],"");
							
						}*/
					}
				} else {
					$ilias_locator->navigate($i++, $row["title"], 
											 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/" . 
											 $scriptname."?".$frameset."ref_id=".$row["child"],"");
				}
			}

			if (isset($_GET["obj_id"]))
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
				$ilias_locator->navigate($i++,$obj_data->getTitle(),
										 $scriptname."?".$frameset."ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"");
			}
		}
		$ilias_locator->output();
	}

/*
	function editMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			$this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

	function saveMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		if (!strcmp($_POST["meta_section"], "General")) {
			$meta = $_POST["meta"];
			$this->object->setTitle(ilUtil::stripSlashes($meta["Title"]["Value"]));
			$this->object->setDescription(ilUtil::stripSlashes($meta["Description"][0]["Value"]));
			$this->object->update();
		}
		ilUtil::redirect($this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]."&cmd=editMeta");
	}

	// called by administration
	function chooseMetaSectionObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_REQUEST["meta_section"]);
	}

	// called by editor
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}

	function addMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_name = $_POST["meta_name"] ? $_POST["meta_name"] : $_GET["meta_name"];
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		if ($meta_index == "")
			$meta_index = 0;
		$meta_path = $_POST["meta_path"] ? $_POST["meta_path"] : $_GET["meta_path"];
		$meta_section = $_POST["meta_section"] ? $_POST["meta_section"] : $_GET["meta_section"];
		if ($meta_name != "")
		{
			$meta_gui->meta_obj->add($meta_name, $meta_path, $meta_index);
		}
		else
		{
			sendInfo($this->lng->txt("meta_choose_element"), true);
		}
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $meta_section);
	}

	function addMeta()
	{
		$this->addMetaObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}

	function deleteMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_GET["meta_section"]);
	}

	function deleteMeta()
	{
		$this->deleteMetaObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}
*/
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

			include_once("./assessment/classes/class.ilObjTest.php");
			$tst = new ilObjTest();
			
			$tests =& ilObjTest::_getAvailableTests(true);
			if (count($tests) > 0)
			{
				foreach ($tests as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_tst");
					$this->tpl->setVariable("OPTION_VALUE_TST", $key);
					$this->tpl->setVariable("TXT_OPTION_TST", $value);
					if ($_POST["tst"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED_TST", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$questionpools =& $tst->getAvailableQuestionpools(true);
			if (count($questionpools) == 0)
			{
			}
			else
			{
				foreach ($questionpools as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_qpl");
					$this->tpl->setVariable("OPTION_VALUE", $key);
					$this->tpl->setVariable("TXT_OPTION", $value);
					if ($_POST["qpl"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}
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

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																	   $_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
			$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_TST", $this->lng->txt("import_tst"));
			$this->tpl->setVariable("TXT_TST_FILE", $this->lng->txt("tst_upload_file"));
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TXT_DUPLICATE_TST", $this->lng->txt("duplicate_tst"));
			$this->tpl->setVariable("TXT_SELECT_TST", $this->lng->txt("obj_tst"));
			$this->tpl->setVariable("OPTION_SELECT_TST", $this->lng->txt("select_tst_option"));
			$this->tpl->setVariable("TXT_DUPLICATE", $this->lng->txt("duplicate"));
		}
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();
		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		if (!defined("ILIAS_MODULE"))
		{
			$this->setAdminTabs($_POST["new_type"]);
		}
		$this->setLocator();

	}
	
	function setAggregatedResultsTabs()
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$path = $this->tree->getPathFull($this->object->getRefID());
		$addcmd = "";
		if (strcmp($_SESSION["il_rep_mode"], "tree") == 0)
		{
			$addcmd = "&cmd=frameset";
		}
		$tabs_gui->addTarget("back", $this->getReturnLocation("cancel","../repository.php?ref_id=" . $path[count($path) - 2]["child"]) . $addcmd, "",	"");
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}
	
	function setEvaluationSettingsTabs()
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$path = $this->tree->getPathFull($this->object->getRefID());
		$tabs_gui->addTarget("eval_all_users", $this->ctrl->getLinkTargetByClass(get_class($this), "eval_stat"), "eval_stat",	"ilobjtestgui");
		$tabs_gui->addTarget("eval_selected_users", $this->ctrl->getLinkTargetByClass(get_class($this), "evalStatSelected"), "evalStatSelected",	"ilobjtestgui");
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}
	
	function setEvaluationTabs($all_users = 0)
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$cmd = "evalAllUsers";
		if ($all_users == 0)
		{
			$cmd = "evalSelectedUsers";
		}
		$path = $this->tree->getPathFull($this->object->getRefID());
		$tabs_gui->addTarget("tst_statistical_evaluation", $this->ctrl->getLinkTargetByClass(get_class($this), "$cmd"), "$cmd",	"ilobjtestgui");
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}
	
	/**
	* Creates the output for user/group invitation to a test
	*
	* Creates the output for user/group invitation to a test
	*
	* @access	public
	*/
	function participantsObject()
	{
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if ($this->object->getTestType() != TYPE_ONLINE_TEST) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("wrong_test_type"), true);
			return;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_invite.html", true);

		if ($_POST["cmd"]["cancel"])
		{
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel",ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH)."/repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}

		if (count($_POST))
		{
			$this->handleCommands();
			//return;
		}
		
		if ($_POST["cmd"]["save"])
		{
			$this->object->saveToDb();
		}
		
		{
			if ($rbacsystem->checkAccess('write', $this->ref_id))
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
		$invited_users = $this->object->getInvitedUsers();

		$buttons = array("save","remove","print_answers","print_results");
		
		if (count($invited_users))
		{
			$this->outUserGroupTable("iv_usr", $invited_users, "invited_user_result", "invited_user_row", $this->lng->txt("tst_participating_users"), "TEXT_INVITED_USER_TITLE",$buttons);
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_INVITATION", $this->lng->txt("invitation"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

    	if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Extracts the results of a posted invitation form
	*
	* Extracts the results of a posted invitation form
	*
	* @access	public
	*/
	function handleCommands()
	{
		global $ilUser;

		$message = "";
		
		if (is_array($_POST["invited_users"]))
		{			
			if ($_POST["cmd"]["print_answers"]) {
				$this->_printAnswerSheets($_POST["invited_users"]);
				return;
			} elseif ($_POST["cmd"]["print_results"]) {
				$this->_printResultSheets($_POST["invited_users"]);
				return;					
			}
			
			for ($i = 0; $i < count($_POST["invited_users"]); $i++)
			{
				$user_id = $_POST["invited_users"][$i];	
				if ($_POST["cmd"]["remove"])
					$this->object->disinviteUser($user_id);				
			}
		}
		if (is_array($_POST["clientip"])) {
			foreach ($_POST["clientip"] as $user_id => $client_ip)
			{
				if ($_POST["cmd"]["save_client_ip"])
					$this->object->setClientIP($user_id, $client_ip);
			}			
		}
		
		if ($_POST["cmd"]["add"])
		{
			// add users 
			if (is_array($_POST["user_select"]))
			{
				$i = 0;
				foreach ($_POST["user_select"] as $user_id)
				{					
					$client_ip = $_POST["client_ip"][$i];
					$this->object->inviteUser($user_id, $client_ip);
					$i++;				
				}
			}
			// add groups members
			if (is_array($_POST["group_select"]))
			{
				foreach ($_POST["group_select"] as $group_id)
				{
					$this->object->inviteGroup($group_id);
				}
			}
			// add role members
			if (is_array($_POST["role_select"]))
			{
				foreach ($_POST["role_select"] as $role_id)
				{					
					$this->object->inviteRole($role_id);
				}
			}
			
		}

		if ($_POST["cmd"]["search"])
		{
			if (is_array($_POST["search_for"]))
			{
				if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]) or in_array("role", $_POST["search_for"]))
				{					
					
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
						sendInfo($message);
					}
					
					if(!$search->getNumberOfResults() && $search->getSearchFor())
					{
						sendInfo($this->lng->txt("search_no_match"));
						return;
					}
					$buttons = array("add");

					$invited_users = $this->object->getInvitedUsers();
				
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
				sendInfo($this->lng->txt("no_user_or_group_selected"));
			}
		}
	}
	
	
	
function outUserGroupTable($a_type, $data_array, $block_result, $block_row, $title_text, $title_label, $buttons)
	{
		global $rbacsystem;
		$rowclass = array("tblrow1", "tblrow2");
		
		switch($a_type)
		{
			case "iv_usr":
				$add_parameter = "?ref_id=" . $_GET["ref_id"];
				$finished = "<a target=\"_BLANK\" href=\"".$this->getCallingScript().$add_parameter."&cmd=resultsheet&user_id=\"><img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("right.png", true) . "\" alt=\"\" />&nbsp;".$this->lng->txt("tst_qst_result_sheet")."</a>" ;
				$finished .= "&nbsp;<a target=\"_BLANK\" href=\"".$this->getCallingScript().$add_parameter."&cmd=answersheet&user_id=\">&nbsp;".$this->lng->txt("tst_show_answer_sheet")."</a>" ;
				$started   = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("right.png", true) . "\" alt=\"\" />" ;
				
				foreach ($data_array as $data)
				{
					$finished_line = str_replace ("&user_id=","&user_id=".$data->usr_id,$finished);
					$started_line = str_replace ("&user_id=","&user_id=".$data->usr_id,$started); 
					$counter = 0;
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $data->usr_id);
					$this->tpl->setVariable("VALUE_IV_USR_ID", $data->usr_id);
					$this->tpl->setVariable("VALUE_IV_LOGIN", $data->login);
					$this->tpl->setVariable("VALUE_IV_FIRSTNAME", $data->firstname);
					$this->tpl->setVariable("VALUE_IV_LASTNAME", $data->lastname);
					$this->tpl->setVariable("VALUE_IV_CLIENT_IP", $data->clientip);
					$this->tpl->setVariable("VALUE_IV_TEST_FINISHED", ($data->test_finished==1)?$finished_line:"&nbsp;");
					$this->tpl->setVariable("VALUE_IV_TEST_STARTED", ($data->test_started==1)?$started_line:"&nbsp;");
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_IV_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_IV_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_IV_LASTNAME", $this->lng->txt("lastname"));
				$this->tpl->setVariable("TEXT_IV_CLIENT_IP", $this->lng->txt("clientip"));
				$this->tpl->setVariable("TEXT_IV_TEST_FINISHED", $this->lng->txt("tst_finished"));
				$this->tpl->setVariable("TEXT_IV_TEST_STARTED", $this->lng->txt("tst_started"));
					
				if ($rbacsystem->checkAccess('write', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "usr":
				$add_parameter = "?ref_id=" . $_GET["ref_id"] . "&cmd=resultsheet";
				$finished = "<a target=\"_BLANK\" href=\"".$this->getCallingScript().$add_parameter."\"><img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("right.png", true) . "\" alt=\"\" />&nbsp;".$this->lng->txt("tst_qst_result_sheet")."</a>" ;
				foreach ($data_array as $data)
				{
					$counter = 0;
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $data->usr_id);
					$this->tpl->setVariable("VALUE_LOGIN", $data->login);
					$this->tpl->setVariable("VALUE_FIRSTNAME", $data->firstname);
					$this->tpl->setVariable("VALUE_LASTNAME", $data->lastname);
					$this->tpl->setVariable("VALUE_CLIENT_IP", $data->clientip);
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				$this->tpl->setVariable("TEXT_CLIENT_IP", $this->lng->txt("clientip"));
					
				if ($rbacsystem->checkAccess('write', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
				
			case "role":
				
			case "grp":
				foreach ($data_array as $key => $data)
				{
					$counter = 0;
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $key);
					$this->tpl->setVariable("VALUE_TITLE", $data->title);
					$this->tpl->setVariable("VALUE_DESCRIPTION", $data->description);
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("$title_label", "<img src=\"" . ilUtil::getImagePath("icon_".$a_type."_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess('write', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
		}
	}
		
		
/**
	* Output of the learners view of an existing test without evaluation
	*
	* @access public
	*/
	function outTestSummary() {
		global $ilUser;

		function sort_title($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			if (strcmp($a["title"],$b["title"])< 0)
				return $smaller;
			else if (strcmp($a["title"],$b["title"])> 0)
				return $greater;
			return 0;
		}
		
		
		function sort_nr($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}
		
		function sort_visited($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) 
				return 0;
			return ($a["visited"] < $b["visited"]) ? $smaller : $greater;
		}

		
		function sort_solved($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["solved"] < $b["solved"]) ? $smaller : $greater;
		}

		$add_parameter = $this->getAddParameter()."&"."sequence=".$_GET["sequence"];
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_summary.html", true);
		$user_id = $ilUser->id;
		$color_class = array ("tblrow1", "tblrow2");
		$counter = 0;
		
		$result_array = & $this->object->getTestSummary($user_id);
		
		$img_title_nr = "";
		$img_title_title = "";
		$img_title_solved = "";
		
		if (!$_GET["sort_summary"] )
		{
			$_GET["sort_summary"]  = "nr";
			$_GET["order"] = "ASC";
		} 
		
		switch ($_GET["sort_summary"]) {
			case nr:
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortnr = "DESC";
				} else {
					$sortnr = "ASC";
				}
				break;			
			
			case "title":
				usort($result_array, "sort_title");
				$img_title_title = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sorttitle = "DESC";
				} else {
					$sorttitle = "ASC";
				}
				break;
			case "solved":
				usort($result_array, "sort_solved");
				$img_title_solved = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortsolved = "DESC";
				} else {
					$sortsolved = "ASC";
				}
				break;			
		}
		if (!$sorttitle) {
			$sorttitle = "ASC";
		}
		if (!$sortsolved) {
			$sortsolved = "ASC";
		}
		if (!$sortnr) {
			$sortnr = "ASC";
		}
		
		$img_solved = " <img border=\"0\"  align=\"middle\" src=\"" . ilUtil::getImagePath("solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$img_not_solved = " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("not_solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$goto_question =  " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("goto_question.png", true) . "\" alt=\"".$this->lng->txt("tst_qst_goto")."\" />";
		
		$disabled = $this->isMaxProcessingTimeReached() | $this->object->endingTimeReached();
		
		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				$this->tpl->setVariable("VALUE_QUESTION_VISITED", ($value["visited"] > 0) ? " checked=\"checked\" ": ""); 
				$this->tpl->setVariable("VALUE_QUESTION_SOLVED", ($value["solved"] > 0) ?$img_solved : $img_not_solved);  
				if (!$disabled)
					$this->tpl->setVariable("VALUE_QUESTION_HREF_GOTO", "<a href=\"".$value["href_goto"]."\">");
				$this->tpl->setVariable("VALUE_QUESTION_GOTO", $goto_question);
				$this->tpl->setVariable("VALUE_QUESTION_HREF_SET_SOLVED", $value["href_setsolved"]."&sequence=".$_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"]);
				$this->tpl->setVariable("VALUE_QUESTION_SET_SOLVED", ($value["solved"] > 0) ?$this->lng->txt("tst_qst_resetsolved"):$this->lng->txt("tst_qst_setsolved"));
				$this->tpl->setVariable("VALUE_QUESTION_DESCRIPTION", $value["description"]);
				$this->tpl->setVariable("VALUE_QUESTION_POINTS", $value["points"]."&nbsp;".$this->lng->txt("points_short"));
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_ACTION","actions");
		$this->tpl->setVariable("QUESTION_COUNTER","<a href=\"".$this->getCallingScript()."$add_parameter&order=$sortnr&sort_summary=nr\">".$this->lng->txt("tst_qst_order")."</a>".$img_title_nr);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"".$this->getCallingScript()."$add_parameter&order=$sorttitle&sort_summary=title\">".$this->lng->txt("tst_question_title")."</a>".$img_title_title);
		$this->tpl->setVariable("QUESTION_VISITED", "<a href=\"".$this->getCallingScript()."$add_parameter&order=$sortvisited&sort_summary=visited\">".$this->lng->txt("tst_question_visited")."</a>".$img_title_visited);
		$this->tpl->setVariable("QUESTION_SOLVED", "<a href=\"".$this->getCallingScript()."$add_parameter&order=$sortsolved&sort_summary=solved\">".$this->lng->txt("tst_question_solved_state")."</a>".$img_title_solved);
		$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("USER_FEEDBACK", $this->lng->txt("tst_qst_summary_text"));
		$this->tpl->setVariable("TXT_SHOW_AND_SUBMIT_ANSWERS", $this->lng->txt("save_finish"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript().$add_parameter);	
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("summary"));		
		$this->tpl->parseCurrentBlock();
		
		if (!$disabled) {
			$this->tpl->setCurrentBlock("back");
			$this->tpl->setVariable("FORM_BACK_ACTION", $this->getCallingScript().$add_parameter);
			$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
			$this->tpl->parseCurrentBlock();
		} else 
		{
			sendinfo($this->lng->txt("detail_max_processing_time_reached"));
		}
		
		
		if ($this->object->getEnableProcessingTime())
			$this->outProcessingTime();

	}
	
	/**
	* confirm submit results
	* if confirm then results are submitted and printview of answers is shown.
	* @access public
	*/
	function confirmSubmitAnswers() {
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isActiveTestSubmitted()) 
		{
			$this->tpl->setCurrentBlock("not_submit_allowed");
			$this->tpl->setVariable("TEXT_ALREADY_SUBMITTED", $this->lng->txt("tst_already_submitted"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_show_answer_sheet"));
		} else 
		{
			$this->tpl->setCurrentBlock("submit_allowed");
			$this->tpl->setVariable("TEXT_CONFIRM_SUBMIT_RESULTS", $this->lng->txt("tst_confirm_submit_answers"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_results"));
		}
		$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));		
		$this->tpl->setVariable("FORM_BACK_ACTION", $this->getCallingScript().$add_parameter);
		$this->tpl->setVariable("FORM_PRINT_ACTION", $this->getCallingScript()."?ref_id=".$this->object->getRefId()."&cmd=printAnswers");
		$this->tpl->parseCurrentBlock();
	}
	
	function printAnswersObject(){
		global $ilUser,$rbacsystem;
		if ((!$rbacsystem->checkAccess("read", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if (!$this->object->isActiveTestSubmitted($ilUser->getId()))
			$this->object->setActiveTestSubmitted($ilUser->getId());

		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable ("FRAME_TITLE", $this->object->getTitle());
		$this->tpl->setVariable ("FRAME_CLIENTIP",$_SERVER["REMOTE_ADDR"]);		
		$this->tpl->setVariable ("FRAME_MATRICULATION",$ilUser->getMatriculation());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		
		$this->outShowAnswersDetails(false, $ilUser); 
	}
	
	function _printAnswerSheets ($users) {	
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		
		foreach ($users as $user_id) {
			if ($this->object->isActiveTestSubmitted($user_id)) {
				$this->outShowAnswersDetails(false, new ilObjUser ($user_id));
			}
		}
	}
	
	function _printResultSheets ($users) {	
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_results.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_results.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_result_details.html", true);
		
		foreach ($users as $user_id) {
			if ($this->object->isActiveTestSubmitted($user_id)) {
				$this->outPrintUserResults($user_id);			
			}
		}
	}	
	
	function outShowAnswers ($isForm, &$ilUser) {
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);		
		$this->outShowAnswersDetails($isForm, $ilUser);
	}
	
	function outShowAnswersDetails($isForm, &$ilUser) {
		$tpl = &$this->tpl;		 				
		$invited_users = array_pop($this->object->getInvitedUsers($ilUser->getId()));
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$t = $active->submittimestamp;
		
		$add_parameter = $this->getAddParameter();
		
			
		$tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$tpl->setVariable("VALUE_USR_NAME", $ilUser->getLastname().", ".$ilUser->getFirstname());
		$tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
		$tpl->setVariable("VALUE_USR_MATRIC", $ilUser->getMatriculation());
		$tpl->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
		$tpl->setVariable("VALUE_CLIENT_IP", $invited_users->clientip);
		
		$tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
		$tpl->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", ilUtil::date_mysql2time($t)));
		$this->tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_answer_sheet"));

		$freefieldtypes = array ("freefield_bottom" => 	array(	array ("title" => $this->lng->txt("tst_signature"), "length" => 300)));
/*					"freefield_top" => 		array (	array ("title" => $this->lng->txt("semester"), "length" => 300), 
											  		array ("title" => $this->lng->txt("career"), "length" => 300)
											  	   ),*/
					
		
		
		foreach ($freefieldtypes as $type => $freefields) {
			$counter = 0;

			while ($counter < count ($freefields)) {
				$freefield = $freefields[$counter];
				
				$tpl->setCurrentBlock($type);
			
				$tpl->setVariable("TXT_FREE_FIELD", $freefield["title"]);
				$tpl->setVariable("VALUE_FREE_FIELD", "<img height=\"30px\" border=\"0\" src=\"".ilUtil :: getImagePath("spacer.gif", false)."\" width=\"".$freefield["length"]."px\" />");
			
				$counter ++;
			
				$tpl->parseCurrentBlock($type);
			}
		}

		$tpl->setCurrentBlock("prolog");
		$tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		$tpl->parseCurrentBlock();
		
		$counter = 1;
		
		foreach ($this->object->questions as $question) {
			$tpl->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);

			$tpl->setVariable("EDIT_QUESTION", $this->getCallingScript().$this->getAddParameter()."&sequence=".$counter);
			$tpl->setVariable("COUNTER_QUESTION", $counter.". ");
			$tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			
			$idx = $this->object->test_id;
			
			switch ($question_gui->getQuestionType()) {
				case "qt_imagemap" :
					$question_gui->outWorkingForm($idx, false, $show_solutions=false, $formaction, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				case "qt_javaapplet" :
					$question_gui->outWorkingForm("", $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				default :
					$question_gui->outWorkingForm($idx, $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
			}
			$tpl->parseCurrentBlock("question");
			$counter ++;
		}

		if ($isForm) {
			$tpl->setCurrentBlock("confirm");
			$tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("tst_submit_answers_txt"));
			$tpl->setVariable("BTN_CANCEL", $this->lng->txt("back"));
			$tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_answers"));
			$tpl->setVariable("FORM_ACTION", $this->getCallingScript().$add_parameter);
			$tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock ("adm_content");
		$this->tpl->parseCurrentBlock();
		
	}
	
	/**
	 * updates working time and stores state saveresult to see if question has to be stored or not
	 */
	
	function updateWorkingTime() {
		// todo: check update within summary and back
		// todo: back in summary does not work
		// todo: check working time in summary
		
		global $ilUser;
		
		// command which do not require update
		
		//print_r($_GET);
				//print_r($_POST);
		
		$negs =  //is_numeric($_GET["set_solved"]) || is_numeric($_GET["question_id"]) ||  
				  isset($_POST["cmd"]["start"]) || isset($_POST["cmd"]["resume"]) || 
				  isset($_POST["cmd"]["showresults"]) || isset($_POST["cmd"]["deleteresults"])|| 
				  isset($_POST["cmd"]["confirmdeleteresults"]) || isset($_POST["cmd"]["canceldeleteresults"]) ||
				  isset($_POST["cmd"]["submit_answers"]) || isset($_POST["cmd"]["confirm_submit_answers"]) ||
				  isset($_POST["cmd"]["cancel_show_answers"]) || isset($_POST["cmd"]["show_answers"]);
		
		// all other commands which require update
		$pos  = count($_POST["cmd"])>0 | isset($_GET["selImage"]) | isset($_GET["sequence"]);
				
		$this->saveResult = false;
		
		if ($pos==true && $negs==false)		
		{
			// set new finish time for test
			if ($_SESSION["active_time_id"]) // && $this->object->getEnableProcessingTime())
			{
				$this->object->updateWorkingTime($_SESSION["active_time_id"]);
				//echo "updating Worktime<br>";
			}	
			
			// save question solution
			if ($this->cmdCtrl->canSaveResult())
			{
				// but only if the ending time is not reached
				$q_id = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);
				if (is_numeric($q_id)) 
				{
				 	$question_gui = $this->object->createQuestionGUI("", $q_id);
				 	$this->saveResult = $question_gui->object->saveWorkingData($this->object->getTestId());
				 	//echo "saving <br>";
				}												
			}			
		}		
	}	
	
	function resultsheetObject () {
		global $rbacsystem, $ilUser;
		
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			echo utf8_decode($this->lng->txt("cannot_edit_test"));
			exit();
		}
		
		$user_id = (int) $_GET["user_id"];
		$user = $this->object->getInvitedUsers($user_id);
		if (!is_array ($user) || count($user)!=1)
		{
			echo utf8_decode($this->lng->txt("user_not_invited"));
			exit();
		}
			
		$this->outPrintTestResults($user_id);
		
	}
	
	function answersheetObject () {
		global $rbacsystem;//, $ilUser;
		
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			echo utf8_decode($this->lng->txt("cannot_edit_test"));
			exit();
		}
		
		$user_id = (int) $_GET["user_id"];
		$user = $this->object->getInvitedUsers($user_id);
		if (!is_array ($user) || count($user)!=1)
		{
			echo utf8_decode($this->lng->txt("user_not_invited"));
			exit();
		}
		$ilUser = new IlObjUser ($user_id);		
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable("FRAME_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("FRAME_CLIENTIP",$_SERVER["REMOTE_ADDR"]);		
		$this->tpl->setVariable("FRAME_MATRICULATION",$ilUser->getMatriculation());
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		$this->outShowAnswersDetails(false, $ilUser);			
	}
	
	
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outPrintTestResults($user_id) {
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_results.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_results.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_result_details.html", true);			
		
		$this->outPrintUserResults ($user_id);
	}
	
	function outPrintUserResults ($user_id) {
		$user = new IlObjUser ($user_id);
		$active = $this->object->getActiveTestUser($user_id);
		$t = $active->submittimestamp;
		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		
		$this->tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$this->tpl->setVariable("VALUE_USR_NAME", $user->getLastname().", ".$user->getFirstname());
		$this->tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
		$this->tpl->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
		$this->tpl->setVariable("TXT_TEST_DATE", $this->lng->txt("tst_tst_date"));
		$this->tpl->setVariable("VALUE_TEST_DATE", strftime("%Y-%m-%d %H:%M:%S",ilUtil::date_mysql2time($t)));
		$this->tpl->setVariable("TXT_PRINT_DATE", $this->lng->txt("tst_print_date"));
		$this->tpl->setVariable("VALUE_PRINT_DATE", strftime("%Y-%m-%d %H:%M:%S",$print_date));
		

		$add_parameter = $this->getAddParameter();
		
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;

		$result_array =& $this->object->getTestResult($user_id);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];

		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$title = preg_replace ("/<a[^>]*>(.*?)<\/a>/", "\\1",$value["title"]);
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $title);
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock("question");
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no"));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved"));

		// SUM
		$this->tpl->setVariable("TOTAL", $this->lng->txt("total"));
		$this->tpl->setVariable("TOTAL_MAX_POINTS", $total_max);
		$this->tpl->setVariable("TOTAL_REACHED_POINTS",  $total_reached);
		$this->tpl->setVariable("TOTAL_PERCENT_SOLVED", sprintf("%01.2f",$percentage)." %");



		$mark_obj = $this->object->mark_schema->get_matching_mark($percentage);
		if ($mark_obj)
		{
			$mark .= "<br /><strong>" . $this->lng->txt("tst_mark") . ": &quot;" . $mark_obj->get_official_name() . "&quot;</strong>";
		}
		if ($this->object->ects_output)
		{
			$ects_mark = $this->object->getECTSGrade($total_reached, $total_max);
			$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
		}	
 
		$this->tpl->setVariable("GRADE", $mark);
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
	}
	
	
	function printobject () {
		global $rbacsystem, $ilUser;
		
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}

		if ($_POST["cmd"]["print"]) {
			$this->outPrinttest();
			return;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_test_confirm.html", true);
		$this->tpl->setVariable("TEXT_CONFIRM_PRINT_TEST", $this->lng->txt("tst_confirm_print"));
		$this->tpl->setVariable("FORM_PRINT_ACTION", $this->getCallingScript().$this->getAddParameter());
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		
	}
	
	function outPrinttest() {
		global $ilUser;
		
		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_test.html", true, true);
		
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_test.css");				
		$this->tpl->setVariable("SYNTAX_CSS","./templates/default/print_syntax.css");
		
		$this->tpl->setVariable("TITLE", $this->object->getTitle());		
		$this->tpl->setVariable("PRINT_TEST", $this->lng->txt("tst_print"));
		$this->tpl->setVariable("TXT_PRINT_DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("VALUE_PRINT_DATE", strftime("%c",$print_date));
			
		$tpl = &$this->tpl;			
		
		$tpl->setVariable("TITLE", $this->object->getTitle());	

		$max_points= 0;
		$counter = 1;
					
		foreach ($this->object->questions as $question) {		
			$tpl->setCurrentBlock("question");			
			$question_gui = $this->object->createQuestionGUI("", $question);
			
			$tpl->setVariable("EDIT_QUESTION", $this->getCallingScript().$this->getAddParameter()."&sequence=".$counter);
			$tpl->setVariable("COUNTER_QUESTION", $counter.".");
			$tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			
			switch ($question_gui->getQuestionType()) {
				
				case "qt_imagemap" :
					$question_gui->outWorkingForm($idx = "", $postponed = false, $show_solution = true, $formaction, $show_pages= true, $show_solutions_only= true);
					break;
				case "qt_javaapplet" :
					$question_gui->outWorkingForm($idx = "", $postponed = false, $show_solution = true, $show_pages = true, $show_solutions_only= true);
					break;
				default :
					$question_gui->outWorkingForm($idx = "", $postponed = false, $show_solution = true, $show_pages = true, $show_solutions_only= true);
			}
			$tpl->parseCurrentBlock("question");
			$counter ++;					
			$max_points += $question_gui->object->getMaximumPoints();			
		}
		$this->tpl->setVariable("TXT_MAXIMUM_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("VALUE_MAXIMUM_POINTS", $max_points);
		
		
	}
	
	
	/**
	 * handle endingTimeReached
	 * @private
	 */
	
	function endingTimeReached () {
		sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			if ($this->object->isOnlineTest())
				$this->outTestSummary();
			else
				$this->outTestResults();
		}
	}
	
	
	function maxProcessingTimeReached (){
		sendInfo($this->lng->txt("detail_max_processing_time_reached"));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			if ($this->object->isOnlineTest())
				$this->outTestSummary();
			else					
				$this->outTestResults();
		}
	}		
		
		
	function outProcessingTime () {
		global $ilUser;
		$this->tpl->setCurrentBlock("enableprocessingtime");
		$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
		$processing_time = $this->object->getProcessingTimeInSeconds();
		$time_seconds = $working_time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("USER_WORKING_TIME", $this->lng->txt("tst_time_already_spent") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$time_seconds = $processing_time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("MAXIMUM_PROCESSING_TIME", $this->lng->txt("tst_processing_time") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->tpl->parseCurrentBlock();
	}
	
	function outShortResult ($user_question_order) {
		$this->tpl->setCurrentBlock("percentage");
		$this->tpl->setVariable("PERCENTAGE", (int)(($this->sequence / count($user_question_order))*200));
		$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($this->sequence / count($user_question_order))*100));
		$this->tpl->setVariable("HUNDRED_PERCENT", "200");
		$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("percentage_bottom");
		$this->tpl->setVariable("PERCENTAGE", (int)(($this->sequence / count($user_question_order))*200));
		$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($this->sequence / count($user_question_order))*100));
		$this->tpl->setVariable("HUNDRED_PERCENT", "200");
		$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
		$this->tpl->parseCurrentBlock();				
	}
	
	
	function isMaxProcessingTimeReached () {
		global $ilUser;
 
		if (!is_bool($this->maxProcessingTimeReached))
			$this->maxProcessingTimeReached = (($this->object->getEnableProcessingTime()) && ($this->object->getCompleteWorkingTime($ilUser->id) > $this->object->getProcessingTimeInSeconds()));
		
		return $this->maxProcessingTimeReached;
	}
	
	function isEndingTimeReached () {
		global $ilUser;
		if (!is_bool($this->endingTimeReached))			
			$this->endingTimeReached = $this->object->endingTimeReached() && ($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->isOnlineTest());
			
		return $this->endingTimeReached;
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		$tabs_gui->getTargetsByObjectType($this, "tst");

		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "meta_data", get_class($this));
	}

				
} // END class.ilObjTestGUI

?>
