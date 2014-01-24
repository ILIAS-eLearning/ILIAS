<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List booking schedules (for booking pool)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingSchedulesTableGUI extends ilTable2GUI
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
		$this->setId("bksd");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("book_is_used"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_schedule_row.html", "Modules/BookingManager");
	
		$this->getItems($ilObjDataCache->lookupObjId($this->ref_id));
	}

	/**
	 * Build summary item rows for given object and filter(s)
	 *
	 * @param	int	$a_pool_id (aka parent obj id)
	 */
	function getItems($a_pool_id)
	{
		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		$data = ilBookingSchedule::getList($a_pool_id);
		
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

		if($a_set["is_used"])
		{
			$this->tpl->setVariable("TXT_IS_USED", $lng->txt("yes"));
		}
		else
		{
			$this->tpl->setVariable("TXT_IS_USED", $lng->txt("no"));
		}

		$ilCtrl->setParameter($this->parent_obj, 'schedule_id', $a_set['booking_schedule_id']);

		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$alist = new ilAdvancedSelectionListGUI();
		$alist->setId($a_set['booking_schedule_id']);
		$alist->setListTitle($lng->txt("actions"));
	
		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTarget($this->parent_obj, 'edit')); // #12306
			
			if(!$a_set["is_used"])
			{
				$alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
			}		
		}

		$this->tpl->setVariable("LAYER", $alist->getHTML());
	}
}
?>
