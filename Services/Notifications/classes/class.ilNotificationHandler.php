<?php

require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';

abstract class ilNotificationHandler {
    abstract public function notify(ilNotificationObject $notification);

    public function showSettings($form) {}
}