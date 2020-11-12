<?php namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;

/**
 * Interface NotificationProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface NotificationProvider extends Provider
{

    /**
     * @return isItem[]
     */
    public function getNotifications() : array;

    /**
     * @return AdministrativeNotification[]
     */
    public function getAdministrativeNotifications() : array;
}
