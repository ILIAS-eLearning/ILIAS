<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List booking types (for booking pool)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingTypesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->ref_id = $a_ref_id;
		$this->setId("bktp");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("book_no_of_objects"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_type_row.html", "Modules/BookingManager");
		
		$this->getItems($ilObjDataCache->lookupObjId($this->ref_id));

		// remove items which cannot be booked for "normal" users
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			foreach($this->row_data as $idx => $row)
			{
				if($row['counter'] == 0)
			    {
					unset($this->row_data[$idx]);
				}
			}
		}
	}

	/**
	 * Build item rows for given object and filter(s)
	 *
	 * @param	int	$a_pool_id (aka parent obj id)
	 */
	function getItems($a_pool_id)
	{
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$data = ilBookingType::getList($a_pool_id);
		
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
	    $this->tpl->setVariable("VALUE_OBJECTS_NO", $a_set["counter"]);

		$ilCtrl->setParameter($this->parent_obj, 'type_id', $a_set['booking_type_id']);
		$ilCtrl->setParameterByClass('ilBookingObjectGUI', 'type_id', $a_set['booking_type_id']);


		$items = array();

		if($a_set["counter"] > 0)
		{
			if($a_set["schedule_id"])
			{
				$items['book'] = array($lng->txt('book_book'), $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
			}
			else 
			{
				$items['list'] = array($lng->txt('book_list_items'), $ilCtrl->getLinkTargetByClass('ilBookingObjectGUI', 'render'));
			}
		}

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$items['add'] = array($lng->txt('book_add_object'), $ilCtrl->getLinkTargetByClass('ilBookingObjectGUI', 'create'));
			
			if($a_set["counter"] == 0)
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
