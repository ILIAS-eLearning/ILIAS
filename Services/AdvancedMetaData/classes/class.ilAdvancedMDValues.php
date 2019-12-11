<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesAdvancedMetaData
*/
class ilAdvancedMDValues
{
    protected $record_id; // [int]
    protected $obj_id; // [int]
    protected $sub_id; // [int]
    protected $sub_type; // [int]

    protected $defs; // [array]
    protected $adt_group; // [ilADTGroup]
    protected $active_record; // [ilADTActiveRecordByType]
        
    protected $disabled; // [array]
    
    protected static $preload_obj_records; // [array]
    
    /**
     * Constructor
     *
     * @param int $a_record_id
     * @param string $a_obj_id
     * @param string $a_sub_type
     * @param int $a_sub_id
     * @return self
     */
    public function __construct($a_record_id, $a_obj_id, $a_sub_type = "-", $a_sub_id = 0)
    {
        $this->record_id = (int) $a_record_id;
        $this->obj_id = (int) $a_obj_id;
        $this->sub_type = $a_sub_type ? $a_sub_type : "-";
        $this->sub_id = (int) $a_sub_id;
    }
        
    /**
     * Get instances for given object id
     *
     * @param int $a_obj_id
     * @param string $a_obj_type
     * @return array
     */
    public static function getInstancesForObjectId($a_obj_id, $a_obj_type = null, $a_sub_type = "-", $a_sub_id = 0)
    {
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
    
    /**
     * Set the primary values for active record
     *
     * @param int $a_obj_id
     * @param string $a_sub_type
     * @param int $a_sub_id
     */
    public function setActiveRecordPrimary($a_obj_id, $a_sub_type = "-", $a_sub_id = 0)
    {
        $this->obj_id = (int) $a_obj_id;
        $this->sub_type = $a_sub_type ? $a_sub_type : "-";
        $this->sub_id = (int) $a_sub_id;
        
        // make sure they get used
        $this->active_record = null;
    }
    
    /**
     * Get record field definitions
     *
     * @return array
     */
    public function getDefinitions()
    {
        if (!is_array($this->defs)) {
            $this->defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->record_id);
        }
        return $this->defs;
    }
    
    /**
     * Init ADT group for current record
     *
     * @return ilADTGroup
     */
    public function getADTGroup()
    {
        if (!$this->adt_group instanceof ilADTGroup) {
            $this->adt_group = ilAdvancedMDFieldDefinition::getADTGroupForDefinitions($this->getDefinitions());
        }
        return $this->adt_group;
    }
    
