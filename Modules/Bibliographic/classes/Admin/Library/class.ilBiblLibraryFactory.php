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
    public function getAll()
    {
        return ilBiblLibrary::get();
    }


    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        return ilBiblLibrary::findOrFail($id);
    }


    /**
     * @inheritDoc
     */
    public function getEmptyInstance()
    {
        return new ilBiblLibrary();
    }
}
