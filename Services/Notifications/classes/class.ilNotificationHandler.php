<?php

require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';

/**
 * Base class for notification handlers
 *
 * ilNotificationHandler::notify() must be implemented and handle the notification.
 * Optionally the showSettings may be overridden to display a custom settings
 * form in the administration. The settings are stored in the notifications setting
 * module. For an simlple example of introducing custom settings see
 * ilNotificationOSDHandler::showSettings().
 * For a general example of a simple handler see ilNotificationMailHandler
 *
 */
abstract class ilNotificationHandler
{
    abstract public function notify(ilNotificationObject $notification);

    public function showSettings($form)
    {
    }
}
