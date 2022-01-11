<?php
/**
 * Interface ilBiblAttributeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblAttributeInterface
{
    
    public function getEntryId() : int;
    
    public function setEntryId(int $entry_id) : void;
    
    public function getName() : string;
    
    public function setName(string $name) : void;
    
    public function getValue() : string;
    
    public function setValue(string $value) : void;
    
    public function getId() : ?int;
    
    public function setId(int $id) : void;
}
