<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once  './Services/Search/classes/class.ilSearchSettings.php';

/**
* TableGUI class for learning progress
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLPProgressTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPProgressTableGUI extends ilTable2GUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_user = "", $obj_ids = NULL, $details = false, $objectives_mode = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->tracked_user = $a_user;
		$this->obj_ids = $obj_ids;
		$this->objectives_mode = $objectives_mode;
		$this->details = $details;

		$this->setId("lp_table");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setLimit(ilSearchSettings::getInstance()->getMaxHits());

		if(!$this->details)
		{
			$this->addColumn("", "", "1", true);
			$this->addColumn($this->lng->txt("trac_title"), "title", "26%");
			$this->addColumn($this->lng->txt("status"), "status", "7%");
			$this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
			$this->addColumn($this->lng->txt("trac_mark"), "", "5%");
			$this->addColumn($this->lng->txt("comment"), "", "10%");
			$this->addColumn($this->lng->txt("trac_mode"), "", "20%");
			$this->addColumn($this->lng->txt("path"), "", "20%");
			$this->addColumn($this->lng->txt("actions"), "", "5%");

			$this->setTitle($this->lng->txt("learning_progress"));
			$this->initFilter();

			$this->setSelectAllCheckbox("item_id");
			$this->addMultiCommand("hideSelected", $lng->txt("trac_hide_selected"));
		}
		else
		{
			$this->addColumn($this->lng->txt("trac_title"), "title", "31%");
			$this->addColumn($this->lng->txt("status"), "status", "7%");
			$this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
			$this->addColumn($this->lng->txt("trac_mark"), "", "5%");
			$this->addColumn($this->lng->txt("comment"), "", "10%");
			$this->addColumn($this->lng->txt("trac_mode"), "", "20%");
			$this->addColumn($this->lng->txt("path"), "", "20%");
		}
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.lp_progress_list_row.html", "Services/Tracking");
		$this->setEnableHeader(true);
		$this->setEnableNumInfo(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		// area selector gets in the way
		if($this->tracked_user)
		{
			$this->getItems();
		}
	}

	function getItems()
	{
		$this->filter = $this->getCurrentFilter(true);
		$obj_ids = $this->obj_ids;
		if(!$obj_ids && !$this->details)
	    {
			$obj_ids = $this->searchObjects($this->filter);
		}
		if($obj_ids)
		{
			include_once("./Services/Tracking/classes/class.ilTrQuery.php");
			if(!$this->objectives_mode)
			{
				$data = ilTrQuery::getObjectsStatusForUser($this->tracked_user->getId(), $obj_ids);
			}
			else
			{
				$data = ilTrQuery::getObjectivesStatusForUser($this->tracked_user->getId(), $obj_ids);
			}
			$this->setData($data);
		}
	}

	function getCurrentFilter($as_query = false)
	{
		include_once("./Services/Tracking/classes/class.ilLPFilterGUI.php");
		$filter_gui = new ilLPFilterGUI($this->tracked_user);

		$filter = array();
		$filter["type"] = $filter_gui->filter->getFilterType();
		$filter["hidden"] = $filter_gui->prepareHidden();
		$filter["title"] = $filter_gui->filter->getQueryString();

		if($as_query)
		{
			switch($filter["type"])
			{
				case 'lm':
					$filter["type"] = array('lm','sahs','htlm','dbk');
					break;

				default:
					$filter["type"] = array($filter["type"]);
					break;
			}

			$filter["hidden"] = array_keys($filter["hidden"]);
		}

		return $filter;
	}

	function searchObjects(array $filter)
	{
		global $ilObjDataCache;

		include_once './Services/Search/classes/class.ilQueryParser.php';

		$query_parser =& new ilQueryParser($filter["title"]);
		$query_parser->setMinWordLength(0);
		$query_parser->setCombination(QP_COMBINATION_OR);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			// echo $query_parser->getMessage();
			return false;
		}

		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search =& new ilLikeObjectSearch($query_parser);
		$object_search->setFilter($filter["type"]);
		$res =& $object_search->performSearch();
		$res->setRequiredPermission("read");

		$res->addObserver($this, "searchFilterListener");
	    $res->filter(ROOT_FOLDER_ID, false);

		$objects = array();
		foreach($res->getResults() as $obj_data)
		{
			$objects[$obj_data['obj_id']][] = $obj_data['ref_id'];
		}

		// Check if search max hits is reached
		$this->limit_reached = $res->isLimitReached();

		return $objects ? $objects : array();
	}

	/**
	 * Listener for SearchResultFilter
	 * Checks wheather the object is hidden and mode is not LP_MODE_DEACTIVATED
	 * @access public
	 */
	function searchFilterListener($a_ref_id, $a_data)
	{
		global $ilUser;
		
		if($this->tracked_user->getId() != $ilUser->getId())
		{
			switch($a_data["type"])
			{
				case 'tst':
					include_once './Modules/Test/classes/class.ilObjTest.php';
					if(ilObjTest::_lookupAnonymity($a_data["obj_id"]))
					{
						return false;
					}
			}
		}
		if(is_array($this->filter["hidden"]) && in_array($a_data["obj_id"], $this->filter["hidden"]))
		{
			return false;
		}
		// :TODO: mode does not have to be set in db
		if(ilLPObjSettings::_lookupMode($a_data["obj_id"]) == LP_MODE_DEACTIVATED)
		{
			return false;
		}
		return true;
	}
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;
		
		$this->setDisableFilterHiding(true);
		
		$filter = $this->getCurrentFilter();
		
		// object type selection
		include_once("./Services/Tracking/classes/class.ilLPFilterGUI.php");
		$options = ilLPFilterGUI::getPossibleTypes();
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($this->lng->txt("obj_type"), "type");
		$si->setOptions($options);
		$si->setValue($filter["type"]);
		$this->addFilterItem($si);

		// hidden items
		$options = $filter["hidden"];
		$values = array_keys($options);
		if (count($options) > 0)
		{
			include_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
			$msi = new ilMultiSelectInputGUI($lng->txt("trac_filter_hidden"), "hide");
			$msi->setValue($values);
			$msi->setOptions($options);
			$this->addFilterItem($msi);
		}
		else
		{
			include_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");
			$ne = new ilNonEditableValueGUI($lng->txt("trac_filter_hidden"),
				"dummy");
			$ne->setValue($lng->txt("none"));
			$this->addFilterItem($ne);
		}

		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("trac_title_description"), "query");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValue($filter["title"]);
		$this->addFilterItem($ti);
		
		// repository area selection
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
		$rs = new ilRepositorySelectorInputGUI($lng->txt("trac_filter_area"), "area");
		$rs->setSelectText($lng->txt("trac_select_area"));
		$this->addFilterItem($rs);
		$rs->readFromSession();
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilObjDataCache, $ilCtrl;

		if(!$this->details)
		{
			$this->tpl->setCurrentBlock("column_checkbox");
			$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("ICON_SRC", ilUtil::getTypeIconPath($a_set["type"], $a_set["obj_id"], "small"));
		$this->tpl->setVariable("ICON_ALT", $lng->txt($a_set["type"]));
		$this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
		$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf("%d%%", $a_set["percentage"]));

		$this->tpl->setVariable("STATUS_ALT", ilLearningProgressBaseGUI::_getStatusText($a_set["status"]));
		$this->tpl->setVariable("STATUS_IMG", ilLearningProgressBaseGUI::_getImagePathForStatus($a_set["status"]));

		$this->tpl->setVariable("MODE_TEXT", ilLPObjSettings::_mode2Text($a_set["u_mode"]));
		$this->tpl->setVariable("MARK_VALUE", $a_set["mark"]);
		$this->tpl->setVariable("COMMENT_TEXT", $a_set["comment"]);

		// path
		$path = $this->buildPath($a_set["ref_ids"]);
		if($path)
		{
			$this->tpl->setCurrentBlock("item_path");
			foreach($path as $path_item)
			{
				$this->tpl->setVariable("PATH_ITEM", $path_item);
				$this->tpl->parseCurrentBlock();
			}
		}

		// tlt warning
		if($a_set["status"] != LP_STATUS_COMPLETED_NUM)
		{
			$ref_id = $a_set["ref_ids"];
			$ref_id = array_shift($ref_id);
			include_once 'Modules/Course/classes/Timings/class.ilTimingCache.php';
			if(ilCourseItems::_hasCollectionTimings($ref_id) && ilTimingCache::_showWarning($ref_id, $this->tracked_user->getId()))
			{
				$this->tpl->setCurrentBlock('warning_img');
				$this->tpl->setVariable('WARNING_IMG', ilUtil::getImagePath('warning.gif'));
				$this->tpl->setVariable('WARNING_ALT', $lng->txt('trac_time_passed'));
				$this->tpl->parseCurrentBlock();
			}
		}

		// hide / unhide?!
		if(!$this->details)
		{
			$this->tpl->setCurrentBlock("item_command");
			$ilCtrl->setParameterByClass('illpfiltergui','hide', $a_set["obj_id"]);
			$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass('illpfiltergui','hide'));
			$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_hide'));
			$this->tpl->parseCurrentBlock();

			if(ilLPObjSettings::_isContainer($a_set["u_mode"]))
			{
				$ref_id = $a_set["ref_ids"];
				$ref_id = array_shift($ref_id);
				$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', $ref_id);
				$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), 'details'));
				$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', '');
				$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_subitems'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("column_action");
			$this->tpl->parseCurrentBlock();
		}
	}

	function buildPath($ref_ids)
	{
		global $tree, $ilCtrl;

		include_once 'classes/class.ilLink.php';
		
		if(!count($ref_ids))
		{
			return false;
		}
		foreach($ref_ids as $ref_id)
		{
			$path = "...";
			$counter = 0;
			$path_full = $tree->getPathFull($ref_id);
			foreach($path_full as $data)
			{
				if(++$counter < (count($path_full)-1))
				{
					continue;
				}
				$path .= " &raquo; ";
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{
					$path .= ('<a target="_top" href="'.
							  ilLink::_getLink($data['ref_id'],$data['type']).'">'.
							  $data['title'].'</a>');
				}
			}

			$result[] = $path;
		}
		return $result;
	}
}
?>
