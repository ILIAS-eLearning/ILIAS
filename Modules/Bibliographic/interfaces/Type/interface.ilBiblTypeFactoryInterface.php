<?php

/**
 * Interface ilBiblTypeFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeFactoryInterface
{
    const DATA_TYPE_RIS = 1;
    const DATA_TYPE_BIBTEX = 2;


    /**
     * @param int $type
     *
     * @throws ilException when type not found
     *
     * @return \ilBiblTypeInterface
     */
    public function getInstanceForType(int $type) : ilBiblTypeInterface;


    /**
     * @param string $filename
     *
     * @throws ilException when type not found
     *
     * @return \ilBiblTypeInterface
     */
    public function getInstanceForFileName(string $filename) : ilBiblTypeInterface;


    /**
     * @deprecated Legacy REFACTOR use type factory
     *
     * @param string $string
     *
     * @return \ilBiblTypeInterface
     */
    public function getInstanceForString(string $string) : ilBiblTypeInterface;


    /**
     * @param string $file_ending
     *
     * @throws ilException when no data type for file_ending was found
     *
     * @return int
     */
    public function convertFileEndingToDataType(string $file_ending) : int;


    /**
     * @param ilBiblTypeInterface $type_inst
     *
     * @return int
     */
    public function getDataTypeIdentifierByInstance(ilBiblTypeInterface $type_inst) : int;
}
