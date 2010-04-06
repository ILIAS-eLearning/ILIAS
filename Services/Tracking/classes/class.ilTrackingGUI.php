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
	 * Constructor
	 */
	function __construct()
	{
		
	}
	
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
}
?>