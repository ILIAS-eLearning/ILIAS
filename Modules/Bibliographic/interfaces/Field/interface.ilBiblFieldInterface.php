<?php

/**
 * Interface ilBiblEntryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldInterface
{
    public const DATA_TYPE_RIS = 1;
    public const DATA_TYPE_BIBTEX = 2;
    
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    public function getIdentifier() : string;
    
    public function setIdentifier(string $identifier) : void;
    
    public function getPosition() : ?int;
    
    public function setPosition(int $position) : void;
    
    public function isStandardField() : bool;
    
    public function setIsStandardField(bool $is_standard_field) : void;
    
    public function getDataType() : int;
    
    public function setDataType(int $data_type) : void;
    
    public function store() : void;
}
