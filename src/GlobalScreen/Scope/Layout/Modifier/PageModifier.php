<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface PageModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PageModifier
{

    /**
     * @param Page $current
     *
     * @return Page
     */
    public function getPage(Page $current) : Page;
}
