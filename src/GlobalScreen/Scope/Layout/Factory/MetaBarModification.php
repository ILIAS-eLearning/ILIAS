<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\MainControls\MetaBar as UIMetaBar;

/**
 * Class MetaBarModification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarModification extends AbstractLayoutModification implements LayoutModification
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
        return UIMetaBar::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return UIMetaBar::class;
    }
}

