<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesMetaData
 */
class ilMDCopyrightUsageGUI
{
	/**
	 * copyright identifier
	 * @var integer
	 */
	protected $entry_id;

	function __construct($a_entry_id)
	{
		global $DIC;

		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC->ctrl();

		$this->entry_id = $a_entry_id;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		//showUsageTable
		$this->$cmd();
	}

	function showUsageTable()
	{
		include_once("./Services/MetaData/classes/class.ilMDCopyrightUsageTableGUI.php");
		$table_gui = new ilMDCopyrightUsageTableGUI($this,'showCopyrightSettings',$this->entry_id);
		$table_gui->setFilterCommand("applyUsageFilter");
		$table_gui->setResetCommand("resetUsageFilter");

		$this->tpl->setContent($table_gui->getHTML());
	}

	function getEntryId()
	{
		return $this->entry_id;
	}

	/**
	 * Apply filter
	 */
	function applyUsageFilter()
	{
		include_once("./Services/MetaData/classes/class.ilMDCopyrightUsageTableGUI.php");
		ilLoggerFactory::getRootLogger()->debug("//////// COPYRIGHT apply = ".$this->entry_id);
		$table_gui = new ilMDCopyrightUsageTableGUI($this,'showCopyrightSettings',$this->entry_id);
		$table_gui->writeFilterToSession();	// writes filter to session
		$table_gui->resetOffset();		// sets record offest to 0 (first page)
		$this->tpl->setContent($table_gui->getHTML());
	}

	/**
	 * Reset filter
	 */
	function resetUsageFilter()
	{
		include_once("./Services/MetaData/classes/class.ilMDCopyrightUsageTableGUI.php");
		ilLoggerFactory::getRootLogger()->debug("//////// COPYRIGHT reset= ".$this->entry_id);
		$table_gui = new ilMDCopyrightUsageTableGUI($this,'showCopyrightSettings',$this->entry_id);
		$table_gui->resetOffset();		// sets record offest to 0 (first page)
		$table_gui->resetFilter();		// clears filter
		$this->tpl->setContent($table_gui->getHTML());
	}
}