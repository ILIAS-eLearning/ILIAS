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
		"filter" => array("storage" => "session"),
		"direction" => array("storage" => "db"),
		"order" => array("storage" => "db"),
		"offset" => array("storage" => "session")
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
		global $ilDB;
		
		switch ($this->properties[$a_property]["storage"])
		{
			case "session":
				$_SESSION["table"][$a_table_id][$a_user_id][$a_property]
					= $a_value;
				break;
				
			case "db":
				$ilDB->replace("table_properties", array(
					"table_id" => array("text", $a_table_id),
					"user_id" => array("integer", $a_user_id),
					"property" => array("text", $a_property)),
					array(
					"value" => array("text", $a_value)
					));
		}
	}
	
	/**
	* Get property in session or db
	*/
	function getProperty($a_table_id, $a_user_id, $a_property)
	{
		global $ilDB;
		
		switch ($this->properties[$a_property]["storage"])
		{
			case "session":
				return $_SESSION["table"][$a_table_id][$a_user_id][$a_property];
				break;
				
			case "db":
				$set = $ilDB->query("SELECT value FROM table_properties ".
					" WHERE table_id = ".$ilDB->quote($a_table_id, "text").
					" AND user_id = ".$ilDB->quote($a_user_id, "integer").
					" AND property = ".$ilDB->quote($a_property, "text")
					);
				$rec  = $ilDB->fetchAssoc($set);
				return $rec["value"];
				break;
		}
	}
	

}
?>
