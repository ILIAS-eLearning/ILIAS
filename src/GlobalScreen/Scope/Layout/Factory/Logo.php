<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\Image\Image;

/**
 * Class Logo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Logo extends AbstractModifier implements Modifier
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
    public function getClosureFirstArgumentTypeOrNull() : ?string
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
}

