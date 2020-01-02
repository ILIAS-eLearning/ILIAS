<?php

/**
 * Class ilBiblTypeFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTypeFactory implements ilBiblTypeFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function getInstanceForType(int $type) : ilBiblTypeInterface
    {
        assert(is_int($type));
        switch ($type) {
        case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
            return new ilBibTex();
        case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
            return new ilRis();
        default:
            throw new ilException("bibliografic type not found");
        }
    }


    /**
     * @inheritDoc
     */
    public function getInstanceForFileName(string $filename) : ilBiblTypeInterface
    {
        //return bib for filetype .bibtex:
        if (strtolower(substr($filename, -6)) == "bibtex"
            || strtolower(substr($filename, -3)) == "bib"
        ) {
            return $this->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX);
        }

        //else return its true filetype
        return $this->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_RIS);
    }


    /**
     * @inheritDoc
     */
    public function getInstanceForString(string $string) : ilBiblTypeInterface
    {
        switch ($string) {
        case "bib":
            return new ilBibTex();
        case "ris":
            return new ilRis();
        default:
            throw new ilException("bibliografic type not found");
        }
    }


    /**
     * @inheritDoc
     */
    public function convertFileEndingToDataType(string $file_ending) : int
    {
        switch ($file_ending) {
        case "ris":
            return ilBiblTypeFactoryInterface::DATA_TYPE_RIS;
            break;
        case "bib":
            return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
            break;
        default:
            throw new ilException("no data type found for this file ending");
        }
    }


    /**
     * @inheritDoc
     */
    public function getDataTypeIdentifierByInstance(ilBiblTypeInterface $type_inst) : int
    {
        if ($type_inst instanceof ilRis) {
            return ilBiblTypeFactoryInterface::DATA_TYPE_RIS;
        } elseif ($type_inst instanceof ilBibTex) {
            return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
        }
    }
}
