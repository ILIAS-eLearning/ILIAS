<?php

/**
 * Interface ilBiblAdminLibraryFacadeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblAdminLibraryFacadeInterface
{

    /**
     * @return int
     */
    public function iliasObjId();


    /**
     * @return int
     */
    public function iliasRefId();


    /**
     * @return \ilBiblLibraryFactoryInterface
     */
    public function libraryFactory();
}
