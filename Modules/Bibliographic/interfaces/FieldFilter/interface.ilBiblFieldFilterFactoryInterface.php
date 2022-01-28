<?php

/**
 * Interface ilBiblFieldFilterFactoryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblFieldFilterFactoryInterface
{
    public function findById(int $id) : \ilBiblFieldFilter;
    
    /**
     * @return ilBiblFieldFilterInterface[]
     */
    public function getAllForObjectId(int $obj_id) : array;
    
    public function filterItemsForTable(int $obj_id, ilBiblTableQueryInfoInterface $info) : array;
    
    public function getByObjectIdAndField(ilBiblFieldInterface $field, int $object_id) : ilBiblFieldFilterInterface;
}
