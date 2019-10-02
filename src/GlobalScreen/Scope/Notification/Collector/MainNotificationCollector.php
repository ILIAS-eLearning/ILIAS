<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
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
     * @var isItem[]
     */
    private $notifications = [];


    /**
     * MetaBarMainCollector constructor.
     *
     * @param NotificationProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
        $this->load();
    }


    private function returnNotificationsFromProviders() : \Generator
    {
        foreach ($this->providers as $provider) {
            yield $provider->getNotifications();
        }
    }


    private function load() : void
    {
        $this->notifications = array_merge([], ...iterator_to_array($this->returnNotificationsFromProviders()));
    }


    /**
     * @return bool
     */
    public function hasNotifications() : bool
    {
        return (is_array($this->notifications) && count($this->notifications) > 0);
    }


    /**
     * @return int
     */
    public function getAmountOfNotifications() : int
    {
        if (is_array($this->notifications)) {
            $count = 0;
            foreach ($this->notifications as $notification) {
                if ($notification instanceof StandardNotificationGroup) {
                    $count += count($notification->getNotifications());
                } else {
                    $count++;
                }
            }

            return $count;
        }

        return 0;
    }


    /**
     * @return isItem[]
     */
    public function getNotifications() : array
    {
        return $this->notifications;
    }
}
