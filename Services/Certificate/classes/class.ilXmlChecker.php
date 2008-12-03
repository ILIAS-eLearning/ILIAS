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

require_once("./classes/class.ilSaxParser.php");

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
  var $error_code;
  var $error_line;
  var $error_col;
  var $error_msg;
	var $has_error;
  var $size;
  var $elements;
  var $attributes;
  var $texts;
  var $text_size;
	
	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function ilXMLChecker($a_xml_file = '')
	{
		parent::ilSaxParser($a_xml_file);
		$this->has_error = FALSE;
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

	/**
	* start the parser
	*/
	function startParsing()
	{
		parent::startParsing();
	}

	/**
	* parse xml file
	* 
	* @access	private
	*/
	function parse($a_xml_parser,$a_fp = null)
	{
		switch($this->getInputType())
		{
			case 'file':

				while($data = fread($a_fp,4096))
				{
					$parseOk = xml_parse($a_xml_parser,$data,feof($a_fp));
				}
				break;
				
			case 'string':
				$parseOk = xml_parse($a_xml_parser,$this->getXMLContent());
				break;
		}
		if(!$parseOk
		   && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE))
		{
      $this->error_code = xml_get_error_code($a_xml_parser);
      $this->error_line = xml_get_current_line_number($a_xml_parser);
      $this->error_col = xml_get_current_column_number($a_xml_parser);
      $this->error_msg = xml_error_string($a_xml_parser);
			$this->has_error = TRUE;
			return false;
		}
		return true;
	}
	
	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
    $this->elements++;
    $this->attributes+=count($a_attribs);
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
    $this->texts++;
    $this->text_size+=strlen($a_data);
	}

  function getErrorCode() 
	{
    return $this->error_code; 
  }
  
  function getErrorLine() 
	{
    return $this->error_line; 
  }
  
  function getErrorColumn() 
	{
    return $this->error_col; 
  }
  
  function getErrorMessage() 
	{
    return $this->error_msg; 
  }
  
  function getFullError() 
	{
    return "Error: ".$this->error_msg." at line:".$this->error_line ." column:".$this->error_col;
  }
  
  function getXMLSize() 
	{
    return $this->size; 
  }
  
  function getXMLElements() 
	{
    return $this->elements; 
  }
  
  function getXMLAttributes() 
	{
    return $this->attributes; 
  }
  
  function getXMLTextSections() 
	{
    return $this->texts; 
  }
  
  function getXMLTextSize() 
	{
    return $this->text_size; 
  }
  
  function hasError() 
	{
    return $this->has_error; 
  }
  
}
?>
