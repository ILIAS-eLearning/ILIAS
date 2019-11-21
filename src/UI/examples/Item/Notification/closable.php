<?php
function closable()
{
    $close_url              = $_SERVER['REQUEST_URI'] . '&mail_closed=true';

    //If closed, an ajax request is fired to the set close_url
    if ($_GET['mail_closed']) {
        //Do Some Magic needed to be done, when this item is closed.
        exit;
    }

    //Creating a closable Mail Notification Item
    global $DIC;
    $f        = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $mail_icon              = $f->symbol()->icon()->standard("mail", "mail");
    $mail_title             = $f->link()->standard("Inbox", "link_to_inbox");
    $mail_notification_item = $f->item()->notification($mail_title, $mail_icon)
                                ->withDescription("You have 23 unread mails in your inbox")
                                ->withProperties(["Time" => "3 days ago"])
                                ->withCloseAction($close_url);


    return $renderer->render($mail_notification_item);


}