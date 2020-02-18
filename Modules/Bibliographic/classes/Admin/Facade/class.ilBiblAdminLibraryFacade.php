<?php

/**
 * Interface ilBiblAdminLibraryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblAdminLibraryFacade implements ilBiblAdminLibraryFacadeInterface
{

    /**
     * @var int
     */
    protected $object_id;
    /**
     * @var int
     */
    protected $ref_id;


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
    public function iliasObjId()
    {
        return $this->object_id;
    }


    /**
     * @inheritDoc
     */
    public function iliasRefId()
    {
        return $this->ref_id;
    }


    /**
     * @inheritDoc
     */
    public function libraryFactory()
    {
        return new ilBiblLibraryFactory();
    }
}
