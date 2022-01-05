<?php

/**
 * Interface ilBiblAdminLibraryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblAdminLibraryFacade implements ilBiblAdminLibraryFacadeInterface
{

    protected int $object_id;
    protected int $ref_id;


    /**
     * ilBiblAdminLibraryFacade constructor.
     *
     * @param \ilObjBibliographicAdmin $ilias_object
     */
    public function __construct(ilObjBibliographicAdmin $ilias_object)
    {
        $this->object_id = $ilias_object->getId();
        $this->ref_id = $ilias_object->getRefId();
    }


    /**
     * @inheritDoc
     */
    public function iliasObjId() : int
    {
        return $this->object_id;
    }


    /**
     * @inheritDoc
     */
    public function iliasRefId() : int
    {
        return $this->ref_id;
    }


    /**
     * @inheritDoc
     */
    public function libraryFactory() : \ilBiblLibraryFactoryInterface
    {
        return new ilBiblLibraryFactory();
    }
}
