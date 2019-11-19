<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Client\Notifications;

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

    /**
     * Generator yielding the Notifications from the set of providers
     *
     * @return \Generator
     */
    private function returnNotificationsFromProviders() : \Generator
    {
        foreach ($this->providers as $provider) {
            yield $provider->getNotifications();
        }
    }

    /**
     * Stores the collected notifications into an array
     */
    private function load() : void
    {
        $this->notifications = array_merge([],...iterator_to_array($this->returnNotificationsFromProviders()));
    }


    /**
     * Returns wheter there are any notifications at all.
     *
     * @return bool
     */
    public function hasNotifications() : bool
    {
        return (is_array($this->notifications) && count($this->notifications) > 0);
    }


    /**
     * Returns the sum of all old notifications values in the
     * Standard Notifications
     *
     * @return int
     */
    public function getAmountOfOldNotifications() : int
    {
        if (is_array($this->notifications)) {
            $count = 0;
            foreach ($this->notifications as $notification) {
                if($notification instanceof StandardNotificationGroup){
                    foreach ($notification->getNotifications()as $notification) {
                        $count += $notification->getOldAmount();
                    }
                }else{
                    $count += $notification->getOldAmount();
                }

            }
            return $count;
        }

        return 0;
    }

    /**
     * Returns the sum of all new notifications values in the
     * Standard Notifications
     *
     * @return int
     */
    public function getAmountOfNewNotifications() : int
    {
        if (is_array($this->notifications)) {
            $count = 0;
            foreach ($this->notifications as $notification) {
                if($notification instanceof StandardNotificationGroup){
                    foreach ($notification->getNotifications()as $notification) {
                        $count += $notification->getNewAmount();
                    }
                }else{
                    $count += $notification->getNewAmount();
                }
            }

            return $count;
        }

        return 0;
    }

    /**
     * Returns the set of collected informations
     *
     * @return isItem[]
     */
    public function getNotifications() : array
    {

        return $this->notifications;
    }

    public function getNotificationsIdentifiersAsArray(){
        $identifiers = [];
        foreach ($this->notifications as $notification) {
            if($notification instanceof StandardNotificationGroup){
                foreach ($notification->getNotifications() as $item) {
                    $identifiers[] = $item->getProviderIdentification()->getInternalIdentifier();
                }
            }
            $identifiers[] = $notification->getProviderIdentification()->getInternalIdentifier();
        }
        return $identifiers;
    }
}
