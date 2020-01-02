<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * AMD field type date
 *
 * Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionInternalLink extends ilAdvancedMDFieldDefinition
{
    /**
     * Get type
     * @return int
     */
    public function getType()
    {
        return self::TYPE_INTERNAL_LINK;
    }
    
    
    /**
     * Init ADT definition
     * @return ilADTDefinition
     */
    protected function initADTDefinition()
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("InternalLink");
    }

    /**
     * Get value for XML
     * @param \ilADT $element
     */
    public function getValueForXML(\ilADT $element)
    {
        $type = ilObject::_lookupType($element->getTargetRefId(), true);

        if ($element->getTargetRefId() && strlen($type)) {
            return 'il_' . IL_INST_ID . '_' . $type . '_' . $element->getTargetRefId();
        }
        return '';
    }

    /**
     * Import value from xml
     * @param string $a_cdata
     */
    public function importValueFromXML($a_cdata)
    {
        $parsed_import_id = ilUtil::parseImportId($a_cdata);
        
        if (
            (strcmp($parsed_import_id['inst_id'], IL_INST_ID) == 0) &&
            ilObject::_exists($parsed_import_id['id'], true, $parsed_import_id['type'])
        ) {
            $this->getADT()->setTargetRefId($parsed_import_id['id']);
        }
    }
    
    
    /**
     * Search
     *
     * @param ilADTSearchBridge $a_adt_search
     * @param ilQueryParser $a_parser
     * @param array $a_object_types
     * @param string $a_locate
     * @param string $a_search_type
     * @return array
     */
    public function searchObjects(ilADTSearchBridge $a_adt_search, ilQueryParser $a_parser, array $a_object_types, $a_locate, $a_search_type)
    {
        $condition = $a_adt_search->getSQLCondition(ilADTActiveRecordByType::SINGLE_COLUMN_NAME);
        if ($condition) {
            $objects = ilADTActiveRecordByType::find("adv_md_values", $this->getADT()->getType(), $this->getFieldId(), $condition, $a_locate);
            if (sizeof($objects)) {
                return $this->parseSearchObjects($objects, $a_object_types);
            }
            return array();
        }
    }
    
    /**
     * translate lucene search string
     * @param type $a_value
     */
    public function getLuceneSearchString($a_value)
    {
        $db = $GLOBALS['DIC']->database();
        
        $query = 'select ref_id from object_reference obr join object_data obd on obr.obj_id = obd.obj_id ' .
            'where ' . $db->like('title', 'text', $a_value . '%');
        $res = $db->query($query);
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->ref_id;
        }
        return $ref_ids;
    }
}
