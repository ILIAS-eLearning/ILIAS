<?php
require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

class ilNotificationMailHandler extends ilNotificationHandler {
    public function notify(ilNotificationObject $notification) {
	$sender_id = (isset($notification->handlerParams['mail']['sender']) ? $notification->handlerParams['mail']['sender'] : ANONYMOUS_USER_ID);

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

