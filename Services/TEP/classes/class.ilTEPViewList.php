<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/TEP/classes/class.ilTEPView.php";

/**
 * TEP list view class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPViewList extends ilTEPView
{
	// 
	// request
	// 
	
	public function normalizeSeed(ilDate $a_value)
	{
		// always today
		return new ilDate(date("Y-m-d"), IL_CAL_DATE);						
	}
	
	public function getPeriod()
	{
		// today + 4 weeks
		$seed = $this->getSeed();
		$end = clone $seed;
		$end->increment(IL_CAL_WEEK, 4);
		return array($seed, $end);
	}	
	
	public function getTutors()
	{
		global $ilUser;
		
		return array($ilUser->getId());
	}
	
	
	//
	// data
	// 
	
	public function loadData()
	{
		$period = $this->getPeriod();
		$from = $period[0];
		$to = $period[1];
		
		include_once "Services/TEP/classes/class.ilTEPEntries.php";
		$entries = new ilTEPEntries($from, $to, $this->getTutors());
		
		$this->entries = $entries->getEntriesForPresentation();	
		
		// only personal entries
		unset($this->entries[0]);
		
		return $this->hasData();
	}
	
	
	//
	// presentation
	// 
	
	public function render()
	{		
		$this->prepareDataForPresentation();
		
		$this->entries = $this->entries[array_shift($this->getTutors())];
	
		include_once "Services/TEP/classes/class.ilTEPViewListTableGUI.php";
		$tbl = new ilTEPViewListTableGUI($this->getParentGUI(), "", $this->entries);		
		return $tbl->getHTML();
	}	
}

