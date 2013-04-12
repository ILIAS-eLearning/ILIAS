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
	
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_pool_has_schedule)
	{
		global $ilCtrl, $lng, $ilAccess;

		$this->ref_id = $a_ref_id;
		$this->pool_id = $a_pool_id;
		$this->has_schedule = $a_pool_has_schedule;
		$this->may_edit = $ilAccess->checkAccess('write', '', $this->ref_id);
		
		$this->setId("bkobj");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("book_objects_list"));

		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("description"), "description");
		
		if(!$this->has_schedule)
		{
			$this->addColumn($this->lng->txt("available"));
		}

		if ($this->may_edit)
		{			
			$this->addColumn($this->lng->txt("book_current_user"));
			
			if($this->has_schedule)
			{
				$this->addColumn($this->lng->txt("book_period"));
			}
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
		global $lng, $ilCtrl, $ilUser;
		
		$has_booking = false;
		$booking_possible = true;
		$has_reservations = false;

	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
	    $this->tpl->setVariable("TXT_DESC", nl2br($a_set["description"]));
		
		if(!$this->has_schedule)		
		{									
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			$reservation = ilBookingReservation::getList(array($a_set['booking_object_id']), 1000, 0, array());
			$cnt = 0;			
			$user_ids = array();				
			foreach($reservation["data"] as $item)
			{			
				if($item["status"] != ilBookingReservation::STATUS_CANCELLED)
				{
					$cnt++;
					$user_ids[$item["user_id"]] = ilObjUser::_lookupFullName($item['user_id']);
					
					if($item["user_id"] == $ilUser->getId())
					{
						$has_booking = true;
					}
					
					$has_reservations = true;
				}
			}
			
			$this->tpl->setVariable("VALUE_AVAIL", $a_set["nr_items"]-$cnt); 
			$this->tpl->setVariable("VALUE_AVAIL_ALL", $a_set["nr_items"]); 

			if($a_set["nr_items"] <= $cnt)
			{
				$booking_possible = false;
			}
			
			if ($this->may_edit)
			{
				if($user_ids)
				{
					$this->tpl->setVariable("TXT_CURRENT_USER", implode("<br />", array_unique($user_ids)));		
				}
				else
				{
					$this->tpl->setVariable("TXT_CURRENT_USER", "");
				}			
			}
		}
		else
		{
			if ($this->may_edit)
			{
				include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
				$reservation = ilBookingReservation::getCurrentOrUpcomingReservation($a_set['booking_object_id']);
			
				if($reservation)
				{
					$date_from = new ilDateTime($reservation['date_from'], IL_CAL_UNIX);
					$date_to = new ilDateTime($reservation['date_to'], IL_CAL_UNIX);

					$this->tpl->setVariable("TXT_CURRENT_USER", ilObjUser::_lookupFullName($reservation['user_id']));
					$this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatPeriod($date_from, $date_to));
					
					$has_reservations = true;
				}
				else
				{
					$this->tpl->setVariable("TXT_CURRENT_USER", "");
					$this->tpl->setVariable("VALUE_DATE", "");
				}
			}
		}

		
		$items = array();
		
		$ilCtrl->setParameter($this->parent_obj, 'object_id', $a_set['booking_object_id']);
		
		if($a_set['info_file'])
		{
			$items['info'] = array($lng->txt('book_download_info'), $ilCtrl->getLinkTarget($this->parent_obj, 'deliverInfo'));
		}	
	
		if(!$has_booking)
		{
			if($booking_possible)
			{
				$items['book'] = array($lng->txt('book_book'), $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
			}
		}
		else
		{	
			if(trim($a_set['post_text']) || $a_set['post_file'])
			{
				$items['post'] = array($lng->txt('book_post_booking_information'), $ilCtrl->getLinkTarget($this->parent_obj, 'displayPostInfo'));
			}	
		
			$items['cancel'] = array($lng->txt('book_set_cancel'), $ilCtrl->getLinkTarget($this->parent_obj, 'rsvCancelUser'));
		}

		if ($this->may_edit)
		{
			// #10890
			if(!$has_reservations)
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