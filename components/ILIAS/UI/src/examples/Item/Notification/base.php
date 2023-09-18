<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Notification;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Creating a Mail Notification Item
    $mail_icon = $f->symbol()->icon()->standard("mail", "mail");
    $mail_title = $f->link()->standard("Inbox", "#");
    $mail_notification_item = $f->item()->notification($mail_title, $mail_icon)
                                ->withDescription("You have 23 unread mails in your inbox")
                                ->withProperties(["Time" => "3 days ago"]);


    return $renderer->render($mail_notification_item);
}
