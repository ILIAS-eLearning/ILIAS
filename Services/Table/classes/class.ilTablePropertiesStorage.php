<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Saves (mostly asynchronously) user properties of tables (e.g. filter on/off)
* 
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesTable
* @ilCtrl_Calls ilTablePropertiesStorage:
*/
class ilTablePropertiesStorage
{
	var $properties = array (
		"filter" => array("storage" => "session")
		);
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilCtrl;
		
		$cmd = $ilCtrl->getCmd();
//		$next_class = $this->ctrl->getNextClass($this);

		$this->$cmd();
	}
	
	/**
	 * Show Filter
	 */
	function showFilter()
	{
		global $ilUser;
		
		if ($_GET["user_id"] == $ilUser->getId())
		{
			$this->storeProperty($_GET["table_id"], $_GET["user_id"],
				"filter", 1);
		}
	}
	
	/**
	 * Hide Filter
	 */
	function hideFilter()
	{
		global $ilUser;
		
		if ($_GET["user_id"] == $ilUser->getId())
		{
			$this->storeProperty($_GET["table_id"], $_GET["user_id"],
				"filter", 0);
		}
	}
	
	/**
	* Store property in session or db
	*/
	function storeProperty($a_table_id, $a_user_id, $a_property,
		$a_value)
	{
		switch ($this->properties[$a_property]["storage"])
		{
			case "session":
				$_SESSION["table"][$a_table_id][$a_user_id][$a_property]
					= $a_value;
				break;
		}
	}
	
	/**
	* Get property in session or db
	*/
	function getProperty($a_table_id, $a_user_id, $a_property)
	{
		switch ($this->properties[$a_property]["storage"])
		{
			case "session":
				return $_SESSION["table"][$a_table_id][$a_user_id][$a_property];
				break;
		}
	}
	

}
?>
