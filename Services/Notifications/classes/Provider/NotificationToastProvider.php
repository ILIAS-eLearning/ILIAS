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

namespace ILIAS\Notifications\Provider;

use ILIAS\Badge\GlobalScreen\BadgeNotificationProvider;
use ILIAS\Chatroom\GlobalScreen\ChatInvitationNotificationProvider;
use ILIAS\Contact\Provider\ContactNotificationProvider;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\Notifications\ilNotificationOSDHandler;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Standard;
use ILIAS\UI\Implementation\Component\Toast\Toast;
use ilSetting;
use function Sabre\Xml\Deserializer\functionCaller;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class NotificationToastProvider extends AbstractToastProvider
{
    /**
     * @inheritDoc
     */
    public function getToasts(): array
    {
        $settings = new ilSetting('notifications');
        $toasts = [];

        if (
            $settings->get('enable_osd', '0') !== '1' ||
            0 === $this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous()
        ) {
            return $toasts;
        }

        $osd_repository = new ilNotificationOSDRepository($this->dic->database());
        $osd_notification_handler = new ilNotificationOSDHandler($osd_repository);

        foreach ($osd_notification_handler->getOSDNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            time() - ($this->dic->http()->request()->getQueryParams()['max_age'] ?? time())
        ) as $notification) {
            $type = $notification->getType();
            $toast = $this->toast_factory
                ->standard(
                    $this->if->identifier((string) $notification->getId()),
                    $notification->getObject()->title
                )
                ->withIcon($this->getIconByType($notification->getType()))
                ->withDescription($notification->getObject()->shortDescription)
                ->withVanishTime((int) $settings->get('osd_vanish', (string) Toast::DEFAULT_VANISH_TIME))
                ->withDelayTime((int) $settings->get('osd_delay', (string) Toast::DEFAULT_DELAY_TIME))
                ->withClosedCallable(static function () use ($osd_repository, $notification) {
                    $osd_repository->deleteOSDNotificationById($notification->getId());
                });

            foreach ($notification->getObject()->links as $id => $link) {
                $toast = $toast->withAdditionToastAction(
                    $this->toast_factory->action(
                        $notification->getId() . '_link_' . $id,
                        $link->getTitle(),
                        function () use ($link, $osd_repository, $notification): void {
                            $osd_repository->deleteOSDNotificationById($notification->getId());
                            $this->dic->ctrl()->redirectToURL($link->getUrl());
                        }
                    )
                );
            }
            $toasts[] = $toast;
        }

        return $toasts;
    }

    protected function getIconByType(string $type): Icon
    {
        $name = 'default';
        switch ($type) {
            case BadgeNotificationProvider::NOTIFICATION_TYPE:
                $name = Standard::BDGA;
                break;
            case ChatInvitationNotificationProvider::NOTIFICATION_TYPE:
                $name = Standard::CHTA;
                break;
            case ContactNotificationProvider::NOTIFICATION_TYPE:
                $name = Standard::CADM;
                break;
        }
        return $this->dic->ui()->factory()->symbol()->icon()->standard($name, $type);
    }
}
