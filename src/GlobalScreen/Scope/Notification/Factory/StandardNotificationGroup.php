<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;

/**
 * Class StandardNotificationGroup
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroup extends AbstractTitleNotification implements hasTitle, isItem
{

    /**
     * @var StandardNotification[]
     */
    private $notifications = [];


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
     * @inheritDoc
     */
    public function getRenderer() : NotificationRenderer
    {
        return new StandardNotificationGroupRenderer();
    }
}
