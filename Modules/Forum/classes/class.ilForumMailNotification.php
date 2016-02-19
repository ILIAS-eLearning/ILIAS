<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Nadia Ahmad <nahmad@databay.de>
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
	 * @param ilForumNotificationMailData $provider
	 */
	public function __construct(ilForumNotificationMailData $provider)
	{
		parent::__construct();
		$this->provider = $provider;
	}

	/**
	 * @return bool
	 */
	public function send()
	{
		/**
		 * @var $ilSetting ilSetting
		 * @var $lng       ilLanguage
		 * @var $ilUser    ilObjUser
		 */
		global $ilSetting, $lng, $ilUser;

		if(!$ilSetting->get('forum_notification', 0))
		{
			return false;
		}

		if(!$this->getRecipients())
		{
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
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_del_thread'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('thread_deleted_by'), $ilUser->getLogin(),  $this->provider->getThreadTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_deleted_thread') ."\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");

					$post_date = ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostDate(), IL_CAL_DATETIME));
					$this->appendBody($this->getLanguageText('date') . ": " . $post_date);
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					$this->appendBody($this->getPermanentLink(self::PERMANENT_LINK_FORUM));
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;

			case self::TYPE_POST_NEW:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_new_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('frm_noti_new_post'), $this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread').": ".$this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('new_post').": \n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author').": ". $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date').": ". ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostDate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject').": ". $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));

				}
				break;

			case self::TYPE_POST_ACTIVATION:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_act_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");

					$this->appendBody($this->getLanguageText('forums_post_activation_mail'));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread').": ".$this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('new_post').": \n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author').": ". $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date').": ". ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostDate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject').": ". $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;

			case self::TYPE_POST_ANSWERED;
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_answ_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");

					$this->appendBody($this->getLanguageText('forum_post_replied'));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle());

					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread').": ".$this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('new_post').": \n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author').": ". $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date').": ". ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostDate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject').": ". $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));

				}
				break;

			case self::TYPE_POST_UPDATED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_upt_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_updated_by'), $this->provider->getPostUpdateUserName(), $this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_post_updated') . "\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostUpdate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;

			case self::TYPE_POST_CENSORED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_cens_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_censored_by'), $this->provider->getPostUpdateUserName() ,$this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_censored_post') . "\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostCensoredDate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;
			case self::TYPE_POST_UNCENSORED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_uncens_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_uncensored_by'), $this->provider->getPostUpdateUserName()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forums_the_post') . "\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostCensoredDate(), IL_CAL_DATETIME)));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					$pos_message = $this->getSecurePostMessage();
					$this->appendBody(strip_tags($pos_message) . "\n");

					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPermanentLink());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;

			case self::TYPE_POST_DELETED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					ilDatePresentation::setLanguage($this->language);

					$this->initMail();

					$this->setSubject(sprintf(
						$this->getLanguageText('frm_noti_subject_del_post'),
						$this->provider->getForumTitle(),
						$this->provider->getThreadTitle()
					));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_deleted_by'), $ilUser->getLogin(),  $this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_deleted_post') ."\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");

					$post_date = ilDatePresentation::formatDate(new ilDateTime($this->provider->getPostDate(), IL_CAL_DATETIME));
					$this->appendBody($this->getLanguageText('date') . ": " . $post_date);
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('frm_noti_message'));
					$this->appendBody("\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					$this->appendBody($this->getPermanentLink(self::PERMANENT_LINK_FORUM));
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
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
		/**
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilClientIniFile;

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
}