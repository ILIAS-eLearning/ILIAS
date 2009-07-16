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
require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
require_once "./Modules/ScormAicc/classes/class.ilObjAICCCourseInterchangeFiles.php";
require_once("./Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php");

/**
* Class ilObjAICCLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjAICCLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjAICCLearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjAICCLearningModuleGUI: ilLicenseGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjAICCLearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjAICCLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;
		
		$lng->loadLanguageModule("content");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		#$this->tabs_gui =& new ilTabsGUI();

	}


	/**
	* assign aicc object to aicc gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjAICCLearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjAICCLearningModule($this->id, false);
			}
		}
	}

//	/**
//	* save new learning module to db
//	*/
//	function saveObject()
//	{
//		global $rbacadmin;
//
//		$this->uploadObject();
//
//		ilUtil::sendInfo($this->lng->txt("alm_added"), true);
//		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
//
//	}

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
	* show tracking data
	*/
	function showTrackingItem()
	{
		parent::showTrackingItem();
	}
		

} // END class.ilObjAICCLearningModule
?>
