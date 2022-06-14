<?php declare(strict_types=1);

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

namespace ILIAS\Badge\GlobalScreen;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

class BadgeNotificationProvider extends AbstractNotificationProvider
{
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
        if ($new_badges === 0) {
            return [];
        }

        //Creating a badge Notification Item
        $badge_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("bdga", $lng->txt("badge_badge"));
        $badge_title = $ui->factory()->link()->standard(
            $lng->txt("mm_badges"),
            $ctrl->getLinkTargetByClass(["ilDashboardGUI"], "jumpToBadges")
        );
        $latest = new \ilDateTime(\ilBadgeAssignment::getLatestTimestamp($user->getId()), IL_CAL_UNIX);
        /** @var \ILIAS\UI\Component\Item\Notification $badge_notification_item */
        $badge_notification_item = $ui->factory()->item()->notification($badge_title, $badge_icon)
            ->withDescription(str_replace("%1", (string) $new_badges, $lng->txt("badge_new_badges")))
            ->withProperties([$lng->txt("time") => \ilDatePresentation::formatDate($latest)]);

        $group = $factory->standardGroup($id('badge_bucket_group'))->withTitle($lng->txt('badge_badge'))
            ->addNotification(
                $factory->standard($id('badge_bucket'))->withNotificationItem($badge_notification_item)
                ->withClosedCallable(
                    function () use ($user) {
                        // Stuff we do, when the notification is closed
                        $noti_repo = new \ILIAS\Badge\Notification\BadgeNotificationPrefRepository($user);
                        $noti_repo->updateLastCheckedTimestamp();
                    }
                )
                ->withNewAmount($new_badges)
            )
            ->withOpenedCallable(static function () {
                // Stuff we do, when the notification is opened
            });

        return [
            $group,
        ];
    }
}
