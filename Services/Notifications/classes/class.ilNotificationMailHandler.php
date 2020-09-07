<?php
require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

/**
 * Notification handler for sending notifications the to recipients email address
 */
class ilNotificationMailHandler extends ilNotificationHandler
{
    public function notify(ilNotificationObject $notification)
    {
        // use a specific sender or ANONYMOUS
        $sender_id = (isset($notification->handlerParams['mail']['sender']) ? $notification->handlerParams['mail']['sender'] : ANONYMOUS_USER_ID);
        include_once 'Services/Mail/classes/class.ilMail.php';
        $mail = new ilMail($sender_id);
        $mail->appendInstallationSignature(true);
        $mail->sendMail(
            $notification->user->getLogin(),
            '',
            '',
            $notification->title,
            $notification->longDescription,
            false,
            array('normal')
        );

        //mail($notification->user->getEmail(), $notification->title, $notification->longDescription);
    }
}
