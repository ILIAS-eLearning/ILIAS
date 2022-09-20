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
 * Manifest parser for ILIAS standard export files
 * @author Aleex Killing <alex.killing@gmx.de>
 */
class ilManifestParser extends ilSaxParser
{
    protected string $chr_data = '';
    protected string $target_release = '';
    protected string $title = '';
    protected string $main_entity = '';
    protected string $install_id = '';
    protected string $install_url = '';
    protected array $expfiles = array();
    protected array $expsets = array();

    public function __construct(string $a_file)
    {
        parent::__construct($a_file, true);
        $this->startParsing();
    }

    final public function setInstallId(string $a_val): void
    {
        $this->install_id = $a_val;
    }

    final public function getInstallId(): string
    {
        return $this->install_id;
    }

    final public function setInstallUrl(string $a_val): void
    {
        $this->install_url = $a_val;
    }

    final public function getInstallUrl(): string
    {
        return $this->install_url;
    }

    public function setMainEntity(string $a_val): void
    {
        $this->main_entity = $a_val;
    }

    public function getMainEntity(): string
    {
        return $this->main_entity;
    }

    public function setTitle(string $a_val): void
    {
        $this->title = $a_val;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTargetRelease(string $a_val): void
    {
        $this->target_release = $a_val;
    }

    public function getTargetRelease(): string
    {
        return $this->target_release;
    }

    public function getExportFiles(): array
    {
        return $this->expfiles;
    }

    public function getExportSets(): array
    {
        return $this->expsets;
    }

    /**
     * Set event handlers
     *
     * @param	resource	reference to the xml parser
     * @access	private
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }

    /**
     * Begin Tag
     */
    public function handleBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($a_name) {
            case "Manifest":
                $this->setInstallId($a_attribs["InstallationId"]);
                $this->setInstallUrl($a_attribs["InstallationUrl"]);
                $this->setTitle($a_attribs["Title"]);
                $this->setTargetRelease($a_attribs["TargetRelease"]);
                $this->setMainEntity($a_attribs["MainEntity"]);
                break;

            case "ExportFile":
                $this->expfiles[] = array("component" => $a_attribs["Component"],
                                          "path" => $a_attribs["Path"]
                );
                break;

            case "ExportSet":
                $this->expsets[] = array(
                    'path' => $a_attribs['Path'],
                    'type' => $a_attribs['Type']
                );
                break;
        }
    }

    /**
     * End Tag
     */
    public function handleEndTag($a_xml_parser, string $a_name): void
    {
        $this->chr_data = "";
    }

    /**
     * End Tag
     */
    public function handleCharacterData($a_xml_parser, string $a_data): void
    {
        //$a_data = str_replace("<","&lt;",$a_data);
        //$a_data = str_replace(">","&gt;",$a_data);
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        //$a_data = preg_replace("/\n/","",$a_data);
        //$a_data = preg_replace("/\t+/","",$a_data);

        $this->chr_data .= $a_data;
    }
}
