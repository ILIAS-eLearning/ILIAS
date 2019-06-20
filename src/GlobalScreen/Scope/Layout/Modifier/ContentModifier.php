<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Interface ContentModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ContentModifier
{

    /**
     * @param Legacy $current
     *
     * @return Legacy
     */
    public function getContent(Legacy $current) : Legacy;
}
