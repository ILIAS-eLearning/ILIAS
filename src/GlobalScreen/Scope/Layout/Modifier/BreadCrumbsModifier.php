<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;

/**
 * Interface BreadCrumbsModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface BreadCrumbsModifier
{

    /**
     * @param Breadcrumbs $current
     *
     * @return Breadcrumbs
     */
    public function getBreadCrumbs(Breadcrumbs $current) : Breadcrumbs;
}
