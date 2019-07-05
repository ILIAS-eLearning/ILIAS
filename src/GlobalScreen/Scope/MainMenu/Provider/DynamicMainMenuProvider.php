<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;

/**
 * Interface DynamicMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends DynamicProvider, MainMenuProviderInterface
{

    /**
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem[] These Slates
     * can be passed to the MainMenu dynamicly for a specific location/context.
     *
     * This is currently not used for Core components but plugins may use it.
     * @see DynamicProvider
     *
     */
    public function getDynamicSlates() : array;
}
