<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemNotification
{
    /** @var \ilObjUser */
    protected $sender;

    /** @var \ilSetting */
    protected $settings;
    
    /** @var array */
    protected $recipient_ids = [];

    /**
     * @param \ilObjUser $user
     * @param \ilSetting $settings
     */
    public function __construct(ilObjUser $user, \ilSetting $settings)
    {
        $this->sender = $user;
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getRecipientIds()
    {
        return $this->recipient_ids;
    }

    /**
     * @param array $recipient_ids
     */
    public function setRecipientIds(array $recipient_ids)
    {
        $this->recipient_ids = $recipient_ids;
    }
    
    /**
     *
     */
    public function send()
    {
        foreach ($this->getRecipientIds() as $usr_id) {
            $user = new ilObjUser((int) $usr_id);

            $rcp_lng = ilLanguageFactory::_getLanguage($user->getLanguage());
            $rcp_lng->loadLanguageModule('buddysystem');

            $notification = new ilNotificationConfig('buddysystem_request');
            $notification->setTitleVar('buddy_notification_contact_request', array(), 'buddysystem');

            $personalProfileLink = $rcp_lng->txt('buddy_noti_cr_profile_not_published');
            if ($this->hasPublicProfile($user->getId())) {
                $personalProfileLink = \ilLink::_getStaticLink($this->sender->getId(), 'usr', true);
            }

            $bodyParams = array(
                'SALUTATION'      => ilMail::getSalutation($user->getId(), $rcp_lng),
                'BR'              => nl2br("\n"),
                'APPROVE_REQUEST' => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_approved') . '">' . $rcp_lng->txt('buddy_notification_contact_request_link_osd') . '</a>',
                'IGNORE_REQUEST'  => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_ignored') . '">' . $rcp_lng->txt('buddy_notification_contact_request_ignore_osd') . '</a>',
                'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId())
            );
            $notification->setShortDescriptionVar('buddy_notification_contact_request_short', $bodyParams, 'buddysystem');

            $bodyParams = array(
                'SALUTATION' => ilMail::getSalutation($user->getId(), $rcp_lng),
                'BR' => "\n",
                'APPROVE_REQUEST' => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_approved'),
                'APPROVE_REQUEST_TXT' => $rcp_lng->txt('buddy_notification_contact_request_link'),
                'IGNORE_REQUEST' => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_ignored'),
                'IGNORE_REQUEST_TXT' => $rcp_lng->txt('buddy_notification_contact_request_ignore'),
                'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId()),
                'PERSONAL_PROFILE_LINK' => $personalProfileLink,
            );
            $notification->setLongDescriptionVar('buddy_notification_contact_request_long', $bodyParams, 'buddysystem');

            $notification->setAutoDisable(false);
            $notification->setValidForSeconds(ilNotificationConfig::TTL_LONG);
            $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
            $notification->setIconPath('templates/default/images/icon_usr.svg');
            $notification->setHandlerParam('mail.sender', ANONYMOUS_USER_ID);
            $notification->notifyByUsers(array($user->getId()));
        }
    }

    /**
     * @param int $recipientUsrId
     * @return bool
     */
    protected function hasPublicProfile($recipientUsrId)
    {
        $portfolioId = \ilObjPortfolio::getDefaultPortfolio($this->sender->getId());
        if (is_numeric($portfolioId) && $portfolioId > 0) {
            $accessHandler = new \ilPortfolioAccessHandler();
            return $accessHandler->checkAccessOfUser($recipientUsrId, 'read', '', $portfolioId);
        }

        return (
            $this->sender->getPref('public_profile') === 'y' ||
            $this->sender->getPref('public_profile') === 'g'
        );
    }
}
