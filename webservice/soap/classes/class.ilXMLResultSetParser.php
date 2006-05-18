<?php
include_once 'classes/class.ilSaxParser.php';
include_once './webservice/soap/classes/class.ilXMLResultSet.php';

class ilXMLResultSetParser extends ilSaxParser
{
	var $xmlResultSet;

	/**
	* Constructor
	* @param	string		$a_xml_data			xml data
	* @access	public
	*/
	function ilXMLResultSetParser($a_xml_data = '')
	{
		parent::ilSaxParser();
		$this->setXMLContent($a_xml_data);
	}

	function getXMLResultSet()
	{
		return $this->xmlResultSet;
	}

	/**
	* set event handlers
	*
	* @param	resource	reference to the xml parser
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}


	/**
	* handler for begin of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	* @param	array		$a_attribs			element attributes array
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch($a_name)
		{
			case 'result':
				$this->xmlResultSet = new ilXMLResultSet();
				break;
			
			case 'colspecs':
				break;
			
			case 'colspec':
				$this->xmlResultSet->addColumn ($a_attribs["idx"], $a_attribs["name"]);
				break;
			case 'row':
				$this->currentRow = new ilXMLResultSetRow();
				$this->xmlResultSet->addRow ($this->currentRow);
				$this->colCounter = -1;
				break;
			case 'column':
				$this->currentColumnIndex ++;
				break;
		}
	}

	/**
	* handler for end of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		switch($a_name)
		{
			case 'column':
				$this->currentRow->setValue ($this->currentColumnIndex, $this->cdata);
				break;
		}
	}

	/**
	* handler for character data
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_data				character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		if($a_data != "\n")
		{
			// Replace multiple tabs with one space
			$a_data = preg_replace("/\t+/"," ",$a_data);

			$this->cdata .= $a_data;
		}


	}

}
?>