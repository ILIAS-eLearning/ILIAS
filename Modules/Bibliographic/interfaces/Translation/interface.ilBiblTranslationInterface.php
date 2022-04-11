<?php

/**
 * Interface ilBiblTranslationInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTranslationInterface
{
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    public function getFieldId() : int;
    
    public function setFieldId(int $field_id) : void;
    
    public function getLanguageKey() : string;
    
    public function setLanguageKey(string $language_key) : void;
    
    public function getTranslation() : string;
    
    public function setTranslation(string $translation) : void;
    
    public function getDescription() : string;
    
    public function setDescription(string $description) : void;
    
    public function store() : void;
}
