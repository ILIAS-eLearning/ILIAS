<?php declare(strict_types=1);
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
 * Adv MD XML Parser
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @extends ilMDSaxParser
 */
class ilAdvancedMDParser extends ilSaxParser implements ilSaxSubsetParser
{
    protected int $obj_id;
    protected int $rec_id = 0;
    protected ilImportMapping $mapping;
    protected string $cdata;
    protected ?ilSaxController $sax_controller = null;

    /**
     * @var array<int, ilAdvancedMDValues>
     */
    protected array $value_records = [];

    protected ?ilAdvancedMDFieldDefinition $current_value = null;
    protected array $record_ids = [];

    // local adv md record support
    protected ?array $local_record = [];
    protected array $local_rec_map = [];
    protected array $local_rec_fields_map = [];

    /**
     * @var ilLogger
     */
    protected ilLogger $log;

    public function __construct(string $a_obj_id, ilImportMapping $a_mapping)
    {
        parent::__construct();

        $this->log = ilLoggerFactory::getLogger('amet');

        $parts = explode(":", $a_obj_id);
        $this->obj_id = $parts[0];
        $this->mapping = $a_mapping;
    }

    public function setHandlers($a_xml_parser) : void
    {
        $this->sax_controller = new ilSaxController();
        $this->sax_controller->setHandlers($a_xml_parser);
        $this->sax_controller->setDefaultElementHandler($this);
    }

