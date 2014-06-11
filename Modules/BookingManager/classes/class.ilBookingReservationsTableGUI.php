<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
require_once "Services/Calendar/classes/class.ilCalendarUtil.php";

/**
 * List booking objects 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingReservationsTableGUI extends ilTable2GUI
{
	protected $ref_id;	// int
	protected $filter;	// array
	protected $pool_id;	// int
	protected $show_all; // bool
	protected $has_schedule; // bool
	protected $objects; // array
	protected $group_id; // int

	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	bool	$a_show_all
	 * @param	bool	$a_has_schedule
	 * @param	array	$a_filter_pre
	 * @param	array	$a_group_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_show_all, $a_has_schedule, array $a_filter_pre = null, $a_group_id = null)
	{
		global $ilCtrl, $lng, $ilUser, $ilAccess;

		$this->pool_id = $a_pool_id;
		$this->ref_id = $a_ref_id;
		$this->show_all = $a_show_all;
		$this->has_schedule = (bool)$a_has_schedule;		
		$this->group_id = $a_group_id;
		
		$this->setId("bkrsv".$a_ref_id);
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_reservations_list"));

		$this->addColumn("", "", 1);
		$this->addColumn($this->lng->txt("title"), "title");
		
		if($this->has_schedule)
		{
			$this->lng->loadLanguageModule("dateplaner");
			
			$this->addColumn($this->lng->txt("date"), "date");
			$this->addColumn($this->lng->txt("wk_short"), "week");
			$this->addColumn($this->lng->txt("cal_weekday"), "weekday");
			$this->addColumn($this->lng->txt("book_schedule_slot"), "slot");			
			$this->addColumn($this->lng->txt("book_no_of_objects"), "counter");
			
			$this->setDefaultOrderField("date");
			$this->setDefaultOrderDirection("asc");
		}
		else
		{						
			$this->addColumn($this->lng->txt("status"), "status");
			
			$this->setDefaultOrderField("title");
			$this->setDefaultOrderDirection("asc");
		}
								
		$this->addColumn($this->lng->txt("user"), "user_name");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager");
		$this->setResetCommand("resetLogFilter");
		$this->setFilterCommand("applyLogFilter");
		$this->setDisableFilterHiding(true);
				
		$this->initFilter($a_filter_pre);				

		if($this->group_id)
		{
			$this->setLimit(9999);
			$this->disable("numinfo");
			$this->filters = array();
		}
		else
		{
			$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
		}
		
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{			
			/*
			if($ilAccess->checkAccess('write', '', $this->ref_id))
			{
				$this->addMultiCommand('rsvInUse', $lng->txt('book_set_in_use'));			
				$this->addMultiCommand('rsvNotInUse', $lng->txt('book_set_not_in_use'));							
			}
			*/
			
			$this->addMultiCommand('rsvConfirmCancel', $lng->txt('book_set_cancel'));
			// $this->addMultiCommand('rsvUncancel', $lng->txt('book_set_not_cancel'));			
			$this->setSelectAllCheckbox('mrsv');
		}
		
		$this->getItems($this->getCurrentFilter());
		
		ilDatePresentation::setUseRelativeDates(false);
	}

	/**
	* Init filter
	*/
	function initFilter(array $a_filter_pre = null)
	{									
		if(is_array($a_filter_pre) && 
			isset($a_filter_pre["object"]))
		{			
			$_SESSION["form_".$this->getId()]["object"] = serialize($a_filter_pre["object"]);			
			if($this->has_schedule)
			{
				$_SESSION["form_".$this->getId()]["fromto"] = serialize(array(
					"from" => serialize(new ilDateTime(date("Y-m-d"), IL_CAL_DATE)),
					"to" => ""
				));
			}
		}
		
		$this->objects = array();
		include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
		foreach(ilBookingObject::getList($this->pool_id) as $item)
		{
			$this->objects[$item["booking_object_id"]] = $item["title"];
		}				
		$item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
		$item->setOptions(array(""=>$this->lng->txt('book_all'))+$this->objects);		
		$this->filter["object"] = $item->getValue();
		
		if($this->has_schedule)
		{
			if(!$_SESSION["form_".$this->getId()]["fromto"])
			{
				// default: from today
				$_SESSION["form_".$this->getId()]["fromto"] = serialize(array(
					"from" => serialize(new ilDateTime(date("Y-m-d"), IL_CAL_DATE)),
					"to" => null
				));			
			}
			
			$item = $this->addFilterItemByMetaType("fromto", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt('book_fromto'));
			$this->filter["fromto"] = $item->getDate();
			
			// only needed for full log
			if($this->show_all)
			{
				// see ilObjBookingPoolGUI::buildDatesBySchedule()
				$map = array_flip(array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'));
				
				$options = array(""=>$this->lng->txt('book_all'));
				
				// schedule to slot
				require_once "Modules/BookingManager/classes/class.ilBookingSchedule.php";
				foreach(ilBookingSchedule::getList($this->pool_id) as $def)
				{
					$schedule = new ilBookingSchedule($def["booking_schedule_id"]);
					foreach($schedule->getDefinition() as $day => $slots)
					{
						$day_caption = ilCalendarUtil::_numericDayToString($map[$day], false);
					
						foreach($slots as $slot)
						{
							$idx = $map[$day]."_".$slot;
							$options[$idx] = $day_caption.", ".$slot;
						}
					}
				}
				
				ksort($options);
				
				$item = $this->addFilterItemByMetaType("book_schedule_slot", ilTable2GUI::FILTER_SELECT);
				$item->setOptions($options);
				$this->filter["slot"] = $item->getValue();		
			}			
		}
		
		// status
		$valid_status = array(-ilBookingReservation::STATUS_CANCELLED,
			ilBookingReservation::STATUS_CANCELLED);		
		if(!$this->has_schedule)
		{
			$options = array(""=>$this->lng->txt('book_all'));
		}
		else
		{
			$options = array();
		}
		foreach($valid_status as $loop)
	    {
			if($loop > 0)
			{
				$options[$loop] = $this->lng->txt('book_reservation_status_'.$loop);
			}
			else
			{
				$options[$loop] = $this->lng->txt('book_not').' '.$this->lng->txt('book_reservation_status_'.-$loop);
			}
		}
		$item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT);
		$item->setOptions($options);
		$this->filter["status"] = $item->getValue();		
							
		// only needed for full log
		if($this->show_all)
		{											
			$options = array(""=>$this->lng->txt('book_all'))+
				ilBookingReservation::getUserFilter(array_keys($this->objects));
			$item = $this->addFilterItemByMetaType("user", ilTable2GUI::FILTER_SELECT);
			$item->setOptions($options);
			$this->filter["user_id"] = $item->getValue();		
		}	
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		$filter = array();		
		if($this->filter["object"])
		{
			$filter["object"] = $this->filter["object"];
		}
		if($this->filter["status"])
		{
			$filter["status"] = $this->filter["status"];
		}
		if($this->filter["user_id"])
		{
			$filter["user_id"] = $this->filter["user_id"];
		}
		
		if($this->has_schedule)
		{
			if(!$filter["status"])
			{
				// needs distinct status because of aggregation
				$filter["status"] = -ilBookingReservation::STATUS_CANCELLED;
			}
			if($this->filter["slot"])
			{
				$filter["slot"] = $this->filter["slot"];
			}
			
			if($this->filter["fromto"]["from"] || $this->filter["fromto"]["to"])
			{
				if($this->filter["fromto"]["from"])
				{
					$filter["from"] = $this->filter["fromto"]["from"]->get(IL_CAL_UNIX);
				}
				if($this->filter["fromto"]["to"])
				{
					$day_end = new ilDateTime($this->filter["fromto"]["to"]->get(IL_CAL_DATE)." 23:59:59", IL_CAL_DATETIME);
					$filter["to"] = $day_end->get(IL_CAL_UNIX);
				}
			}
		}
		
		return $filter;
	}
	
	/**
	 * Gather data and build rows
	 * @param	array	$filter
	 */
	function getItems(array $filter)
	{		
		global $ilUser;
		
		$this->determineOffsetAndOrder();
		
		if(!$filter["object"])
		{
			$ids = array_keys($this->objects);
		}
		else
		{
			$ids = array($filter["object"]);
		}
		
		if(!$this->show_all)
		{
			$filter["user_id"] = $ilUser->getId();
		}
	
		include_once "Modules/BookingManager/classes/class.ilBookingReservation.php";
		$data = ilBookingReservation::getListByDate($this->has_schedule, $ids, $this->getOrderField(), $this->getOrderDirection(), $this->getOffset(), $this->getLimit(),  $filter);		
		
		$this->setData($data['data']);
		$this->setMaxCount($data['counter']);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilAccess, $ilCtrl, $ilUser;
		
	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		
		$can_be_cancelled = (($ilAccess->checkAccess('write', '', $this->ref_id) || 
			$a_set['user_id'] == $ilUser->getId()) &&
			$a_set["can_be_cancelled"]);

		if($can_be_cancelled)
		{
			$this->tpl->setVariable("MULTI_ID", $a_set["booking_reservation_id"]);
		}

		// #11995
		$uname = $a_set["user_name"];
		if(!trim($uname))
		{
			$uname = "[".$lng->txt("user_deleted")."]";
		}
		else
		{			
			$ilCtrl->setParameter($this->parent_obj, 'user_id', $a_set['user_id']);
			$this->tpl->setVariable("HREF_PROFILE", $ilCtrl->getLinkTarget($this->parent_obj, 'showprofile'));
			$ilCtrl->setParameter($this->parent_obj, 'user_id', '');
		}
		$this->tpl->setVariable("TXT_CURRENT_USER", $uname);

		if($this->has_schedule)
		{			
			$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
			$this->tpl->setVariable("VALUE_WEEK", $a_set["week"]);
			$this->tpl->setVariable("VALUE_WEEKDAY", ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
			$this->tpl->setVariable("VALUE_SLOT", $a_set["slot"]);
			$this->tpl->setVariable("VALUE_COUNTER", $a_set["counter"]);			
		}
		else
		{			
			if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
			{
				$this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_'.$a_set['status']));
			}	
			else
			{
				$this->tpl->setVariable("TXT_STATUS", "&nbsp;");
			}
		}
					
		if($can_be_cancelled)
		{			 
			$ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);
			$this->tpl->setVariable("URL_ACTION", $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
			$ilCtrl->setParameter($this->parent_obj, 'reservation_id', "");
			$this->tpl->setVariable("TXT_ACTION", $lng->txt('book_set_cancel'));
		}	
		
		/* advsellist version
		if (!$this->has_schedule || $date_to->get(IL_CAL_UNIX) > time())
		{
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set['booking_reservation_id']);
			$alist->setListTitle($lng->txt("actions"));

			$ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);

			if(!$a_set['group_id'])
			{
				if($ilAccess->checkAccess('write', '', $this->ref_id))
				{
					if($a_set['status'] == ilBookingReservation::STATUS_CANCELLED)
					{					
						// can be uncancelled?
						// if(ilBookingReservation::getAvailableObject(array($a_set['object_id']), $date_from->get(IL_CAL_UNIX), $date_to->get(IL_CAL_UNIX)))
						// {
						//	  $alist->addItem($lng->txt('book_set_not_cancel'), 'not_cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvUncancel'));
						// }						
					}
					else if($a_set['status'] != ilBookingReservation::STATUS_IN_USE)
					{
						if($this->has_schedule)
						{
							$alist->addItem($lng->txt('book_set_in_use'), 'in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvInUse'));
						}
						$alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
					}
					else if($this->has_schedule)
					{
						$alist->addItem($lng->txt('book_set_not_in_use'), 'not_in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvNotInUse'));
					}
				}
				else if($a_set['user_id'] == $ilUser->getId() && $a_set['status'] != ilBookingReservation::STATUS_CANCELLED)
				{
					$alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
				}
			}
			else if($ilAccess->checkAccess('write', '', $this->ref_id) || $a_set['user_id'] == $ilUser->getId())
			{				
				$alist->addItem($lng->txt('details'), 'details', $ilCtrl->getLinkTarget($this->parent_obj, 'logDetails'));
			}
			
			if(sizeof($alist->getItems()))
			{
				if(!$a_set['group_id'])
				{
					$this->tpl->setVariable('MULTI_ID', $a_set['booking_reservation_id']);
				}
				$this->tpl->setVariable('LAYER', $alist->getHTML());
			}
		}		
		*/
	}
	
	protected function fillHeaderExcel($a_worksheet, &$a_row)
	{		
		$a_worksheet->write($a_row, 0, $this->lng->txt("title"));					
		$col = 0;
		if($this->has_schedule)
		{
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("date"));
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("book_schedule_weekday"));
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("book_schedule_slot"));
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("book_no_of_objects"));
		}
		else 
		{
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("status"));		
		}		
		$a_worksheet->write($a_row, ++$col, $this->lng->txt("user"));			
	}

	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$a_worksheet->write($a_row, 0, $a_set["title"]);		
		$col = 0;
		if($this->has_schedule)
		{			
			$a_worksheet->write($a_row, ++$col, ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
			$a_worksheet->write($a_row, ++$col, ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
			$a_worksheet->write($a_row, ++$col, $a_set["slot"]);
			$a_worksheet->write($a_row, ++$col, $a_set["counter"]);
		}						
		else
		{		
			$status = "";
			if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
			{
				$status = $this->lng->txt('book_reservation_status_'.$a_set['status']);			
			}
			$a_worksheet->write($a_row, ++$col, $status);
		}
		$a_worksheet->write($a_row, ++$col, $a_set['user_name']);		
	}

	protected function fillHeaderCSV($a_csv)
	{		
		$a_csv->addColumn($this->lng->txt("title"));					
		if($this->has_schedule)
		{
			$a_csv->addColumn($this->lng->txt("date"));
			$a_csv->addColumn($this->lng->txt("book_schedule_weekday"));
			$a_csv->addColumn($this->lng->txt("book_schedule_slot"));
			$a_csv->addColumn($this->lng->txt("book_no_of_objects"));			
		}				
		else
		{
			$a_csv->addColumn($this->lng->txt("status"));			
		}
		$a_csv->addColumn($this->lng->txt("user"));				
		$a_csv->addRow();		
	}

	protected function fillRowCSV($a_csv, $a_set)
	{		
		$a_csv->addColumn($a_set["title"]);		
		if($this->has_schedule)
		{
			$a_csv->addColumn(ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
			$a_csv->addColumn(ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
			$a_csv->addColumn($a_set["slot"]);
			$a_csv->addColumn($a_set["counter"]);			
		}						
		else
		{
			$status = "";
			if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
			{
				$status = $this->lng->txt('book_reservation_status_'.$a_set['status']);			
			}
			$a_csv->addColumn($status);
		}
		$a_csv->addColumn($a_set['user_name']);		
		$a_csv->addRow();
	}
}

?>