<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Icon\Icon;

/**
 * Interface hasIcon
 *
 * Methods for Entries with Icons
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasIcon
{

    /**
     * @param Icon $icon
     *
     * @return hasIcon
     */
    public function withIconPath(Icon $icon) : hasIcon;


    /**
     * @return string
     */
    public function getIconPath() : Icon;
}
