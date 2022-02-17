<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class NotificationFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationFactory
{

    /**
     * @param IdentificationInterface $identification
     * @return StandardNotification
     */
    public function standard(IdentificationInterface $identification) : StandardNotification
    {
        return new StandardNotification($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return StandardNotificationGroup
     */
    public function standardGroup(IdentificationInterface $identification) : StandardNotificationGroup
    {
        return new StandardNotificationGroup($identification);
    }
}
