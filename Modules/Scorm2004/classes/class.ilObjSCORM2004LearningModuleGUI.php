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
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjSCORMLearningModuleGUI.php 13133 2007-01-30 11:13:06Z akill $
*
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI
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

	$header_params = $this->ctrl->getParameterArray($this, "showTrackingItems");

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
			$this->tpl->setVariable("TXT_ITEM_TITLE", $item['title']);
			$this->ctrl->setParameter($this, "obj_id", $item['id']);
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
**
* show tracking data of item
*/
function showTrackingItem()
{

	include_once "./Services/Table/classes/class.ilTableGUI.php";

	// load template for table
	$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
	// load template for table content data
	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm2004_track_item.html", "Modules/Scorm2004");

	$num = 2;

	$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

	// create table
	$tbl = new ilTableGUI();

	include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");

	// title & header columns
	$tbl->setTitle(ilObjSCORM2004LearningModule::_lookupItemTitle($_GET["obj_id"]));

	$tbl->setHeaderNames(array($this->lng->txt("name"),
	$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
	$this->lng->txt("cont_score"), $this->lng->txt("cont_last_access")));

	$header_params = $this->ctrl->getParameterArray($this, "showTrackingItem");

	$cols = array("name", "status", "time", "score");
	$tbl->setHeaderVars($cols, $header_params);
	//$tbl->setColumnWidth(array("25%",));

	// control
	$tbl->setOrderColumn($_GET["sort_by"]);
	$tbl->setOrderDirection($_GET["sort_order"]);
	$tbl->setLimit($_GET["limit"]);
	$tbl->setOffset($_GET["offset"]);
	$tbl->setMaxCount($this->maxcount);

	// footer
	$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
	#$tbl->disable("footer");

	$tr_data = $this->object->getTrackingDataAgg($_GET["obj_id"]);

	$tbl->setMaxCount(count($tr_data));
	$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

	$tbl->render();
	if (count($tr_data) > 0)
	{
		foreach ($tr_data as $data)
		{
			if (ilObject::_exists($data["user_id"]))
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$user = new ilObjUser($data["user_id"]);
				$this->tpl->setVariable("VAL_USERNAME", $user->getLastname().", ".
					$user->getFirstname());
				$this->ctrl->setParameter($this, "user_id", $data["user_id"]);
				$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				/*$this->tpl->setVariable("LINK_USER",
					$this->ctrl->getLinkTarget($this, "showTrackingItemPerUser"));*/
				$this->tpl->setVariable("VAL_TIME", $data["time"]);
				$this->tpl->setVariable("VAL_STATUS", $data["status"]);
				$this->tpl->setVariable("VAL_SCORE", $data["score"]);
				
				//convert last access to local value
				$this->tpl->setVariable("VAL_TIMESTAMP",$data["last_access"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}
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
