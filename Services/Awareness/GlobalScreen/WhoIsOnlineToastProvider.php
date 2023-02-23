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

namespace ILIAS\Awareness\GlobalScreen;

use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\Notifications\ilNotificationOSDHandler;

class WhoIsOnlineToastProvider extends AbstractToastProvider
{
    public const NOTIFICATION_TYPE = 'who_is_online';

    public function getToasts(): array
    {
        $toasts = [];

        if (0 === $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return $toasts;
        }

        $osd_notification_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));

        $toast = null;
        foreach ($osd_notification_handler->getOSDNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            0,
            self::NOTIFICATION_TYPE
        ) as $invitation) {
            if ($toast === null && count($invitation->getObject()->links) > 0) {
                $toast = $this->getDefaultToast(
                    $invitation->getObject()->title,
                    $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::AWRA, 'who_is_online')
                );
            }
            foreach ($invitation->getObject()->links as $link) {
                $toast = $toast->withAdditionalLink($this->dic->ui()->factory()->link()->standard(
                    $link->getTitle(),
                    $link->getUrl()
                ));
            }
        }
        if ($toast !== null) {
            $toasts[] = $toast;
        }

        return $toasts;
    }
}
