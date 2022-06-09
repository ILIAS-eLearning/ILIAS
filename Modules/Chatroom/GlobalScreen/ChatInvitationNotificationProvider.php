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

namespace ILIAS\Chatroom\GlobalScreen;

use ilDatePresentation;
use ilDateTime;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\Notifications\ilNotificationOSDHandler;

class ChatInvitationNotificationProvider extends AbstractNotificationProvider
{

    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        if (0 === $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $invitations = [];
        $latest_time = 0;
        $repository = new ilNotificationOSDRepository($this->dic->database());
        foreach ((new ilNotificationOSDHandler($repository))->getNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            0,
            'chat_invitation'
        ) as $osd) {
            $invitations[] = $osd;
            if ($latest_time < $osd->getTimeAdded()) {
                $latest_time = $osd->getTimeAdded();
            }
        }

        $this->dic->language()->loadLanguageModule('chatroom');
        $notificationItem = $this->dic->ui()->factory()->item()->notification(
            $this->dic->language()->txt('chat_invitations'),
            $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
        )
        ->withDescription($this->dic->language()->txt('chat_invitation_nc_no_inv'));

        if (count($invitations) !== 0) {
            $aggregatedItems = [];
            foreach ($invitations as $invitation) {
                $link = '';
                if (count($invitation->getObject()->links) === 1) {
                    $link = $this->dic->ui()->renderer()->render(
                        $this->dic->ui()->factory()->link()->standard(
                            $invitation->getObject()->shortDescription,
                            $invitation->getObject()->links[0]->getUrl()
                        )
                    );
                }
                $aggregatedItems[] = $this->dic->ui()->factory()->item()->notification(
                    $invitation->getObject()->title,
                    $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
                )
                ->withDescription($link)
                ->withProperties([
                    $this->dic->language()->txt('time') => ilDatePresentation::formatDate(
                        new ilDateTime($invitation->getTimeAdded(), IL_CAL_UNIX)
                    )
                ]);
            }

            $notificationItem = $this->dic->ui()->factory()->item()->notification(
                $this->dic->ui()->factory()->link()->standard($this->dic->language()->txt('chat_invitations'), '#'),
                $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
            )
                ->withAggregateNotifications($aggregatedItems)
                ->withDescription(sprintf($this->dic->language()->txt('chat_invitation_nc_inv_x'), count($aggregatedItems)))
                ->withProperties([
                    $this->dic->language()->txt('time') => ilDatePresentation::formatDate(
                        new ilDateTime($latest_time, IL_CAL_UNIX)
                    )
                ]);
        } else {
            return [];
        }

        return [
            $this->globalScreen()->notifications()->factory()->standardGroup($this->if->identifier('chat_invitation_bucket_group'))
                 ->withTitle('Chat')
                 ->addNotification(
                     $this->globalScreen()->notifications()->factory()->standard($this->if->identifier('chat_invitation_bucket'))
                          ->withNotificationItem($notificationItem)
                          ->withNewAmount(count($invitations))
                 )
        ];
    }
}
