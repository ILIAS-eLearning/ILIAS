<?php

namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;
use ILIAS\GlobalScreen\Provider\PluginProviderHelper;

/**
 * Class AbstractModificationPluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractModificationPluginProvider extends AbstractModificationProvider implements ModificationProvider, PluginProvider
{

    use PluginProviderHelper;
}
