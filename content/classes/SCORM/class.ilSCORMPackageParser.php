<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("classes/class.ilMetaData.php"); // i guess we will neet this sometime

/**
* SCORM Package Parser
*
* @author
* @version $Id$
*
* @extends ilSaxParser
* @package content
*/
class ilSCORMPackageParser extends ilSaxParser
{
	var $cnt;				// counts open elements
	var $current_element;	// store current element type
	var $slm_object;


	/**
	* Constructor
	*
	* @param	object		$a_lm_object	must be of type ilObjLearningModule
	* @param	string		$a_xml_file		xml file
	* @access	public
	*/
	function ilSCORMPackageParser(&$a_slm_object, $a_xml_file)
	{
		parent::ilSaxParser($a_xml_file);
		$this->cnt = array();
		$this->current_element = array();
		$this->slm_object =& $a_slm_object;

	}

	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	function startParsing()
	{
		parent::startParsing();
	}

	/*
	* update parsing status for a element begin
	*/
	function beginElement($a_name)
	{
		if(!isset($this->status["$a_name"]))
		{
			$this->cnt[$a_name] == 1;
		}
		else
		{
			$this->cnt[$a_name]++;
		}
		$this->current_element[count($this->current_element)] = $a_name;
	}

	/*
	* update parsing status for an element ending
	*/
	function endElement($a_name)
	{
		$this->cnt[$a_name]--;
		unset ($this->current_element[count($this->current_element) - 1]);
	}

	/*
	* returns current element
	*/
	function getCurrentElement()
	{
		return ($this->current_element[count($this->current_element) - 1]);
	}

	/*
	* returns number of current open elements of type $a_name
	*/
	function getOpenCount($a_name)
	{
		if (isset($this->cnt[$a_name]))
		{
			return $this->cnt[$a_name];
		}
		else
		{
			return 0;
		}

	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" for starting or ending tag
	* @param	string		element/tag name
	* @param	array		array of attributes
	*/
	function buildTag ($type, $name, $attr="")
	{
		$tag = "<";

		if ($type == "end")
			$tag.= "/";

		$tag.= $name;

		if (is_array($attr))
		{
			while (list($k,$v) = each($attr))
				$tag.= " ".$k."=\"$v\"";
		}

		$tag.= ">";

		return $tag;
	}

	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{

		switch($a_name)
		{
			case "":
				break;

		}
		$this->beginElement($a_name);
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{

		switch($a_name)
		{
			case "":
				break;
		}
		$this->endElement($a_name);

	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			switch($this->getCurrentElement())
			{
				case "":
					break;
			}
		}

	}

}
?>
