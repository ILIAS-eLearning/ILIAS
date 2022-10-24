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
/**
 * Manifest parser for ILIAS standard export files
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDataSetImportParser extends ilSaxParser
{
    protected string $dspref;
    protected string $schema_version;
    protected string $top_entity;
    protected ilDataSet $ds;
    protected ?ilImport $import = null;				// import object
    protected array $entities = array();			// types array
    protected string $current_entity = "";			// current entity
    protected string $current_version = "";		// current version
    protected array $current_ftypes = array();	// current field types
    protected bool $entities_sent = false;		// sent entities to import class?
    protected bool $in_record = false;			// are we currently in a rec tag?
    protected string $current_field = "";			// current field
    protected array $current_field_values = array();	// current field values
    protected string $current_installation_id = "";
    protected string $chr_data = "";
    protected ilImportMapping $mapping;

    public function __construct(
        string $a_top_entity,
        string $a_schema_version,
        string $a_xml,
        ilDataSet $a_ds,
        ilImportMapping $a_mapping
    ) {
        $this->ds = $a_ds;
        $this->mapping = $a_mapping;
        $this->top_entity = $a_top_entity;
        $this->schema_version = $a_schema_version;
        $this->dspref = ($this->ds->getDSPrefix() !== "")
            ? $this->ds->getDSPrefix() . ":"
            : "";

        parent::__construct();
        $this->setXMLContent($a_xml);
        $this->startParsing();
    }

    public function getCurrentInstallationId(): string
    {
        return $this->current_installation_id;
    }


    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }


    public function handleBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        switch ($a_name) {
            case $this->dspref . "DataSet":
//				$this->import->initDataset($this->ds_component, $a_attribs["top_entity"]);
                $this->current_installation_id = $a_attribs["InstallationId"];
                $this->ds->setCurrentInstallationId($a_attribs["InstallationId"]);
                break;

            case $this->dspref . "Types":
                $this->current_entity = $a_attribs["Entity"];
                $this->current_version = $a_attribs["Version"];
                break;

            case $this->dspref . "FieldType":
                $this->current_ftypes[$a_attribs["Name"]] =
                    $a_attribs["Type"];
                break;

            case $this->dspref . "Rec":
                $this->current_entity = $a_attribs["Entity"];
                $this->in_record = true;
                $this->current_field_values = array();
                break;

            default:
                if ($this->in_record) {
                    $field = explode(":", $a_name);		// remove namespace
                    $field = $field[count($field) - 1];
                    $this->current_field = $field;
                }
        }
    }

    public function handleEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        switch ($a_name) {
            case $this->dspref . "Types":
                $this->entities[$this->current_entity] =
                    array(
                        "version" => $this->current_version,
                        "types" => $this->current_ftypes
                        );
                $this->current_ftypes = array();
                $this->current_entity = "";
                $this->current_version = "";
                break;

            case $this->dspref . "Rec":
                $this->ds->importRecord(
                    $this->current_entity,
                    $this->entities[$this->current_entity]["types"] ?? [],
                    $this->current_field_values,
                    $this->mapping,
                    $this->schema_version
                );
                $this->in_record = false;
                $this->current_entity = "";
                $this->current_field_values = array();
                break;

            default:
                if ($this->in_record && $this->current_field !== "") {
                    $this->current_field_values[$this->current_field] =
                        $this->chr_data;
                }
                $this->current_field = "";
                break;
        }

        $this->chr_data = "";
    }


    public function handleCharacterData(
        $a_xml_parser,
        string $a_data
    ): void {
        $this->chr_data .= $a_data;
    }
}
