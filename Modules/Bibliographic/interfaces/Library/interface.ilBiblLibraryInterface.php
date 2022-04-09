<?php

/**
 * Interface ilBiblLibraryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblLibraryInterface
{
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    public function getImg() : ?string;
    
    public function setImg(string $img) : void;
    
    public function getName() : string;
    
    public function setName(string $name) : void;
    
    public function isShownInList() : bool;
    
    public function setShowInList(bool $show_in_list) : void;
    
    public function getUrl() : string;
    
    public function setUrl(string $url) : void;
    
    public function store();
    
    public function delete();
    
    public function create();
    
    public function update();
}
