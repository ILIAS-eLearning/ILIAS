<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\Image\Image;

/**
 * Class LogoModification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LogoModification extends AbstractLayoutModification implements LayoutModification
{

    /**
     * @inheritDoc
     */
    public function isFinal() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentType() : string
    {
        return Image::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return Image::class;
    }


    /**
     * @inheritDoc
     */
    public function firstArgumentAllowsNull() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function returnTypeAllowsNull() : bool
    {
        return true;
    }
}

