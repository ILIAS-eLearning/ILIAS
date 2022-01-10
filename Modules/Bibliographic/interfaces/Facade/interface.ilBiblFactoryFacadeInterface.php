<?php

/**
 * Interface ilBiblFactoryFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFactoryFacadeInterface
{

    public function typeFactory() : \ilBiblTypeFactoryInterface;

    public function overviewModelFactory() : \ilBiblOverviewModelFactoryInterface;

    public function type() : \ilBiblTypeInterface;

    public function libraryFactory() : \ilBiblLibraryFactoryInterface;

    public function fieldFactory() : \ilBiblFieldFactoryInterface;

    public function translationFactory() : \ilBiblTranslationFactoryInterface;

    public function entryFactory() : \ilBiblEntryFactoryInterface;

    public function fileReaderFactory() : \ilBiblFileReaderFactoryInterface;

    public function filterFactory() : \ilBiblFieldFilterFactoryInterface;

    public function attributeFactory() : \ilBiblAttributeFactoryInterface;

    public function iliasObjId() : int;

    public function iliasRefId() : int;

    public function dataFactory() : \ilBiblDataFactoryInterface;
}
