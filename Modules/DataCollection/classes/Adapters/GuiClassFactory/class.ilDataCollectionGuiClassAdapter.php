<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionGuiClassAdapter implements ilDataCollectionGuiClassPort
{
    private string $lowerCaseGuiClassName;
    private object $guiObject;

    private function __construct(
        string $lowerCaseGuiClassName,
        object $guiObject
    ) {
        $this->lowerCaseGuiClassName = $lowerCaseGuiClassName;
        $this->guiObject = $guiObject;
    }

    public static function new(
        string $lowerCaseGuiClassName,
        object $guiObject
    ) : self {
        return new self($lowerCaseGuiClassName, $guiObject);
    }

    public function getLowerCaseGuiClassName() : string
    {
        return $this->lowerCaseGuiClassName;
    }

    public function getGuiObject() : object
    {
        return $this->guiObject;
    }
}