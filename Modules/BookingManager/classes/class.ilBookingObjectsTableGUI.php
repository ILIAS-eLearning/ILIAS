<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List booking objects (for booking type)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObjectsTableGUI extends ilTable2GUI
{
	protected $ref_id; // [int]
	protected $pool_id;	// [int]
	protected $has_schedule;	// [bool]
	protected $may_edit;	// [bool]
	protected $overall_limit;	// [int]
	protected $reservations = array();	// [array]
	protected $current_bookings; // [int]
	protected $advmd; // [array]
	protected $filter; // [array]
	
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	bool	$a_pool_has_schedule
	 * @param	int		$a_pool_overall_limit
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_pool_has_schedule, $a_pool_overall_limit)
	{
		global $ilCtrl, $lng, $ilAccess;

		$this->ref_id = $a_ref_id;
		$this->pool_id = $a_pool_id;
		$this->has_schedule = $a_pool_has_schedule;
		$this->overall_limit = $a_pool_overall_limit;
		$this->may_edit = $ilAccess->checkAccess('write', '', $this->ref_id);
		
		$this->advmd = ilObjBookingPool::getAdvancedMDFields($this->pool_id);
		
		$this->setId("bkobj");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_objects_list"));

		// $this->setLimit(9999);		
		
		$this->addColumn($this->lng->txt("title"), "title");
		
		$cols = $this->getSelectableColumns();
		foreach($this->getSelectedColumns() as $col)
		{
			$this->addColumn($cols[$col]["txt"], $col);
		}
		
		if(!$this->has_schedule)
		{
			$this->addColumn($this->lng->txt("available"));
		}

		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_object_row.html", "Modules/BookingManager");
		
		$this->initFilter();
		$this->getItems();
	}
		
	/**
	 * needed for advmd filter handling
	 * 
	 * @return ilAdvancedMDRecordGUI
	 */
	protected function getAdvMDRecordGUI()
	{
		// #16827
		return $this->record_gui;
	}
	
	function initFilter()
	{		
		global $lng;
		
		/* 
		// preset period from parameters, e.g. course period
		// currently NOT active 
		if(trim($_GET["pf"]) || 
			trim($_GET["pt"]))
		{						
			$_SESSION["form_".$this->getId()]["period"] = serialize(array(
				"from" => $_GET["pf"] 
					? serialize(new ilDateTime(trim($_GET["pf"]), IL_CAL_DATE))
					: "",
				"to" =>  $_GET["pt"] 
					? serialize(new ilDateTime(trim($_GET["pt"]), IL_CAL_DATE))
					: "",
			));			
		}
		*/
		
		// title/description
		$title = $this->addFilterItemByMetaType(
			"title", 
			ilTable2GUI::FILTER_TEXT, 
			false, 
			$lng->txt("title")."/".$lng->txt("description")
		);		
		$this->filter["title"] = $title->getValue();
		
		// booking period
		$period = $this->addFilterItemByMetaType(
			"period", 
			ilTable2GUI::FILTER_DATE_RANGE,
			false,
			$lng->txt("book_period")
		);
		$this->filter["period"] = $period->getValue();
	}
	
	/**
	 * Gather data and build rows
	 */
	function getItems()
	{		
		global $ilUser;
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$data = ilBookingObject::getList($this->pool_id, $this->filter["title"]);
		
		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		
		// check schedule availability
		if($this->has_schedule)
		{			
			$now = time();
			$limit = strtotime("+1year");
			foreach($data as $idx => $item)
			{
				$schedule = new ilBookingSchedule($item["schedule_id"]);
				$av_from = ($schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull())
					? $schedule->getAvailabilityFrom()->get(IL_CAL_UNIX)
					: null;
				$av_to = ($schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull())
					? strtotime($schedule->getAvailabilityTo()->get(IL_CAL_DATE)." 23:59:59")
					: null;
				if(($av_from && $av_from > $limit) ||
					($av_to && $av_to < $now))
				{
					unset($data[$idx]);
				}
				if($av_from > $now)
				{
					$data[$idx]["not_yet"] = ilDatePresentation::formatDate(new ilDate($av_from, IL_CAL_UNIX));
				}
			}
		}
		
		foreach($data as $item)
		{
			$item_id = $item["booking_object_id"];
			
			// available for given period?
			if(is_object($this->filter["period"]["from"]) ||
				is_object($this->filter["period"]["to"]))
			{
				$from = is_object($this->filter["period"]["from"])
					? strtotime($this->filter["period"]["from"]->get(IL_CAL_DATE)." 00:00:00")
					: null;
				$to = is_object($this->filter["period"]["to"]) 
					? strtotime($this->filter["period"]["to"]->get(IL_CAL_DATE)." 23:59:59")
					: null;
								
				$bobj = new ilBookingObject($item_id);
				$schedule = new ilBookingSchedule($bobj->getScheduleId());			
			
				if(!ilBookingReservation::isObjectAvailableInPeriod($item_id, $schedule, $from, $to))
				{
					unset($data[$idx]);
					continue;
				}
			}
			
			// cache reservations
			$item_rsv = ilBookingReservation::getList(array($item_id), 1000, 0, array());
			$this->reservations[$item_id] = $item_rsv["data"];
		}				
		
		if(!$this->has_schedule && 
			$this->overall_limit)		
		{	
			$this->current_bookings = 0;
			foreach($this->reservations as $obj_rsv)
			{
				foreach($obj_rsv as $item)
				{
					if($item["status"] != ilBookingReservation::STATUS_CANCELLED)
					{						
						if($item["user_id"] == $ilUser->getId())
						{
							$this->current_bookings++;
						}
					}
				}
			}			
			
			if($this->current_bookings >= $this->overall_limit)
			{
				ilUtil::sendInfo($this->lng->txt("book_overall_limit_warning"));
			}
		}
		
		if($this->advmd)
		{						
			// advanced metadata
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
			$this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_FILTER, "book", $this->pool_id, "bobj");
			$this->record_gui->setTableGUI($this);
			$this->record_gui->parse();
			
			include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
			$data = ilAdvancedMDValues::queryForRecords($this->pool_id, "bobj", $data, "pool_id", "booking_object_id", $this->record_gui->getFilterElements());
		}
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}
	
	function numericOrdering($a_field)
	{
		if (substr($a_field, 0, 3) == "md_")
		{
			$md_id = (int) substr($a_field, 3);
			if ($this->advmd[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE)
			{
				return true;
			}
		}
		return false;
	}
	
	function getSelectableColumns()
	{
		$cols = array();
		
		$cols["description"] = array(
			"txt" => $this->lng->txt("description"),
			"default" => true
		);
		
		foreach($this->advmd as $field)
		{
			$cols["advmd".$field["id"]] = array(
				"txt" => $field["title"],
				"default" => false
			);
		}
		
		return $cols;
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilUser;
		
		$has_booking = false;
		$booking_possible = true;
		$has_reservations = false;
		
		$selected = $this->getSelectedColumns();

	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		
	   if(in_array("description", $selected))
		{
			$this->tpl->setVariable("TXT_DESC", nl2br($a_set["description"]));
		}
		
		if($a_set["not_yet"])
		{
			$this->tpl->setVariable("NOT_YET", $a_set["not_yet"]);
		}
		
		if(!$this->has_schedule)		
		{												
			$cnt = 0;						
			foreach($this->reservations[$a_set["booking_object_id"]] as $item)
			{			
				if($item["status"] != ilBookingReservation::STATUS_CANCELLED)
				{
					$cnt++;
				
					if($item["user_id"] == $ilUser->getId())
					{
						$has_booking = true;
					}
					
					$has_reservations = true;
				}
			}
			
			$this->tpl->setVariable("VALUE_AVAIL", $a_set["nr_items"]-$cnt); 
			$this->tpl->setVariable("VALUE_AVAIL_ALL", $a_set["nr_items"]); 

			if($a_set["nr_items"] <= $cnt || $has_booking 
				|| ($this->overall_limit && $this->current_bookings && $this->current_bookings >= $this->overall_limit))
			{
				$booking_possible = false;
			}			
		}
		else if(!$this->may_edit)
		{							
			foreach($this->reservations[$a_set["booking_object_id"]] as $item)
			{			
				if($item["status"] != ilBookingReservation::STATUS_CANCELLED &&
					$item["user_id"] == $ilUser->getId())
				{
					$has_booking = true;
				}				
			}
		}
		
		$items = array();
		
		$ilCtrl->setParameter($this->parent_obj, 'object_id', $a_set['booking_object_id']);
		
		if($booking_possible)
		{
			if(is_object($this->filter['period']['from']))
			{
				$ilCtrl->setParameter($this->parent_obj, 'sseed', $this->filter['period']['from']->get(IL_CAL_DATE));
			}
			
			$items['book'] = array($lng->txt('book_book'), $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
			
			$ilCtrl->setParameter($this->parent_obj, 'sseed', '');
		}
		
		// #16663
		if(!$this->has_schedule && $has_booking)
		{						
			if(trim($a_set['post_text']) || $a_set['post_file'])
			{
				$items['post'] = array($lng->txt('book_post_booking_information'), $ilCtrl->getLinkTarget($this->parent_obj, 'displayPostInfo'));
			}	
			
			$items['cancel'] = array($lng->txt('book_set_cancel'), $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancelUser'));								
		}
			
		if($this->may_edit || $has_booking)
		{
			$ilCtrl->setParameterByClass('ilObjBookingPoolGUI', 'object_id', $a_set['booking_object_id']);
			$items['log'] = array($lng->txt('book_log'), $ilCtrl->getLinkTargetByClass('ilObjBookingPoolGUI', 'log'));				
			$ilCtrl->setParameterByClass('ilObjBookingPoolGUI', 'object_id', '');
		}

		if($a_set['info_file'])
		{
			$items['info'] = array($lng->txt('book_download_info'), $ilCtrl->getLinkTarget($this->parent_obj, 'deliverInfo'));
		}	
		
		if ($this->may_edit)
		{			
			$items['edit'] = array($lng->txt('edit'), $ilCtrl->getLinkTarget($this->parent_obj, 'edit'));
			
			// #10890
			if(!$has_reservations)
			{
				$items['delete'] = array($lng->txt('delete'), $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
			}
		}
		
		if($this->advmd)
		{
			foreach ($this->advmd as $item)
			{
				$advmd_id = (int)$item["id"];
				
				if(!in_array("advmd".$advmd_id, $selected))						
				{
					continue;
				}
								
				$val = " ";
				if(isset($a_set["md_".$advmd_id."_presentation"]))
				{
					$pb = $a_set["md_".$advmd_id."_presentation"]->getList();
					if($pb)
					{
						$val = $pb;
					}
				}		
				
				$this->tpl->setCurrentBlock("advmd_bl");										
				$this->tpl->setVariable("ADVMD_VAL", $val);
				$this->tpl->parseCurrentBlock();
			}
		}

		if(sizeof($items))
		{
			$this->tpl->setCurrentBlock("actions");
			foreach($items as $item)
			{
				$this->tpl->setVariable("ACTION_CAPTION", $item[0]);
				$this->tpl->setVariable("ACTION_LINK", $item[1]);
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}

?>