<?php
require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

class ilNotificationEchoHandler extends ilNotificationHandler {

    public function notify(ilNotificationObject $notification) {
        echo "Notification for Recipient {$notification->user->getId()}: {$notification->title} <br />";
    }

}