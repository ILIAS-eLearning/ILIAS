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
	protected $ref_id;	// int
	protected $filter;	// array
	protected $pool;	// object

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

		$this->pool = $a_parent_obj->object;
		$this->ref_id = $a_ref_id;
		$this->setId("bkrsv");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_reservations_list"));

		$this->setLimit(9999);
		
		$this->addColumn("", "", "1%");
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("user"));
		$this->addColumn($this->lng->txt("book_period"));
		
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager");
		$this->setResetCommand("resetLogFilter");
		$this->setFilterCommand("applyLogFilter");

		$this->initFilter();

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$options = array();
			for($loop = 1; $loop < 7; $loop++)
			{
				$options[$loop] = $this->lng->txt('book_reservation_status_'.$loop);
			}
			$this->addMultiItemSelectionButton('tstatus', $options, 'changeStatus', $this->lng->txt('book_change_status'));
		}

		$this->getItems($this->getCurrentFilter());
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		include_once "Modules/BookingManager/classes/class.ilBookingType.php";
		$options = array(""=>$this->lng->txt('book_all'));
		foreach(ilBookingType::getList($this->pool->getId()) as $item)
		{
			$options[$item["booking_type_id"]] = $item["title"];
		}
		$item = $this->addFilterItemByMetaType("type", ilTable2GUI::FILTER_SELECT);
		$item->setOptions($options);
		$this->filter["type"] = $item->getValue();

		$options = array(""=>$this->lng->txt('book_all'));
		for($loop = 1; $loop < 7; $loop++)
	    {
			$options[$loop] = $this->lng->txt('book_reservation_status_'.$loop);
		}
		$item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT);
		$item->setOptions($options);
		$this->filter["status"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("fromto", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt('book_fromto'));
		$this->filter["fromto"] = $item->getDate();
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
		return $filter;
	}
	
	/**
	 * Gather data and build rows
	 * @param	array	$filter
	 */
	function getItems(array $filter)
	{
		global $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$data = ilBookingReservation::getList($this->getLimit(), $this->getOffset(), $filter);
		
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
	    $this->tpl->setVariable("RESERVATION_ID", $a_set["booking_reservation_id"]);

		$date_from = new ilDateTime($a_set['date_from'], IL_CAL_UNIX);
		$date_to = new ilDateTime($a_set['date_to'], IL_CAL_UNIX);
		$this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_'.$a_set['status']));
		$this->tpl->setVariable("TXT_CURRENT_USER", ilObjUser::_lookupFullName($a_set['user_id']));
		$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatPeriod($date_from, $date_to));
	}
}

?>