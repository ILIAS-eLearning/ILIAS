<?php

/**
 * Interface ilBiblFactoryFacadeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFactoryFacadeInterface
{

    /**
     * @return \ilBiblTypeFactoryInterface
     */
    public function typeFactory();

    /**
     * @return \ilBiblOverviewModelFactoryInterface
     */
    public function overviewModelFactory();

    /**
     * @return \ilBiblTypeInterface
     */
    public function type();


    /**
     * @return \ilBiblLibraryFactoryInterface
     */
    public function libraryFactory();


    /**
     * @return \ilBiblFieldFactoryInterface
     */
    public function fieldFactory();


    /**
     * @return \ilBiblTranslationFactoryInterface
     */
    public function translationFactory();


    /**
     * @return \ilBiblEntryFactoryInterface
     */
    public function entryFactory();


    /**
     * @return \ilBiblFileReaderFactoryInterface
     */
    public function fileReaderFactory();


    /**
     * @return \ilBiblFieldFilterFactoryInterface
     */
    public function filterFactory();


    /**
     * @return \ilBiblAttributeFactoryInterface
     */
    public function attributeFactory();


    /**
     * @return int
     */
    public function iliasObjId();


    /**
     * @return int
     */
    public function iliasRefId();

    /**
     * @return \ilBiblDataFactoryInterface
     */
    public function dataFactory();
}
