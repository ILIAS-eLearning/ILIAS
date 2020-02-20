<?php

/**
 * Interface ilBiblFieldFilterInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblFieldFilterInterface
{
    const FILTER_TYPE_MULTI_SELECT_INPUT = 3;
    const FILTER_TYPE_SELECT_INPUT = 2;
    const FILTER_TYPE_TEXT_INPUT = 1;


    /**
     * @return int
     */
    public function getId();


    /**
     * @param int $id
     */
    public function setId($id);


    /**
     * @return int
     */
    public function getFieldId();


    /**
     * @param int $field_id
     */
    public function setFieldId($field_id);


    /**
     * @return int
     */
    public function getObjectId();


    /**
     * @param int $object_id
     */
    public function setObjectId($object_id);


    /**
     * @return int
     */
    public function getFilterType();


    /**
     * @param int $filter_type
     */
    public function setFilterType($filter_type);


    public function create();


    public function update();


    public function delete();
}
