<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationPluginProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;

/**
 * Class PluginProviderCollection
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderCollection
{

    /**
     * @return AbstractModificationPluginProvider
     */
    public function getModificationProvider() : ?AbstractModificationPluginProvider;


    /**
     * @return AbstractStaticMainMenuPluginProvider
     */
    public function getMainBarProvider() : ?AbstractStaticMainMenuPluginProvider;


    /**
     * @return AbstractDynamicToolPluginProvider
     */
    public function getToolProvider() : ?AbstractDynamicToolPluginProvider;


    /**
     * @return AbstractStaticMetaBarPluginProvider
     */
    public function getMetaBarProvider() : ?AbstractStaticMetaBarPluginProvider;


    /**
     * @return AbstractNotificationPluginProvider
     */
    public function getNotificationProvider() : ?AbstractNotificationPluginProvider;
}