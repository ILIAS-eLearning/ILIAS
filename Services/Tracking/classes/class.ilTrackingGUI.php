<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking user interface class.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrackingGUI
{
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl;
	
		$nextClass = $ilCtrl->getNextClass();
		
		switch($nextClass)
		{
			default:
				$cmd = $ilCtrl->getCmd();
				$this->$cmd();
				break;
		}
		
	}
	
	/**
	 * Set object id
	 *
	 * @param	integer	object id
	 */
	function setObjectId($a_val)
	{
		$this->obj_id = $a_val;
	}
	
	/**
	 * Get object id
	 *
	 * @return	integer	object id
	 */
	function getObjectId()
	{
		return $this->obj_id;
	}
	
	/**
	 * For one object: List users (rows) and tracking properties in columns
	 * Prerequisite: object id is set
	 */
	function showObjectUsersProps()
	{
		global $tpl;
	
		include_once("./Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php");
		$table = new ilTrObjectUsersPropsTableGUI($this, "showObjectUsersProps", "troup".$this->getObjectId(),
			$this->getObjectId());
		$tpl->setContent($table->getHTML());
	}

	/**
	 * For one user: list objects (rows) and tracking properties in columns
	 * Prerequisite: user id is set
	 */
	function showUserObjectsProps()
	{
		global $tpl;

		$user_id = (int)$_GET["obj_id"];

		include_once("./Services/Tracking/classes/class.ilTrUserObjectsPropsTableGUI.php");
		$table = new ilTrUserObjectsPropsTableGUI($this, "showUserObjectsProps", "truop".$user_id,
			$user_id);
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Show object-baes summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl;

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary");
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Apply filter settings - used by table2gui
	 */
	public function applyFilterSummary()
	{
		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary");
		$utab->resetOffset();
		$utab->writeFilterToSession();
		$this->showObjectSummary();
	}

	/**
	 * Reset filter settings - used by table2gui
	 */
	public function resetFilterSummary()
	{
		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary");
		$utab->resetOffset();
		$utab->resetFilter();
		$this->showObjectSummary();
	}
}
?>