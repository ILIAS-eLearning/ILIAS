<?php

/**
 * Interface ilBiblEntryFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryFactoryInterface
{

    /**
     * @param int    $id
     * @param string $type_string
     *
     * @deprecated REFACTOR This has to be refactored to type_id and not type_string
     *
     * @return \ilBiblEntryInterface
     */
    public function findByIdAndTypeString($id, $type_string) : ilBiblEntryInterface;

    /**
     * @param int        $id
     * @param int        $bibliographic_obj_id
     * @param string     $entry_type
     *
     * @return \ilBiblEntryInterface
     */
    public function findOrCreateEntry($id, $bibliographic_obj_id, $entry_type);


    /**
     * @param $bibliographic_obj_id
     * @param $entry_type
     *
     * @return \ilBiblEntryInterface
     */
    public function createEntry($bibliographic_obj_id, $entry_type);


    /**
     * @return \ilBiblEntryInterface
     */
    public function getEmptyInstance();


    /**
     * @param                            $object_id
     * @param \ilBiblTableQueryInfo|null $info
     *
     * @return \ilBiblEntryInterface[]
     */
    public function filterEntriesForTable($object_id, ilBiblTableQueryInfo $info = null);


    /**
     * @param                            $object_id
     * @param \ilBiblTableQueryInfo|null $info
     *
     * @return array
     */
    public function filterEntryIdsForTableAsArray($object_id, ilBiblTableQueryInfo $info = null);

    /**
     * @param int    $id
     *
     */
    public function deleteEntryById($id);

    /**
     * Read all entries from the database
     *
     * @param int $object_id
     *
     * @return \ilBiblEntryInterface[]
     */
    public function getAllEntries($object_id);

    /**
     * Get entry from the database
     *
     * @param int $object_id
     *
     * @return \ilBiblEntryInterface
     */
    public function getEntryById($id);

    /**
     * Reads all the entrys attributes from database
     *
     * @param integer $entry_id
     *
     * @return ilBiblEntryInterface[]
     */
    public function loadParsedAttributesByEntryId($entry_id);
}
