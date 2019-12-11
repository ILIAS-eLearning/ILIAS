<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Badge\GlobalScreen;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MailNotificationProvider
 */
class BadgeNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $lng = $this->dic->language();
        $ui = $this->dic->ui();
        $user = $this->dic->user();
        $ctrl = $this->dic->ctrl();

        $lng->loadLanguageModule("badge");

        $factory = $this->globalScreen()->notifications()->factory();
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $new_badges = \ilBadgeAssignment::getNewCounter($user->getId());

        if ($new_badges == 0) {
            return [];
        }

        //Creating a mail Notification Item
        $mail_icon = $ui->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/badge.svg"), $lng->txt("badge_badge"));
        $mail_title = $ui->factory()->link()->standard($lng->txt("mm_badges"),
            $ctrl->getLinkTargetByClass(["ilDashboardGUI"], "jumpToBadges"));
        $badge_notification_item = $ui->factory()->item()->notification($mail_title,$mail_icon)
            ->withDescription(str_replace("%1", $new_badges, $lng->txt("badge_new_badges")));
            //->withProperties(["Time" => "3 days ago"]);

        $group = $factory->standardGroup($id('badge_bucket_group'))->withTitle($lng->txt('badge_badge'))
            ->addNotification($factory->standard($id('badge_bucket'))->withNotificationItem($badge_notification_item)
                ->withClosedCallable(
                    function() use ($user) {
                        // Stuff we do, when the notification is closed
                        $noti_repo = new \ILIAS\Badge\Notification\BadgeNotificationPrefRepository($user);
                        $noti_repo->updateLastCheckedTimestamp();
                    })
                ->withNewAmount($new_badges)
            )
            ->withOpenedCallable(function(){
                // Stuff we do, when the notification is opened
            });

        return [
            $group,
        ];
    }
}
