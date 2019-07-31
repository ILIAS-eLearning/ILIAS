<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\MainControls\MainBar as UIMainBar;

/**
 * Class MainBar
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainBarModification extends AbstractLayoutModification implements LayoutModification
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
        return UIMainBar::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return UIMainBar::class;
    }
}

