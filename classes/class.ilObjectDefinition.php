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
* parses the objects.xml
* it handles the xml-description of all ilias objects
*
* @author Stefan Meyer <smeyer@databay>
* @version $Id$
*
* @extends PEAR
* @package ilias-core
*/
require_once("classes/class.ilSaxParser.php");

class ilObjectDefinition extends ilSaxParser
{
	/**
	* // TODO: var is not used
	* object id of specific object
	* @var obj_id
	* @access private
	*/
	var $obj_id;

	/**
	* parent id of object
	* @var parent id
	* @access private
	*/
	var $parent;

	/**
	* array representation of objects
	* @var objects
	* @access private
	*/
	var $obj_data;

	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ilObjectDefinition()
	{
		parent::ilSaxParser("./objects.xml");
	}

// PUBLIC METHODS
	/**
	* get object definition by type
	*
	* @access	public
	*/
	function getDefinition($a_obj_name)
	{
		return $this->obj_data[$a_obj_name];
	}

	/**
	* get class name by type
	*
	* @access	public
	*/
	function getClassName($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["class_name"];
	}

	/**
	* should the object get a checkbox (needed for 'cut','copy' ...)
	* 
	* @param	string	object type
	* @access	public
	*/
	function hasCheckbox($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["checkbox"];
	}
	
	/**
	* Does object permits stopping inheritance?
	* 
	* @param	string	object type
	* @access	public
	*/
	function stopInheritance($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["inherit"];
	}

	/**
	* get properties by type
	* 
	* @access	public
	*/
	function getProperties($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["properties"];
	}

	/**
	* get subobjects by type
	* 
	* @access	public
	*/
	function getSubObjects($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["subobjects"];
	}

	/**
	* get possible actions by type
	* 
	* @access	public
	*/
	function getActions($a_obj_name)
	{
		$ret = (is_array($this->obj_data[$a_obj_name]["actions"])) ?
			$this->obj_data[$a_obj_name]["actions"] :
			array();
		return $ret;
	}

	/**
	* get default property by type
	* 
	* @access	public
	*/
	function getFirstProperty($a_obj_name)
	{
		$data = array_keys($this->obj_data[$a_obj_name]["properties"]);
		return $data[0];
	}

	/**
	* get name of property by type
	* 
	* @access	public
	*/
	function getPropertyName($a_cmd, $a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["properties"][$a_cmd]["lng"];
	}

	/**
	* get a string of all subobjects by type
	* 
	* @access	public
	*/
	function getSubObjectsAsString($a_obj_type)
	{
		$string = "";
		
		if (is_array($this->obj_data[$a_obj_type]["subobjects"]))
		{
			$data = array_keys($this->obj_data[$a_obj_type]["subobjects"]);
			
			$string = "'".implode("','", $data)."'";
		}
			return $string;
	}

// PRIVATE METHODS
	/**
	* set event handler
	* 
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* start tag handler
	* 
	* @access	private
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch($a_name)
		{
			case 'objects':
				$this->current_tag = '';
				break;
			case 'object':
				$this->parent_tag_name = $a_attribs["name"];
				$this->current_tag = '';
				$this->obj_data["$a_attribs[name]"]["name"] = $a_attribs["name"];
				$this->obj_data["$a_attribs[name]"]["class_name"] = $a_attribs["class_name"];
				$this->obj_data["$a_attribs[name]"]["checkbox"] = $a_attribs["checkbox"];
				$this->obj_data["$a_attribs[name]"]["inherit"] = $a_attribs["inherit"];
				break;
			case 'subobj':
				$this->current_tag = "subobj";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["name"] = $a_attribs["name"];
				// NUMBER OF ALLOWED SUBOBJECTS (NULL means no limit)
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["max"] = $a_attribs["max"];
				break;
			case 'property':
				$this->current_tag = "property";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["name"] = $a_attribs["name"];
				break;
			case 'action':
				$this->current_tag = "action";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["actions"][$this->current_tag_name]["name"] = $a_attribs["name"];
				break;
		}
				

	}

	/**
	* end tag handler
	* 
	* @access	private
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			switch($this->current_tag)
			{
				case "subobj":
					$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["lng"] = $a_data;
					break;
				case "action" :
					$this->obj_data[$this->parent_tag_name]["actions"][$this->current_tag_name]["lng"] = $a_data;
					break;
				case "property" :
					$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["lng"] = $a_data;
					break;
				default:
					break;
			}
		}
	}

	/**
	* end tag handler
	* 
	* @access	private
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		$this->current_tag = '';
		$this->current_tag_name = '';
	}
}
?>
