<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id$
 *
 */
class ilForumMailNotification extends ilMailNotification
{
	const TYPE_THREAD_DELETED 	= 54;

	const TYPE_POST_NEW 		= 60;
	const TYPE_POST_ACTIVATION 	= 61;
	const TYPE_POST_UPDATED 	= 62;
	const TYPE_POST_CENSORED 	= 63;
	const TYPE_POST_DELETED 	= 64;
	const TYPE_POST_ANSWERED	= 65;
	const TYPE_POST_UNCENSORED 	= 66;

	const PERMANENT_LINK_POST	= 'PL_Post';
	const PERMANENT_LINK_FORUM	= 'PL_Forum';

	/**
	 * @var bool
	 */
	protected $is_cronjob = false;

	/**
	 * @var ilForumNotificationMailData
	 */
	protected $provider;

	/**
	 * @var \ilLogger
	 */
	protected $logger;

	/**
	 * ilForumMailNotification constructor.
	 * @param ilForumNotificationMailData $provider
	 * @param ilLogger                    $logger
	 */
	public function __construct(ilForumNotificationMailData $provider, \ilLogger $logger)
	{
		parent::__construct(false);
		$this->provider = $provider;
		$this->logger   = $logger;
	}

	/**
	 * @inheritdoc
	 */
	protected function initMail()
	{
		$mail = parent::initMail();
		$this->logger->debug('Initialized mail service');
		return $mail;
	}

	/**
	 * @inheritdoc
	 */
	public function sendMail(array $a_rcp, $a_type, $a_parse_recipients = true)
	{
		$this->logger->debug('Delegating notification transport to mail service ...');
		parent::sendMail($a_rcp, $a_type, $a_parse_recipients);
		$this->logger->debug('Notification transport delegated');
	}

	/**
	 * @inheritdoc
	 */
	protected function setSubject($a_subject)
	{
		$value = parent::setSubject($a_subject);
		$this->logger->debug(sprintf('Setting subject to: %s', $a_subject));
		return $value;
	}

	/**
	 * 
	 */
	protected function appendAttachments()
	{
		if (count($this->provider->getAttachments()) > 0) {
			$this->logger->debug('Adding attachments ...');
			foreach ($this->provider->getAttachments() as $attachment) {
				$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
			}
			$this->appendBody("\n------------------------------------------------------------\n");
			$this->setAttachments($this->provider->getAttachments());
		}
	}

	/**
	 * @return bool
	 */
	public function send()
	{
		global $DIC; 
		$ilSetting = $DIC->settings();
		$lng = $DIC->language();

		if(!$ilSetting->get('forum_notification', 0))
		{
			$this->logger->debug('Forum notifications are globally disabled');
			return false;
		}

		if(!$this->getRecipients())
		{
			$this->logger->debug('No notification recipients, nothing to do');
			return false;
		}

		$lng->loadLanguageModule('forum');

		$date_type = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		switch($this->getType())
		{
			case self::TYPE_THREAD_DELETED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('thread_deleted_by'), $this->provider->getDeletedBy(),  $this->provider->getForumTitle());
					$this->sendMailWithoutAttachments('frm_noti_subject_del_thread', (int) $rcp, (string) $customText, 'content_deleted_thread');
				}
				break;

