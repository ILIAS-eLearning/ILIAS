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
 * Interface ilBiblAdminFactoryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblAdminFactoryFacade implements ilBiblAdminFactoryFacadeInterface
{
    protected \ilBiblTranslationFactory $translation_factory;
    protected \ilBiblFieldFactory $field_factory;
    protected \ilBiblTypeInterface $type;
    protected \ilBiblTypeFactory $type_factory;
    protected int $object_id;
    protected int $ref_id;


    /**
     * ilBiblAdminFactoryFacade constructor.
     */
    public function __construct(ilObjBibliographicAdmin $ilObjBibliographicAdmin, int $type_id)
    {
        $this->object_id = $ilObjBibliographicAdmin->getId();
        $this->ref_id = $ilObjBibliographicAdmin->getRefId();
        $this->type_factory = new ilBiblTypeFactory();
        $this->type = $this->type_factory->getInstanceForType($type_id);
        $this->field_factory = new ilBiblFieldFactory($this->type);
        $this->translation_factory = new ilBiblTranslationFactory($this->field_factory);
    }


    /**
     * @inheritDoc
     */
    public function typeFactory(): \ilBiblTypeFactoryInterface
    {
        return $this->type_factory;
    }


    /**
     * @inheritDoc
     */
    public function type(): \ilBiblTypeInterface
    {
        return $this->type;
    }


    /**
     * @inheritDoc
     */
    public function translationFactory(): \ilBiblTranslationFactoryInterface
    {
        return $this->translation_factory;
    }


    /**
     * @inheritDoc
     */
    public function fieldFactory(): \ilBiblFieldFactoryInterface
    {
        return $this->field_factory;
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
}
