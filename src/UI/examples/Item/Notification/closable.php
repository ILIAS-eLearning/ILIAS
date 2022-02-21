<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Notification;

function closable()
{
    global $DIC;
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $close_url = $_SERVER['REQUEST_URI'] . '&mail_closed=true';

    //If closed, an ajax request is fired to the set close_url
    if ($request_wrapper->has('mail_closed') && $request_wrapper->retrieve('mail_closed', $refinery->kindlyTo()->bool())) {
        //Do Some Magic needed to be done, when this item is closed.
        exit;
    }

    //Creating a closable Mail Notification Item
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $mail_icon = $f->symbol()->icon()->standard("mail", "mail");
    $mail_title = $f->link()->standard("Inbox", "#");
    $mail_notification_item = $f->item()->notification($mail_title, $mail_icon)
                                ->withDescription("You have 23 unread mails in your inbox")
                                ->withProperties(["Time" => "3 days ago"])
                                ->withCloseAction($close_url);


    return $renderer->render($mail_notification_item);
}
