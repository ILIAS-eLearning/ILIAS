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
 * Class ilBiblFactoryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFactoryFacade implements ilBiblFactoryFacadeInterface
{
    protected \ilBiblLibraryFactory $library_factory;
    protected \ilBiblAttributeFactoryInterface $attribute_factory;
    protected int $object_id;
    protected int $ref_id;
    protected \ilBiblFileReaderFactory $file_reader_factory;
    protected \ilBiblEntryFactory $entry_factory;
    protected \ilBiblTranslationFactory $translation_factory;
    protected \ilBiblFieldFactory $field_factory;
    protected \ilBiblFieldFilterFactory $filter_factory;
    protected \ilBiblTypeFactory $type_factory;
    protected \ilBiblOverviewModelFactory $overview_factory;
    protected \ilBiblTypeInterface $type;
    protected \ilBiblDataFactory $data_factory;


    /**
     * ilBiblFactoryFacade constructor.
     */
    public function __construct(ilObjBibliographic $ilObjBibliographic)
    {
        $this->object_id = $ilObjBibliographic->getId();
        $this->ref_id = $ilObjBibliographic->getRefId();
        $this->type_factory = new ilBiblTypeFactory();
        $this->type = $this->typeFactory()->getInstanceForType($ilObjBibliographic->getFileType());
        $this->filter_factory = new ilBiblFieldFilterFactory();
        $this->field_factory = new ilBiblFieldFactory($this->type_factory->getInstanceForType($ilObjBibliographic->getFileType()));
        $this->translation_factory = new ilBiblTranslationFactory($this->field_factory);
        $this->overview_factory = new ilBiblOverviewModelFactory();
        $this->entry_factory = new ilBiblEntryFactory($this->fieldFactory(), $this->type(), $this->overview_factory);
        $this->file_reader_factory = new ilBiblFileReaderFactory();
        $this->attribute_factory = new ilBiblAttributeFactory($this->fieldFactory());
        $this->library_factory = new ilBiblLibraryFactory();
        $this->data_factory = new ilBiblDataFactory();
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
    public function libraryFactory() : \ilBiblLibraryFactoryInterface
    {
        return $this->library_factory;
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
    public function translationFactory() : \ilBiblTranslationFactoryInterface
    {
        return $this->translation_factory;
    }


    /**
     * @inheritDoc
     */
    public function entryFactory() : \ilBiblEntryFactoryInterface
    {
        return $this->entry_factory;
    }


    /**
     * @inheritDoc
     */
    public function fileReaderFactory() : \ilBiblFileReaderFactoryInterface
    {
        return $this->file_reader_factory;
    }


    /**
     * @inheritDoc
     */
    public function filterFactory() : \ilBiblFieldFilterFactoryInterface
    {
        return $this->filter_factory;
    }


    /**
     * @inheritDoc
     */
    public function attributeFactory() : \ilBiblAttributeFactoryInterface
    {
        return $this->attribute_factory;
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


    public function dataFactory() : \ilBiblDataFactoryInterface
    {
        return $this->data_factory;
    }


    /**
     * @inheritDoc
     */
    public function overviewModelFactory() : \ilBiblOverviewModelFactoryInterface
    {
        return $this->overview_factory;
    }
}
