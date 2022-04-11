<?php

/**
 * Interface ilBiblEntryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryInterface
{
    public function setId(int $id) : void;
    
    public function getId() : ?int;
    
    public function getDataId() : int;
    
    public function setDataId(int $data_id) : void;
    
    public function getType() : string;
    
    public function setType(string $type) : void;
    
    public function getOverview() : string;
}
