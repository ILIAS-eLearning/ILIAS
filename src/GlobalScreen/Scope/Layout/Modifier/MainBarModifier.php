<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\MainControls\MainBar;

/**
 * Interface MainBarModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MainBarModifier
{

    /**
     * @param MainBar $current
     *
     * @return MainBar
     */
    public function getMainBar(MainBar $current) : MainBar;
}