			case self::TYPE_POST_NEW:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('frm_noti_new_post'), $this->provider->getForumTitle());
					$this->sendMailWithAttachments('frm_noti_subject_new_post', (int) $rcp, (string) $customText, 'new_post');
				}
				break;

			case self::TYPE_POST_ACTIVATION:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = $this->getLanguageText('forums_post_activation_mail');
					$this->sendMailWithAttachments('frm_noti_subject_act_post', (int) $rcp, (string) $customText, 'new_post');
				}
				break;

			case self::TYPE_POST_ANSWERED;
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = $this->getLanguageText('forum_post_replied');
					$this->sendMailWithAttachments('frm_noti_subject_answ_post', (int) $rcp, (string) $customText, 'new_post');
				}
				break;

			case self::TYPE_POST_UPDATED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('post_updated_by'), $this->provider->getPostUpdateUserName($this->getLanguage()), $this->provider->getForumTitle());
					$date = $this->provider->getPostUpdate();
					$this->sendMailWithAttachments('frm_noti_subject_upt_post', (int) $rcp, (string) $customText, 'content_post_updated', $date);
				}
				break;

			case self::TYPE_POST_CENSORED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('post_censored_by'), $this->provider->getPostUpdateUserName($this->getLanguage()) ,$this->provider->getForumTitle());
					$date = $this->provider->getPostCensoredDate();
					$this->sendMailWithAttachments('frm_noti_subject_cens_post', (int) $rcp, (string) $customText, 'content_censored_post', $date);
				}
				break;

			case self::TYPE_POST_UNCENSORED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('post_uncensored_by'), $this->provider->getPostUpdateUserName($this->getLanguage()));
					$date = $this->provider->getPostCensoredDate();
					$this->sendMailWithAttachments('frm_noti_subject_uncens_post', (int) $rcp, (string) $customText, 'forums_the_post', $date);
				}
				break;

			case self::TYPE_POST_DELETED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$customText = sprintf($this->getLanguageText('post_deleted_by'), $this->provider->getDeletedBy(),  $this->provider->getForumTitle());
					$this->sendMailWithoutAttachments('frm_noti_subject_del_post', (int) $rcp, (string) $customText, 'content_deleted_post');
				}
				break;
		}

		ilDatePresentation::setLanguage($lng);
		ilDatePresentation::setUseRelativeDates($date_type);

		return true;
	}

	/**
	 * @param int $a_usr_id
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->language->loadLanguageModule('forum');
	}

	/**
	 * @return boolean
	 */
	public function isCronjob()
	{
		return (bool)$this->is_cronjob;
	}

	/**
	 * @param boolean $is_cronjob
	 */
	public function setIsCronjob($is_cronjob)
	{
		$this->is_cronjob = (bool)$is_cronjob;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	private function getPermanentLink($type = self::PERMANENT_LINK_POST)
	{
		global $DIC;
		$ilClientIniFile = $DIC['ilClientIniFile'];

		if($type == self::PERMANENT_LINK_FORUM)
		{
			$language_text = $this->getLanguageText("forums_notification_show_frm");
			$forum_parameters =  $this->provider->getRefId();
		}
		else
		{
			$language_text = $this->getLanguageText("forums_notification_show_post");
			$forum_parameters =  $this->provider->getRefId() . "_" . $this->provider->getThreadId() . "_" . $this->provider->getPostId();
		}

		$this->logger->debug(sprintf(
			'Building permanent with parameters %s', $forum_parameters
		));

		if($this->isCronjob())
		{
			$posting_link = sprintf($language_text,
					ilUtil::_getHttpPath() . "/goto.php?target=frm_" .$forum_parameters. '&client_id=' . CLIENT_ID) . "\n\n";

			$posting_link .= sprintf($this->getLanguageText("forums_notification_intro"),
					$ilClientIniFile->readVariable("client", "name"),
					ilUtil::_getHttpPath() . '/?client_id=' . CLIENT_ID) . "\n\n";
		}
		else
		{
			$posting_link = sprintf($language_text,
					ilUtil::_getHttpPath() . "/goto.php?target=frm_" .$forum_parameters. '&client_id=' . CLIENT_ID) . "\n\n";

			$posting_link .= sprintf($this->getLanguageText("forums_notification_intro"),
					$ilClientIniFile->readVariable("client", "name"),
					ilUtil::_getHttpPath() . '/?client_id=' . CLIENT_ID) . "\n\n";
		}

		$this->logger->debug(sprintf(
			'Link built: %s', $posting_link
		));

		return $posting_link;
	}

	/**
	 * @return string
	 */
	private function getSecurePostMessage()
	{
		$pos_message = $this->provider->getPostMessage();
		if(strip_tags($pos_message) != $pos_message)
		{
			$pos_message = preg_replace("/\n/i", "", $pos_message);
			$pos_message = preg_replace("/<br(\s*)(\/?)>/i", "\n", $pos_message);
			$pos_message = preg_replace("/<p([^>]*)>/i", "\n\n", $pos_message);
			$pos_message = preg_replace("/<\/p([^>]*)>/i", '', $pos_message);
			return $pos_message;
		}
		return strip_tags($pos_message);
	}

	/**
	 * Add body and send mail with attachments
	 *
	 * @param string $subjectLanguageId - Language id of subject
	 * @param int $userId - id of the user recipient of the mail
	 * @param string $customText - mail text after salutation
	 * @param string $action - Language id of action
	 * @param string|null $date - date to be added in mail
	 */
	private function sendMailWithAttachments(
		string $subjectLanguageId,
		int $userId,
		string $customText,
		string $action,
		string $date = ''
	) {
		$this->createMail($subjectLanguageId, $userId, $customText, $action, $date);
		$this->appendAttachments();
		$this->addLinkToMail();
		$this->sendMail(array($userId), array('system'));
	}

	/**
	 * Add body and send mail without attachments
	 *
	 * @param string $subjectLanguageId - Language id of subject
	 * @param int $userId - id of the user recipient of the mail
	 * @param string $customText - mail text after salutation
	 * @param string $action - Language id of action
	 * @param string|null $date - date to be added in mail
	 */
	private function sendMailWithoutAttachments(
		string $subjectLanguageId,
		int $userId,
		string $customText,
		string $action,
		string $date = ''
	) {
		$this->createMail($subjectLanguageId, $userId, $customText, $action, $date);
		$this->addLinkToMail();
		$this->sendMail(array($userId), array('system'));
	}

	/**
	 * @internal
	 *
	 * @param string $subject - Language id of subject
	 * @param int $userId - id of the user recipient of the mail
	 * @param string $customText - mail text after salutation
	 * @param string $action - Language id of action
	 * @param string|null $date - date to be added in mail
	 */
	private function createMail(
		string $subject,
		int $userId,
		string $customText,
		string $action,
		string $date
	) {
		$date = $this->createMailDate($date);

		$this->addMailSubject($subject);

		$this->setBody(ilMail::getSalutation($userId, $this->getLanguage()));
		$this->appendBody("\n\n");
		$this->appendBody($customText);
		$this->appendBody("\n\n");
		$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
		$this->appendBody("\n\n");
		$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
		$this->appendBody("\n\n");
		$this->appendBody($this->getLanguageText($action) . ": \n------------------------------------------------------------\n");

		$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
		$this->appendBody("\n");
		$this->appendBody($this->getLanguageText('date') . ": " . $date);
		$this->appendBody("\n");
		$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
		$this->appendBody("\n");
		$this->appendBody($this->getLanguageText('frm_noti_message'));
		$this->appendBody("\n");

		$message = strip_tags($this->getSecurePostMessage());

		if($this->provider->getPostCensored() == 1)
		{
			$message = $this->provider->getCensorshipComment();
		}

		$this->appendBody($message . "\n");
		$this->appendBody("------------------------------------------------------------\n");
	}

	/**
	 * @internal
	 * @param string $subject
	 */
	private function addMailSubject(string $subject)
	{
		$this->initMail();

		$this->setSubject(sprintf(
			$this->getLanguageText($subject),
			$this->provider->getForumTitle(),
			$this->provider->getThreadTitle()
		));
	}

	/**
	 * @internal
	 *
	 * @param string $date
	 * @return string
	 */
	private function createMailDate(string $date) : string
	{
		ilDatePresentation::setLanguage($this->language);

		if ($date === '') {
			$date = $this->provider->getPostDate();
		}

		$date = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_DATETIME));

		return $date;
	}

	/**
	 * @internal
	 */
	private function addLinkToMail()
	{
		$this->appendBody($this->getPermanentLink());
		$this->appendBody(ilMail::_getInstallationSignature());
	}
}
