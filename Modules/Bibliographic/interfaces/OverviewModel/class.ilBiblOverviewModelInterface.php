<?php
/**
 * Class ilBiblOverviewModelInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelInterface
{
    public function getOvmId() : ?int;
    
    public function setOvmId(int $ovm_id) : void;
    
    public function getFileTypeId() : int;
    
    public function setFileTypeId(int $file_type) : void;
    
    public function getLiteratureType() : string;
    
    public function setLiteratureType(string $literature_type) : void;
    
    public function getPattern() : string;
    
    public function setPattern(string $pattern) : void;
}
