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
	 * Set reference id
	 *
	 * @param	integer	object id
	 */
	function setRefId($a_val)
	{
		$this->ref_id = $a_val;
	}

	/**
	 * Get reference id
	 *
	 * @return	integer	ref id
	 */
	function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * Show object-baes summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl;

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->ref_id);
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Apply filter settings - used by table2gui
	 */
	public function applyFilterSummary()
	{
		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->ref_id);
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
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->ref_id);
		$utab->resetOffset();
		$utab->resetFilter();
		$this->showObjectSummary();
	}
}
?>