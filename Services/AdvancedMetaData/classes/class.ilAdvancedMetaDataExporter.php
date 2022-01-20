<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export class for adv md
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMetaDataExporter extends ilXmlExporter
{
    private static array $local_recs_done = [];

    /**
     * Initialisation
     */
    public function init() : void
    {
    }

    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        return array();
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        return array();
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $parts = explode(":", $a_id);
        if (sizeof($parts) != 2) {
            return "";
        }
        $obj_id = (int) $parts[0];
        $rec_id = (int) $parts[1];

        // any data for current record and object?
        $raw = ilAdvancedMDValues::findByObjectId($obj_id);
        if (!$raw) {
            return "";
        }

        // gather sub-item data from value entries
        $sub_items = array();
        foreach ($raw as $item) {
            $sub_items[$item["sub_type"]][] = $item["sub_id"];
        }

        // gather all relevant data
        $items = array();
        foreach ($sub_items as $sub_type => $sub_ids) {
            foreach (array_unique($sub_ids) as $sub_id) {
                $values_record = new ilAdvancedMDValues($rec_id, $obj_id, $sub_type, $sub_id);
                $defs = $values_record->getDefinitions();
                $values_record->read();
                foreach ($values_record->getADTGroup()->getElements() as $element_id => $element) {
                    if (!$element->isNull()) {
                        $def = $defs[$element_id];
                        $items[$rec_id][] = array(
                            'id' => $def->generateImportId($def->getFieldId()),
                            'sub_type' => $sub_type,
                            'sub_id' => $sub_id,
                            'value' => $def->getValueForXML($element)
                        );
                    }
                }
            }
        }

        // #17066 - local advmd record
        $local_recs = array();
        $rec_obj = new ilAdvancedMDRecord($rec_id);
        if ($rec_obj->getParentObject()) {
            $xml = new ilXmlWriter();
            $rec_obj->toXML($xml);
            $xml = $xml->xmlDumpMem(false);

            $local_recs[$rec_obj->getRecordId()] = base64_encode($xml);
        }

        // we only want non-empty fields
        if (sizeof($items)) {
            $xml = new ilXmlWriter();

            foreach ($items as $record_id => $record_items) {
                $xml->xmlStartTag('AdvancedMetaData');

                $is_local = array_key_exists($record_id, $local_recs);

                // add local record data?
                if ($is_local) {
                    // we need to add this only once
                    if (!array_key_exists($record_id, self::$local_recs_done)) {
                        $xml->xmlElement(
                            'Record',
                            array('local_id' => $record_id),
                            $local_recs[$record_id]
                        );

                        self::$local_recs_done[] = $record_id;
                    }
                }

                foreach ($record_items as $item) {
                    $att = array(
                        'id' => $item['id'],
                        'sub_type' => $item['sub_type'],
                        'sub_id' => $item['sub_id']
                    );

                    if ($is_local) {
                        $att['local_rec_id'] = $record_id;
                    }

                    $xml->xmlElement(
                        'Value',
                        $att,
                        $item['value']
                    );
                }

                $xml->xmlEndTag('AdvancedMetaData');
            }

            return $xml->xmlDumpMem(false);
        }
        return "";
    }

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.4.0" => array(
                "namespace" => "http://www.ilias.de/Services/AdvancedMetaData/advmd/4_4",
                "xsd_file" => "ilias_advmd_4_4.xsd",
                "uses_dataset" => true,
                "min" => "4.4.0",
                "max" => ""
            )
        );
    }
}
