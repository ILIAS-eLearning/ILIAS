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
require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php");
require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
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
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}

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
		$tbl->setTitle($sc_item->getTitle());

		$tbl->setHeaderNames(array($this->lng->txt("name"),
		$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
		$this->lng->txt("cont_score")));

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
				if (ilObject::_exists($data["user_id"]))
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
					$this->tpl->setVariable("VAL_SCORE", $data["score"]);
	
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

		// title & header columns
		$tbl->setTitle($sc_item->getTitle());

		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),
			$this->lng->txt("cont_lvalue"), $this->lng->txt("cont_rvalue")));

		$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
			"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"],
			"user_id" => $_GET["user_id"]);
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

} // END class.ilObjSCORMLearningModule
?>
