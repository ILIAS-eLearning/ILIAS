<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemNotification
{
	/**
	 * @var ilObjUser
	 */
	protected $sender;
	
	/**
	 * @var array
	 */
	protected $recipient_ids = array();

	/**
	 * @param ilObjUser $ilObjUser
	 */
	public function __construct(ilObjUser $ilObjUser)
	{
		$this->sender = $ilObjUser;
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
		require_once 'Services/Mail/classes/class.ilMail.php';
		foreach($this->getRecipientIds() as $usr_id)
		{
			$user = new ilObjUser((int)$usr_id);

			require_once 'Services/Language/classes/class.ilLanguageFactory.php';
			require_once 'Services/User/classes/class.ilUserUtil.php';
			require_once 'Services/Link/classes/class.ilLink.php';

			$rcp_lng = ilLanguageFactory::_getLanguage($user->getLanguage());
			$rcp_lng->loadLanguageModule('buddysystem');

			require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
			$notification = new ilNotificationConfig('buddysystem_request');
			$notification->setTitleVar('buddy_notification_contact_request', array(), 'buddysystem');

			$bodyParams = array(
				'SALUTATION'      => ilMail::getSalutation($user->getId(), $rcp_lng),
				'BR'              => nl2br("\n"),
				'APPROVE_REQUEST' => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_approved') . '">' . $rcp_lng->txt('buddy_notification_contact_request_link_osd') . '</a>',
				'IGNORE_REQUEST'  => '<a href="' . ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_ignored') . '">' . $rcp_lng->txt('buddy_notification_contact_request_ignore_osd') . '</a>',
				'REQUESTING_USER' => ilUserUtil::getNamePresentation($this->sender->getId())
			);
			$notification->setShortDescriptionVar('buddy_notification_contact_request_short', $bodyParams, 'buddysystem');

			$bodyParams = array(
				'SALUTATION'          => ilMail::getSalutation($user->getId(), $rcp_lng),
				'BR'                  => "\n",
				'APPROVE_REQUEST'     => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_approved'),
				'APPROVE_REQUEST_TXT' => $rcp_lng->txt('buddy_notification_contact_request_link'),
				'IGNORE_REQUEST'      => ilLink::_getStaticLink($this->sender->getId(), 'usr', true, '_contact_ignored'),
				'IGNORE_REQUEST_TXT'  => $rcp_lng->txt('buddy_notification_contact_request_ignore'),
				'REQUESTING_USER'     => ilUserUtil::getNamePresentation($this->sender->getId())
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
}