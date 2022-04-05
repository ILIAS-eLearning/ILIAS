<?php declare(strict_types=1);

namespace ILIAS\Chatroom\GlobalScreen;

use ilDatePresentation;
use ilDateTime;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilNotificationOSDHandler;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
        foreach (ilNotificationOSDHandler::getNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            0,
            'chat_invitation'
        ) as $osd) {
            $invitations[] = $osd;
            if ($latest_time < (int) $osd['time_added']) {
                $latest_time = (int) $osd['time_added'];
            }
        }

        $this->dic->language()->loadLanguageModule('chatroom');
        $notificationItem = $this->dic->ui()->factory()->item()->notification(
            $this->dic->language()->txt('chat_invitations'),
            $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
                ->withIsOutlined(true)
        )
        ->withDescription($this->dic->language()->txt('chat_invitation_nc_no_inv'));

        if (count($invitations) !== 0) {
            $aggregatedItems = [];
            foreach ($invitations as $invitation) {
                $link = '';
                if (count($invitation['data']->links) === 1) {
                    $link = $this->dic->ui()->renderer()->render(
                        $this->dic->ui()->factory()->link()->standard(
                            $invitation['data']->shortDescription,
                            $invitation['data']->links[0]->getUrl()
                        )
                    );
                }
                $aggregatedItems[] = $this->dic->ui()->factory()->item()->notification(
                    $invitation['data']->title,
                    $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
                              ->withIsOutlined(true)
                )
                ->withDescription($link)
                ->withProperties([
                    $this->dic->language()->txt('time') => ilDatePresentation::formatDate(
                        new ilDateTime($invitation['time_added'], IL_CAL_UNIX)
                    )
                ]);
            }

            $notificationItem = $this->dic->ui()->factory()->item()->notification(
                $this->dic->ui()->factory()->link()->standard($this->dic->language()->txt('chat_invitations'), '#'),
                $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CHTA, 'chat_invitations')
                          ->withIsOutlined(true)
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
