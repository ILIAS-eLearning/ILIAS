<?php

/**
 * Interface ilBiblFactoryFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblAdminFactoryFacadeInterface
{

    public function typeFactory() : \ilBiblTypeFactoryInterface;

    public function type() : \ilBiblTypeInterface;

    public function translationFactory() : \ilBiblTranslationFactoryInterface;

    public function fieldFactory() : \ilBiblFieldFactoryInterface;

    public function iliasObjId() : int;

    public function iliasRefId() : int;
}
