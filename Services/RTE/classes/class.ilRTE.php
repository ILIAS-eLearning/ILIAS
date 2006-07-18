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

	/**
	* synchronises appearances of media objects in $a_text with media
	* object usage table
	*
	* @param	string	$a_text			text, including media object tags
	* @param	string	$a_usage_type	type of context of usage, e.g. cat:html
	* @param	int		$a_usage_id		if of context of usage, e.g. category id
	*/
	function _cleanupMediaObjectUsage($a_text, $a_usage_type, $a_usage_id)
	{
		// get current stored mobs
		include_once("./content/classes/Media/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject($a_usage_type,
			$a_usage_id);
		
		while (eregi("data\/".CLIENT_ID."\/mobs\/mm_([0-9]+)", $a_text, $found))
		{
			$a_text = str_replace($found[0], "", $a_text);
			if (!in_array($found[1], $mobs))
			{
				// save usage if missing
				ilObjMediaObject::_saveUsage($found[1], $a_usage_type,
					$a_usage_id);
			}
			else
			{
				// if already saved everything ok -> take mob out of mobs array
				unset($mobs[$found[1]]);
			}
		}
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, $a_usage_type,
				$a_usage_id);
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
		}
	}
}

?>
