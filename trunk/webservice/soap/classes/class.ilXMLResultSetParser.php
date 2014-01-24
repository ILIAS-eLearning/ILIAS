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
   * Parser for XMLResultSet
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

include_once './Services/Xml/classes/class.ilSaxParser.php';
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

	/**
	 * parsed result
	 *
	 * @return ilXMLResultSet xmlResultSet
	 */
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
				$this->xmlResultSet->addColumn ($a_attribs["name"]);
				break;
			case 'row':
				$this->currentRow = new ilXMLResultSetRow();
				$this->xmlResultSet->addRow ($this->currentRow);
				$this->currentColumnIndex = 0;
				break;
			case 'column':
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
				$this->currentColumnIndex ++;				
				break;
		}
		$this->cdata = '';
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

			$this->cdata .= trim($a_data);
		}


	}

}
?>