<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class DefaultProviderCollection
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderCollection
{

    /**
     * @return ModificationProvider
     */
    public function getModificationProvider() : ?ModificationProvider;


    /**
     * @return StaticMainMenuProvider
     */
    public function getStaticMainMenuProvider() : ?StaticMainMenuProvider;


    /**
     * @return DynamicToolProvider
     */
    public function getDynamicToolProvider() : ?DynamicToolProvider;


    /**
     * @return StaticMetaBarProvider
     */
    public function getStaticMetaBarProvider() : ?StaticMetaBarProvider;


    /**
     * @return NotificationProvider
     */
    public function getNotificationProvider() : ?NotificationProvider;
}