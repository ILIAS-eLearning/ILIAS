<?php

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
     *
     * @param \ilObjBibliographicAdmin $ilObjBibliographicAdmin
     */
    public function __construct(ilObjBibliographicAdmin $ilObjBibliographicAdmin, $type_id)
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
    public function typeFactory() : \ilBiblTypeFactoryInterface
    {
        return $this->type_factory;
    }


    /**
     * @inheritDoc
     */
    public function type() : \ilBiblTypeInterface
    {
        return $this->type;
    }


    /**
     * @inheritDoc
     */
    public function translationFactory() : \ilBiblTranslationFactoryInterface
    {
        return $this->translation_factory;
    }


    /**
     * @inheritDoc
     */
    public function fieldFactory() : \ilBiblFieldFactoryInterface
    {
        return $this->field_factory;
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
}
