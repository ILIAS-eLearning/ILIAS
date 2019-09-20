<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;

/**
 * Class AbstractStaticPluginMainMenuProvider
 *
 * @deprecated use AbstractStaticMainMenuPluginProvider instead. This class will be removed in ILIAS 7
 * @see        AbstractStaticMainMenuPluginProvider
 *
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticPluginMainMenuProvider extends AbstractStaticMainMenuPluginProvider implements PluginProvider, StaticMainMenuProvider
{

}
