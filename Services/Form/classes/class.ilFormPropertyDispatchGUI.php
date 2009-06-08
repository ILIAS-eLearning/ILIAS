<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Form property dispatcher. Forwards control flow to property form input GUI
* classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilFormPropertyDispatchGUI:
* @ingroup 
*/
class ilFormPropertyDispatchGUI
{
	/**
	* Constructor
	*/
	function _construct()
	{
	}
	
	/**
	* Set item
	*
	* @param	object		item
	*/
	function setItem($a_val)
	{
		$this->item = $a_val;
	}
	
	/**
	* Get item
	*
	* @return	object		item
	*/
	function getItem()
	{
		return $this->item;
	}
	
	/**
	* Execute command.
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		if (strtolower(get_class($this->getItem())) != $next_class)
		{
			die("ilFormPropertyDispatch: Forward Error.");
		}
		
		return $ilCtrl->forwardCommand($this->getItem());
	}

}
