<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ContentModification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContentModification extends AbstractLayoutModification implements LayoutModification
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
        return Legacy::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return Legacy::class;
    }
}

