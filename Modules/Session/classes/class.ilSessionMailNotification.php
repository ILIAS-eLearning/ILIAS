<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilSessionMailNotification extends ilMailNotification
{
	const REGISTERED_EVENT = 'registered';
	const DELETION_EVENT = 'deletion';
	const ENTERED_EVENT = 'entered';

	function __construct($a_is_personal_workspace = false)
	{
		global $DIC;
		parent::__construct($a_is_personal_workspace);

		$this->user = $DIC->user();
	}

	const TYPE_USER_BLOCKED = 10;

	public function send($type, $userId, $objectId)
	{
		foreach($this->getRecipients() as $recipient)
		{
			$userObject = ilObjectFactory::getInstanceByObjId($userId, false);
			if (!$userObject || !($userObject instanceof \ilObjUser)) {
				throw new ilException(sprintf('User with ID "%s" does not exist.', $userId));
			}

			$sessionObject = ilObjectFactory::getInstanceByObjId($objectId, false);
			if (!$sessionObject || !($sessionObject instanceof \ilObjSession)) {
				throw new ilException(sprintf('Session with ID "%s" does not exist.', $objectId));
			}

			$this->initLanguage($recipient);
			$this->getLanguage()->loadLanguageModule('sess');
			$this->initMail();

			$subject = $this->createSubject($type, $userObject, $sessionObject);
			$this->setSubject($subject);

			$body = $this->createBodyText($type, $recipient, $userObject, $sessionObject);
			$this->setBody($body);

			$this->getMail()->appendInstallationSignature(true);
			$this->sendMail(array($recipient), array('system'));
		}
		return true;
	}

	/**
	 * Init language
	 *
	 * @param int $userId user id
	 */
	protected function initLanguage($userId)
	{
		parent::initLanguage($userId);
		$this->getLanguage()->loadLanguageModule('content');
	}

	private function createSubject($type, ilObjUser $userObject, ilObjSession $objectSession) {
		if (self::REGISTERED_EVENT === $type) {
			return sprintf(
				$this->getLanguageText('session_mail_subject_registered'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
		} elseif (self::DELETION_EVENT === $type) {
			return sprintf(
				$this->getLanguageText('session_mail_subject_deletion'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
		} elseif (self::ENTERED_EVENT === $type) {
			return sprintf(
				$this->getLanguageText('session_mail_subject_entered'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
		}

		throw new ilException(sprintf('The type "%s" is not valid for a session mail notification', $type));
	}

	/**
	 * @param $type
	 * @param $recipient
	 * @param ilObjUser $userObject
	 * @return string
	 * @throws ilException
	 */
	private function createBodyText($type, $recipient, ilObjUser $userObject, ilObjSession $objectSession)
	{
		$body = ilMail::getSalutation($recipient,$this->getLanguage());
		$body .= "\n\n";

		if (self::REGISTERED_EVENT === $type) {
			$body .= sprintf(
				$this->language->txt('register_notification'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
			return $body;
		} elseif (self::DELETION_EVENT === $type) {
			$body .= sprintf(
				$this->language->txt('deletion_notification'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
			return $body;
		} elseif (self::ENTERED_EVENT === $type) {
			$body .= sprintf(
				$this->language->txt('entered_notification'),
				$userObject->getFullname(),
				$objectSession->getTitle()
			);
			return $body;
		}

		throw new ilException(sprintf('The type "%s" is not valid for a session mail notification', $type));
	}
}
