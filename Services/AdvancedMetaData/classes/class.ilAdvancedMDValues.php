<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ilCtrl_Calls
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDValues
{
    protected int $record_id;
    protected int $obj_id;
    protected int $sub_id;
    protected string $sub_type;

    protected ?array $defs = null;
    protected ?ilADTGroup $adt_group = null;
    protected ?ilADTActiveRecordByType $active_record = null;

    protected array $disabled = [];

    protected static array $preload_obj_records = [];

    public function __construct($a_record_id, $a_obj_id, $a_sub_type = "-", $a_sub_id = 0)
    {
        $this->record_id = (int) $a_record_id;
        $this->obj_id = (int) $a_obj_id;
        $this->sub_type = $a_sub_type ?: "-";
        $this->sub_id = (int) $a_sub_id;
    }

    public static function getInstancesForObjectId(
        int $a_obj_id,
        ?string $a_obj_type = null,
        string $a_sub_type = "-",
        int $a_sub_id = 0
    ) : array {
        $res = array();

        if (!$a_obj_type) {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        // @todo refactor
        $refs = ilObject::_getAllReferences($a_obj_id);
        foreach ($refs as $ref_id) {
            $records = ilAdvancedMDRecord::_getSelectedRecordsByObject($a_obj_type, $ref_id, $a_sub_type);
            $orderings = new ilAdvancedMDRecordObjectOrderings();
            $records = $orderings->sortRecords($records, $a_obj_id);

            foreach ($records as $record) {
                $id = $record->getRecordId();

                if (!isset($res[$id])) {
                    $res[$id] = new self($id, $a_obj_id, $a_sub_type, $a_sub_id);
                }
            }
        }
        return $res;
    }

    public function setActiveRecordPrimary(int $a_obj_id, string $a_sub_type = "-", int $a_sub_id = 0) : void
    {
        $this->obj_id = $a_obj_id;
        $this->sub_type = $a_sub_type ?: "-";
        $this->sub_id = $a_sub_id;

        // make sure they get used
        $this->active_record = null;
    }

    /**
     * Get record field definitions
     * @return ilAdvancedMDFieldDefinition[]
     */
    public function getDefinitions() : array
    {
        if (!is_array($this->defs)) {
            $this->defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->record_id);
        }
        return $this->defs;
    }

    public function getADTGroup() : ilADTGroup
    {
        if (!$this->adt_group instanceof ilADTGroup) {
            $this->adt_group = ilAdvancedMDFieldDefinition::getADTGroupForDefinitions($this->getDefinitions());
        }
        return $this->adt_group;
    }

    /**
     * Init ADT DB Bridge (aka active record helper class)
     */
    protected function getActiveRecord() : ilADTActiveRecordByType
    {
        if (!$this->active_record instanceof ilADTActiveRecordByType) {
            $factory = ilADTFactory::getInstance();

            $adt_group_db = $factory->getDBBridgeForInstance($this->getADTGroup());

            $primary = array(
                "obj_id" => array("integer", $this->obj_id),
                "sub_type" => array("text", $this->sub_type),
                "sub_id" => array("integer", $this->sub_id)
            );
            $adt_group_db->setPrimary($primary);
            $adt_group_db->setTable("adv_md_values");

            // multi-enum fakes single in adv md
            foreach ($adt_group_db->getElements() as $element) {
                if ($element->getADT()->getType() == "MultiEnum") {
                    $element->setFakeSingle(true);
                }
            }

            $this->active_record = $factory->getActiveRecordByTypeInstance($adt_group_db);
            $this->active_record->setElementIdColumn("field_id", "integer");
        }

        return $this->active_record;
    }

    /**
     * Find all entries for object (regardless of sub-type/sub-id)
     */
    public static function findByObjectId(int $a_obj_id) : ?array
    {
        ilADTFactory::initActiveRecordByType();
        return ilADTActiveRecordByType::readByPrimary("adv_md_values", array("obj_id" => array("integer", $a_obj_id)));
    }


    //
    // disabled
    //

    // to set disabled use self::write() with additional data

    public function isDisabled(string $a_element_id) : ?bool
    {
        if (is_array($this->disabled)) {
            return in_array($a_element_id, $this->disabled);
        }
        return null;
    }

    /**
     * Get record values
     */
    public function read() : void
    {
        $this->disabled = array();

        $tmp = $this->getActiveRecord()->read(true);
        if ($tmp) {
            foreach ($tmp as $element_id => $data) {
                if ($data["disabled"]) {
                    $this->disabled[] = $element_id;
                }
            }
        }
    }

    /**
     * Write record values
     */
    public function write(array $a_additional_data = null) : void
    {
        $this->getActiveRecord()->write($a_additional_data);
    }

    /**
     * Delete values by field_id.
     * Typically called after deleting a field
     * @param int $a_field_id
     * @param ilADT $a_adt
     */
    public static function _deleteByFieldId(int $a_field_id, ilADT $a_adt) : void
    {
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::deleteByPrimary(
            "adv_md_values",
            array("field_id" => array("integer", $a_field_id)),
            $a_adt->getType()
        );
    }

    /**
     * Delete by objekt id
     */
    public static function _deleteByObjId(int $a_obj_id)
    {
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::deleteByPrimary(
            "adv_md_values",
            array("obj_id" => array("integer", $a_obj_id))
        );
    }

    /**
     * Preload list gui data
     * @param int[] $a_obj_ids
     */
    public static function preloadByObjIds(array $a_obj_ids) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // preload values
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::preloadByPrimary(
            "adv_md_values",
            array("obj_id" => array("integer", $a_obj_ids))
        );

        // preload record ids for object types

        self::$preload_obj_records = array();

        // get active records for object types
        $query = "SELECT amro.*" .
            " FROM adv_md_record_objs amro" .
            " JOIN adv_md_record amr ON (amr.record_id = amro.record_id)" .
            " WHERE active = " . $ilDB->quote(1, "integer");
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            self::$preload_obj_records[(string) $row["obj_type"]][] = array((int) $row["record_id"],
                                                                            (int) $row["optional"]
            );
        }
    }

    public static function preloadedRead(string $a_type, int $a_obj_id) : array
    {
        $res = array();

        if (isset(self::$preload_obj_records[$a_type])) {
            foreach (self::$preload_obj_records[$a_type] as $item) {
                $record_id = $item[0];

                // record is optional, check activation for object
                if ($item[1]) {
                    $found = false;
                    foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject($a_type, $a_obj_id) as $record) {
                        if ($record->getRecordId() == $item[0]) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        continue;
                    }
                }

                $res[$record_id] = new self($record_id, $a_obj_id);
                $res[$record_id]->read();
            }
        }

        return $res;
    }

    /**
     * Clone Advanced Meta Data
     */
    public static function _cloneValues(
        int $copy_id,
        int $a_source_id,
        int $a_target_id,
        ?string $a_sub_type = null,
        ?int $a_source_sub_id = null,
        ?int $a_target_sub_id = null
    ) : void {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        // clone local records

        // new records are created automatically, only if source and target id differs.
        $new_records = $fields_map = array();

        $record_mapping = [];
        foreach (ilAdvancedMDRecord::_getRecords() as $record) {
            if ($record->getParentObject() == $a_source_id) {
                $tmp = array();
                if ($a_source_id != $a_target_id) {
                    $new_records[$record->getRecordId()] = $record->_clone($tmp, $a_target_id);
                    $record_mapping[$record->getRecordId()] = $new_records[$record->getRecordId()]->getRecordId();
                } else {
                    $new_records[$record->getRecordId()] = $record->getRecordId();
                }
                $fields_map[$record->getRecordId()] = $tmp;
            }
        }
        if ($copy_id > 0) {
            $cp_options = ilCopyWizardOptions::_getInstance($copy_id);
            $cp_options->appendMapping(
                $a_target_id . '_adv_rec',
                $record_mapping
            );
            $cp_options->read();        // otherwise mapping will not be available for getMappings
        }
        
        // object record selection

        $source_sel = ilAdvancedMDRecord::getObjRecSelection($a_source_id, (string) $a_sub_type);
        if ($source_sel) {
            $target_sel = array();
            foreach ($source_sel as $record_id) {
                // (local) record has been cloned
                if (array_key_exists($record_id, $new_records)) {
                    $record_id = $new_records[$record_id]->getRecordId();
                }
                $target_sel[] = $record_id;
            }
            ilAdvancedMDRecord::saveObjRecSelection($a_target_id, $a_sub_type, $target_sel);
        }

        // clone values

        $source_primary = array("obj_id" => array("integer", $a_source_id));
        $target_primary = array("obj_id" => array("integer", $a_target_id));

        // sub-type support
        if ($a_sub_type &&
            $a_source_sub_id &&
            $a_target_sub_id) {
            $source_primary["sub_type"] = array("text", $a_sub_type);
            $source_primary["sub_id"] = array("integer", $a_source_sub_id);
            $target_primary["sub_type"] = array("text", $a_sub_type);
            $target_primary["sub_id"] = array("integer", $a_target_sub_id);
        }

        ilADTFactory::getInstance()->initActiveRecordByType();
        $has_cloned = ilADTActiveRecordByType::cloneByPrimary(
            "adv_md_values",
            array(
                "obj_id" => "integer",
                "sub_type" => "text",
                "sub_id" => "integer",
                "field_id" => "integer"
            ),
            $source_primary,
            $target_primary,
            array("disabled" => "integer")
        );

        // move values of local records to newly created fields

        foreach ($fields_map as $source_record_id => $fields) {
            // just to make sure
            if (array_key_exists($source_record_id, $new_records)) {
                foreach ($fields as $source_field_id => $target_field_id) {
                    // delete entry for old field id (was cloned above)
                    $del_target_primary = $target_primary;
                    $del_target_primary["field_id"] = array("integer", $source_field_id);
                    ilADTActiveRecordByType::deleteByPrimary("adv_md_values", $del_target_primary);

                    // create entry for new id
                    $fix_source_primary = $source_primary;
                    $fix_source_primary["field_id"] = array("integer", $source_field_id);
                    $fix_target_primary = $target_primary;
                    $fix_target_primary["field_id"] = array("integer", $target_field_id);
                    ilADTActiveRecordByType::cloneByPrimary(
                        "adv_md_values",
                        array(
                            "obj_id" => "integer",
                            "sub_type" => "text",
                            "sub_id" => "integer",
                            "field_id" => "integer"
                        ),
                        $fix_source_primary,
                        $fix_target_primary,
                        array("disabled" => "integer")
                    );
                }
            }
        }

        if (!$has_cloned) {
            $ilLog->write(__METHOD__ . ': No advanced meta data found.');
        } else {
            $ilLog->write(__METHOD__ . ': Start cloning advanced meta data.');
        }
    }

    /**
     * Get xml of object values
     * @param ilXmlWriter $a_xml_writer
     * @param int         $a_obj_id
     */
    public static function _appendXMLByObjId(ilXmlWriter $a_xml_writer, int $a_obj_id) : void
    {
        $a_xml_writer->xmlStartTag('AdvancedMetaData');

        self::preloadByObjIds(array($a_obj_id));
        $values_records = self::preloadedRead(ilObject::_lookupType($a_obj_id), $a_obj_id);

        foreach ($values_records as $values_record) {
            $defs = $values_record->getDefinitions();
            foreach ($values_record->getADTGroup()->getElements() as $element_id => $element) {
                $def = $defs[$element_id];

                $value = null;
                if (!$element->isNull()) {
                    $value = $def->getValueForXML($element);
                }

                $a_xml_writer->xmlElement(
                    'Value',
                    array('id' => $def->getImportId()),
                    $value
                );
            }
        }
        $a_xml_writer->xmlEndTag('AdvancedMetaData');
    }

    /**
     * @todo refactor this
     */
    public static function queryForRecords(
        int $adv_rec_obj_ref_id,
        string $adv_rec_obj_type,
        string $adv_rec_obj_subtype,
        array $a_obj_id,
        string $a_subtype,
        array $a_records,
        string $a_obj_id_key,
        string $a_obj_subid_key,
        array $a_amet_filter = null
    ) : array {
        $results = array();

        $sub_obj_ids = array();
        foreach ($a_records as $rec) {
            $sub_obj_ids[] = $rec[$a_obj_subid_key];
        }

        // preload adv data for object id(s)
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::preloadByPrimary(
            "adv_md_values",
            array(
                "obj_id" => array("integer", $a_obj_id),
                "sub_type" => array("text", $a_subtype),
                "sub_id" => array("integer", $sub_obj_ids)
            )
        );

        $record_groups = array();

        foreach ($a_records as $rec) {
            $obj_id = (int) ($rec[$a_obj_id_key] ?? 0);
            $sub_id = $rec[$a_obj_subid_key];

            // get adv records
            foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject(
                $adv_rec_obj_type,
                $adv_rec_obj_ref_id,
                $adv_rec_obj_subtype
            ) as $adv_record) {
                $record_id = $adv_record->getRecordId();

                if (!isset($record_groups[$record_id])) {
                    $defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
                    $record_groups[$record_id] = ilAdvancedMDFieldDefinition::getADTGroupForDefinitions($defs);
                    $record_groups[$record_id] = ilADTFactory::getInstance()->getDBBridgeForInstance($record_groups[$record_id]);
                    $record_groups[$record_id]->setTable("adv_md_values");
                }

                // prepare ADT group for record id
                $record_groups[$record_id]->setPrimary(array(
                    "obj_id" => array("integer", $obj_id),
                    "sub_type" => array("text", $a_subtype),
                    "sub_id" => array("integer", $sub_id)
                ));
                // multi-enum fakes single in adv md
                foreach ($record_groups[$record_id]->getElements() as $element) {
                    if ($element->getADT()->getType() == "MultiEnum") {
                        $element->setFakeSingle(true);
                    }
                }

                // read (preloaded) data
                $active_record = new ilADTActiveRecordByType($record_groups[$record_id]);
                $active_record->setElementIdColumn("field_id", "integer");
                $active_record->read();

                $adt_group = $record_groups[$record_id]->getADT();

                // filter against amet values
                if ($a_amet_filter) {
                    foreach ($a_amet_filter as $field_id => $element) {
                        if ($adt_group->hasElement($field_id)) {
                            if (!$element->isInCondition($adt_group->getElement($field_id))) {
                                continue;
                            }
                        }
                    }
                }
                // add amet values to glossary term record
                foreach ($adt_group->getElements() as $element_id => $element) {
                    if (!$element->isNull()) {
                        // we are reusing the ADT group for all $a_records, so we need to clone
                        $pb = ilADTFactory::getInstance()->getPresentationBridgeForInstance(clone $element);
                        $rec["md_" . $element_id] = $pb->getSortable();
                        $rec["md_" . $element_id . "_presentation"] = $pb;
                    } else {
                        $rec["md_" . $element_id] = null;
                    }
                }
            }

            $results[] = $rec;
        }

        return $results;
    }
}
