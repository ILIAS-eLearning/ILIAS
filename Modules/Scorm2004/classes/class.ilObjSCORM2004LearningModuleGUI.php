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

require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id: class.ilObjSCORMLearningModuleGUI.php 13133 2007-01-30 11:13:06Z akill $
*
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilCertificateGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORM2004LearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$lng->loadLanguageModule("search");	
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();
	}


	/**
	* scorm 2004 module properties
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm2004_properties.html", "Modules/Scorm2004");
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
	* save scorm 2004 module properties
	*/
	function saveProperties()
	{
		$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
		$this->object->setCreditMode($_POST["credit_mode"]);
		$this->object->setMaxAttempt($_POST["max_attempt"]);
		
		$this->object->setDefaultLessonMode($_POST["lesson_mode"]);
		$this->object->update();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
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
				$this->object =& new ilObjSCORM2004LearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, false);
			}
		}
	}
	
/*

	function showTrackingItems()
	{
		global $lng, $tpl;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004TrackingTableGUI.php");
		$table_gui = new ilSCORM2004TrackingTableGUI($this, "showTrackingItems");
				
		$tr_data_sets = $this->object->getTrackedUsers();
		$table_gui->setTitle($lng->txt("cont_tracking_data"));
		$table_gui->setData($tr_data_sets);
		$tpl->setContent($table_gui->getHTML());
	}
*/
/**
* show tracking data
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
			
	$tbl->setColumnWidth(array("1%", "50%", "29%", "10%","10%"));
		
	$cols = array("user_id","username","last_access","attempts","version");
	$tbl->setHeaderVars($cols, $header_params);

	//set defaults
	$_GET["sort_order"] = $_GET["sort_order"] ? $_GET["sort_order"] : "asc";
	$_GET["sort_by"] = $_GET["sort_by"] ? $_GET["sort_by"] : "username";

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
	$items  = ilUtil::sortArray($items ,$_GET["sort_by"],$_GET["sort_order"]);
	$items = array_slice($items, $_GET["offset"], $_GET["limit"]);

	$tbl->render();
	
	if (count($items) > 0)
	{
		foreach ($items as $item)
		{		
			if (ilObject::_exists($item["user_id"])  && ilObject::_lookUpType($item["user_id"])=="usr") 
			{	
				$user = new ilObjUser($item["user_id"]);
			     $this->tpl->setCurrentBlock("tbl_content");
			     $this->tpl->setVariable("VAL_USERNAME", $item["username"]);
			     $this->tpl->setVariable("VAL_LAST", $item["last_access"]);
			     $this->tpl->setVariable("VAL_ATTEMPT", $item["attempts"]);
			     $this->tpl->setVariable("VAL_VERSION", $version['version']);
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


function exportAll(){
	$this->object->exportSelected(1);
}

function exportSelected()
{
	if (!isset($_POST["user"]))
	{
		ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	} else {
		$this->object->exportSelected(0);
	}	
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
	* display deletion confirmation screen
	*/
	function deleteTrackingForUser()
	{
		if(!isset($_POST["user"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["scorm_user_delete"] = $_POST["user"];

		unset($this->data);
		$this->data["cols"] = array("type","title", "description");

		foreach($_POST["user"] as $id)
		{
			if (ilObject::_exists($id) && ilObject::_lookUpType($id)=="usr" ) {	
				$user = new ilObjUser($id);
				$this->data["data"]["$id"] = array(
					"type"		  => "sahs",
					"title"       => $user->getLastname().", ".$user->getFirstname(),
					"desc"        => $this->lng->txt("cont_tracking_data")
				);
			}
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function resetSearch() {
		unset($_SESSION["scorm_search_string"]);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* cancel deletion of export files
	*/
	function cancelDelete()
	{
		session_unregister("scorm_user_delete");
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	function confirmedDelete()
	{
	 	global $ilDB, $ilUser;
    
    	$scos = array();

		//get all SCO's of this object		
		$query = "SELECT cp_node_id FROM cp_node WHERE".
				" nodeName='item' AND cp_node.slm_id = ".$ilDB->quote($this->object->getId());		
								
		$val_set = $ilDB->query($query);
		while ($val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($scos,$val_rec['cp_node_id']);
		}
		
	 	foreach ($_SESSION["scorm_user_delete"] as $user)
	 	{
		
			foreach ($scos as $sco)
			{
   	 			$query = "DELETE FROM cmi_node WHERE".
	 			" user_id = ".$ilDB->quote($user).
	 			" AND cp_node_id = ".$ilDB->quote($sco);
	 			$ret = $ilDB->query($query);
 			}
	 	}
    
	 	$this->ctrl->redirect($this, "showTrackingItems");
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
			$query = "SELECT * FROM cmi_custom WHERE".
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
				$query = "REPLACE INTO cmi_custom (rvalue,user_id,sco_id,obj_id,lvalue) values(".
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
	
	function deleteTrackingData()
	{
		if (is_array($_POST["id"]))
		{
			$this->object->deleteTrackingDataOfUsers($_POST["id"]);
		}
		$this->showTrackingItems();
	}

} // END class.ilObjSCORM2004LearningModuleGUI
?>
