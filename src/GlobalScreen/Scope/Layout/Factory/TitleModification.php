<?php

namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

/**
 * Class TitleModification
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class TitleModification extends AbstractLayoutModification implements LayoutModification
{

    /**
     * @inheritDoc
     */
    public function isFinal() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentType() : string
    {
        return 'string';
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return 'string';
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
        return false;
    }
}