    public function createLocalRecord(int $a_old_id, string $a_xml, int $a_obj_id, ?string $a_sub_type = null) : void
    {
        $tmp_file = ilFileUtils::ilTempnam();
        file_put_contents($tmp_file, $a_xml);

        // see ilAdvancedMDSettingsGUI::importRecord()
        $parser = null;
        try {
            // the (old) record parser does only support files
            $parser = new ilAdvancedMDRecordParser($tmp_file);
            $parser->setContext($a_obj_id, ilObject::_lookupType($a_obj_id), $a_sub_type);
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT_VALIDATION);
            $parser->startParsing();
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT);
            $parser->startParsing();
        } catch (ilSaxParserException $exc) {
            $this->log->error('Parsing failed with message: ' . $exc->getMessage());
            return;
        } finally {
            unlink($tmp_file);
        }
        $map = $parser->getRecordMap();
        foreach ($map as $record_id => $fields) {
            $this->local_rec_fields_map[$record_id] = $fields;

            // needed for glossary field order
            foreach ($fields as $import_id => $new_id) {
                $import_ids = explode('_', $import_id);
                $old_id = array_pop($import_ids);
                $this->mapping->addMapping("Services/AdvancedMetaData", "lfld", $old_id, $new_id);
            }
        }
        $map_keys = array_keys($map);
        $new_id = array_shift($map_keys);
        $this->local_rec_map[$a_old_id] = $new_id;
    }

    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($a_name) {
            case 'AdvancedMetaData':
                break;

            case 'Record':
                $this->local_record = array('id' => $a_attribs['local_id']);
                break;

            case 'Value':
                $this->initValue(
                    (string) $a_attribs['id'],
                    (string) $a_attribs['sub_type'],
                    (int) $a_attribs['sub_id'],
                    (int) $a_attribs['local_rec_id']
                );
                break;
        }
    }

    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        switch ($a_name) {
            case 'AdvancedMetaData':
                // we need to write all records that have been created (1 for each sub-item)
                foreach ($this->value_records as $record) {
                    $record->write();
                }
                break;

            case 'Record':
                $this->local_record['xml'] = base64_decode(trim($this->cdata));
                $this->log->debug("Local Record XML: " . $this->local_record['xml']);
                break;

            case 'Value':
                $value = trim($this->cdata);
                $this->log->debug("End Tag Value: -" . is_object($this->current_value) . "-" . $value);
                if (is_object($this->current_value) && $value != "") {
                    $this->current_value->importValueFromXML($value);
                }
                break;
        }
        $this->cdata = '';
    }
    
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }

    protected function initValue(
        string $a_import_id,
        string $a_sub_type = "",
        int $a_sub_id = 0,
        int $a_local_rec_id = null
    ) : void {
        $this->current_value = null;

        // get parent objects
        $new_parent_id = (int) $this->mapping->getMapping("Services/AdvancedMetaData", "parent", $this->obj_id);
        $this->log->notice('Found new parent id:' . $new_parent_id);
        if (!$new_parent_id) {
            return;
        }
        $new_sub_id = '';
        if ($a_sub_type && strcmp($a_sub_type, '-') !== 0) {
            $new_sub_id = $this->mapping->getMapping(
                "Services/AdvancedMetaData",
                "advmd_sub_item",
                "advmd:" . $a_sub_type . ":" . $a_sub_id
            );
            if (!$new_sub_id) {
                return;
            }
        }

        // init local record?
        // done here because we need object context
        if (is_array($this->local_record)) {
            $this->createLocalRecord(
                $this->local_record['id'],
                $this->local_record['xml'],
                $new_parent_id,
                $a_sub_type
            );
            $this->local_record = null;
        }

        $rec_id = null;

        // find record via import id
        if (!$a_local_rec_id) {
            if ($field = ilAdvancedMDFieldDefinition::getInstanceByImportId($a_import_id)) {
                $rec_id = $field->getRecordId();
            }
        } // (new) local record
        else {
            $rec_id = $this->local_rec_map[$a_local_rec_id];
        }

        if (!$rec_id) {
            return;
        }

        // init record definitions
        if ($a_sub_type) {
            $rec_idx = $rec_id . ";" . $a_sub_type . ";" . $new_sub_id;
            if (!array_key_exists($rec_idx, $this->value_records)) {
                $this->value_records[$rec_idx] = new ilAdvancedMDValues(
                    $rec_id,
                    $new_parent_id,
                    $a_sub_type,
                    $new_sub_id
                );
            }
        } else {
            $rec_idx = $rec_id . ";;";
            if (!array_key_exists($rec_idx, $this->value_records)) {
                $this->value_records[$rec_idx] = new ilAdvancedMDValues($rec_id, $new_parent_id);
            }
        }

        // init ADTGroup before definitions to bind definitions to group
        $this->value_records[$rec_idx]->getADTGroup();

        // find element with import id
        $this->log->debug("Find element: " . $a_import_id . ", local rec_id: " . $a_local_rec_id);
        if (!$a_local_rec_id) {
            foreach ($this->value_records[$rec_idx]->getDefinitions() as $def) {
                if ($a_import_id == $def->getImportId()) {
                    $this->current_value = $def;
                    break;
                }
            }
        } else {
            // find element in new local record
            $field_id = $this->local_rec_fields_map[$rec_id][$a_import_id];
            if ($field_id) {
                $this->log->debug("- Field id: " . $field_id);
                foreach ($this->value_records[$rec_idx]->getDefinitions() as $def) {
                    $this->log->debug("- Def field id: " . $def->getFieldId());
                    if ($field_id == $def->getFieldId()) {
                        $this->current_value = $def;
                        break;
                    }
                }
            } else {
                $this->log->debug("- No Field id. local rec: " . $a_local_rec_id .
                    ", rec id:" . $rec_id . ", import id: " . $a_import_id . ", map: " . print_r(
                        $this->local_rec_fields_map,
                        true
                    ));
            }
        }

        // record will be selected for parent
        // see ilAdvancedMetaDataImporter
        if ($this->current_value &&
            !$a_local_rec_id) {
            $this->record_ids[$new_parent_id][$a_sub_type][] = $rec_id;
        }
    }

    /**
     * @return int[]
     */
    public function getRecordIds() : array
    {
        return $this->record_ids;
    }
}
