<?php

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
    public function getAll() : array
    {
        return ilBiblLibrary::get();
    }


    /**
     * @inheritDoc
     */
    public function findById(int $id) : \ilBiblLibraryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblLibrary::findOrFail($id);
    }


    /**
     * @inheritDoc
     */
    public function getEmptyInstance() : \ilBiblLibraryInterface
    {
        return new ilBiblLibrary();
    }
}
