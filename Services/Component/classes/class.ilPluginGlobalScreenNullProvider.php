<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;

/**
 * Class ilPluginGlobalScreenNullProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class ilPluginGlobalScreenNullProvider extends AbstractStaticPluginMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }


    /**
     * @inheritDoc
     */
    final public function getPurpose() : string
    {
        return "mainmenu";
    }


    /**
     * @inheritDoc
     */
    final public function getStaticTopItems() : array
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    final public function getStaticSubItems() : array
    {
        return array();
    }
}
