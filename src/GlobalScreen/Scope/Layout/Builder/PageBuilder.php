<?php namespace ILIAS\UI\Component\Layout\Builder;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface PageBuilder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PageBuilder
{

    /**
     * @param PagePartProvider $parts
     *
     * @return Page
     */
    public function build(PagePartProvider $parts) : Page;
}
