<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

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
	protected $has_schedule;	// bool

	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_has_schedule)
	{
		global $ilCtrl, $lng;

		$this->pool_id = $a_pool_id;
		$this->ref_id = $a_ref_id;
		$this->has_schedule = (bool)$a_has_schedule;
		
		$this->setId("bkrsv");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_reservations_list"));

		$this->addColumn($this->lng->txt("title"));
		
		if($this->has_schedule)
		{
			$this->addColumn($this->lng->txt("book_period"));
		}
		
		$this->addColumn($this->lng->txt("user"));
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager");
		$this->setResetCommand("resetLogFilter");
		$this->setFilterCommand("applyLogFilter");
		$this->setDisableFilterHiding(true);
		
		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';

		$this->initFilter();

		$this->getItems($this->getCurrentFilter());
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		if($this->hasSchedule)
		{
			$valid_status = array(ilBookingReservation::STATUS_IN_USE, 
				ilBookingReservation::STATUS_CANCELLED, 
				-ilBookingReservation::STATUS_IN_USE, 
				-ilBookingReservation::STATUS_CANCELLED);
		}
		else
		{
			$valid_status = array(ilBookingReservation::STATUS_CANCELLED, 				
				-ilBookingReservation::STATUS_CANCELLED);
		};
		
		$options = array(""=>$this->lng->txt('book_all'));
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

		if($this->has_schedule)
		{
			$item = $this->addFilterItemByMetaType("fromto", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt('book_fromto'));
			$this->filter["fromto"] = $item->getDate();
		}
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		$filter = array();
		if($this->filter["type"])
		{
			$filter["type"] = $this->filter["type"];
		}
		if($this->filter["status"])
		{
			$filter["status"] = $this->filter["status"];
		}
		
		if($this->has_schedule)
		{
			if($this->filter["fromto"]["from"] || $this->filter["fromto"]["to"])
			{
				if($this->filter["fromto"]["from"])
				{
					$filter["from"] = $this->filter["fromto"]["from"]->get(IL_CAL_UNIX);
				}
				if($this->filter["fromto"]["to"])
				{
					$filter["to"] = $this->filter["fromto"]["to"]->get(IL_CAL_UNIX);
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
		$this->determineOffsetAndOrder();
				
		$ids = array();
		include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
		foreach(ilBookingObject::getList($this->pool_id) as $item)
		{
			$ids[] = $item["booking_object_id"];
		}
		
		include_once "Modules/BookingManager/classes/class.ilBookingReservation.php";
		$data = ilBookingReservation::getList($ids, $this->getLimit(), $this->getOffset(), $filter);
		
		$this->setMaxCount($data['counter']);
		$this->setData($data['data']);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilAccess, $ilCtrl, $ilUser;

	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
	    $this->tpl->setVariable("RESERVATION_ID", $a_set["booking_reservation_id"]);

		if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
		{
			$this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_'.$a_set['status']));
		}
		
		$this->tpl->setVariable("TXT_CURRENT_USER", ilObjUser::_lookupFullName($a_set['user_id']));

		$ilCtrl->setParameter($this->parent_obj, 'user_id', $a_set['user_id']);
		$this->tpl->setVariable("HREF_PROFILE", $ilCtrl->getLinkTarget($this->parent_obj, 'showprofile'));
		$ilCtrl->setParameter($this->parent_obj, 'user_id', '');

		if($this->has_schedule)
		{
			$date_from = new ilDateTime($a_set['date_from'], IL_CAL_UNIX);
			$date_to = new ilDateTime($a_set['date_to'], IL_CAL_UNIX);
			$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatPeriod($date_from, $date_to));
		}
	
		if (!$this->has_schedule || $date_from->get(IL_CAL_UNIX) > time())
		{
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set['booking_reservation_id']);
			$alist->setListTitle($lng->txt("actions"));

			$ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);

			if($ilAccess->checkAccess('write', '', $this->ref_id))
			{
				if($a_set['status'] == ilBookingReservation::STATUS_CANCELLED)
				{
					/*
					// can be uncancelled?
					if(ilBookingReservation::getAvailableObject(array($a_set['object_id']), $date_from->get(IL_CAL_UNIX), $date_to->get(IL_CAL_UNIX)))
					{
						$alist->addItem($lng->txt('book_set_not_cancel'), 'not_cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvUncancel'));
					}
					*/
				}
				else if($a_set['status'] != ilBookingReservation::STATUS_IN_USE)
				{
					if($this->has_schedule)
					{
						$alist->addItem($lng->txt('book_set_in_use'), 'in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvInUse'));
					}
					$alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvCancel'));
				}
				else if($this->has_schedule)
				{
					$alist->addItem($lng->txt('book_set_not_in_use'), 'not_in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvNotInUse'));
				}
			}
			else if($a_set['user_id'] == $ilUser->getId() && $a_set['status'] != ilBookingReservation::STATUS_CANCELLED)
			{
				$alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvCancel'));
			}

			$this->tpl->setVariable('LAYER', $alist->getHTML());
		}		
	}
	
	protected function fillHeaderExcel($a_worksheet, &$a_row)
	{		
		$a_worksheet->write($a_row, 0, $this->lng->txt("title"));					
		$col = 0;
		if($this->has_schedule)
		{
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("from"));
			$a_worksheet->write($a_row, ++$col, $this->lng->txt("to"));
		}				
		$a_worksheet->write($a_row, ++$col, $this->lng->txt("user"));		
		$a_worksheet->write($a_row, ++$col, $this->lng->txt("status"));		
		$a_row++;
	}

	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$a_worksheet->write($a_row, 0, $a_set["title"]);		
		$col = 0;
		if($this->has_schedule)
		{
			$date_from = new ilDateTime($a_set['date_from'], IL_CAL_UNIX);
			$date_to = new ilDateTime($a_set['date_to'], IL_CAL_UNIX);
			$a_worksheet->write($a_row, ++$col, ilDatePresentation::formatDate($date_from));
			$a_worksheet->write($a_row, ++$col, ilDatePresentation::formatDate($date_to));
		}						
		$a_worksheet->write($a_row, ++$col, ilObjUser::_lookupFullName($a_set['user_id']));		
		
		$status = "";
		if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
		{
			$status = $this->lng->txt('book_reservation_status_'.$a_set['status']);			
		}
		$a_worksheet->write($a_row, ++$col, $status);
		
		$a_row++;
	}

	protected function fillHeaderCSV($a_csv)
	{		
		$a_csv->addColumn($this->lng->txt("title"));					
		if($this->has_schedule)
		{
			$a_csv->addColumn($this->lng->txt("from"));
			$a_csv->addColumn($this->lng->txt("to"));
		}				
		$a_csv->addColumn($this->lng->txt("user"));				
		$a_csv->addColumn($this->lng->txt("status"));				
		$a_csv->addRow();		
	}

	protected function fillRowCSV($a_csv, $a_set)
	{		
		$a_csv->addColumn($a_set["title"]);		
		if($this->has_schedule)
		{
			$date_from = new ilDateTime($a_set['date_from'], IL_CAL_UNIX);
			$date_to = new ilDateTime($a_set['date_to'], IL_CAL_UNIX);
			$a_csv->addColumn(ilDatePresentation::formatDate($date_from));
			$a_csv->addColumn(ilDatePresentation::formatDate($date_to));
		}						
		$a_csv->addColumn(ilObjUser::_lookupFullName($a_set['user_id']));	
		
		$status = "";
		if(in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
		{
			$status = $this->lng->txt('book_reservation_status_'.$a_set['status']);			
		}
		$a_csv->addColumn($status);
		
		$a_csv->addRow();
	}
}

?>