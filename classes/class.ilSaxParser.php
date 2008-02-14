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
* Base class for sax-based expat parsing
* extended classes need to overwrite the method setHandlers and implement their own handler methods
* 
*
* @author Stefan Meyer <smeyer@databay>
* @version $Id$
*
* @extends PEAR
*/


class ilSaxParser
{
	/**
	 * XML-Content type 'file' or 'string'
	 * If you choose file set the filename in constructor
	 * If you choose 'String' call the constructor with no argument and use setXMLContent()
	 * @var string
	 * @access private
	 */
	var $input_type = null;

	/**
	 * XML-Content in case of content type 'string'

	 * @var string 
	 * @access private
	 */
	var $xml_content = '';

	/**
	 * ilias object
	 * @var object ilias
	 * @access private
	 */
	var $ilias;

	/**
	 * language object
	 * @var object language
	 * @access private
	 */
	var $lng;

	/**
	 * xml filename
	 * @var filename
	 * @access private
	 */
	var $xml_file;

	/**
	 * error handler which handles error messages (used if parsers are called from soap)
	 *
	 * @var boolean
	 */
	var $throwException = false;
	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ilSaxParser($a_xml_file = '', $throwException = false)
	{
		global $ilias, $lng;

		if($a_xml_file)
		{
			$this->xml_file = $a_xml_file;
			$this->input_type = 'file';
		}

		$this->throwException = $throwException;
		$this->ilias = &$ilias;
		$this->lng = &$lng;
	}

	function setXMLContent($a_xml_content)
	{
		$this->xml_content = $a_xml_content;
		$this->input_type = 'string';
	}
	
	function getXMLContent()
	{
		return $this->xml_content;
	}

	function getInputType()
	{
		return $this->input_type;
	}

	/**
	* stores xml data in array
	* 
	* @access	private
	* @throws ilSaxParserException or ILIAS Error
	*/
	function startParsing()
	{
		$xml_parser = $this->createParser();
		$this->setOptions($xml_parser);
		$this->setHandlers($xml_parser);

		switch($this->getInputType())
		{
			case 'file':
				$fp = $this->openXMLFile();
				$this->parse($xml_parser,$fp);
				break;

			case 'string':
				$this->parse($xml_parser);
				break;

			default:
				$this->handleError("No input type given. Set filename in constructor or choose setXMLContent()",
										 $this->ilias->error_obj->FATAL);
				break;
		}
		$this->freeParser($xml_parser);
	}
	/**
	* create parser
	* 
	* @access	private
	*/
	function createParser()
	{
		$xml_parser = xml_parser_create("UTF-8");

		if($xml_parser == false)
		{
			$this->handleError("Cannot create an XML parser handle",$this->ilias->error_obj->FATAL);
		}
		return $xml_parser;
	}
	/**
	* set parser options
	* 
	* @access	private
	*/
	function setOptions($a_xml_parser)
	{
		xml_parser_set_option($a_xml_parser,XML_OPTION_CASE_FOLDING,false);
	}
	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		echo 'ilSaxParser::setHandlers() must be overwritten';
	}
	/**
	* open xml file
	* 
	* @access	private
	*/
	function openXMLFile()
	{
		if(!($fp = fopen($this->xml_file,'r')))
		{
			$this->handleError("Cannot open xml file \"".$this->xml_file."\"",$this->ilias->error_obj->FATAL);
		}
		return $fp;
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
			$errorCode = xml_get_error_code($a_xml_parser);
			$line = xml_get_current_line_number($a_xml_parser);
			$col = xml_get_current_column_number($a_xml_parser);			
			$this->handleError("XML Parse Error: ".xml_error_string($errorCode)." at line ".$line.", col ". $col . " (Code: ".$errorCode.")" ,$this->ilias->error_obj->FATAL);
		}
		return true;
				
	}
	
	/**
	 * use given error handler to handle error message or internal ilias error message handle
	 *
	 * @param string $message
	 * @param string $code
	 */
	protected function handleError($message, $code) {
		if ($this->throwException) {
			require_once ('./classes/class.ilSaxParserException.php');			
			throw new ilSaxParserException ($message, $code);
		} else {
			if (is_object($this->ilias))
			{
				$this->ilias->raiseError($message, $code);
			}
			else
			{
				die($message);
			}
		}
		return false;
	}
	/**
	* free xml parser handle
	* 
	* @access	private
	*/
	function freeParser($a_xml_parser)
	{
		if(!xml_parser_free($a_xml_parser))
		{
			$this->ilias->raiseError("Error freeing xml parser handle ",$this->ilias->error_obj->FATAL);
		}
	}
	
	/**
	 * set error handling
	 *
	 * @param  $error_handler
	 */
	public function setThrowException ($throwException) 
	{
		$this->throwException = $throwException;
	}
}
?>
