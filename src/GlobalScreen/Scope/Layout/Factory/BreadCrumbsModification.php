<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs as UIBreadcrumbs;

/**
 * Class BreadCrumbs
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BreadCrumbsModification extends AbstractLayoutModification implements LayoutModification
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
        return UIBreadcrumbs::class;
    }


    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return UIBreadcrumbs::class;
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

