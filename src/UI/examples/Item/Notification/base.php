<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Notification;

/**
 * ---
 * description: >
 *   Example for rendering a notification item.
 *
 * expected output: >
 *   ILIAS shows a box titled "Inbox" on a white background and including following text: "You have 23 unread mails in
 *   your inbox". A dashed line and a time indication (Time 3 days ago) are displayed below.
 * ---
 */
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
