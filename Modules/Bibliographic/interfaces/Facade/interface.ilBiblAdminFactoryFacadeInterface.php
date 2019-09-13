<?php

/**
 * Interface ilBiblFactoryFacadeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblAdminFactoryFacadeInterface
{

    /**
     * @return \ilBiblTypeFactoryInterface
     */
    public function typeFactory();


    /**
     * @return \ilBiblTypeInterface
     */
    public function type();


    /**
     * @return \ilBiblTranslationFactoryInterface
     */
    public function translationFactory();


    /**
     * @return \ilBiblFieldFactoryInterface
     */
    public function fieldFactory();


    /**
     * @return int
     */
    public function iliasObjId();


    /**
     * @return int
     */
    public function iliasRefId();
}
