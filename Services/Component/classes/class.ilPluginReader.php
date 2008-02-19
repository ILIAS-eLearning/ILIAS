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

include_once("./classes/class.ilSaxParser.php");

/**
* Class ilPluginReader
*
* Reads plugin information of plugin.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilPluginReader extends ilSaxParser
{

	function ilPluginReader($a_path, $a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		parent::ilSaxParser($a_path);

die("Deprecated. Plugin information is stored in plugin.php");
		
		$this->ctype = $a_ctype;
		$this->cname = $a_cname;
		$this->slot_id = $a_slot_id;
		$this->pname = $a_pname;
	}
	
	function startParsing()
	{
		parent::startParsing();
	}
	
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
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

		switch ($a_name)
		{
			case 'plugin':
				
				// check whether record exists
				$q = "SELECT * FROM il_plugin WHERE ".
					" component_type = ".$ilDB->quote($this->ctype).
					" AND component_name = ".$ilDB->quote($this->cname).
					" AND slot_id = ".$ilDB->quote($this->slot_id).
					" AND name = ".$ilDB->quote($this->pname);
				$set = $ilDB->query($q);
				if ($set->numRows() == 0)
				{
					$q = "REPLACE INTO il_plugin (component_type,component_name,slot_id,".
						"name, id, last_update_version, current_version, ilias_min_version,".
						" ilias_max_version, active) VALUES ".
						"(".$ilDB->quote($this->ctype).",".
						$ilDB->quote($this->cname).",".
						$ilDB->quote($this->slot_id).",".
						$ilDB->quote($this->pname).",".
						$ilDB->quote($a_attribs["id"]).",".
						$ilDB->quote("0.0.0").",".
						$ilDB->quote($a_attribs["version"]).",".
						$ilDB->quote($a_attribs["ilias_min_version"]).",".
						$ilDB->quote($a_attribs["ilias_max_version"]).",".
						"0)";
					$ilDB->query($q);
				}
				else
				{
					$q = "UPDATE il_plugin SET ".
						" id = ".$ilDB->quote($a_attribs["id"]).",".
						" current_version = ".$ilDB->quote($a_attribs["version"]).",".
						" ilias_min_version = ".$ilDB->quote($a_attribs["ilias_min_version"]).",".
						" ilias_max_version = ".$ilDB->quote($a_attribs["ilias_max_version"]).
						" WHERE ".
						" component_type = ".$ilDB->quote($this->ctype).
						" AND component_name = ".$ilDB->quote($this->cname).
						" AND slot_id = ".$ilDB->quote($this->slot_id).
						" AND name = ".$ilDB->quote($this->pname);
					$ilDB->query($q);
				}
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
