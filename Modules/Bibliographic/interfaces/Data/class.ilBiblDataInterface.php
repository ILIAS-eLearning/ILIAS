<?php
/**
 * Class ilBiblDataInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblDataInterface
{
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    /**
     * @deprecated
     */
    public function getFilename() : ?string;
    
    /**
     * @deprecated
     */
    public function setFilename(string $filename) : void;
    
    public function isOnline() : bool;
    
    public function setIsOnline(int $is_online) : void;
    
    public function getFileType() : int;
    
    public function setFileType(int $file_type) : void;
}
