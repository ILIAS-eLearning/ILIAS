<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

class DefaultProviderCollection implements ProviderCollection
{

    /**
     * @var ModificationProvider
     */
    private $modification_provider;
    /**
     * @var StaticMainMenuProvider
     */
    private $static_mai_menu_provider;
    /**
     * @var DynamicToolProvider
     */
    private $dynamic_tool_provider;
    /**
     * @var StaticMetaBarProvider
     */
    private $static_meta_bar_provider;
    /**
     * @var NotificationProvider
     */
    private $notification_provider;


    public function getModificationProvider() : ?ModificationProvider
    {
        return $this->modification_provider;
    }


    /**
     * @param ModificationProvider $modification_provider
     *
     * @return DefaultProviderCollection
     */
    public function setModificationProvider(ModificationProvider $modification_provider) : DefaultProviderCollection
    {
        $this->modification_provider = $modification_provider;

        return $this;
    }


    public function getStaticMainMenuProvider() : ?StaticMainMenuProvider
    {
        return $this->static_mai_menu_provider;
    }


    /**
     * @param StaticMainMenuProvider $static_mai_menu_provider
     *
     * @return DefaultProviderCollection
     */
    public function setStaticMainMenuProvider(StaticMainMenuProvider $static_mai_menu_provider) : DefaultProviderCollection
    {
        $this->static_mai_menu_provider = $static_mai_menu_provider;

        return $this;
    }


    public function getDynamicToolProvider() : ?DynamicToolProvider
    {
        return $this->dynamic_tool_provider;
    }


    /**
     * @param DynamicToolProvider $dynamic_tool_provider
     *
     * @return DefaultProviderCollection
     */
    public function setDynamicToolProvider(DynamicToolProvider $dynamic_tool_provider) : DefaultProviderCollection
    {
        $this->dynamic_tool_provider = $dynamic_tool_provider;

        return $this;
    }


    public function getStaticMetaBarProvider() : ?StaticMetaBarProvider
    {
        return $this->static_meta_bar_provider;
    }


    /**
     * @param StaticMetaBarProvider $static_meta_bar_provider
     *
     * @return DefaultProviderCollection
     */
    public function setStaticMetaBarProvider(StaticMetaBarProvider $static_meta_bar_provider) : DefaultProviderCollection
    {
        $this->static_meta_bar_provider = $static_meta_bar_provider;

        return $this;
    }


    public function getNotificationProvider() : ?NotificationProvider
    {
        return $this->notification_provider;
    }


    /**
     * @param NotificationProvider $notification_provider
     *
     * @return DefaultProviderCollection
     */
    public function setNotificationProvider(NotificationProvider $notification_provider) : DefaultProviderCollection
    {
        $this->notification_provider = $notification_provider;

        return $this;
    }
}
