<?php

/**
 * Interface ilBiblFileReaderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderInterface
{

    /**
     * @param $path_to_file
     *
     * @return bool
     */
    public function readContent($path_to_file);


    /**
     * @deprecated REFACTOR Implementierungen mit Objekten statt mit Arrays
     * @return array
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
