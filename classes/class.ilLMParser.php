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

require_once("classes/class.ilPageObject.php");
require_once("classes/class.ilStructureObject.php");
require_once("classes/class.ilLearningModule.php");
require_once("classes/class.ilMetaData.php");

/**
* Learning Module Parser
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
* @package ilias-core
*/
class ilLMParser extends ilSaxParser
{
	var $cnt;				// counts open elements
	var $current_element;	// store current element type
	var $learning_module;	// current learning module
	var $page_object;		// current page object
	var $structure_objects;	// array of current structure objects
	var $current_object;	// at the time a LearningModule, PageObject or StructureObject
	var $meta_data;			// current meta data object

	/**
	* Constructor
	* @access	public
	*/
	function ilLMParser($a_xml_file)
	{
		parent::ilSaxParser($a_xml_file);
		$this->cnt = array();
		$this->current_element = array();
		$this->structure_objects = array();
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
			case "LearningModule":
				$this->learning_module =& new ilLearningModule();
				$this->current_object =& $this->learning_module;
				break;

			case "StructurObject":
				$this->structure_objects[count($this->structure_objects)]
					=& new ilStructureObject();
				$this->current_object =& $this->structure_objects[count($this->structure_objects) - 1];
				break;

			case "PageObject":
				$this->page_object =& new ilPageObject();
				$this->current_object =& $this->page_object;
				break;

			case "PageAlias":
				$this->page_object->setAlias(true);
				$this->page_object->setOriginID($a_attribs["OriginId"]);
				break;

			case "MetaData":
				//$this->in_meta = true;
				$this->meta_data =& new ilMetaData();
				$this->current_object->assignMetaData($this->meta_data);
				break;

			case "Identifier":
				$this->meta_data->setIdentifierEntryID($a_attribs["Entry"]);
				$this->meta_data->setIdentifierCatalog($a_attribs["Catalog"]);
				break;

			case "Paragraph":
				$this->page_obj_content.= $this->buildTag("start", $a_name, $a_attribs);
				break;
		}
		$this->beginElement($a_name);
//echo "Begin Tag: $a_name<br>";
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		switch($a_name)
		{
			case "PageObject":
				$this->page_obj_content.= $this->buildTag("end", $a_name);
echo htmlentities($this->page_obj_content)."<br><br>";
				break;

			case "MetaObject":
				break;

			case "Paragraph":
				$this->page_obj_content.= $this->buildTag("end", $a_name);
				break;
		}
		$this->endElement($a_name);
//echo "End Tag: $a_name<br>";
	}
	
	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		//$a_data = preg_replace("/\n/","",$a_data);
		//$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			switch($this->getCurrentElement())
			{
				case "Paragraph":
					$this->page_obj_content.= $a_data;
					break;
			}
		}
//echo "Char Data: $a_data<br>";
	}

}
?>
