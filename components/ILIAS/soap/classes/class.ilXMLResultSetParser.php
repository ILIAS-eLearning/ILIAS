<?php

declare(strict_types=1);

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *****************************************************************************/
class ilXMLResultSetParser extends ilSaxParser
{
    private ?ilXMLResultSet $xmlResultSet = null;
    private ?ilXMLResultSetRow $currentRow = null;
    private int $currentColumnIndex = 0;
    private string $cdata = '';

    public function __construct(string $a_xml_data = '')
    {
        parent::__construct();
        $this->setXMLContent($a_xml_data);
    }

    public function getXMLResultSet(): ?ilXMLResultSet
    {
        return $this->xmlResultSet;
    }

    /**
     * @param XMLParser|resource A reference to the xml parser
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @param XMLParser|resource $a_xml_parser xml parser
     * @param string $a_name element name
     * @param array $a_attribs element attributes array
     * @return void
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
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
        }
    }

    /**
     * Handler for end of element
     * @param XMLParser|resource $a_xml_parser xml parser
     * @param string $a_name element name
     * @return void
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
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
     * Handler for character data
     * @param XMLParser|resource $a_xml_parser xml parser
     * @param string $a_data character data
     * @return void
     */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data !== "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);
            $this->cdata .= trim($a_data);
        }
    }
}
