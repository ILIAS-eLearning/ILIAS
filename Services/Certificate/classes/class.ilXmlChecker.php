<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
* XML checker
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @extends ilSaxParser
* @ingroup ModulesTest
*/
class ilXMLChecker extends ilSaxParser
{
    public $error_code;
    public $error_line;
    public $error_col;
    public $error_msg;
    public $has_error;
    public $size;
    public $elements;
    public $attributes;
    public $texts;
    public $text_size;
    
    /**
    * Constructor
    *
    * @param	string		$a_xml_file		xml file
    *
    * @access	public
    */
    public function __construct($a_xml_file = '', $throwException = false)
    {
        parent::__construct($a_xml_file, $throwException);
        $this->has_error = false;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start the parser
    */
    public function startParsing()
    {
        parent::startParsing();
    }

    /**
    * parse xml file
    *
    * @access	private
    */
    public function parse($a_xml_parser, $a_fp = null)
    {
        switch ($this->getInputType()) {
            case 'file':

                while ($data = fread($a_fp, 4096)) {
                    $parseOk = xml_parse($a_xml_parser, $data, feof($a_fp));
                }
                break;
                
            case 'string':
                $parseOk = xml_parse($a_xml_parser, $this->getXMLContent());
                break;
        }
        if (!$parseOk
           && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE)) {
            $this->error_code = xml_get_error_code($a_xml_parser);
            $this->error_line = xml_get_current_line_number($a_xml_parser);
            $this->error_col = xml_get_current_column_number($a_xml_parser);
            $this->error_msg = xml_error_string($a_xml_parser);
            $this->has_error = true;
            return false;
        }
        return true;
    }
    
    /**
    * handler for begin of element
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        $this->elements++;
        $this->attributes += count($a_attribs);
    }

    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
    }

    /**
    * handler for character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        $this->texts++;
        $this->text_size += strlen($a_data);
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
  
    public function getErrorLine()
    {
        return $this->error_line;
    }
  
    public function getErrorColumn()
    {
        return $this->error_col;
    }
  
    public function getErrorMessage()
    {
        return $this->error_msg;
    }
  
    public function getFullError()
    {
        return "Error: " . $this->error_msg . " at line:" . $this->error_line . " column:" . $this->error_col;
    }
  
    public function getXMLSize()
    {
        return $this->size;
    }
  
    public function getXMLElements()
    {
        return $this->elements;
    }
  
    public function getXMLAttributes()
    {
        return $this->attributes;
    }
  
    public function getXMLTextSections()
    {
        return $this->texts;
    }
  
    public function getXMLTextSize()
    {
        return $this->text_size;
    }
  
    public function hasError()
    {
        return $this->has_error;
    }
}
