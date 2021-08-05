<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ilBiblFileReaderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderInterface
{

    /**
     * @param ResourceIdentification $identification
     *
     * @return bool
     */
    public function readContent(ResourceIdentification $identification);


    /**
     * @return array
     * @deprecated REFACTOR Implementierungen mit Objekten statt mit Arrays
     */
    public function parseContent();


    /**
     * @return ilBiblEntryFactoryInterface
     */
    public function getEntryFactory();


    /**
     * @return ilBiblFieldFactoryInterface
     */
    public function getFieldFactory();


    /**
     * @return ilBiblAttributeFactoryInterface
     */
    public function getAttributeFactory();
}
