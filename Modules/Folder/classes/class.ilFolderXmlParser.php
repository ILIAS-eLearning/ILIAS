<?php

declare(strict_types=1);

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
/**
 * XML  parser for folder xml
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilFolderXmlParser extends ilSaxParser
{
    protected ilErrorHandling $error;
    private ilObject $folder;
    protected string $cdata = "";

    public function __construct(ilObject $folder, string $xml)
    {
        global $DIC;

        $this->error = $DIC["ilErr"];
        parent::__construct();
        $this->setXMLContent($xml);
        $this->setFolder($folder);
        $this->setThrowException(true);
    }

    public function setFolder(ilObject $folder): void
    {
        $this->folder = $folder;
    }

    public function getFolder(): ilObject
    {
        return $this->folder;
    }

    /**
     * @throws ilSaxParserException
     */
    public function start(): void
    {
        $this->startParsing();
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @param array  $a_attribs
     * @return void
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($a_name) {

            case 'Folder':
            case 'Title':
            case 'Description':
                break;

            case 'Sorting':
            case 'Sort':
                ilContainerSortingSettings::_importContainerSortingSettings($a_attribs, $this->getFolder()->getId());
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @return void
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        $GLOBALS['ilLog']->write(__METHOD__ . ': Called ' . $a_name);

        switch ($a_name) {

            case 'Folder':
                $this->getFolder()->update();
                break;

            case 'Title':
                $this->getFolder()->setTitle(trim($this->cdata));
                break;

            case 'Description':
                $this->getFolder()->setDescription(trim($this->cdata));
                break;

        }

        // Reset cdata
        $this->cdata = '';
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_data
     * @return void
     */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data !== "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);
            $this->cdata .= $a_data;
        }
    }
}
