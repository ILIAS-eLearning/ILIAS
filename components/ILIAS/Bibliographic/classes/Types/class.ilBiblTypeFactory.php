<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getInstanceForType(int $type): ilBiblTypeInterface
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
    public function getInstanceForFileName(string $filename): ilBiblTypeInterface
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
    public function getInstanceForString(string $string): ilBiblTypeInterface
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
    public function convertFileEndingToDataType(string $file_ending): int
    {
        switch ($file_ending) {
            case "ris":
                return ilBiblTypeFactoryInterface::DATA_TYPE_RIS;
            case "bib":
                return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
            default:
                throw new ilException("no data type found for this file ending");
        }
    }


    /**
     * @inheritDoc
     */
    public function getDataTypeIdentifierByInstance(ilBiblTypeInterface $type_inst): int
    {
        return $type_inst->getId();
    }
}
