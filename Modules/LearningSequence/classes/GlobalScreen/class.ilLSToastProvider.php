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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\Notifications\ilNotificationOSDHandler;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;

class ilLSToastProvider extends AbstractToastProvider
{
    private const NOTIFICATION_TYPE = ilLSCompletionNotificationProvider::NOTIFICATION_TYPE;
    private const NOTIFICATION_TIME_PREFERENCE_KEY = ilLSCompletionNotificationProvider::NOTIFICATION_TIME_PREFERENCE_KEY;

    /**
     * @inheritDoc
     */
    public function getToasts(): array
    {
        $toasts = [];
        $current_user = $this->dic['ilUser'];
        if ($current_user->getId() === 0  || $current_user->isAnonymous()) {
            return $toasts;
        }

        $left_interval_timestamp = $current_user->getPref(self::NOTIFICATION_TIME_PREFERENCE_KEY);
        $osd_notification_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));
        $notifications = $osd_notification_handler->getOSDNotificationsForUser(
            $current_user->getId(),
            false,
            time() - $left_interval_timestamp,
            self::NOTIFICATION_TYPE
        );

        $notifications = array_filter(
            $notifications,
            fn ($n) => $n->getTimeAdded() > (new \DateTime())->getTimestamp() - 2
        );

        if (count($notifications) === 0) {
            return $toasts;
        }

        $ui_factory = $this->dic['ui.factory'];
        $lng = $this->dic['lng'];
        $lng->loadLanguageModule('lso');

        $toasts[] = $this->getDefaultToast(
            $lng->txt('lso_toast_completed_title'),
            $ui_factory->symbol()->icon()->standard('lso', 'Learning Sequence completed')->withSize('large')
        )
        ->withDescription($lng->txt('lso_toast_completed_desc'));

        return $toasts;
    }
}
