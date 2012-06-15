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
	protected $ref_id;
	protected $pool_id;	
	
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id)
	{
		global $ilCtrl, $lng, $ilAccess;

		$this->ref_id = $a_ref_id;
		$this->pool_id = $a_pool_id;
		$this->setId("bkobj");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_objects_list"));

		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("description"), "description");

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$this->addColumn($this->lng->txt("status"));
			$this->addColumn($this->lng->txt("book_current_user"));
			$this->addColumn($this->lng->txt("book_period"));
		}
		
		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_object_row.html", "Modules/BookingManager");
		
		$this->getItems();
	}

	/**
	 * Gather data and build rows
	 */
	function getItems()
	{		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$data = ilBookingObject::getList($this->pool_id);
		
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
	    $this->tpl->setVariable("TXT_DESC", nl2br($a_set["description"]));

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			$reservation = ilBookingReservation::getCurrentOrUpcomingReservation($a_set['booking_object_id']);
		}

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$this->tpl->setCurrentBlock('details');

			if($reservation)
			{
				$date_from = new ilDateTime($reservation['date_from'], IL_CAL_UNIX);
				$date_to = new ilDateTime($reservation['date_to'], IL_CAL_UNIX);

				if(in_array($reservation['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)))
				{
					$this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_'.$reservation['status']));
				}
				$this->tpl->setVariable("TXT_CURRENT_USER", ilObjUser::_lookupFullName($reservation['user_id']));
				$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatPeriod($date_from, $date_to));
			}
			else
			{
				$this->tpl->setVariable("TXT_STATUS", "");
				$this->tpl->setVariable("TXT_CURRENT_USER", "");
				$this->tpl->setVariable("VALUE_DATE", "");
			}

			$this->tpl->parseCurrentBlock();
		}

		$items = array();
		
		$ilCtrl->setParameter($this->parent_obj, 'object_id', $a_set['booking_object_id']);
		
		if ($a_set["schedule_id"])
		{
			$items['book'] = array($lng->txt('book_book'), $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
		}

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			if(!$reservation)
			{
				$items['delete'] = array($lng->txt('delete'), $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
			}

			$items['edit'] = array($lng->txt('edit'), $ilCtrl->getLinkTarget($this->parent_obj, 'edit'));
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