<?php

/**
 * Interface ilBiblLibraryFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblLibraryFactoryInterface
{

    /**
     * @return \ilBiblLibraryInterface[]
     */
    public function getAll();


    /**
     * @param int $id
     *
     * @return \ilBiblLibraryInterface
     */
    public function findById($id);


    /**
     * @return \ilBiblLibraryInterface
     */
    public function getEmptyInstance();
}
