<?php
/**
* Base class for sax-based expat parsing
* extended classes need to overwrite the method setHandlers and implement their own handler methods
* 
*
* @author Stefan Meyer <smeyer@databay>
* @version $Id$
*
* @extends PEAR
* @package ilias-core
*/
class SaxParser extends PEAR
{
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
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function SaxParser($a_xml_file)
	{
		global $ilias, $lng;

		$this->xml_file = $a_xml_file;
		
		$this->ilias = &$ilias;
		$this->lng = &$lng;
	}

	/**
	* stores xml data in array
	* 
	* @access	private
	*/
	function startParsing()
	{
		$xml_parser = $this->createParser();
		$this->setOptions($xml_parser);
		$this->setHandlers($xml_parser);
		$fp = $this->openXMLFile();
		$this->parse($xml_parser,$fp);
		$this->freeParser($xml_parser);
	}
	/**
	* create parser
	* 
	* @access	private
	*/
	function createParser()
	{
		$xml_parser = xml_parser_create();
		
		if($xml_parser == false)
		{
			$this->ilias->raiseError("Cannot create an XML parser handle",$this->ilias->error_obj->FATAL);
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
			$this->ilias->raiseError("Cannot open xml file",$this->ilias->error_obj->FATAL);
		}
		return $fp;
	}
	/**
	* parse xml file
	* 
	* @access	private
	*/
	function parse($a_xml_parser,$a_fp)
	{
		while($data = fread($a_fp,4096))
		{
			$parseOk = xml_parse($a_xml_parser,$data,feof($a_fp));
			if(!$parseOk 
			   && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE))
			{
				$this->ilias->raiseError("XML Parse Error: ",$this->ilias->error_obj->FATAL);
			}
		}
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
}
?>