<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;
use ILIAS\GlobalScreen\Provider\PluginProviderHelper;

/**
 * Interface AbstractStaticMetaBarPluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMetaBarPluginProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider, PluginProvider
{

    use PluginProviderHelper;
}
