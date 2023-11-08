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
 * Class ilBiblLibraryFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblLibraryFactory implements ilBiblLibraryFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return ilBiblLibrary::get();
    }


    /**
     * @inheritDoc
     */
    public function findById(int $id): \ilBiblLibraryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblLibrary::findOrFail($id);
    }


    /**
     * @inheritDoc
     */
    public function getEmptyInstance(): \ilBiblLibraryInterface
    {
        return new ilBiblLibrary();
    }
}
