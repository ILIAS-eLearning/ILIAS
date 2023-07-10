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
     */
    public function __construct(ilObjBibliographicAdmin $ilias_object)
    {
        $this->object_id = $ilias_object->getId();
        $this->ref_id = $ilias_object->getRefId();
    }


    /**
     * @inheritDoc
     */
    public function iliasObjId(): int
    {
        return $this->object_id;
    }


    /**
     * @inheritDoc
     */
    public function iliasRefId(): int
    {
        return $this->ref_id;
    }


    /**
     * @inheritDoc
     */
    public function libraryFactory(): \ilBiblLibraryFactoryInterface
    {
        return new ilBiblLibraryFactory();
    }
}
