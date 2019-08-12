<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemNotification
{
    /** @var ilObjUser */
    protected $sender;

    /** @var ilSetting */
    protected $settings;

    /** @var array */
    protected $recipientIds = [];

    /**
     * @param ilObjUser $user
     * @param ilSetting $settings
     */
    public function __construct(ilObjUser $user, ilSetting $settings)
    {
        $this->sender = $user;
        $this->settings = $settings;
    }

    /**
     * @return int[]
     */
    public function getRecipientIds() : array
    {
        return $this->recipientIds;
    }

    /**
     * @param int[] $recipientIds
     */
    public function setRecipientIds(array $recipientIds)
    {
        $this->recipientIds = $recipientIds;
    }

    /**
     *
     */
    public function send() : void
    {
        foreach ($this->getRecipientIds() as $usr_id) {
            $user = new ilObjUser((int) $usr_id);

            $recipientLanguage = ilLanguageFactory::_getLanguage($user->getLanguage());
            $recipientLanguage->loadLanguageModule('buddysystem');

            $notification = new ilNotificationConfig('buddysystem_request');
            $notification->setTitleVar('buddy_notification_contact_request', [], 'buddysystem');

            $personalProfileLink = $recipientLanguage->txt('buddy_noti_cr_profile_not_published');
            if ($this->hasPublicProfile((int) $user->getId())) {
                $personalProfileLink = ilLink::_getStaticLink($this->sender->getId(), 'usr', true);
            }

            $bodyParams = [
                'SALUTATION' => ilMail::getSalutation($user->getId(), $recipientLanguage),
                'BR' => nl2br("\n"),
                'APPROVE_REQUEST' => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true,
                        '_contact_approved') . '">' . $recipientLanguage->txt('buddy_notification_contact_request_link_osd') . '</a>',
                'IGNORE_REQUEST' => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true,
                        '_contact_ignored') . '">' . $recipientLanguage->txt('buddy_notification_contact_request_ignore_osd') . '</a>',
                'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId())
            ];
            $notification->setShortDescriptionVar('buddy_notification_contact_request_short', $bodyParams,
                'buddysystem');

            $bodyParams = [
                'SALUTATION' => ilMail::getSalutation($user->getId(), $recipientLanguage),
                'BR' => "\n",
                'APPROVE_REQUEST' => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_approved'),
                'APPROVE_REQUEST_TXT' => $recipientLanguage->txt('buddy_notification_contact_request_link'),
                'IGNORE_REQUEST' => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_ignored'),
                'IGNORE_REQUEST_TXT' => $recipientLanguage->txt('buddy_notification_contact_request_ignore'),
                'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId()),
                'PERSONAL_PROFILE_LINK' => $personalProfileLink,
            ];
            $notification->setLongDescriptionVar('buddy_notification_contact_request_long', $bodyParams, 'buddysystem');

            $notification->setAutoDisable(false);
            $notification->setValidForSeconds(ilNotificationConfig::TTL_LONG);
            $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
            $notification->setIconPath('templates/default/images/icon_usr.svg');
            $notification->setHandlerParam('mail.sender', ANONYMOUS_USER_ID);
            $notification->notifyByUsers([$user->getId()]);
        }
    }

    /**
     * @param int $recipientUsrId
     * @return bool
     */
    protected function hasPublicProfile(int $recipientUsrId) : bool
    {
        $portfolioId = ilObjPortfolio::getDefaultPortfolio($this->sender->getId());
        if (is_numeric($portfolioId) && $portfolioId > 0) {
            $accessHandler = new ilPortfolioAccessHandler();
            return $accessHandler->checkAccessOfUser($recipientUsrId, 'read', '', $portfolioId);
        }

        return (
            $this->sender->getPref('public_profile') === 'y' ||
            $this->sender->getPref('public_profile') === 'g'
        );
    }
}