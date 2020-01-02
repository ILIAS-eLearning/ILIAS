<?php
/**
 * Interface ilWsdlType
 */
interface ilWsdlType
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTypeClass();

    /**
     * @return string
     */
    public function getPhpType();

    /**
     * @return string
     */
    public function getCompositor();

    /**
     * @return string
     */
    public function getRestrictionBase();

    /**
     * @return array
     */
    public function getElements();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return string
     */
    public function getArrayType();
}
