<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import mapping
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilImportMapping
{
	var $mappings;
	var $install_id;
	var $install_url;
	
	protected $target_id = 0;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		$this->mappings = array();
	}

	/**
	 * Set Installation ID
	 *
	 * @param	string	Installation ID
	 */
	final function setInstallId($a_val)
	{
		$this->install_id = $a_val;
	}

	/**
	 * Get Installation ID
	 *
	 * @return	string	Installation ID
	 */
	final function getInstallId()
	{
		return $this->install_id;
	}

	/**
	 * Set Installation Url
	 *
	 * @param	string	Installation Url
	 */
	final function setInstallUrl($a_val)
	{
		$this->install_url = $a_val;
	}

	/**
	 * Get Installation Url
	 *
	 * @return	string	Installation Url
	 */
	final function getInstallUrl()
	{
		return $this->install_url;
	}
	
	/**
	 * set target id
	 * @param object $a_target_id
	 * @return 
	 */
	public final function setTagetId($a_target_id)
	{
		$this->target_id = $a_target_id;
	}
	
	/**
	 * get target id
	 * @return 
	 */
	public final function getTargetId()
	{
		return $this->target_id;
	}

	/**
	 * Add mapping
	 *
	 * @param	string		component
	 * @param	string		entity
	 * @param	string		old id
	 * @param	string		new id
	 */
	function addMapping($a_comp, $a_entity, $a_old_id, $a_new_id)
	{
		$this->mappings[$a_comp][$a_entity][$a_old_id] = $a_new_id;
	}

	/**
	 * Get a mapping
	 *
	 * @param	string		component
	 * @param	string		entity
	 * @param	string		old id
	 *
	 * @return	string		new id, or false if no mapping given
	 */
	function getMapping($a_comp, $a_entity, $a_old_id)
	{
		if(!isset ($this->mappings[$a_comp]) or !isset ($this->mappings[$a_comp][$a_entity]))
		{
			return false;
		}
		if (isset($this->mappings[$a_comp][$a_entity][$a_old_id]))
		{
			return $this->mappings[$a_comp][$a_entity][$a_old_id];
		}

		return false;
	}

	/**
	 * Get mapping
	 *
	 * @return	array	mapping
	 */
	function getAllMappings()
	{
		return $this->mappings;
	}

	/**
	 * Get mappings for entity
	 *
	 * @param	string	component
	 * @param	string	entity
	 * @return
	 */
	function getMappingsOfEntity($a_comp, $a_entity)
	{
		if (isset($this->mappings[$a_comp][$a_entity]))
		{
			return $this->mappings[$a_comp][$a_entity];
		}
		return array();
	}

}
?>