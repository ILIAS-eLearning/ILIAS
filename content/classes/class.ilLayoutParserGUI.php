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

/**
* Layout Parser. Parse presentation layout for object (e.g. learning module)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
* @package content
*/
class ilLayoutParserGUI extends ilSaxParser
{
	var $current_element;


	/**
	* Constructor
	* @access	public
	*/
	function ilLayoutParser(&$a_lm_object, $a_xml_file)
	{
		global $ilias, $lng;
		parent::ilSaxParser($a_xml_file);

		$this->ilias = &$ilias;
		$this->lng = &$lng;
		$this->current_element = array();
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

	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
//echo "BeginTag:$a_name:<br>";
		switch($a_name)
		{
			case "ilFrameset
		}
		$this->beginElement($a_name);
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
//echo "EndTag:$a_name:<br>";
		/*
		switch($a_name)
		{
		}
		*/
		$this->endElement($a_name);
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
//echo "Data:$a_name:<br>";
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			switch($this->getCurrentElement())
			{
				case "Paragraph":
					$this->paragraph->appendText($a_data);
//echo "setText(".htmlentities($a_data)."), strlen:".strlen($a_data)."<br>";
					break;
			}
		}

	}

}
?>
