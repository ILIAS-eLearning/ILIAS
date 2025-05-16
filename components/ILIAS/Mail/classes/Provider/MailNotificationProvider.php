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

class MailNotificationProvider extends AbstractNotificationProvider
{
    final public const string MUTED_UNTIL_PREFERENCE_KEY = 'mail_nc_muted_until';

    public function getNotifications(): array
    {
        $id = fn(string $id): IdentificationInterface => $this->if->identifier($id);

        if ($this->dic->user()->getId() === 0 || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $has_internal_mail_access = $this->dic->rbac()->system()->checkAccess(
            'internal_mail',
            ilMailGlobalServices::getMailObjectRefId()
        );
        if (!$has_internal_mail_access) {
            return [];
        }

        $left_interval_timestamp = $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY);
        $new_mail_data = ilMailGlobalServices::getNewMailsData(
            $this->dic->user(),
            is_numeric($left_interval_timestamp) ? (int) $left_interval_timestamp : 0
        );

        $number_of_new_messages = $new_mail_data['count'];
        if ($number_of_new_messages === 0) {
            return [];
        }

        $this->dic->language()->loadLanguageModule('mail');

        $factory = $this->globalScreen()->notifications()->factory();

        $mail_url = 'ilias.php?baseClass=' . \ilMailGUI::class;

        if ($number_of_new_messages === 1) {
            $link_text = $this->dic->language()->txt('nc_mail_unread_messages_number_s');
        } else {
            $link_text = \sprintf(
                $this->dic->language()->txt('nc_mail_unread_messages_number_p'),
                $number_of_new_messages
            );
        }

        $body = \sprintf(
            $this->dic->language()->txt('nc_mail_unread_messages'),
            $this->dic->ui()->renderer()->render(
                $this->dic->ui()->factory()
                ->link()
                ->standard($link_text, $mail_url)
            )
        );

        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::MAIL, 'mail');
        $title = $this->dic->ui()->factory()->link()->standard(
            $this->dic->language()->txt('nc_mail_noti_item_title'),
            $mail_url
        );

        /** @var Notification $notification_item */
        $notification_item = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($body);

        try {
            $date_time = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $new_mail_data['max_time']);
            $notification_item = $notification_item->withProperties([
                $this->dic->language()->txt('nc_mail_prop_time') => ilDatePresentation::formatDate(
                    new ilDateTime($date_time->getTimestamp(), IL_CAL_UNIX)
                ),
            ]);
        } catch (Throwable) {
        }

        $group = $factory->standardGroup($id('mail_bucket_group'))
            ->withTitle($this->dic->language()->txt('mail'))
            ->addNotification(
                $factory->standard($id('mail_bucket'))
                    ->withNotificationItem($notification_item)
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
