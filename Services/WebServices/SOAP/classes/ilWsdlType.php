<?php

/**
 * Interface ilWsdlType
 */
interface ilWsdlType
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getTypeClass(): string;

    /**
     * @return string
     */
    public function getPhpType(): string;

    /**
     * @return string
     */
    public function getCompositor(): string;

    /**
     * @return string
     */
    public function getRestrictionBase(): string;

    /**
     * @return array
     */
    public function getElements(): array;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @return string
     */
    public function getArrayType(): string;
}
