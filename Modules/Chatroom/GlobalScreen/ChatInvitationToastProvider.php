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

namespace ILIAS\Chatroom\GlobalScreen;

use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\Notifications\ilNotificationOSDHandler;

class ChatInvitationToastProvider extends AbstractToastProvider
{
    public const MUTED_UNTIL_PREFERENCE_KEY = 'chatinv_nc_muted_until';
    public const NOTIFICATION_TYPE = 'chat_invitation';

    public function getToasts(): array
    {
        $toasts = [];

        if (0 === $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return $toasts;
        }

        $osd_notification_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));

        foreach ($osd_notification_handler->getOSDNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            time() - $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY),
            self::NOTIFICATION_TYPE
        ) as $invitation) {
            $toast = $this->toast_factory->standard(
                $invitation->getObject()->title,
                $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
            )->withDescription($invitation->getObject()->shortDescription);
            foreach ($invitation->getObject()->links as $link) {

                $toast = $toast->withAdditionalLink(
                    $this->dic->ui()->factory()->link()->standard(
                        $link->getTitle(),
                        $link->getUrl()
                    )
                );
            }
            $toasts[] = $toast;
        }

        return $toasts;
    }
}
