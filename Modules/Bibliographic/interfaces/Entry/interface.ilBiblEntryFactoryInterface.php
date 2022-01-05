<?php declare(strict_types=1);

/**
 * Interface ilBiblEntryFactoryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryFactoryInterface
{
    
    /**
     * @return \ilBiblEntryInterface
     * @deprecated REFACTOR This has to be refactored to type_id and not type_string
     */
    public function findByIdAndTypeString(int $id, string $type_string) : ilBiblEntryInterface;
    
    public function findOrCreateEntry(int $id, int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface;
    
    /**
     * @param int    $bibliographic_obj_id
     * @param string $entry_type
     * @return \ilBiblEntryInterface
     */
    public function createEntry(int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface;
    
    /**
     * @return ilBiblEntry
     */
    public function getEmptyInstance() : \ilBiblEntry;
    
    /**
     * @param int                        $object_id
     * @param \ilBiblTableQueryInfo|null $info
     * @return \ilBiblEntryInterface[]
     */
    public function filterEntriesForTable(int $object_id, ilBiblTableQueryInfo $info = null) : array;
    
    /**
     * @param int                        $object_id
     * @param \ilBiblTableQueryInfo|null $info
     * @return array
     */
    public function filterEntryIdsForTableAsArray(int $object_id, ?ilBiblTableQueryInfo $info = null) : array;
    
    public function deleteEntryById(int $id) : void;
    
    /**
     * Reads all the entrys attributes from database
     */
    public function loadParsedAttributesByEntryId(int $entry_id) : array;
}
