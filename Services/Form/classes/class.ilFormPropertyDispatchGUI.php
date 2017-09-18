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
	 * @var ilCtrl
	 */
	protected $ctrl;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
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
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		if (strtolower(get_class($this->getItem())) != $next_class)
		{
			die("ilFormPropertyDispatch: Forward Error. (".get_class($this->getItem())."-".$next_class.")");
		}
		
		return $ilCtrl->forwardCommand($this->getItem());
	}

}
