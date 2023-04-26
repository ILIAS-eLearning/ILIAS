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

use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\Notifications\ilNotificationOSDHandler;

class ilLSCompletionNotificationProvider extends AbstractNotificationProvider
{
    public const NOTIFICATION_TYPE = 'lso_completion';
    public const NOTIFICATION_TIME_PREFERENCE_KEY = 'lso_completion_note_ts';

    public function getNotifications(): array
    {
        $current_user = $this->dic['ilUser'];

        if ($current_user->getId() === 0  || $current_user->isAnonymous()) {
            return [];
        }

        $osd_notification_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));
        $left_interval_timestamp = $current_user->getPref(self::NOTIFICATION_TIME_PREFERENCE_KEY);
        $notifications = $osd_notification_handler->getOSDNotificationsForUser(
            $current_user->getId(),
            false,
            time() - $left_interval_timestamp,
            self::NOTIFICATION_TYPE
        );

        if (count($notifications) === 0) {
            return [];
        }

        $icon = $this->dic['ui.factory']->symbol()->icon()->standard(Standard::LSO, 'lso_completion');
        $item_factory = $this->dic['ui.factory']->item();
        $lng = $this->dic['lng'];
        $lng->loadLanguageModule('lso');


        $group =  $this->globalScreen()->notifications()->factory()->standardGroup(
            $this->if->identifier('lso_bucket_group')
        )
        ->withTitle($lng->txt('obj_lso'));

        foreach ($notifications as $notification) {
            $lso_title = $notification->getObject()->title;
            $title = sprintf($lng->txt('notification_lso_completed_title'), $lso_title);
            $notification_item = $item_factory->notification($title, $icon);

            $group->addNotification(
                $this->globalScreen()->notifications()->factory()->standard(
                    $this->if->identifier('lso_bucket')
                )
                ->withNotificationItem($notification_item)
                ->withNewAmount(count($notifications))
                ->withClosedCallable(
                    function () use ($current_user, $osd_notification_handler): void {
                        $current_user->writePref(self::NOTIFICATION_TIME_PREFERENCE_KEY, (string) time());
                        $osd_notification_handler->deleteStaleNotificationsForUserAndType(
                            $current_user->getId(),
                            self::NOTIFICATION_TYPE
                        );
                    }
                )
            );
        }

        return [$group];
    }
}
