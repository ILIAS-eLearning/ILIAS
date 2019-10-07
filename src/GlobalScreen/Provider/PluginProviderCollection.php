<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationPluginProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;

class PluginProviderCollection implements ProviderCollection
{

    /**
     * @var AbstractModificationPluginProvider
     */
    private $modification_provider;
    /**
     * @var AbstractStaticMainMenuPluginProvider
     */
    private $main_bar_provider;
    /**
     * @var AbstractDynamicToolPluginProvider
     */
    private $tool_provider;
    /**
     * @var AbstractStaticMetaBarPluginProvider
     */
    private $meta_bar_provider;
    /**
     * @var AbstractNotificationPluginProvider
     */
    private $notification_provider;


    /**
     * @inheritDoc
     */
    public function getModificationProvider() : ?AbstractModificationPluginProvider
    {
        return $this->modification_provider;
    }


    /**
     * @param AbstractModificationPluginProvider $modification_provider
     *
     * @return PluginProviderCollection
     */
    public function setModificationProvider(AbstractModificationPluginProvider $modification_provider) : PluginProviderCollection
    {
        $this->modification_provider = $modification_provider;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : ?AbstractStaticMainMenuPluginProvider
    {
        return $this->main_bar_provider;
    }


    /**
     * @param AbstractStaticMainMenuPluginProvider $static_mai_menu_provider
     *
     * @return PluginProviderCollection
     */
    public function setMainBarProvider(AbstractStaticMainMenuPluginProvider $static_mai_menu_provider) : PluginProviderCollection
    {
        $this->main_bar_provider = $static_mai_menu_provider;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getToolProvider() : ?AbstractDynamicToolPluginProvider
    {
        return $this->tool_provider;
    }


    /**
     * @param AbstractDynamicToolPluginProvider $dynamic_tool_provider
     *
     * @return PluginProviderCollection
     */
    public function setToolProvider(AbstractDynamicToolPluginProvider $dynamic_tool_provider) : PluginProviderCollection
    {
        $this->tool_provider = $dynamic_tool_provider;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarProvider() : ?AbstractStaticMetaBarPluginProvider
    {
        return $this->meta_bar_provider;
    }


    /**
     * @param AbstractStaticMetaBarPluginProvider $static_meta_bar_provider
     *
     * @return PluginProviderCollection
     */
    public function setMetaBarProvider(AbstractStaticMetaBarPluginProvider $static_meta_bar_provider) : PluginProviderCollection
    {
        $this->meta_bar_provider = $static_meta_bar_provider;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getNotificationProvider() : ?AbstractNotificationPluginProvider
    {
        return $this->notification_provider;
    }


    /**
     * @param AbstractNotificationPluginProvider $notification_provider
     *
     * @return PluginProviderCollection
     */
    public function setNotificationProvider(AbstractNotificationPluginProvider $notification_provider) : PluginProviderCollection
    {
        $this->notification_provider = $notification_provider;

        return $this;
    }
}
