<?php namespace ILIAS\BackgroundTasks\Provider;

use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class BTNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BTNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{

    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        return [];
    }
}
