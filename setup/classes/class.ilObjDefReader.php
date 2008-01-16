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
* Object definition reader (reads objects tags in module.xml and service.xml files
*
* Reads reads module information of modules.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilModuleReader.php 15675 2008-01-06 13:53:17Z akill $
*
*/
class ilObjDefReader extends ilSaxParser
{

	function ilModuleReader($a_path)
	{
		parent::ilSaxParser($a_path);
	}
	
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* clear the tables
	*/
	static function clearTables()
	{
		global $ilDB;

		$q = "DELETE FROM il_object_def";
		$ilDB->query($q);
		
		$q = "DELETE FROM il_object_subobj";
		$ilDB->query($q);
	}

	/**
	* start tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @param	array		element attributes
	* @access	private
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		global $ilDB;

		$this->current_tag = $a_name;
		
		switch ($a_name)
		{
			case 'object':
				$this->current_object = $a_attribs["id"];
				$q = "REPLACE INTO il_object_def (id, class_name, component,location,".
					"checkbox,inherit,translate,devmode,allow_link,allow_copy,rbac,sideblock,system) VALUES (".
					$ilDB->quote($a_attribs["id"]).",".
					$ilDB->quote($a_attribs["class_name"]).",".
					$ilDB->quote($this->current_component).",".
					$ilDB->quote($this->current_component."/".$a_attribs["dir"]).",".
					$ilDB->quote((int) $a_attribs["checkbox"]).",".
					$ilDB->quote((int) $a_attribs["inherit"]).",".
					$ilDB->quote($a_attribs["translate"]).",".
					$ilDB->quote((int) $a_attribs["devmode"]).",".
					$ilDB->quote((int) $a_attribs["allow_link"]).",".
					$ilDB->quote((int) $a_attribs["allow_copy"]).",".
					$ilDB->quote((int) $a_attribs["rbac"]).",".
					$ilDB->quote((int) $a_attribs["sideblock"]).",".
					$ilDB->quote((int) $a_attribs["system"]).")";
				$ilDB->query($q);
				break;
			
			case "subobj":
				$ilDB->query("INSERT INTO il_object_subobj (parent, subobj, max) VALUES (".
					$ilDB->quote($this->current_object).",".
					$ilDB->quote($a_attribs["id"]).",".
					$ilDB->quote($a_attribs["max"]).")");
				break;

			case "parent":
				$ilDB->query("INSERT INTO il_object_subobj (parent, subobj, max) VALUES (".
					$ilDB->quote($a_attribs["id"]).",".
					$ilDB->quote($this->current_object).",".
					$ilDB->quote($a_attribs["max"]).")");
				break;
		}
	}
			
	/**
	* end tag handler
	* 
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @access	private
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
	}

			
	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		data
	* @access	private
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);

		if (!empty($a_data))
		{
			switch ($this->current_tag)
			{
				case '':
			}
		}
	}

}
