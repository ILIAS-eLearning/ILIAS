<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Rich Text Editor base class
*
* This class provides access methods to a Rich Text Editor (RTE)
* integrated in ILIAS
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilRTE.php
*/
class ilRTE
{
	/**
	* Additional plugins for the rich text editor
	*
	* Additional plugins for the rich text editor
	*
	* @var array
	*/
	var $plugins;
	var $tpl;
	
	function ilRTE()
	{
		global $tpl;
		$this->tpl =& $tpl;
		$this->plugins = array();
	}
	
	/**
	* Adds a plugin to the plugin list
	*
	* Adds a plugin to the plugin list
	*
	* @param string $a_plugin_name The name of the plugin
	* @access public
	*/
	function addPlugin($a_plugin_name)
	{
		array_push($this->plugins, $a_plugin_name);
	}
	
	/**
	* Removes a plugin from the plugin list
	*
	* Removes a plugin from the plugin list
	*
	* @param string $a_plugin_name The name of the plugin
	* @access public
	*/
	function removePlugin($a_plugin_name)
	{
		$key = array_search($a_plugin_name, $this->plugins);
		if ($key !== FALSE)
		{
			unset($this->plugins[$key]);
		}
	}
	
	/**
	* Adds support for an RTE in an ILIAS form
	*
	* Adds support for an RTE in an ILIAS form
	*
	* @access public
	*/
	function addRTESupport()
	{
		// must be overwritten in parent classes
	}
	
	function _getRTEClassname()
	{
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$editor = ilObjAdvancedEditing::_getRichTextEditor();
		switch ($editor)
		{
			case "tinymce":
				return "ilTinyMCE";
				break;
			default:
				return "ilRTE";
				break;
		}
	}

}

?>
