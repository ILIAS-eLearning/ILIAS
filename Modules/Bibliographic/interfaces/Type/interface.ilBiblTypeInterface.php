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
 * Interface ilBiblTypeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeInterface
{
    public function isStandardField(string $identifier): bool;

    public function isEntryType(string $identifier): bool;

    /**
     * @return string such as "ris" or "bib"
     */
    public function getStringRepresentation(): string;

    /**
     * @return int ID, see ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX or
     *             DATA_TYPE_BIBTEX::DATA_TYPE_RIS
     */
    public function getId(): int;

    /**
     * @return string[]
     */
    public function getStandardFieldIdentifiers(): array;
}
