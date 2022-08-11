<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Class ilBiblEntryFactory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryFactory implements ilBiblEntryFactoryInterface
{
    protected int $bibliographic_obj_id;
    protected int $entry_id;
    protected string $type;
    protected array $attributes;
    protected \ilBiblTypeInterface $file_type;
    protected \ilBiblFieldFactoryInterface $field_factory;
    protected \ilBiblOverviewModelFactoryInterface $overview_factory;
    protected ilDBInterface $db;
    
    /**
     * ilBiblEntryFactory constructor.
     */
    public function __construct(ilBiblFieldFactoryInterface $field_factory, \ilBiblTypeInterface $file_type, ilBiblOverviewModelFactoryInterface $overview_factory)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->file_type = $file_type;
        $this->field_factory = $field_factory;
        $this->overview_factory = $overview_factory;
    }
    
    /**
     * @inheritDoc
     */
    public function loadParsedAttributesByEntryId(int $entry_id) : array
    {
        $ilBiblEntry = ilBiblEntry::where(array('id' => $entry_id))->first();
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
    public function findByIdAndTypeString(int $id, string $type_string) : ilBiblEntryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblEntry::where(array('id' => $id))->first();
    }
    
    /**
     * @inheritDoc
     */
    public function findOrCreateEntry(int $id, int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface
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
    public function createEntry(int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface
    {
        $inst = new ilBiblEntry();
        $inst->setDataId($bibliographic_obj_id);
        $inst->setEntryType($entry_type);
        $inst->create();
        
        return $inst;
    }
    
    public function getEmptyInstance() : \ilBiblEntry
    {
        return new ilBiblEntry();
    }
    
    private function getARInstance(int $id) : ?\ilBiblEntry
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblEntry::where(["ïd" => $id])->first();
    }
    
    /**
     * @return \ilBiblEntryInterface[]
     */
    public function filterEntriesForTable(int $object_id, ilBiblTableQueryInfo $info = null) : array
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
    public function filterEntryIdsForTableAsArray(int $object_id, ?ilBiblTableQueryInfo $info = null) : array
    {
        $types = ["integer"];
        $values = [$object_id];
        
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
                    $q .= " AND e.id IN (SELECT a.entry_id FROM il_bibl_attribute AS a WHERE a.name = %s AND " . $this->db->in("a.value", $value, false, "text") . ")";
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
        $entries = [];
        $set = $this->db->queryF($q, $types, $values);
        
        $i = 0;
        while ($rec = $this->db->fetchAssoc($set)) {
            $entries[$i]['entry_id'] = $rec['id'];
            $entries[$i]['entry_type'] = $rec['type'];
            $i++;
        }
        
        return $entries;
    }
    
    public function deleteEntryById(int $id) : void
    {
        $entry = ilBiblEntry::where(array('id' => $id))->first();
        if ($entry instanceof ilBiblEntry) {
            $entry->delete();
        }
    }
    
    public function deleteEntriesById(int $object_id) : void
    {
        $this->db->manipulateF("DELETE FROM il_bibl_entry WHERE data_id = %s", ['integer'], [$object_id]);
    }
    
    /**
     * @return \ilBiblAttribute[]
     */
    public function getAllAttributesByEntryId(int $id) : array
    {
        return ilBiblAttribute::where(array('entry_id' => $id))->get();
    }
    
    public function getFileType() : \ilBiblTypeInterface
    {
        return $this->file_type;
    }
    
    public function setFileType(string $file_type) : void
    {
        $this->file_type = $file_type;
    }
    
    /**
     * @param ilBiblFieldInterface[] $attributes
     */
    public function setAttributes(array $attributes) : void
    {
        $this->attributes = $attributes;
    }
    
    /**
     * @return string[]
     * @deprecated REFACTOR nach refactoring von loadAttributes Methoden die getAttributes verwenden entsprechend anpassen. (Statt Array Objekte verwenden)
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
}
