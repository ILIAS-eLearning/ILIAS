<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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

	/**
	* Constructor
	* @access	public
	*/
	function ilPCPluggedGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id,
		$a_plugin_name = "")
	{
		global $ilCtrl;
		
		$this->setPluginName($a_plugin_name);
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id);
		
		if ($a_plugin_name != "")
		{
			$ilCtrl->setParameter($this, "plugin_name", rawurlencode($a_plugin_name));
		}
		$ilCtrl->saveParameter($this, "plugin_name");
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
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
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
			if ($a_insert)
			{
				$plugin_obj->setMode(ilPageComponentPlugin::CMD_INSERT);
			}
			else
			{
				$plugin_obj->setMode(ilPageComponentPlugin::CMD_EDIT);
			}
			$html = $ilCtrl->getHTML($plugin_obj);
        }
		
		$tpl->setContent($html);
	}


	/**
	* Create new plugged component
	*/
	function create()
	{
		$this->content_obj = new ilPCPlugged($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$properties = array(
			"Table" => $_POST["table"]
			);
		$this->content_obj->setProperties($properties);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}

	/**
	* Update Section.
	*/
	function update()
	{
		$properties = array(
			"Table" => $_POST["table"]);
		$this->content_obj->setProperties($properties);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}
}
?>
