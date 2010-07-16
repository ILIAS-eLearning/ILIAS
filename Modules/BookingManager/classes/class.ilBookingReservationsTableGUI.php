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
class ilBookingReservationsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_type_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->ref_id = $a_ref_id;
		$this->setId("bkrsv");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_reservations_list"));

		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("user"));
		$this->addColumn($this->lng->txt("book_period"));
		
		$this->addColumn($this->lng->txt("actions"));

		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager");
		$this->initFilter();

		$this->getItems($this->type_id, $this->getCurrentFilter());
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		/*
		$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["country"] = $item->getValue();
		 */
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{

	}
	
	/**
	 * Gather data and build rows
	 */
	function getItems()
	{
		global $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$data = ilBookingReservation::getList($this->getLimit(), $this->getOffset());
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilAccess, $ilCtrl;

	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);

		$date_from = new ilDateTime($a_set['date_from'], IL_CAL_UNIX);
		$date_to = new ilDateTime($a_set['date_to'], IL_CAL_UNIX);
		$this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_'.$a_set['status']));
		$this->tpl->setVariable("TXT_CURRENT_USER", ilObjUser::_lookupFullName($a_set['user_id']));
		$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatPeriod($date_from, $date_to));
		
		$ilCtrl->setParameter($this->parent_obj, 'object_id', $a_set['booking_object_id']);
		
		$this->tpl->setCurrentBlock('item_command');

		$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTarget($this->parent_obj, 'changeStatus'));
		$this->tpl->setVariable('TXT_COMMAND', $lng->txt('book_reservation_cancel'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTarget($this->parent_obj, 'changeStatus'));
		$this->tpl->setVariable('TXT_COMMAND', $lng->txt('book_reservation_block'));
		$this->tpl->parseCurrentBlock();
	}
}

?>