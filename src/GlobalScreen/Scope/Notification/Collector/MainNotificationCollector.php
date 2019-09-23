<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MainNotificationCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainNotificationCollector
{

    /**
     * @var NotificationProvider[]
     */
    private $providers = [];


    /**
     * MetaBarMainCollector constructor.
     *
     * @param NotificationProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }


    /**
     * @return isItem[]
     */
    public function getNotifications() : array
    {
        $notifications = [];

        foreach ($this->providers as $provider) {
            $notifications = array_merge($notifications, $provider->getNotifications());
        }

        return $notifications;
    }
}
