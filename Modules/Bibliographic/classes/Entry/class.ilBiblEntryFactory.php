<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBiblEntryFactory
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryFactory implements ilBiblEntryFactoryInterface
{

    /**
     * ILIAS-Id of bibliographic-object
     *
     * @var int
     */
    protected $bibliographic_obj_id;
    /**
     * Internal id of entry
     *
     * @var int
     */
    protected $entry_id;
    /**
     * type of entry
     *
     * @var string
     */
    protected $type;
    /**
     * array containing all types of attributes the entry has, except the type
     *
     * @var string[]
     */
    protected $attributes;
    /**
     * file type
     *
     * @var \ilBiblTypeInterface
     */
    protected $file_type;
    /**
     * @var \ilBiblFieldFactoryInterface
     */
    protected $field_factory;
    /**
     * @var \ilBiblOverviewModelFactoryInterface
     */
    protected $overview_factory;


    /**
     * ilBiblEntryFactory constructor.
     *
     * @param ilBiblFieldFactoryInterface $field_factory
     * @param ilBiblTypeInterface         $file_type
     */
    public function __construct(ilBiblFieldFactoryInterface $field_factory, \ilBiblTypeInterface $file_type, ilBiblOverviewModelFactoryInterface $overview_factory)
    {
        $this->file_type = $file_type;
        $this->field_factory = $field_factory;
        $this->overview_factory = $overview_factory;
    }


    /**
     * @inheritDoc
     */
    public function loadParsedAttributesByEntryId($entry_id)
    {
        $ilBiblEntry = ilBiblEntry::where(array( 'id' => $entry_id ))->first();
        $attributes = $this->getAllAttributesByEntryId($entry_id);

        if ($this->file_type->getId() == ilBiblTypeFactoryInterface::DATA_TYPE_RIS) {
            //for RIS-Files also add the type;
            $type = $ilBiblEntry->getType();
        } else {
            $type = 'default';
        }
        $parsed_attributes = array();
        foreach ($attributes as $attribute) {
            // surround links with <a href="">
            // Allowed signs in URL: a-z A-Z 0-9 . ? & _ / - ~ ! ' * ( ) + , : ; @ = $ # [ ] %
            $value = preg_replace('!(http)(s)?:\/\/[a-zA-Z0-9.?&_/\-~\!\'\*()+,:;@=$#\[\]%]+!', "<a href=\"\\0\" target=\"_blank\">\\0</a>", $attribute->getValue());
            $attribute->setValue($value);
            $parsed_attributes[strtolower($this->file_type->getStringRepresentation() . '_' . $type . '_' . $attribute->getName())] = $value;

            $this->field_factory->findOrCreateFieldOfAttribute($attribute);
        }

        return $parsed_attributes;
    }


    /**
     * @inheritDoc
     */
    public function findByIdAndTypeString($id, $type_string) : ilBiblEntryInterface
    {
        return ilBiblEntry::where(array( 'id' => $id))->first();
    }


    /**
     * @inheritDoc
     */
    public function findOrCreateEntry($id, $bibliographic_obj_id, $entry_type)
    {
        $inst = $this->getARInstance($id);
        if (!$inst) {
            $inst = $this->createEntry($bibliographic_obj_id, $entry_type);
        }
        $inst->setDataId($bibliographic_obj_id);
        $inst->setEntryType($entry_type);
        $inst->update();

        return $inst;
    }


    /**
     * @inheritDoc
     */
    public function createEntry($bibliographic_obj_id, $entry_type)
    {
        $inst = new ilBiblEntry();
        $inst->setDataId($bibliographic_obj_id);
        $inst->setEntryType($entry_type);
        $inst->create();

        return $inst;
    }


    /**
     * @inheritDoc
     */
    public function getEmptyInstance()
    {
        return new ilBiblEntry();
    }


    /**
     * @param int $id
     *
     * @return \ilBiblField
     */
    private function getARInstance($id)
    {
        return ilBiblEntry::where([ "ïd" => $id ])->first();
    }


    /**
     * @inheritDoc
     */
    public function filterEntriesForTable($object_id, ilBiblTableQueryInfo $info = null)
    {
        $entries = $this->filterEntryIdsForTableAsArray($object_id, $info);
        $entry_objects = [];
        foreach ($entries as $entry_id => $entry) {
            $entry_objects[$entry_id] = $this->findByIdAndTypeString($entry['type'], $entry['id']);
        }

        return $entry_objects;
    }


    /**
     * @inheritDoc
     */
    public function filterEntryIdsForTableAsArray($object_id, ilBiblTableQueryInfo $info = null)
    {
        global $DIC;

        $types = [ "integer" ];
        $values = [ $object_id ];

        if ($info instanceof ilBiblTableQueryInfo) {
            $filters = $info->getFilters();
            if (!empty($filters)) {
                $q = "SELECT (e.id), e.type FROM il_bibl_entry AS e WHERE data_id = %s";
                foreach ($filters as $filter) {
                    $value = $filter->getFieldValue();
                    if (!$value) {
                        continue;
                    }
                    if ($filter->getOperator() === "IN" && is_array($filter->getFieldValue())) {
                        $types[] = "text";
                        $values[] = $filter->getFieldName();
                        $q .= " AND e.id IN (SELECT a.entry_id FROM il_bibl_attribute AS a WHERE a.name = %s AND " . $DIC->database()->in("a.value", $value, false, "text") . ")";
                    } else {
                        $types[] = "text";
                        $values[] = $filter->getFieldName();
                        $types[] = "text";
                        $values[] = "{$value}";
                        $q .= " AND e.id IN (SELECT a.entry_id FROM il_bibl_attribute AS a WHERE a.name = %s AND a.value {$filter->getOperator()} %s )";
                    }
                }
            } else {
                $q = "SELECT DISTINCT (e.id), e.type FROM il_bibl_entry AS e
                JOIN il_bibl_attribute AS a ON a.entry_id = e.id
                        WHERE data_id = %s";
            }
        }
        $entries = array();
        $set = $DIC->database()->queryF($q, $types, $values);

        $i = 0;
        while ($rec = $DIC->database()->fetchAssoc($set)) {
            $entries[$i]['entry_id'] = $rec['id'];
            $entries[$i]['entry_type'] = $rec['type'];
            $i++;
        }

        return $entries;
    }


    public function deleteEntryById($id)
    {
        $entry = ilBiblEntry::where(array('id' => $id))->first();
        if ($entry instanceof ilBiblEntry) {
            $entry->delete();
        }
    }


    public function getAllEntries($object_id)
    {
        return ilBiblEntry::where(array( 'data_id' => $object_id ))->first();
    }


    public function getEntryById($id)
    {
        return ilBiblEntry::where(array( 'id' => $id ))->first();
    }


    public function getAllAttributesByEntryId($id)
    {
        return ilBiblAttribute::where(array( 'entry_id' => $id ))->get();
    }

    /**
     * @return string
     */
    public function getFileType()
    {
        return $this->file_type;
    }


    /**
     * @param string $file_type
     */
    public function setFileType($file_type)
    {
        $this->file_type = $file_type;
    }


    /**
     * @param $attributes ilBiblFieldInterface[]
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }


    /**
     * @deprecated REFACTOR nach refactoring von loadAttributes Methoden die getAttributes verwenden entsprechend anpassen. (Statt Array Objekte verwenden)
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
