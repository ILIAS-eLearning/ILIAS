<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract parent class for all page component plugin gui classes.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
abstract class ilPageComponentPluginGUI
{
	protected $plugin;
	protected $pc_gui;
	protected $pc;
	
	/**
	 * Set pc gui object
	 *
	 * @param object $a_val pc gui object	
	 */
	function setPCGUI($a_val)
	{
		$this->pc_gui = $a_val;
	}
	
	/**
	 * Get pc gui object
	 *
	 * @return object pc gui object
	 */
	function getPCGUI()
	{
		return $this->pc_gui;
	}
	
	/**
	 * Set plugin object
	 *
	 * @param object $a_val plugin object	
	 */
	function setPlugin($a_val)
	{
		$this->plugin = $a_val;
	}
	
	/**
	 * Get plugin object
	 *
	 * @return object plugin object
	 */
	function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set Mode.
	 *
	 * @param	string	$a_mode	Mode
	 */
	final function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	 * Get Mode.
	 *
	 * @return	string	Mode
	 */
	final function getMode()
	{
		return $this->mode;
	}

	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		if ($this->getMode() == ilPageComponentPlugin::CMD_INSERT)
		{
			$this->insert();
		}
		else if ($this->getMode() == ilPageComponentPlugin::CMD_EDIT)
		{
			$this->edit();
		}
		
	}

	abstract function executeCommand();
	abstract function insert();
	abstract function edit();
	abstract function create();
	abstract function getElementHTML($a_mode, array $a_properties, $plugin_version);
	
	function createElement(array $a_properties)
	{
		return $this->getPCGUI()->createElement($a_properties);
	}
	
	function updateElement(array $a_properties)
	{
		return $this->getPCGUI()->updateElement($a_properties);
	}
	
	/**
	 * Return to parent
	 */
	function returnToParent()
	{
		$this->getPCGUI()->returnToParent();
	}

	/**
	 * Set properties
	 *
	 * @param array $a_val properties array	
	 */
	function setProperties(array $a_val)
	{
		$co = $this->getPCGUI()->getContentObject();
		if (is_object($co))
		{
			$co->setProperties($a_val);
		}
	}
	
	/**
	 * Get properties
	 *
	 * @return array properties array
	 */
	function getProperties()
	{
		$co = $this->getPCGUI()->getContentObject();
		if (is_object($co))
		{
			return $co->getProperties($a_val);
		}
		return array();
	}

	/**
	 * Add creation button
	 *
	 * @param
	 * @return
	 */
	final protected function addCreationButton($a_form)
	{
		global $lng;
		
		$a_form->addCommandButton("create_plug", $lng->txt("save"));
	}
}
?>
