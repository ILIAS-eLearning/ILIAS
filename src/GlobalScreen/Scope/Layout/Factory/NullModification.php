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
    public function getClosureFirstArgumentType() : string
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

