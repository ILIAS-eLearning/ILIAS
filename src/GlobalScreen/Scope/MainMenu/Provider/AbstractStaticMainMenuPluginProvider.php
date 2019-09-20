<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;
use ILIAS\GlobalScreen\Provider\PluginProviderHelper;

/**
 * Class AbstractStaticMainMenuPluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMainMenuPluginProvider extends AbstractStaticMainMenuProvider implements PluginProvider, StaticMainMenuProvider
{

    use PluginProviderHelper;
}
