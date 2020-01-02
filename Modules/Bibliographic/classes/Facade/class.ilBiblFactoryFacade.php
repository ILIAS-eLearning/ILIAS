<?php

/**
 * Class ilBiblFactoryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFactoryFacade implements ilBiblFactoryFacadeInterface
{

    /**
     * @var \ilBiblLibraryFactory
     */
    protected $library_factory;
    /**
     * @var \ilBiblAttributeFactoryInterface
     */
    protected $attribute_factory;
    /**
     * @var int
     */
    protected $object_id;
    /**
     * @var int
     */
    protected $ref_id;
    /**
     * @var \ilBiblFileReaderFactory
     */
    protected $file_reader_factory;
    /**
     * @var \ilBiblEntryFactory
     */
    protected $entry_factory;
    /**
     * @var \ilBiblTranslationFactory
     */
    protected $translation_factory;
    /**
     * @var \ilBiblFieldFactory
     */
    protected $field_factory;
    /**
     * @var \ilBiblFieldFilterFactory
     */
    protected $filter_factory;
    /**
     * @var \ilBiblTypeFactory
     */
    protected $type_factory;
    /**
     * @var \ilBiblOverviewModelFactory
     */
    protected $overview_factory;
    /**
     * @var \ilBiblTypeInterface
     */
    protected $type;
    /**
     * @var \ilBiblDataFactory
     */
    protected $data_factory;

    /**
     * ilBiblFactoryFacade constructor.
     *
     * @param \ilObjBibliographic $ilObjBibliographic
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
    public function typeFactory()
    {
        return $this->type_factory;
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function libraryFactory()
    {
        return $this->library_factory;
    }


    /**
     * @inheritDoc
     */
    public function fieldFactory()
    {
        return $this->field_factory;
    }


    /**
     * @inheritDoc
     */
    public function translationFactory()
    {
        return $this->translation_factory;
    }


    /**
     * @inheritDoc
     */
    public function entryFactory()
    {
        return $this->entry_factory;
    }


    /**
     * @inheritDoc
     */
    public function fileReaderFactory()
    {
        return $this->file_reader_factory;
    }


    /**
     * @inheritDoc
     */
    public function filterFactory()
    {
        return $this->filter_factory;
    }


    /**
     * @inheritDoc
     */
    public function attributeFactory()
    {
        return $this->attribute_factory;
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


    public function dataFactory()
    {
        return $this->data_factory;
    }


    /**
     * @inheritDoc
     */
    public function overviewModelFactory()
    {
        return $this->overview_factory;
    }
}
