<?php

/**
 * Interface ilBiblTypeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeInterface
{

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isStandardField(string $identifier) : bool;


    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isEntryType(string $identifier) : bool;


    /**
     * @return string such as "ris" or "bib"
     */
    public function getStringRepresentation() : string;


    /**
     * @return int ID, see ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX or
     *             DATA_TYPE_BIBTEX::DATA_TYPE_RIS
     */
    public function getId() : int;


    /**
     * @return string[]
     */
    public function getStandardFieldIdentifiers() : array;
}
