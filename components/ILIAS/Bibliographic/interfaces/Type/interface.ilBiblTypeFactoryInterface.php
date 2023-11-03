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
 * Interface ilBiblTypeFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeFactoryInterface
{
    public const DATA_TYPE_RIS = 1;
    public const DATA_TYPE_BIBTEX = 2;


    /**
     * @throws ilException when type not found
     *
     */
    public function getInstanceForType(int $type): ilBiblTypeInterface;


    /**
     * @throws ilException when type not found
     *
     */
    public function getInstanceForFileName(string $filename): ilBiblTypeInterface;


    /**
     *
     * @deprecated Legacy REFACTOR use type factory
     *
     */
    public function getInstanceForString(string $string): ilBiblTypeInterface;


    /**
     *
     * @throws ilException when no data type for file_ending was found
     *
     */
    public function convertFileEndingToDataType(string $file_ending): int;


    public function getDataTypeIdentifierByInstance(ilBiblTypeInterface $type_inst): int;
}
