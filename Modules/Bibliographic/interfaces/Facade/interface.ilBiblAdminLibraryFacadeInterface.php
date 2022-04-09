<?php

/**
 * Interface ilBiblAdminLibraryFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblAdminLibraryFacadeInterface
{
    public function iliasObjId() : int;

    public function iliasRefId() : int;

    public function libraryFactory() : \ilBiblLibraryFactoryInterface;
}
