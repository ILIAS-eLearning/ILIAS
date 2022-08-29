<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilMailGlobalServices;
use DateTimeImmutable;
use ilDateTime;
use ilDatePresentation;
use Throwable;
use ILIAS\UI\Component\Item\Notification;

/**
 * Class NotificationProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class NotificationProvider extends AbstractNotificationProvider
{
    public const MUTED_UNTIL_PREFERENCE_KEY = 'mail_nc_muted_until';

    public function getNotifications(): array
    {
        $id = function (string $id): IdentificationInterface {
            return $this->if->identifier($id);
        };

        if (0 === $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $hasInternalMailAccess = $this->dic->rbac()->system()->checkAccess(
            'internal_mail',
            ilMailGlobalServices::getMailObjectRefId()
        );
        if (!$hasInternalMailAccess) {
            return [];
        }

        $leftIntervalTimestamp = $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY);
        $newMailData = ilMailGlobalServices::getNewMailsData(
            $this->dic->user(),
            is_numeric($leftIntervalTimestamp) ? (int) $leftIntervalTimestamp : 0
        );

        $numberOfNewMessages = $newMailData['count'];
        if (0 === $numberOfNewMessages) {
            return [];
        }

        $this->dic->language()->loadLanguageModule('mail');

        $factory = $this->globalScreen()->notifications()->factory();

        $mailUrl = 'ilias.php?baseClass=ilMailGUI';

        if (1 === $numberOfNewMessages) {
            $linkText = $this->dic->language()->txt('nc_mail_unread_messages_number_s');
        } else {
            $linkText = sprintf(
                $this->dic->language()->txt('nc_mail_unread_messages_number_p'),
                $numberOfNewMessages
            );
        }

        $body = sprintf(
            $this->dic->language()->txt('nc_mail_unread_messages'),
            $this->dic->ui()->renderer()->render(
                $this->dic->ui()->factory()
                ->link()
                ->standard($linkText, $mailUrl)
            )
        );

        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::MAIL, 'mail');
        $title = $this->dic->ui()->factory()->link()->standard(
            $this->dic->language()->txt('nc_mail_noti_item_title'),
            $mailUrl
        );

        /** @var Notification $notificationItem */
        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($body);

        try {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $newMailData['max_time']);
            $notificationItem = $notificationItem->withProperties([
                $this->dic->language()->txt('nc_mail_prop_time') => ilDatePresentation::formatDate(
                    new ilDateTime($dateTime->getTimestamp(), IL_CAL_UNIX)
                ),
            ]);
        } catch (Throwable) {
        }

        $group = $factory->standardGroup($id('mail_bucket_group'))
            ->withTitle($this->dic->language()->txt('mail'))
            ->addNotification(
                $factory->standard($id('mail_bucket'))
                    ->withNotificationItem($notificationItem)
                    ->withClosedCallable(
                        function (): void {
                            $this->dic->user()->writePref(self::MUTED_UNTIL_PREFERENCE_KEY, (string) time());
                        }
                    )
                    ->withNewAmount(1)
            );

        return [
            $group,
        ];
    }
}
