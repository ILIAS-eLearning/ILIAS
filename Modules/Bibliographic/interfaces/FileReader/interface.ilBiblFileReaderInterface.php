<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ilBiblFileReaderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderInterface
{
    public function readContent(ResourceIdentification $identification) : bool;
    
    public function parseContentToEntries(ilObjBibliographic $bib) : array;
    
    public function getEntryFactory() : ilBiblEntryFactoryInterface;
    
    public function getFieldFactory() : ilBiblFieldFactoryInterface;
    
    public function getAttributeFactory() : ilBiblAttributeFactoryInterface;
}
