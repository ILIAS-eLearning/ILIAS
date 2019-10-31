<?php namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;
use ILIAS\GlobalScreen\Provider\PluginProviderHelper;

/**
 * Class AbstractDynamicToolPluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractDynamicToolPluginProvider extends AbstractDynamicToolProvider implements DynamicToolProvider, PluginProvider
{

    use PluginProviderHelper;
}
