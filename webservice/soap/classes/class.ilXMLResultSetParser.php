<?php



  /******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilXMLResultSetParser extends ilSaxParser
{
    public $xmlResultSet;

    /**
    * Constructor
    * @param	string		$a_xml_data			xml data
    * @access	public
    */
    public function __construct($a_xml_data = '')
    {
        parent::__construct();
        $this->setXMLContent($a_xml_data);
    }

    /**
     * parsed result
     *
     * @return ilXMLResultSet xmlResultSet
     */
    public function getXMLResultSet() : ?\ilXMLResultSet
    {
        return $this->xmlResultSet;
    }

    /**
    * set event handlers
    *
    * @param	resource	reference to the xml parser
    * @access	private
    */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }


    /**
    * handler for begin of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    * @param	array		$a_attribs			element attributes array
    */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($a_name) {
            case 'result':
                $this->xmlResultSet = new ilXMLResultSet();
                break;

            case 'colspecs':
                break;

            case 'colspec':
                $this->xmlResultSet->addColumn($a_attribs["name"]);
                break;
            case 'row':
                $this->currentRow = new ilXMLResultSetRow();
                $this->xmlResultSet->addRow($this->currentRow);
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
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        switch ($a_name) {
            case 'column':
                $this->currentRow->setValue($this->currentColumnIndex, $this->cdata);
                $this->currentColumnIndex++;
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
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= trim($a_data);
        }
    }
}
