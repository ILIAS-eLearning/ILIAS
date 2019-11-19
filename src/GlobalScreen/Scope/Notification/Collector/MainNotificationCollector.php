<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector;

use ILIAS\GlobalScreen\Collector\Collector;
use ILIAS\GlobalScreen\Collector\LogicException;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MainNotificationCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainNotificationCollector implements Collector
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


    private function returnNotificationsFromProviders() : \Generator
    {
        foreach ($this->providers as $provider) {
            yield $provider->getNotifications();
        }
    }


    public function collect() : void
    {
        $this->notifications = array_merge([], ...iterator_to_array($this->returnNotificationsFromProviders()));
    }


    public function collectStructure() : void
    {
        // TODO: Implement collectStructure() method.
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
}
