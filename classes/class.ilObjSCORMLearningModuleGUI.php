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

require_once "classes/class.ilObjectGUI.php";
require_once("classes/class.ilFileSystemGUI.php");
require_once("classes/class.ilTabsGUI.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilFileSystemGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjSCORMLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORMLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "slm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->tabs_gui =& new ilTabsGUI();

	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$this->fs_gui =& new ilFileSystemGUI($this->object->getDataDirectory());
		$this->getTemplate();
		$this->setLocator();
		$this->setTabs();

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilfilesystemgui":
				//$ret =& $this->fs_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($this->fs_gui);
				break;

			default:
				$cmd = $this->ctrl->getCmd("frameset");
				$ret =& $this->$cmd();
				break;
		}
		$this->tpl->show();
	}


	function viewObject()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","content/sahs_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","content/sahs_edit.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* scorm module properties
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl;

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view link
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", "sahs_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		// scorm lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.sahs_properties.html", true);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_PROPERTIES", $this->lng->txt("cont_lm_properties"));

		// online
		$this->tpl->setVariable("TXT_ONLINE", $this->lng->txt("cont_online"));
		$this->tpl->setVariable("CBOX_ONLINE", "cobj_online");
		$this->tpl->setVariable("VAL_ONLINE", "y");
		if ($this->object->getOnline())
		{
			$this->tpl->setVariable("CHK_ONLINE", "checked");
		}

		// api adapter name
		$this->tpl->setVariable("TXT_API_ADAPTER", $this->lng->txt("cont_api_adapter"));
		$this->tpl->setVariable("VAL_API_ADAPTER", $this->object->getAPIAdapterName());

		// api functions prefix
		$this->tpl->setVariable("TXT_API_PREFIX", $this->lng->txt("cont_api_func_prefix"));
		$this->tpl->setVariable("VAL_API_PREFIX", $this->object->getAPIFunctionsPrefix());

		// default lesson mode
		$this->tpl->setVariable("TXT_LESSON_MODE", $this->lng->txt("cont_def_lesson_mode"));
		$lesson_modes = array("normal" => $this->lng->txt("cont_sc_less_mode_normal"),
			"browse" => $this->lng->txt("cont_sc_less_mode_browse"));
		$sel_lesson = ilUtil::formSelect($this->object->getDefaultLessonMode(),
			"lesson_mode", $lesson_modes, false, true);
		$this->tpl->setVariable("SEL_LESSON_MODE", $sel_lesson);

		// credit mode
		$this->tpl->setVariable("TXT_CREDIT_MODE", $this->lng->txt("cont_credit_mode"));
		$credit_modes = array("credit" => $this->lng->txt("cont_credit_on"),
			"no_credit" => $this->lng->txt("cont_credit_off"));
		$sel_credit = ilUtil::formSelect($this->object->getCreditMode(),
			"credit_mode", $credit_modes, false, true);
		$this->tpl->setVariable("SEL_CREDIT_MODE", $sel_credit);

		// auto review mode
		$this->tpl->setVariable("TXT_AUTO_REVIEW", $this->lng->txt("cont_sc_auto_review"));
		$this->tpl->setVariable("CBOX_AUTO_REVIEW", "auto_review");
		$this->tpl->setVariable("VAL_AUTO_REVIEW", "y");
		if ($this->object->getAutoReview())
		{
			$this->tpl->setVariable("CHK_AUTO_REVIEW", "checked");
		}

		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* save properties
	*/
	function saveProperties()
	{
		$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
		$this->object->setAutoReview(ilUtil::yn2tf($_POST["auto_review"]));
		$this->object->setAPIAdapterName($_POST["api_adapter"]);
		$this->object->setAPIFunctionsPrefix($_POST["api_func_prefix"]);
		$this->object->setCreditMode($_POST["credit_mode"]);
		$this->object->setDefaultLessonMode($_POST["lesson_mode"]);
		$this->object->update();
		sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}



	/**
	* no manual SCORM creation, only import at the time
	*/
	function createObject()
	{
		$this->importObject();
	}

	/**
	* display dialogue for importing SCORM package
	*
	* @access	public
	*/
	function importObject()
	{
		// display import form
		// print_r($this->lng);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.slm_import.html");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
			$_GET["ref_id"])); // ."&new_type=slm"));

		$this->tpl->setVariable("BTN_NAME", "save");
		
		$this->tpl->setVariable("TXT_SELECT_LMTYPE", $this->lng->txt("type"));
		$this->tpl->setVariable("TXT_TYPE_AICC", $this->lng->txt("lm_type_aicc"));
		$this->tpl->setVariable("TXT_TYPE_HACP", $this->lng->txt("lm_type_hacp"));
		$this->tpl->setVariable("TXT_TYPE_SCORM", $this->lng->txt("lm_type_scorm"));
		
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_lm"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
		$this->tpl->setVariable("TXT_VALIDATE_FILE", $this->lng->txt("cont_validate_file"));

		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf=get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms=get_cfg_var("post_max_size");

		// use the smaller one as limit
		$max_filesize=min($umf, $pms);
		if (!$max_filesize) $max_filesize=max($umf, $pms);
		// gives out the limit as a littel notice :)
		$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice")." $max_filesize.");
	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject()
	{
		global $HTTP_POST_FILES, $rbacsystem;

		// check if file was uploaded
		$source = $HTTP_POST_FILES["scormfile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}
		// get_cfg_var("upload_max_filesize"); // get the may filesize form t he php.ini
		switch ($_HTTP_POST_FILES["scormfile"]["error"])
		{
			case UPLOAD_ERR_INI_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_PARTIAL:
				$this->ilias->raiseError($this->lng->txt("err_partial_file_upload"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_NO_FILE:
				$this->ilias->raiseError($this->lng->txt("err_no_file_uploaded"),$this->ilias->error_obj->MESSAGE);
				break;
		}

		$file = pathinfo($_FILES["scormfile"]["name"]);
		$name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
		if ($name == "")
		{
			$name = $this->lng->txt("no_title");
		}

		// create and insert object in objecttree
		$newObj = $this->getNewObject();
		//$newObj->setType("slm");
		//$dummy_meta =& new ilMetaData();
		//$dummy_meta->setObject($newObj);
		//$newObj->assignMetaData($dummy_meta);
		$newObj->setTitle($name);
		$newObj->setDescription("");
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create data directory, copy file to directory
		$newObj->createDataDirectory();

		// copy uploaded file to data directory
		$file_path = $newObj->getDataDirectory()."/".$_FILES["scormfile"]["name"];
		move_uploaded_file($_FILES["scormfile"]["tmp_name"], $file_path);

		ilUtil::unzip($file_path);
		ilUtil::renameExecutables($newObj->getDataDirectory());
		
		$this->readObject($newObj);

	}
	
	function getNewObject() {
		// create  object in objecttree
		include_once("classes/class.ilObjSCORMLearningModule.php");
		return new ilObjSCORMLearningModule();
	}
	
	/**
	* @access	public
	*/
	function readObject($newObj)
	{

		// convert imsmanifest.xml file in iso to utf8
		// include_once("include/inc.convertcharset.php");
		$manifest_file = $newObj->getDataDirectory()."/imsmanifest.xml";

		// check if manifestfile exists and space left on device...
		$check_for_manifest_file=is_file($manifest_file);
		if ($check_for_manifest_file) {
			// to copy the file we need some extraspace, counted in bytes *2 ... we need 2 copies....
			$estimated_manifest_filesize=filesize($manifest_file) * 2;
			$check_disc_free=disk_free_space($newObj->getDataDirectory()) - $estimated_manifest_filesize;
		}
		
		
		// if file exists and enough space left on device		
		if ($check_for_manifest_file && ($check_disc_free > 1)) {
			
			// create backup from original
			if (!copy($manifest_file, $manifest_file.".old")) {
				echo "Failed to copy $manifest_file...<br>\n";
			}
		
			// read backupfile, convert each line to utf8, write line to new file
			// php < 4.3 style
			$f_write_handler=fopen($manifest_file.".new", "w");
			$f_read_handler=fopen($manifest_file.".old", "r");
			while (!feof($f_read_handler))
			{
				$zeile =fgets($f_read_handler);
				fputs($f_write_handler, utf8_encode($zeile));
			}
			fclose($f_read_handler);
			fclose($f_write_handler);
		
			// copy new utf8-file to imsmanifest.xml
			if (!copy($manifest_file.".new", $manifest_file)) {
				echo "Failed to copy $manifest_file...<br>\n";
			}
			
			if (!@is_file($manifest_file))
			{
				$this->ilias->raiseError($this->lng->txt("cont_no_manifest"),
				$this->ilias->error_obj->WARNING);
			}
		} else { 
			// gives out the specific error
		
			if (!$check_for_manifest_file)
				$this->ilias->raiseError($this->lng->txt("Manifestfile $manifest_file not found!"),$this->ilias->error_obj->MESSAGE);
			else if (!($check_disc_free > 1))
				$this->ilias->raiseError($this->lng->txt("Not enough space left on device!"),$this->ilias->error_obj->MESSAGE);
			return;
		}
			
		//validate the XML-Files in the SCORM-Package
		if ($_POST["validate"] == "y")
		{
			if (!$newObj->validate($newObj->getDataDirectory()))
			{
				$this->ilias->raiseError("<b>Validation Error(s):</b><br>".$newObj->getValidationSummary(),
					$this->ilias->error_obj->WARNING);
			}
		}

		// start SCORM package parser
		include_once ("content/classes/SCORM/class.ilSCORMPackageParser.php");
		// todo determine imsmanifest.xml path here...
		$slmParser = new ilSCORMPackageParser($newObj, $manifest_file);
		$slmParser->startParsing();

		//return $newObj;

		//header("Location: adm_object.php?cmd=view&ref_id=".$_GET["ref_id"]);
		//exit();
	}

	function upload()
	{
		$this->uploadObject();
	}

	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;
	
		$this->uploadObject();

		sendInfo( $this->lng->txt("lm_added"), true);
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));

	}

	/**
	* permission form
	*/
	function perm()
	{
		$this->setFormAction("permSave", "sahs_edit.php?cmd=permSave&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->setFormAction("addRole", "sahs_edit.php?ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]."&cmd=addRole");
		$this->permObject();
	}

	/**
	* save permissions
	*/
	function permSave()
	{
		$this->setReturnLocation("permSave",
			"sahs_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->permSaveObject();
	}

	/**
	* add role
	*/
	function addRole()
	{
		$this->setReturnLocation("addRole",
			"sahs_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->addRoleObject();
	}

	/**
	* show owner of learning module
	*/
	function owner()
	{
		$this->ownerObject();
	}

	/**
	* choose meta data section
	* (called by administration)
	*/
	function chooseMetaSectionObject($a_target = "")
	{
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=".$this->object->getRefId();
		}

		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			$a_target, $_REQUEST["meta_section"]);
	}

	/**
	* choose meta data section
	* (called by module)
	*/
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* add meta data object
	* (called by administration)
	*/
	function addMetaObject($a_target = "")
	{
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=".$this->object->getRefId();
		}

		include_once "classes/class.ilMetaDataGUI.php";
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
		$meta_gui->edit("ADM_CONTENT", "adm_content", $a_target, $meta_section);
	}

	/**
	* add meta data object
	* (called by module)
	*/
	function addMeta()
	{
		$this->addMetaObject($this->ctrl->getLinkTarget($this));
	}


	/**
	* delete meta data object
	* (called by administration)
	*/
	function deleteMetaObject($a_target = "")
	{
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=".$this->object->getRefId();
		}

		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit("ADM_CONTENT", "adm_content", $a_target, $_GET["meta_section"]);
	}

	/**
	* delete meta data object
	* (called by module)
	*/
	function deleteMeta()
	{
		$this->deleteMetaObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* edit meta data
	* (called by administration)
	*/
	function editMetaObject($a_target = "")
	{
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=".$this->object->getRefId();
		}

		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content", $a_target, $_GET["meta_section"]);
	}

	/**
	* edit meta data
	* (called by module)
	*/
	function editMeta()
	{
		$this->editMetaObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* save meta data
	* (called by administration)
	*/
	function saveMetaObject($a_target = "")
	{
		if ($a_target == "")
		{
			$a_target = "adm_object.php?cmd=editMeta&ref_id=".$this->object->getRefId();
		}

		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		ilUtil::redirect(ilUtil::appendUrlParameterString($a_target,
			"meta_section=" . $_POST["meta_section"]));
	}

	/**
	* save meta data
	* (called by module)
	*/
	function saveMeta()
	{
		$this->saveMetaObject($this->ctrl->getLinkTarget($this, "editMeta"));
	}


	/**
	* output main header (title and locator)
	*/
	function getTemplate()
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		//$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
	}

	/**
	* show tracking data
	*/
	function showTrackingItems()
	{

		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_items.html", true);

		$num = 1;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_tracking_items"));

		$tbl->setHeaderNames(array($this->lng->txt("title")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this));
		$cols = array("title");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("100%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
		//$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		#$tbl->disable("footer");

		//$items = $this->object->getTrackingItems();
		$items = $this->object->getTrackedItems();

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($items));
		$items = array_slice($items, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if (count($items) > 0)
		{
			foreach ($items as $item)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_ITEM_TITLE", $item->getTitle());
				$this->ctrl->setParameter($this, "obj_id", $item->getId());
				$this->tpl->setVariable("LINK_ITEM",
					$this->ctrl->getLinkTarget($this, "showTrackingItem"));

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show tracking data of item
	*/
	function showTrackingItem()
	{

		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_item.html", true);

		$num = 2;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		include_once("content/classes/SCORM/class.ilSCORMItem.php");
		$sc_item =& new ilSCORMItem($_GET["obj_id"]);

		// title & header columns
		$tbl->setTitle($sc_item->getTitle());

		$tbl->setHeaderNames(array($this->lng->txt("name"),
			$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
			$this->lng->txt("cont_score")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"]);
		$cols = array("name", "status", "time", "score");
		$tbl->setHeaderVars($cols, $header_params);
		//$tbl->setColumnWidth(array("25%",));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
		//$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		#$tbl->disable("footer");

		$tr_data = $this->object->getTrackingDataAgg($_GET["obj_id"]);

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($tr_data));
		$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if (count($tr_data) > 0)
		{
			foreach ($tr_data as $data)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$user = new ilObjUser($data["user_id"]);
				$this->tpl->setVariable("VAL_USERNAME", $user->getLastname().", ".
					$user->getFirstname());
				$this->ctrl->setParameter($this, "user_id", $data["user_id"]);
				$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				$this->tpl->setVariable("LINK_USER",
					$this->ctrl->getLinkTarget($this, "showTrackingItemPerUser"));
				$this->tpl->setVariable("VAL_TIME", $data["time"]);
				$this->tpl->setVariable("VAL_STATUS", $data["status"]);
				$this->tpl->setVariable("VAL_CREDIT", $data["score"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show tracking data of item per user
	*/
	function showTrackingItemPerUser()
	{

		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_item_per_user.html", true);

		$num = 2;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		include_once("content/classes/SCORM/class.ilSCORMItem.php");
		$sc_item =& new ilSCORMItem($_GET["obj_id"]);

		// title & header columns
		$tbl->setTitle($sc_item->getTitle());

		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),
			$this->lng->txt("cont_lvalue"), $this->lng->txt("cont_rvalue")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"]);
		$cols = array("firstname", "lastname", "lvalue", "rvalue");
		$tbl->setHeaderVars($cols, $header_params);
		//$tbl->setColumnWidth(array("25%",));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
		//$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		#$tbl->disable("footer");

		$tr_data = $this->object->getTrackingDataPerUser($_GET["obj_id"], $_GET["user_id"]);

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($tr_data));
		$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if (count($tr_data) > 0)
		{
			foreach ($tr_data as $data)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$user = new ilObjUser($data["user_id"]);
				$this->tpl->setVariable("VAL_FIRSTNAME", $user->getFirstname());
				$this->tpl->setVariable("VAL_LASTNAME", $user->getLastname());
				$this->tpl->setVariable("VAR", $data["lvalue"]);
				$this->tpl->setVariable("VAL", $data["rvalue"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.sahs_edit_frameset.html", false, false, "content");
		$this->tpl->setVariable("REF_ID",$this->ref_id);
		$this->tpl->show();
	}

	/**
	* set locator
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="adm_object.php")
	{
		global $ilias_locator, $tree;
		if (!defined("ILIAS_MODULE"))
		{
			parent::setLocator();
		}
		else
		{
			$a_tree =& $tree;
			$a_id = $_GET["ref_id"];

			$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

			$path = $a_tree->getPathFull($a_id);

			// this is a stupid workaround for a bug in PEAR:IT
			$modifier = 1;

			if (!empty($_GET["obj_id"]))
			{
				$modifier = 0;
			}

			// ### AA 03.11.10 added new locator GUI class ###
			$i = 1;

			if ($this->object->getType() != "grp" && ($_GET["cmd"] == "delete" || $_GET["cmd"] == "edit"))
			{
				unset($path[count($path) - 1]);
			}

			foreach ($path as $key => $row)
			{

				if ($key < count($path) - $modifier)
				{
					$this->tpl->touchBlock("locator_separator");
				}

				$this->tpl->setCurrentBlock("locator_item");
				if ($row["child"] != $a_tree->getRootId())
				{
					$this->tpl->setVariable("ITEM", $row["title"]);
				}
				else
				{
					$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				}
				if($row["type"] == "slm" || $row["type"] == "alm" || $row["type"] == "hlm")
				{
					$this->tpl->setVariable("LINK_ITEM", "sahs_edit.php?ref_id=".$row["child"]."&type=".$row["type"]);
				}
				else
				{
					$this->tpl->setVariable("LINK_ITEM", "../repository.php?ref_id=".$row["child"]);
				}
				//$this->tpl->setVariable("LINK_TARGET", " target=\"bottom\" ");

				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("locator");

				// ### AA 03.11.10 added new locator GUI class ###
				// navigate locator
				if ($row["child"] != $a_tree->getRootId())
				{
					$ilias_locator->navigate($i++,$row["title"],"../repository.php?ref_id=".$row["child"],"bottom");
				}
				else
				{
					$ilias_locator->navigate($i++,$this->lng->txt("repository"),"../repository.php?ref_id=".$row["child"],"bottom");
				}
			}

			/*
			if (DEBUG)
			{
				$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
			}

			$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

			if ($_GET["cmd"] == "confirmDeleteAdm")
			{
				$prop_name = "delete_object";
			}*/

			$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
			$this->tpl->parseCurrentBlock();
		}

	}


	/**
	* output tabs
	*/
	function setTabs()
	{
		$this->getTabs($this->tabs_gui);
		$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		if ($this->ctrl->getCmd() == "delete")
		{
			return;
		}

		// properties
		$tabs_gui->addTarget("properties",
			$this->ctrl->getLinkTarget($this, "properties"), "properties",
			get_class($this));
		
		// file system gui tabs
		if (is_object($this->fs_gui))
		{
			$this->fs_gui->getTabs($tabs_gui);
		}

		// tracking data
		$tabs_gui->addTarget("cont_tracking_data",
			$this->ctrl->getLinkTarget($this, "showTrackingItems"), "showTrackingItems",
			get_class($this));

		// edit meta
		$tabs_gui->addTarget("meta_data",
			$this->ctrl->getLinkTarget($this, "editMeta"), "editMeta",
			get_class($this));

		// perm
		$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTarget($this, "perm"), "perm",
			get_class($this));

		// owner
		$tabs_gui->addTarget("owner",
			$this->ctrl->getLinkTarget($this, "owner"), "owner",
			get_class($this));
	}



} // END class.ilObjSCORMLearningModule
?>
