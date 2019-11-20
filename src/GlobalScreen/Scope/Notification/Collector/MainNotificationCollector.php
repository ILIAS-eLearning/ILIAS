<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector;

use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Collector\ItemCollector;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MainNotificationCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainNotificationCollector extends AbstractBaseCollector implements ItemCollector
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


    public function collectStructure() : void
    {
        $this->notifications = array_merge([], ...iterator_to_array($this->returnNotificationsFromProviders()));
    }


    public function filterItemsByVisibilty(bool $skip_async = false) : void
    {
        // TODO: Implement filterItemsByVisibilty() method.
    }


    public function prepareItemsForUIRepresentation() : void
    {
        // TODO: Implement prepareItemsForUIRepresentation() method.
    }


    /**
     * @inheritDoc
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        yield from $this->notifications;
    }


    /**
     * @inheritDoc
     */
    public function hasItems() : bool
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
                if ($notification instanceof StandardNotificationGroup) {
                    foreach ($notification->getNotifications() as $notification) {
                        $count += $notification->getOldAmount();
                    }
                } else {
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
                if ($notification instanceof StandardNotificationGroup) {
                    foreach ($notification->getNotifications() as $notification) {
                        $count += $notification->getNewAmount();
                    }
                } else {
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


    public function getNotificationsIdentifiersAsArray()
    {
        $identifiers = [];
        foreach ($this->notifications as $notification) {
            if ($notification instanceof StandardNotificationGroup) {
                foreach ($notification->getNotifications() as $item) {
                    $identifiers[] = $item->getProviderIdentification()->getInternalIdentifier();
                }
            }
            $identifiers[] = $notification->getProviderIdentification()->getInternalIdentifier();
        }

        return $identifiers;
    }
}
