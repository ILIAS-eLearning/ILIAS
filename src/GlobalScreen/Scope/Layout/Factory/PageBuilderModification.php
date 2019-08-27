<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Class PageBuilderModification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageBuilderModification extends AbstractLayoutModification implements LayoutModification
{

    /**
     * @inheritDoc
     */
    public function firstArgumentAllowsNull() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function returnTypeAllowsNull() : bool
    {
        return false;
    }


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
        return PagePartProvider::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return Page::class;
    }
}

