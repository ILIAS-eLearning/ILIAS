<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type date
 * Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionInternalLink extends ilAdvancedMDFieldDefinition
{
    public function getType() : int
    {
        return self::TYPE_INTERNAL_LINK;
    }

    protected function initADTDefinition() : ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("InternalLink");
    }

    public function getValueForXML(ilADT $element) : string
    {
        $type = ilObject::_lookupType($element->getTargetRefId(), true);

        if ($element->getTargetRefId() && strlen($type)) {
            return 'il_' . IL_INST_ID . '_' . $type . '_' . $element->getTargetRefId();
        }
        return '';
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $parsed_import_id = ilUtil::parseImportId($a_cdata);

        if (
            (strcmp($parsed_import_id['inst_id'], IL_INST_ID) == 0) &&
            ilObject::_exists($parsed_import_id['id'], true, $parsed_import_id['type'])
        ) {
            $this->getADT()->setTargetRefId($parsed_import_id['id']);
        }
    }

    public function searchObjects(
        ilADTSearchBridge $a_adt_search,
        ilQueryParser $a_parser,
        array $a_object_types,
        string $a_locate,
        string $a_search_type
    ) : array {
        $condition = $a_adt_search->getSQLCondition(ilADTActiveRecordByType::SINGLE_COLUMN_NAME);
        if ($condition) {
            $objects = ilADTActiveRecordByType::find(
                "adv_md_values",
                $this->getADT()->getType(),
                $this->getFieldId(),
                $condition,
                $a_locate
            );
            if (!is_null($objects) && count($objects)) {
                return $this->parseSearchObjects($objects, $a_object_types);
            }
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLuceneSearchString($a_value) : string
    {
        $query = 'select ref_id from object_reference obr join object_data obd on obr.obj_id = obd.obj_id ' .
            'where ' . $this->db->like('title', 'text', $a_value . '%');
        $res = $this->db->query($query);
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = (int) $row->ref_id;
        }
        if (count($ref_ids)) {
            return '(' . implode(' ', $ref_ids) . ') ';
        }
        return 'null';
    }
}
