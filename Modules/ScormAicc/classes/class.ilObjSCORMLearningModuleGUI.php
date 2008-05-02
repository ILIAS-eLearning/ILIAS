<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
require_once("Services/User/classes/class.ilObjUser.php");

require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php");
require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id$
*
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilInfoScreenGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMLearningModuleGUI extends ilObjSAHSLearningModuleGUI
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
		$lng->loadLanguageModule("search");
		
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();
	}

	/**
	* assign scorm object to scorm gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjSCORMLearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjSCORMLearningModule($this->id, false);
			}
		}
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
		$this->tpl->setVariable("BTN_LINK",
			"ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();
		
		// upload new version
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "newModuleVersion"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_sc_new_version"));
		$this->tpl->parseCurrentBlock();

		// scorm lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.sahs_properties.html", "Modules/ScormAicc");
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
		
		// max attempts
		$this->tpl->setVariable("MAX_ATTEMPTS", $this->lng->txt("cont_sc_max_attempt"));
		$this->tpl->setVariable("VAL_MAX_ATTEMPT", $this->object->getMaxAttempt());
		
		// version
		$this->tpl->setVariable("TXT_VERSION", $this->lng->txt("cont_sc_version"));
		$this->tpl->setVariable("VAL_VERSION", $this->object->getModuleVersion());
		
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* upload new version of module
	*/
	function newModuleVersion()
	{
		
	   $obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
	   $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
	  
	   // display import form
	   $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_new_version_import.html", "Modules/ScormAicc");
    
	   $this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
	   $this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));
    
	   $this->ctrl->setParameter($this, "new_type", "sahs");
	   $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
    
	   $this->tpl->setVariable("BTN_NAME", "newModuleVersionUpload");
	   $this->tpl->setVariable("TARGET", ' target="'.
	   	ilFrameTargetInfo::_getFrame("MainContent").'" ');
    
	   $this->tpl->setVariable("TXT_SELECT_LMTYPE", $this->lng->txt("type"));
	  
	   if ($type == "scorm2004") {
		   $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("lm_type_scorm2004"));
	   } else {
		   $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("lm_type_scorm"));
	   }    
	
	   $this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
	   $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
	   $this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_sahs"));
	   $this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
    
	   // gives out the limit as a little notice
	   $this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice")." ".$this->getMaxFileSize());
	}
	
	function getMaxFileSize()
	{
		
	   // get the value for the maximal uploadable filesize from the php.ini (if available)
	   $umf=get_cfg_var("upload_max_filesize");
	   // get the value for the maximal post data from the php.ini (if available)
	   $pms=get_cfg_var("post_max_size");
     
	   //convert from short-string representation to "real" bytes
	   $multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);
     
	   $umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
	   $pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
     
	   if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
	   if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }
     
	   // use the smaller one as limit
	   $max_filesize=min($umf, $pms);
     
	   if (!$max_filesize) $max_filesize=max($umf, $pms);
     
	   //format for display in mega-bytes
	   return $max_filesize=sprintf("%.1f MB",$max_filesize/1024/1024);
	}
	
	
	function newModuleVersionUpload()
	{
		global $_FILES, $rbacsystem;

		$unzip = PATH_TO_UNZIP;
		$tocheck = "imsmanifest.xml";
		
		// check if file was uploaded
		$source = $_FILES["scormfile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			ilUtil::sendInfo($this->lng->txt("No file selected"),true);
			$this->newModuleVersion();
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "sahs"))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}
		
		//unzip the imsmanifest-file from new uploaded file
		$pathinfo = pathinfo($source);
		$dir = $pathinfo["dirname"];
		$file = $pathinfo["basename"];
		$cdir = getcwd();
		chdir($dir);
		
		//we need more flexible unzip here than ILIAS standard classes allow
		$unzipcmd = $unzip." -o ".ilUtil::escapeShellArg($source)." ".$tocheck;
		exec($unzipcmd);
		chdir($cdir);
		$tmp_file = $dir."/".$tocheck.".".$_GET["ref_id"];
		
		rename($dir."/".$tocheck,$tmp_file);
		$new_manifest = file_get_contents($tmp_file);
		
		//remove temp file
		unlink($tmp_file);
		
		//get old manifest file	
		$old_manifest = file_get_contents($this->object->getDataDirectory()."/".$tocheck);
		//do testing for converted versions as well as earlier ILIAS version messed up utf8 conversion
		if ($new_manifest == $old_manifest || utf8_encode($new_manifest) == $old_manifest ){

			//get exisiting module version
			$module_version = $this->object->getModuleVersion();
			
			//build targetdir in lm_data
			$file_path = $this->object->getDataDirectory()."/".$_FILES["scormfile"]["name"].".".$module_version;
			
			//move to data directory and add subfix for versioning
			ilUtil::moveUploadedFile($_FILES["scormfile"]["tmp_name"],$_FILES["scormfile"]["name"], $file_path);
			
			//unzip and replace old extracted files
			ilUtil::unzip($file_path, true);
			ilUtil::renameExecutables($this->object->getDataDirectory()); //(security)
			
			//increase module version
			$this->object->setModuleVersion($module_version+1);
			$this->object->update();
			
			//redirect to properties and display success
			ilUtil::sendInfo( $this->lng->txt("cont_new_module_added"), true);
			ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=".$_GET["ref_id"]);
			exit;
		} else {
			ilUtil::sendInfo($this->lng->txt("cont_invalid_new_module"),true);
			$this->newModuleVersion();
		}
				
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
		$this->object->setMaxAttempt($_POST["max_attempt"]);
		$this->object->update();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}


	/**
	* show tracking data
	*/
	/*
	function showTrackingItems()
	{

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_items.html", "Modules/ScormAicc");

		$num = 1;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_tracking_items"));

		$tbl->setHeaderNames(array($this->lng->txt("title")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "baseClass"=>"ilSAHSEditGUI");
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
	*/
	/**
	* show tracking data
	*/
	function showTrackingItems()
	{

		include_once "./Services/Table/classes/class.ilTableGUI.php";
		
		//set search
		
		if ($_POST["search_string"] != "")
		{
			$_SESSION["scorm_search_string"] = trim($_POST["search_string"]);
		} else 	if (isset($_POST["search_string"]) && $_POST["search_string"] == "") {
			unset($_SESSION["scorm_search_string"]);
		}

		// load template for search additions
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl_scorm_track_items_search.html","Modules/ScormAicc");
		// load template for table
		$this->tpl->addBlockfile("USR_TABLE", "usr_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_items.html", "Modules/ScormAicc");

		$num = 1;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();
		
		// title & header columns
		if (isset($_SESSION["scorm_search_string"])) {
			$tbl->setTitle($this->lng->txt("cont_tracking_items").' - Aktive Suche: "'.$_SESSION["scorm_search_string"].'"');
		} else {
			$tbl->setTitle($this->lng->txt("cont_tracking_items"));
		}
		
		$tbl->setHeaderNames(array("",$this->lng->txt("name"), $this->lng->txt("last_access"), $this->lng->txt("attempts"), $this->lng->txt("version")  ));


		$header_params = $this->ctrl->getParameterArray($this, "showTrackingItems");
				
		$tbl->setColumnWidth(array("1%", "40%", "40%", "10%","10%"));
			
		$cols = array("","user_id","timestamp","attempts");
		$tbl->setHeaderVars($cols, $header_params);

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);
		
		$this->tpl->setVariable("COLUMN_COUNTS", 5);
		
		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "deleteTrackingForUser");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();
		
		// decrease attempts
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "decreaseAttempts");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("decrease_attempts"));
		$this->tpl->parseCurrentBlock();
		
		// export aggregated data for selected users
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "exportSelected");
		$this->tpl->setVariable("BTN_VALUE",  $this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();
			
		// add search and export all
		// export aggregated data for all users
		$this->tpl->setVariable("EXPORT_ACTION",$this->ctrl->getFormAction($this));
		
		$this->tpl->setVariable("EXPORT_ALL_VALUE", $this->lng->txt('cont_export_all'));
		$this->tpl->setVariable("EXPORT_ALL_NAME", "exportAll");
		$this->tpl->setVariable("IMPORT_VALUE", $this->lng->txt('import'));
		$this->tpl->setVariable("IMPORT_NAME", "Import");
		
		$this->tpl->setVariable("SEARCH_TXT_SEARCH",$this->lng->txt('search'));
		$this->tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_NAME",'showTrackingItems');
		if (isset($_SESSION["scorm_search_string"])) {
			$this->tpl->setVariable("STYLE",'display:inline;');
		} else {
			$this->tpl->setVariable("STYLE",'display:none;');
		}
		$this->tpl->setVariable("SEARCH_VAL", 	$_SESSION["scorm_search_string"]);
		$this->tpl->setVariable("SEARCH_VALUE",$this->lng->txt('search_users'));
		$this->tpl->parseCurrentBlock();
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$items = $this->object->getTrackedUsers($_SESSION["scorm_search_string"]);

		$tbl->setMaxCount(count($items));
		$items = array_slice($items, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		
		if (count($items) > 0)
		{
			foreach ($items as $item)
			{		
				if (ilObject::_exists($item["user_id"]) && ilObject::_lookUpType($item["user_id"])=="usr") 
				{	
					$user = new ilObjUser($item["user_id"]);
				     $this->tpl->setCurrentBlock("tbl_content");
				     $this->tpl->setVariable("VAL_USERNAME", $user->getLastname().", ".$user->getFirstname());
				     $this->tpl->setVariable("VAL_LAST", $item["last_access"]);
				     $this->tpl->setVariable("VAL_ATTEMPT", $this->object->getAttemptsForUser($item["user_id"]));
				     $this->tpl->setVariable("VAL_VERSION", $this->object->getModuleVersionForUser($item["user_id"]));
				     $this->ctrl->setParameter($this, "user_id", $item["user_id"]);
				     $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				     $this->tpl->setVariable("LINK_ITEM",
				     $this->ctrl->getLinkTarget($this, "showTrackingItem"));
				     $this->tpl->setVariable("CHECKBOX_ID", $item["user_id"]);
				     $css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				     $this->tpl->setVariable("CSS_ROW", $css_row);
				     $this->tpl->parseCurrentBlock();
				}	
			}
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
			
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
		
	}
	
	function resetSearch() {
		unset($_SESSION["scorm_search_string"]);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	
	function deleteTrackingForUser()
	{
	 	global $ilDB, $ilUser;
    
	 	if (!isset($_POST["user"]))
	 	{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
			exit;
	 	}
    
	 	foreach ($_POST["user"] as $user)
	 	{
   	 		$query = "DELETE FROM scorm_tracking WHERE".
	 			" user_id = ".$ilDB->quote($user).
	 			" AND obj_id = ".$ilDB->quote($this->object->getID());
	 		$ret = $ilDB->query($query);
	 		
	 	}
    
	 	//$this->ctrl->saveParameter($this, "cdir");
	 	$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* overwrite..jump back to trackingdata not parent
	*/
	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
	 	$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	
	/**
	* gui functions for GUI export
	*/
	
	function import()
	{	
		if (!isset($_POST["type"])) {
			//show form
			$this->importForm();
		} else {
			//
			// check if file was uploaded
			$source = $_FILES["datafile"]["tmp_name"];
			if (($source == 'none') || (!$source))
			{
				ilUtil::sendInfo($this->lng->txt("No file selected!"),true);
				$this->importForm();
			} else {
				$error = $this->object->importTrackingData($source);
				switch ($error) {
					case 0 :
						ilUtil::sendInfo($this->lng->txt("Trackingdata imported"),true);
					 	$this->ctrl->redirect($this, "showTrackingItems");
						break;
					case -1 :
						ilUtil::sendInfo($this->lng->txt("Invalid import file"),true);
						$this->importForm();
						break;	
				}
			}
		}		
	}		
	
	
	function importForm(){
	 	$obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
	    $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
       
	    // display import form
	    $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_tracking_data_import.html", "Modules/ScormAicc");
       
	    $this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
	    $this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));
       
	    $this->ctrl->setParameter($this, "new_type", "sahs");
	    $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
       
	    $this->tpl->setVariable("BTN_NAME", "import");
	    $this->tpl->setVariable("TARGET", ' target="'.
	    	ilFrameTargetInfo::_getFrame("MainContent").'" ');
       
	    $this->tpl->setVariable("TXT_SELECT_LMTYPE", $this->lng->txt("type"));
       
	    $this->tpl->setVariable("TXT_TYPE","CSV");
       
	    $this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
	    $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
	    $this->tpl->setVariable("TXT_IMPORT_TRACKING", $this->lng->txt("cont_import_tracking"));
	    $this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
		// gives out the limit as a little notice
	   	$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice")." ".$this->getMaxFileSize());
	}
	
	
	function exportAll(){
		$this->export(1);
	}
	
	function exportSelected()
	{
		if (!isset($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, "showTrackingItems");
		} else {
			$this->export(0);
		}	
	}
	
	function export($a_export_all = 0)
	{	
		if (!isset($_POST["export_type"])) {
			//show form
			$this->exportOptions($a_export_all,$_POST["user"]);
		} else {
			if (isset($_POST["cancel"])) {
				$this->ctrl->redirect($this, "showTrackingItems");
			} else {
				$a_export_all = $_POST["export_all"];
				if ($_POST["export_type"]=="raw") {
					$this->object->exportSelectedRaw($a_export_all, unserialize(stripslashes($_POST["user"])));
				} else {
					$this->object->exportSelected($a_export_all, unserialize(stripslashes($_POST["user"])));	
				}
			}
		}
	}
	
	
	function exportOptions($a_export_all=0, $a_users)
	{
	  	$obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
	    $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
      
	    // display import form
	    $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_tracking_data_export.html", "Modules/ScormAicc");
      
	    $this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
	    $this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));
      
	    $this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("cont_export_options"));

	    $this->ctrl->setParameter($this, "new_type", "sahs");
	    $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
	
	    $this->tpl->setVariable("BTN_NAME", "export");
		
	    $this->tpl->setVariable("TARGET", ' target="'.
	    	ilFrameTargetInfo::_getFrame("MainContent").'" ');
      
	    $this->tpl->setVariable("TXT_SELECT_TYPE", $this->lng->txt("cont_export_type"));
	    $this->tpl->setVariable("TXT_EXPORT_RAW", $this->lng->txt("cont_export_raw"));
	    $this->tpl->setVariable("TXT_EXPORT_SUCCESS", $this->lng->txt("cont_export_success"));
	    $this->tpl->setVariable("TXT_EXPORT_TRACKING", $this->lng->txt("cont_export_tracking"));
	
	    $this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
	    $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));	
	    $this->tpl->setVariable("VAL_USER", htmlentities(serialize($a_users)));	
	    $this->tpl->setVariable("VAL_EXPORTALL",$a_export_all);		
		
		
	}
	
	
	
	function decreaseAttempts()
	{
		global $ilDB, $ilUser;
		
		if (!isset($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		}
		
		foreach ($_POST["user"] as $user)
		{
			//first check if there is a package_attempts entry

			//get existing account - sco id is always 0
			$query = "SELECT * FROM scorm_tracking WHERE".
				" user_id = ".$ilDB->quote($user).
				" AND sco_id = 0".
				" AND lvalue='package_attempts'".
				" AND obj_id = ".$ilDB->quote($this->object->getID());

			$val_set = $ilDB->query($query);
			$val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC);
			$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
			if ($val_rec["rvalue"] != null && $val_rec["rvalue"] != 0) {
				$new_rec =  $val_rec["rvalue"]-1;
				//decrease attempt by 1
				$query = "REPLACE INTO scorm_tracking (rvalue,user_id,sco_id,obj_id,lvalue) values(".
			 		$ilDB->quote($new_rec).",".
					$ilDB->quote($user).",".
					" 0,".
					$ilDB->quote($this->object->getID()).",".
					$ilDB->quote("package_attempts").")";

				$val_set = $ilDB->query($query);
			}
		}

		//$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	
	/**
	* show tracking data of item
	*/
	function showTrackingItem()
	{

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_item.html", "Modules/ScormAicc");

		$num = 2;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
		$sc_item =& new ilSCORMItem($_GET["obj_id"]);

		// title & header columns
		$user = new ilObjUser( $_GET["user_id"]);
		$tbl->setTitle($user->getLastname().", ".$user->getFirstname());

		$tbl->setHeaderNames(array($this->lng->txt("title"),
			$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
			$this->lng->txt("cont_score")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"], "baseClass"=>"ilSAHSEditGUI", 'user_id'=>$_GET["user_id"]);
		$cols = array("title", "status", "time", "score");
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

		$tr_data = $this->object->getTrackingDataAgg($_GET["user_id"]);

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($tr_data));
		$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if (count($tr_data) > 0)
		{
			foreach ($tr_data as $data)
			{
					$this->tpl->setCurrentBlock("tbl_content");
					$this->tpl->setVariable("VAL_TITLE", $data["title"]);
					$this->ctrl->setParameter($this, "user_id",  $_GET["user_id"]);
					$this->ctrl->setParameter($this, "obj_id",  $data["sco_id"]);
					
					$this->tpl->setVariable("LINK_SCO",
						$this->ctrl->getLinkTarget($this, "showTrackingItemPerUser"));
					$this->tpl->setVariable("VAL_TIME", $data["time"]);
					$this->tpl->setVariable("VAL_STATUS", $data["status"]);
					$this->tpl->setVariable("VAL_SCORE", $data["score"]);
	
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

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_item_per_user.html", "Modules/ScormAicc");

		$num = 2;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
		$sc_item =& new ilSCORMItem($_GET["obj_id"]);
		$user = new ilObjUser($_GET["user_id"]);

		// title & header columns
		$tbl->setTitle($sc_item->getTitle()." - ".$user->getLastname().", ".$user->getFirstname());

		$tbl->setHeaderNames(array($this->lng->txt("cont_lvalue"), $this->lng->txt("cont_rvalue")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"],
			"user_id" => $_GET["user_id"],"baseClass"=>"ilSAHSEditGUI");
		$cols = array("lvalue", "rvalue");
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

} // END class.ilObjSCORMLearningModule
?>
