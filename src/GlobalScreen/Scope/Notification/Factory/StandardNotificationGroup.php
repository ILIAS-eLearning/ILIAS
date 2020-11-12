<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Class StandardNotificationGroup
 *
 * Groups a set of Notification.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroup extends AbstractBaseNotification implements isStandardItem
{

    /**
     * @var StandardNotification[]
     */
    private $notifications = [];

    /**
     * @var string
     */
    protected $title = "";


    /**
     * @param string $title
     * @return StandardNotificationGroup
     */
    public function withTitle(string $title) : StandardNotificationGroup
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @param StandardNotification $notification
     *
     * @return StandardNotificationGroup
     */
    public function addNotification(StandardNotification $notification) : StandardNotificationGroup
    {
        $this->notifications[] = $notification;

        return $this;
    }


    /**
     * @return StandardNotification[]
     */
    public function getNotifications() : array
    {
        return $this->notifications;
    }

    /**
     * @return int
     */
    public function getNotificationsCount() : int
    {
        return count($this->notifications);
    }

    /**
     * @return int
     */
    public function getOldNotificationsCount() : int
    {
        $count = 0;
        foreach ($this->notifications as $notification) {
            $count += $notification->getOldAmount();
        }
        return $count;
    }

    /**
     * @return int
     */
    public function getNewNotificationsCount() : int
    {
        $count = 0;
        foreach ($this->notifications as $notification) {
            $count += $notification->getNewAmount();
        }
        return $count;
    }

    /**
     * @inheritDoc
     */
    public function getRenderer(UIFactory $factory) : NotificationRenderer
    {
        return new StandardNotificationGroupRenderer($factory);
    }
}
