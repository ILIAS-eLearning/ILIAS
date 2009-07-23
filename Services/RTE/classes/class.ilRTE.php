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
	var $buttons;
	var $tpl;
	var $ctrl;
	var $lng;	
	
	function ilRTE()
	{
		global $tpl, $ilCtrl, $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->plugins = array();
		$this->buttons = array();
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
	* Adds a button to the button list
	*
	* Adds a button to the button list
	*
	* @param string $a_button_name The name of the button
	* @access public
	*/
	function addButton($a_button_name)
	{
		array_push($this->buttons, $a_button_name);
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
	* Removes a button from the button list
	*
	* Removes a button from the button list
	*
	* @param string $a_button_name The name of the button
	* @access public
	*/
	function removeButton($a_button_name)
	{
		$key = array_search($a_button_name, $this->buttons);
		if ($key !== FALSE)
		{
			unset($this->buttons[$key]);
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
	
	/**
	* Adds support for an user text editor
	*
	* @access public
	*/
	function addUserTextEditor($editor_selector)
	{
		// must be overwritten in parent classes
	}

	/**
	* Adds custom support for an RTE in an ILIAS form
	*
	* Adds custom support for an RTE in an ILIAS form
	*
	* @access public
	*/
	function addCustomRTESupport($obj_id, $obj_type, $tags)
	{
		// must be overwritten in parent classes
	}
	
	
	function _getRTEClassname()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
	
	/**
	* replaces image source from mob image urls with the mob id or
	* replaces mob id with the correct image source
	*
	* @param	string	$a_text			text, including media object tags
	* @param  integer $a_direction 0 to replace image src => mob id, 1 to replace mob id => image src
	* @return string The text containing the replaced media object src
	*/
	function _replaceMediaObjectImageSrc($a_text, $a_direction = 0)
	{
		if (!strlen($a_text)) return $a_text;
		switch ($a_direction)
		{
			case 0:
				$a_text = preg_replace("/src\=\"(.*?\/mobs\/mm_([0-9]+)\/.*?)\"/", "src=\"il_" . IL_INST_ID . "_mob_" . "\\2" . "\"", $a_text);
				break;
			default:
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
				$resulttext = $a_text;
				if (preg_match_all("/src\=\"il_([0-9]+)_mob_([0-9]+)\"/", $a_text, $matches))
				{
					foreach ($matches[2] as $idx => $mob)
					{
						if (ilObjMediaObject::_exists($mob))
						{
							$mob_obj =& new ilObjMediaObject($mob);
							$replace = "il_" . $matches[1][$idx] . "_mob_" . $mob;
							$resulttext = str_replace("src=\"$replace\"", "src=\"" . ILIAS_HTTP_PATH . "/data/" . CLIENT_ID . "/mobs/mm_" . $mob . "/" . $mob_obj->getTitle() . "\"", $resulttext);
						}
					}
				}
				$a_text = $resulttext;
				break;
		}
		return $a_text;
	}
	
	/**
	* Returns all media objects found in the passed string
	*
	* @param	string	$a_text			text, including media object tags
	* @param  integer $a_direction 0 to find image src, 1 to find mob id
	* @return array array of media objects
	*/
	function _getMediaObjects($a_text, $a_direction = 0)
	{
		if (!strlen($a_text)) return array();
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		
		$mediaObjects = array();
		switch ($a_direction)
		{
			case 0:
				if(preg_match_all("/src\=\"(.*?\/mobs\/mm_([0-9]+)\/.*?)\"/", $a_text, $matches))
				{
					foreach ($matches[2] as $idx => $mob)
					{
						if (ilObjMediaObject::_exists($mob) && !in_array($mob, $mediaObjects))
						{
							$mediaObjects[] = $mob;
						}
					}
				}
				break;
			default:
				
				if(preg_match_all("/src\=\"il_([0-9]+)_mob_([0-9]+)\"/", $a_text, $matches))
				{
					foreach ($matches[2] as $idx => $mob)
					{
						if (ilObjMediaObject::_exists($mob) && !in_array($mob, $mediaObjects))
						{
							$mediaObjects[] = $mob;
						}
					}
				}
				break;
		}
		return $mediaObjects;
	}
	
	public function setRTERootBlockElement()
	{
		// must be overwritten in sub classes
	}
	
	public function getRTERootBlockElement()
	{
		// must be overwritten in sub classes
	}
	
	public function disableButtons()
	{
		// must be overwritten in sub classes
	}
	
	public function getDisabledButtons()
	{
		// must be overwritten in sub classes
	}
}

?>
