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

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCPlugged
* Plugged content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCPlugged extends ilPageContent
{
	var $dom;
	var $plug_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("plug");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->plug_node =& $a_node->first_child();		// this is the Plugged node
	}

	/**
	* Create plugged node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_plugin_name,
		$a_plugin_version)
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->plug_node =& $this->dom->create_element("Plugged");
		$this->plug_node =& $this->node->append_child($this->plug_node);
		$this->plug_node->set_attribute("PluginName", $a_plugin_name);
		$this->plug_node->set_attribute("PluginVersion", $a_plugin_version);
	}

	/**
	* Set properties of plugged component.
	*
	* @param	array	$a_properties		component properties
	*/
	function setProperties($a_properties)
	{
		if (!is_object($this->plug_node))
		{
			return;
		}
		
		// delete properties
		$children = $this->plug_node->child_nodes();
		for($i=0; $i<count($children); $i++)
		{
			$this->plug_node->remove_child($children[$i]);
		}
		
		// set properties
		foreach($a_properties as $key => $value)
		{
			$prop_node = $this->dom->create_element("PluggedProperty");
			$prop_node =& $this->node->append_child($this->plug_node);
			$prop_node->set_attribute("Name", $key);
			$prop_node->set_content($value);
		}
	}

	/**
	* Get properties of plugged component
	*
	* @return	string		characteristic
	*/
	function getProperties()
	{
		$properties = array();
		
		if (is_object($this->plug_node))
		{
			// delete properties
			$children = $this->plug_node->child_nodes();
			for($i=0; $i<count($children); $i++)
			{
				if ($children[$i]->node_name() == "PluggedProperty")
				{
					$properties[$children[$i]->get_attribute("Name")] =
						$children[$i]->get_content();
				}
			}
		}
		
		return $properties;
	}
	
	/**
	* Set version of plugged component
	*
	* @param	string	$a_version		version
	*/
	function setPluginVersion($a_version)
	{
		if (!empty($a_version))
		{
			$this->plug_node->set_attribute("PluginVersion", $a_version);
		}
		else
		{
			if ($this->plug_node->has_attribute("PluginVersion"))
			{
				$this->plug_node->remove_attribute("PluginVersion");
			}
		}
	}

	/**
	* Get version of plugged component
	*
	* @return	string		version
	*/
	function getPluginVersion()
	{
		if (is_object($this->plug_node))
		{
			return $this->plug_node->get_attribute("PluginVersion");
		}
	}

	/**
	* Set name of plugged component
	*
	* @param	string	$a_name		name
	*/
	function setPluginName($a_name)
	{
		if (!empty($a_name))
		{
			$this->plug_node->set_attribute("PluginName", $a_name);
		}
		else
		{
			if ($this->plug_node->has_attribute("PluginName"))
			{
				$this->plug_node->remove_attribute("PluginName");
			}
		}
	}

	/**
	* Get name of plugged component
	*
	* @return	string		name
	*/
	function getPluginName()
	{
		if (is_object($this->plug_node))
		{
			return $this->plug_node->get_attribute("PluginName");
		}
	}

}

?>
