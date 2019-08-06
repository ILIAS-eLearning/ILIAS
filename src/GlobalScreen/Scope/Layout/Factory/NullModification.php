<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

/**
 * Class NullModification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullModification extends AbstractLayoutModification implements LayoutModification
{

    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentTypeOrNull() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getPriority() : int
    {
        return -1;
    }
}

