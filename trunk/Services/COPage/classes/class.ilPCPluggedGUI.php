<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPCPlugged.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCPluggedGUI
 *
 * User Interface for plugged page component
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCPluggedGUI extends ilPageContentGUI
{
	protected $current_plugin = null;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilPCPluggedGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id,
		$a_plugin_name = "", $a_pc_id = "")
	{
		global $ilCtrl;
		$this->setPluginName($a_plugin_name);
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);		
	}

	/**
	* Set PluginName.
	*
	* @param	string	$a_pluginname	PluginName
	*/
	function setPluginName($a_pluginname)
	{
		$this->pluginname = $a_pluginname;
	}

	/**
	* Get PluginName.
	*
	* @return	string	PluginName
	*/
	function getPluginName()
	{
		return $this->pluginname;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilPluginAdmin, $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->setBackTarget($lng->txt("pg"), $ilCtrl->getLinkTarget($this, "returnToParent"));
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get all plugins and check, whether next class belongs to one
		// of them, then forward
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE,
			"COPage", "pgcp");
		foreach ($pl_names as $pl_name)
		{
			if ($next_class == strtolower("il".$pl_name."plugingui"))
			{
				$plugin = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE,
					"COPage", "pgcp", $pl_name);
				$this->current_plugin = $plugin;
				$this->setPluginName($pl_name);
				$gui_obj = $plugin->getUIClassInstance();
				$gui_obj->setPCGUI($this);
				$ret = $this->ctrl->forwardCommand($gui_obj);
			}
		}

		// get current command
		$cmd = $this->ctrl->getCmd();

		if ($next_class == "" || $next_class == "ilpcpluggedgui")
		{
			$ret = $this->$cmd();
		}

		return $ret;
	}

	/**
	* Insert new section form.
	*/
	function insert()
	{
		$this->edit(true);
	}

	/**
	 * Edit section form.
	 */
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng, $ilPluginAdmin;
		
		$this->displayValidationError();
		
		// edit form
		if ($a_insert)
		{
			$plugin_name = $this->getPluginName();
		}
		else
		{
			$plugin_name = $this->content_obj->getPluginName();
		}
        if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name))
        {
			$plugin_obj = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, "COPage",
				"pgcp", $plugin_name);
			$gui_obj = $plugin_obj->getUIClassInstance();
			$gui_obj->setPCGUI($this);
			if ($a_insert)
			{
				$gui_obj->setMode(ilPageComponentPlugin::CMD_INSERT);
			}
			else
			{
				$gui_obj->setMode(ilPageComponentPlugin::CMD_EDIT);
			}
			$html = $ilCtrl->getHTML($gui_obj);
        }
		
		$tpl->setContent($html);
	}


	/**
	 * Create new element
	 */
	function createElement(array $a_properties)
	{
		$this->content_obj = new ilPCPlugged($this->getPage());
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id,
			$this->current_plugin->getPluginName(), $this->current_plugin->getVersion());
		$this->content_obj->setProperties($a_properties);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			return true;
		}
		return false;
	}

	/**
	 * Update element
	 */
	function updateElement(array $a_properties)
	{
		$this->content_obj->setProperties($a_properties);
		$this->content_obj->setPluginVersion($this->current_plugin->getVersion());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Return to parent
	 */
	function returnToParent()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}
	
}
?>
