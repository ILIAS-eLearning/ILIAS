<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once("./setup/classes/class.ilObjDefReader.php");

/**
* Class ilServiceReader
*
* Reads reads service information of services.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilServiceReader extends ilObjDefReader
{
    public function __construct($a_path, $a_name, $a_type)
    {
        parent::__construct($a_path, $a_name, $a_type);
    }
    
    public function getServices()
    {
        $this->startParsing();
    }

    
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }


    /**
    * clear the tables
    */
    public function clearTables()
    {
        global $ilDB;

        $ilDB->manipulate("DELETE FROM service_class");
    }


    /**
    * start tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @param	array		element attributes
    * @access	private
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        global $ilDB;

        
        switch ($a_name) {
            case 'service':
                $this->current_service = $this->name;
                $this->current_component = $this->type . "/" . $this->name;
                $ilDB->manipulateF(
                    "INSERT INTO il_component (type, name, id) " .
                    "VALUES (%s,%s,%s)",
                    array("text", "text", "text"),
                    array($this->type, $this->name, $a_attribs["id"])
                );
                
                $this->setComponentId($a_attribs['id']);
                break;
                
            case 'baseclass':
                $ilDB->manipulateF(
                    "INSERT INTO service_class (service, class, dir) " .
                    "VALUES (%s,%s,%s)",
                    array("text", "text", "text"),
                    array($this->name, $a_attribs["name"], $a_attribs["dir"])
                );
                break;
                
        }

        // smeyer: first read outer xml
        parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
    }
            
    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @access	private
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        parent::handlerEndTag($a_xml_parser, $a_name);
    }

            
    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		data
    * @access	private
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        parent::handlerCharacterData($a_xml_parser, $a_data);
        
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);

        if (!empty($a_data)) {
            switch ($this->current_tag) {
                case '':
            }
        }
    }
}
