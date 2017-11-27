<?php

/**
 * Class ilBiblFactoryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFactoryFacade implements ilBiblFactoryFacadeInterface {

	/**
	 * @var \ilBiblLibraryFactory
	 */
	protected $library_factory;
	/**
	 * @var \ilBiblAttributeFactoryInterface
	 */
	protected $attribute_factory;
	/**
	 * @var \ilObjBibliographic
	 */
	protected $ilias_object;
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
	 * ilBiblFactoryFacade constructor.
	 *
	 * @param \ilObjBibliographic $ilObjBibliographic
	 */
	public function __construct(ilObjBibliographic $ilObjBibliographic) {
		$this->ilias_object = $ilObjBibliographic;
		$this->type_factory = new ilBiblTypeFactory();
		$this->filter_factory = new ilBiblFieldFilterFactory();
		$this->field_factory = new ilBiblFieldFactory($this->type_factory->getInstanceForType($ilObjBibliographic->getFileType()));
		$this->translation_factory = new ilBiblTranslationFactory($this->field_factory);
		$this->entry_factory = new ilBiblEntryFactory();
		$this->file_reader_factory = new ilBiblFileReaderFactory();
		$this->attribute_factory = new ilBiblAttributeFactory();
		$this->library_factory = new ilBiblLibraryFactory();
	}


	/**
	 * @inheritDoc
	 */
	public function typeFactory() {
		return $this->type_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function libraryFactory() {
		return $this->library_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function fieldFactory() {
		return $this->field_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function translationFactory() {
		return $this->translation_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function entryFactory() {
		return $this->entry_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function fileReaderFactory() {
		return $this->file_reader_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function filterFactory() {
		return $this->filter_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function attributeFactory() {
		return $this->attribute_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function iliasObject() {
		return $this->ilias_object;
	}
}
