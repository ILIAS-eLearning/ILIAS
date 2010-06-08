<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

/**
 * Tracking user interface class.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrackingGUI extends ilLearningProgressBaseGUI
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
	 * Show object-baes summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl;

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId());
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Apply filter settings - used by table2gui
	 */
	public function applyFilterSummary()
	{
		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId());
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
		$utab = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId());
		$utab->resetOffset();
		$utab->resetFilter();
		$this->showObjectSummary();
	}
}
?>