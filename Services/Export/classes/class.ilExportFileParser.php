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
 * Export file parser
 * @author Aleex Killing <alex.killing@gmx.de>
 */
class ilExportFileParser extends ilSaxParser
{
    private string $entity = '';
    private string $install_id = '';
    private string $install_url = '';
    private string $schema_version = '';
    // currently not used.
    private array $expfiles = [];
    private string $current_id = '';
    protected string $item_xml = "";
    protected bool $in_export_item = false;
    protected string $chr_data = "";

    private object $callback_obj;
    private string $callback_func;
    private ?ilXmlWriter $export_item_writer = null;

    /**
     * ilExportFileParser constructor.
     * @inheritDoc
     */
    public function __construct(string $a_file, object $a_callback_obj, string $a_callback_func)
    {
        $this->callback_obj = $a_callback_obj;
        $this->callback_func = $a_callback_func;

        parent::__construct($a_file, true);
        $this->startParsing();
    }

    /**
     * @inheritDoc
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }

    /**
     * @inheritDoc
     */
    public function startParsing(): void
    {
        parent::startParsing();
    }

    public function handleBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        if ($this->in_export_item) {
            $this->export_item_writer->xmlStartTag($a_name, $a_attribs);
        }

        switch ($a_name) {
            case "exp:Export":
                $this->entity = $a_attribs["Entity"];
                $this->install_id = $a_attribs["InstallationId"];
                $this->install_url = $a_attribs["InstallationUrl"];
                $this->schema_version = $a_attribs["SchemaVersion"];
                break;

            case "exp:ExportItem":
                $this->in_export_item = true;
                $this->current_id = $a_attribs["Id"];
                $this->export_item_writer = new ilXmlWriter();
                $this->item_xml = "";
                $this->expfiles[] = array(
                    "component" => $a_attribs["Component"] ?? null,
                    "path" => $a_attribs["Path"] ?? null
                );
                break;
        }
    }

    public function handleEndTag($a_xml_parser, string $a_name): void
    {
        switch ($a_name) {
            case "exp:ExportItem":
                $this->in_export_item = false;
                $cf = $this->callback_func;
                $this->callback_obj->$cf(
                    $this->entity,
                    $this->schema_version,
                    $this->current_id,
                    $this->export_item_writer->xmlDumpMem(false),
                    $this->install_id,
                    $this->install_url
                );
                break;

        }

        if ($this->in_export_item) {
            $this->export_item_writer->xmlEndTag($a_name);
        }

        $this->chr_data = "";
    }

    /**
     * End Tag
     */
    public function handleCharacterData($a_xml_parser, string $a_data): void
    {
        $this->chr_data .= $a_data;
        if ($this->in_export_item) {
            $this->export_item_writer->xmlData($a_data);
        }
    }
}
