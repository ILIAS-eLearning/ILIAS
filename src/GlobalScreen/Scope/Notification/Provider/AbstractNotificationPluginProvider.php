<?php namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;
use ILIAS\GlobalScreen\Provider\PluginProviderHelper;

/**
 * Interface AbstractNotificationPluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractNotificationPluginProvider extends AbstractNotificationProvider implements NotificationProvider, PluginProvider
{

    use PluginProviderHelper;
}