    /**
     * Init ADT DB Bridge (aka active record helper class)
     *
     * @return ilADTActiveRecordByType
     */
    protected function getActiveRecord()
    {
        if (!$this->active_record instanceof ilADTActiveRecordByType) {
            include_once "Services/ADT/classes/class.ilADTFactory.php";
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
     *
     * @param int $a_obj_id
     * @return array
     */
    public static function findByObjectId($a_obj_id)
    {
        include_once "Services/ADT/classes/class.ilADTFactory.php";
        ilADTFactory::initActiveRecordByType();
        return ilADTActiveRecordByType::readByPrimary("adv_md_values", array("obj_id"=>array("integer", $a_obj_id)));
    }
    
        
    //
    // disabled
    //
    
    // to set disabled use self::write() with additional data
    
    /**
     * Is element disabled?
     *
     * @param string $a_element_id
     * @return bool
     */
    public function isDisabled($a_element_id)
    {
        if (is_array($this->disabled)) {
            return in_array($a_element_id, $this->disabled);
        }
    }
    
    
    //
    // CRUD
    //
    
    /**
     * Get record values
     */
    public function read()
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
     *
     * @param array $a_additional_data
     */
    public function write(array $a_additional_data = null)
    {
        $this->getActiveRecord()->write($a_additional_data);
    }
    
    /**
     * Delete values by field_id.
     * Typically called after deleting a field
     *
     * @param int $a_field_id
     * @param ilADT $a_adt
     */
    public static function _deleteByFieldId($a_field_id, ilADT $a_adt)
    {
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::deleteByPrimary(
            "adv_md_values",
            array("field_id"=>array("integer", $a_field_id)),
            $a_adt->getType()
        );
    }
        
    /**
     * Delete by objekt id
     *
     * @param int $a_obj_id
     */
    public static function _deleteByObjId($a_obj_id)
    {
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::deleteByPrimary(
            "adv_md_values",
            array("obj_id"=>array("integer", $a_obj_id))
        );
    }
    
    
    
    //
    // substitutions (aka list gui)
    //
    
    /**
     * Preload list gui data
     *
     * @param array $a_obj_ids
     */
    public static function preloadByObjIds(array $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // preload values
        ilADTFactory::getInstance()->initActiveRecordByType();
        ilADTActiveRecordByType::preloadByPrimary(
            "adv_md_values",
            array("obj_id"=>array("integer", $a_obj_ids))
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
            self::$preload_obj_records[$row["obj_type"]][] = array($row["record_id"], $row["optional"]);
        }
    }
    
    public static function preloadedRead($a_type, $a_obj_id)
    {
        $res = array();
        
        if (isset(self::$preload_obj_records[$a_type])) {
            foreach (self::$preload_obj_records[$a_type] as $item) {
                $record_id = $item[0];
                
                // record is optional, check activation for object
                if ($item[1]) {
                    $found = false;
                    include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
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
    
        
    //
    // copy/export (import: ilAdvancedMDValueParser)
    //
            
    /**
     * Clone Advanced Meta Data
     *
     * @param int source obj_id
     * @param int target obj_id
     * @param string sub_type (both source/target)
     * @param int source sub_id
     * @param int target sub_id
     */
    public static function _cloneValues($a_source_id, $a_target_id, $a_sub_type = null, $a_source_sub_id = null, $a_target_sub_id=null)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        // clone local records

        // new records are created automatically, only if source and target id differs.
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
        $new_records = $fields_map = array();

        foreach (ilAdvancedMDRecord::_getRecords() as $record) {
            if ($record->getParentObject() == $a_source_id) {
                $tmp = array();
                if ($a_source_id != $a_target_id) {
                    $new_records[$record->getRecordId()] = $record->_clone($tmp, $a_target_id);
                } else {
                    $new_records[$record->getRecordId()] = $record->getRecordId();
                }
                $fields_map[$record->getRecordId()] = $tmp;
            }
        }
        
        
        // object record selection
        
        $source_sel = ilAdvancedMDRecord::getObjRecSelection($a_source_id, $a_sub_type);
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
        
        $source_primary = array("obj_id"=>array("integer", $a_source_id));
        $target_primary = array("obj_id"=>array("integer", $a_target_id));
        
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
            array("disabled"=>"integer")
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
                    $fix_source_primary["field_id"] =  array("integer", $source_field_id);
                    $fix_target_primary = $target_primary;
                    $fix_target_primary["field_id"] =  array("integer", $target_field_id);
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
                        array("disabled"=>"integer")
                    );
                }
            }
        }
        
        if (!$has_cloned) {
            $ilLog->write(__METHOD__ . ': No advanced meta data found.');
        } else {
            $ilLog->write(__METHOD__ . ': Start cloning advanced meta data.');
        }
        return true;
    }
    
    /**
     * Get xml of object values
     *
     * @param ilXmlWriter $a_xml_writer
     * @param int $a_obj_id
     */
    public static function _appendXMLByObjId(ilXmlWriter $a_xml_writer, $a_obj_id)
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
    

    //
    // glossary (might be generic)
    //

    /**
     * Query data for given object records
     *
     * @param
     * @return
     */
    public static function queryForRecords($adv_rec_obj_ref_id, $adv_rec_obj_type, $adv_rec_obj_subtype, $a_obj_id, $a_subtype, $a_records, $a_obj_id_key, $a_obj_subid_key, array $a_amet_filter = null)
    {
        $results = array();
        
        if (!is_array($a_obj_id)) {
            $a_obj_id = array($a_obj_id);
        }
            
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
            $obj_id = (int) $rec[$a_obj_id_key];
            $sub_id = $rec[$a_obj_subid_key];

            // get adv records
            foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject($adv_rec_obj_type, $adv_rec_obj_ref_id, $adv_rec_obj_subtype) as $adv_record) {
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
                                continue(3);
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
