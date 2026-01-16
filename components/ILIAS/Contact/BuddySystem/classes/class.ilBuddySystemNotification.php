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

use ILIAS\Notifications\Identification\NotificationIdentification;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ILIAS\Contact\Provider\ContactNotificationProvider;

class ilBuddySystemNotification
{
    public const string CONTACT_REQUEST_KEY = 'contact_requested_by';

    /** @var list<int> */
    protected array $recipient_ids = [];

    public function __construct(protected ilObjUser $sender, protected ilSetting $settings)
    {
    }

    /**
     * @return list<int>
     */
    public function getRecipientIds(): array
    {
        return $this->recipient_ids;
    }

    /**
     * @param list<int> $recipient_ids
     */
    public function setRecipientIds(array $recipient_ids): void
    {
        $this->recipient_ids = array_map('\intval', $recipient_ids);
    }

    public function send(): void
    {
        foreach ($this->getRecipientIds() as $usr_id) {
            $user = new ilObjUser($usr_id);

            $recipient_language = ilLanguageFactory::_getLanguage($user->getLanguage());
            $recipient_language->loadLanguageModule('buddysystem');

            $notification = new ilNotificationConfig(ContactNotificationProvider::NOTIFICATION_TYPE);

            $approve_url = ilLink::_getLink($this->sender->getId(), 'contact', ['approve']);
            $ignore_url = ilLink::_getLink($this->sender->getId(), 'contact', ['ignore']);

            $profile_url = $recipient_language->txt('buddy_noti_cr_profile_not_published');
            if ($this->hasPublicProfile($this->sender->getId())) {
                $profile_url = ilLink::_getStaticLink($this->sender->getId(), 'usr', true);

                $links[] = new ilNotificationLink(
                    new ilNotificationParameter(
                        $this->sender->getFirstname() . ', ' .
                        $this->sender->getLastname() . ' ' .
                        $this->sender->getLogin()
                    ),
                    $profile_url
                );
            }
            $links[] = new ilNotificationLink(
                new ilNotificationParameter('buddy_notification_contact_request_link_osd', [], 'buddysystem'),
                $approve_url
            );
            $links[] = new ilNotificationLink(
                new ilNotificationParameter('buddy_notification_contact_request_ignore_osd', [], 'buddysystem'),
                $ignore_url
            );

            $body_params = [
                'SALUTATION' => ilMail::getSalutation($user->getId(), $recipient_language),
                'BR' => "\n",
                'APPROVE_REQUEST' => $approve_url,
                'APPROVE_REQUEST_TXT' => $recipient_language->txt('buddy_notification_contact_request_link'),
                'IGNORE_REQUEST' => $ignore_url,
                'IGNORE_REQUEST_TXT' => $recipient_language->txt('buddy_notification_contact_request_ignore'),
                'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId()),
                'PERSONAL_PROFILE_LINK' => $profile_url,
            ];
            $notification->setTitleVar('buddy_notification_contact_request', [], 'buddysystem');
            $notification->setShortDescriptionVar('buddy_notification_contact_request_short', $body_params, 'buddysystem');
            $notification->setLongDescriptionVar('buddy_notification_contact_request_long', $body_params, 'buddysystem');
            $notification->setLinks($links);
            $notification->setValidForSeconds(ilNotificationConfig::TTL_LONG);
            $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
            $notification->setIconPath('assets/images/standard/icon_usr.svg');
            $notification->setHandlerParam('mail.sender', (string) ANONYMOUS_USER_ID);
            $notification->setIdentification(new NotificationIdentification(
                ContactNotificationProvider::NOTIFICATION_TYPE,
                self::CONTACT_REQUEST_KEY . '_' . $this->sender->getId(),
            ));

            $notification->notifyByUsers([$user->getId()]);
        }
    }

    protected function hasPublicProfile(int $recipientUsrId): bool
    {
        $portfolio_id = ilObjPortfolio::getDefaultPortfolio($this->sender->getId());
        if (is_numeric($portfolio_id) && $portfolio_id > 0) {
            return (new ilPortfolioAccessHandler())->checkAccessOfUser($recipientUsrId, 'read', '', $portfolio_id);
        }

        return (
            $this->sender->getPref('public_profile') === 'y' ||
            $this->sender->getPref('public_profile') === 'g'
        );
    }
}
