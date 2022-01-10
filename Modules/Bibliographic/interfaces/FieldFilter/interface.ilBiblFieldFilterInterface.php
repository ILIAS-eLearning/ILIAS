<?php

/**
 * Interface ilBiblFieldFilterInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblFieldFilterInterface
{
    public const FILTER_TYPE_MULTI_SELECT_INPUT = 3;
    public const FILTER_TYPE_SELECT_INPUT = 2;
    public const FILTER_TYPE_TEXT_INPUT = 1;
    
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    public function getFieldId() : int;
    
    public function setFieldId(int $field_id) : void;
    
    public function getObjectId() : int;
    
    public function setObjectId(int $object_id) : void;
    
    public function getFilterType() : int;
    
    public function setFilterType(int $filter_type) : void;
    
    public function create();
    
    public function update();
    
    public function delete();
}
