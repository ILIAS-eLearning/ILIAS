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
	protected $reservations;	// [array]
	protected $current_bookings; // [int]
	
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
		global $ilUser;
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$data = ilBookingObject::getList($this->pool_id);
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		foreach($data as $item)
		{
			$item_id = $item["booking_object_id"];
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
			$items['book'] = array($lng->txt('book_book'), $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
		}
		
		if(!$this->schedule && $has_booking)
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