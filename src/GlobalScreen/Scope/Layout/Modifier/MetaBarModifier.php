<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Interface MetaBarModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MetaBarModifier
{

    /**
     * @param MetaBar $current
     *
     * @return MetaBar
     */
    public function getMetaBar(MetaBar $current) : MetaBar;
}
