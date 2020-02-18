<?php

/**
 * Interface ilBiblFieldFilterFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblFieldFilterFactoryInterface
{

    /**
     * @param int $id
     *
     * @return ilBiblFieldFilterInterface
     */
    public function findById($id);


    /**
     * @param int $obj_id
     *
     * @return ilBiblFieldFilterInterface[]
     */
    public function getAllForObjectId($obj_id);


    /**
     * @param                                $obj_id
     * @param \ilBiblTableQueryInfoInterface $info
     *
     * @return array
     */
    public function filterItemsForTable($obj_id, ilBiblTableQueryInfoInterface $info);


    /**
     * @param \ilBiblFieldInterface $field
     * @param int                   $object_id
     *
     * @return ilBiblFieldFilterInterface
     * @throws \ilLogException if non existing
     *
     */
    public function getByObjectIdAndField(ilBiblFieldInterface $field, $object_id);
}
