<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cache class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesCache
 */
class ilCache
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_component, $a_cache_name, $a_use_long_content = false)
	{
		$this->setComponent($a_component);
		$this->setName($a_cache_name);
		$this->setUseLongContent($a_use_long_content);
	}
	
	/**
	 * Set component
	 *
	 * @param	string	component
	 */
	function setComponent($a_val)
	{
		$this->component = $a_val;
	}
	
	/**
	 * Get component
	 *
	 * @return	string	component
	 */
	protected function getComponent()
	{
		return $this->component;
	}
	
	/**
	 * Set name
	 *
	 * @param	string	name
	 */
	protected function setName($a_val)
	{
		$this->name = $a_val;
	}
	
	/**
	 * Get name
	 *
	 * @return	string	name
	 */
	protected function getName()
	{
		return $this->name;
	}
	
	/**
	 * Set use long content
	 *
	 * @param	boolean	use long content
	 */
	protected function setUseLongContent($a_val)
	{
		$this->use_long_content = $a_val;
	}
	
	/**
	 * Get use long content
	 *
	 * @return	boolean	use long content
	 */
	protected function getUseLongContent()
	{
		return $this->use_long_content;
	}
	
	/**
	 * Set expires after x seconds
	 *
	 * @param	int	expires after x seconds
	 */
	public function setExpiresAfter($a_val)
	{
		$this->expires_after = $a_val;
	}
	
	/**
	 * Get expires after x seconds
	 *
	 * @return	int	expires after x seconds
	 */
	public function getExpiresAfter()
	{
		return $this->expires_after;
	}
	
	/**
	 * Get entry
	 *
	 * @param	string	entry id
	 * @return	string	entry value
	 */
	final public function getEntry($a_id)
	{
		if ($this->readEntry($a_id))	// cache hit
		{
			$this->last_access = "hit";
			return $this->entry;
		}
		$this->last_access = "miss";
	}
	
	/**
	 * Read entry
	 *
	 * @param
	 * @return
	 */
	final private function readEntry($a_id)
	{
		global $ilDB;
		
		$table = $this->getUseLongContent()
			? "cache_clob"
			: "cache_text";
	
		$set = $ilDB->query("SELECT value FROM $table WHERE ".
			"component = ".$ilDB->quote($this->getComponent(), "text")." AND ".
			"name = ".$ilDB->quote($this->getName(), "text")." AND ".
			"expire_time > ".$ilDB->quote(time(), "integer")." AND ".
			"ilias_version = ".$ilDB->quote(ILIAS_VERSION_NUMERIC, "text")." AND ".
			"entry_id = ".$ilDB->quote($a_id, "text")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->entry = $rec["value"];
			return true;
		}
		return false;
	}
	
	/**
	 * Last access
	 */
	function getLastAccessStatus()
	{
		return $this->last_access;
	}
	
	
	/**
	 * Store entry
	 *
	 * @param
	 * @return
	 */
	function storeEntry($a_id, $a_value)
	{
		global $ilDB;

		$table = $this->getUseLongContent()
			? "cache_clob"
			: "cache_text";
		$type =  $this->getUseLongContent()
			? "clob"
			: "text";
			
		// do not store values, that do not fit into the text field
		if (strlen($a_value) > 4000 && $type == "text")
		{
			return;
		}

		$set = $ilDB->replace($table, array(
			"component" => array("text", $this->getComponent()),
			"name" => array("text", $this->getName()),
			"entry_id" => array("text", $a_id)
			), array (
			"value" => array($type, $a_value),
			"expire_time" => array("integer", (int) (time() + $this->getExpiresAfter())),
			"ilias_version" => array("text", ILIAS_VERSION_NUMERIC)
			));
		
		// In 1/2000 times, delete old entries
		$num = rand(1,2000);
		if ($num == 500)
		{
			$ilDB->manipulate("DELETE FROM $table WHERE ".
				" ilias_version <> ".$ilDB->quote(ILIAS_VERSION_NUMERIC, "text").
				" OR expire_time < ".$ilDB->quote(time(), "integer")
				);
		}
			
	}
	
}
?>
