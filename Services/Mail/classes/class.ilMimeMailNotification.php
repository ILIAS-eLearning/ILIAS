<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMimeMail.php';

/**
 * Base class for mime mail notifications
 * @version $Id$
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
abstract class ilMimeMailNotification extends ilMailNotification
{
	/**
	 * @var ilMimeMail
	 */
	protected $mime_mail;
	
	/**
	 * @var string
	 */
	protected $current_recipient;

	/**
	 * @param string $a_rcp
	 */
	public function sendMimeMail($a_rcp)
	{
		$this->mime_mail->To($a_rcp);
		$this->mime_mail->Subject($this->getSubject());
		$this->mime_mail->Body($this->getBody());
		$this->mime_mail->Send();
	}

	/**
	 * @return ilMimeMail
	 */
	protected function initMimeMail()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$this->mime_mail = new ilMimeMail();
		$this->mime_mail->From($ilSetting->get('admin_email'));
		$this->mime_mail->autoCheck(false);

		return $this->mime_mail;
	}

	/**
	 * @param string $a_code
	 */
	protected function initLanguageByIso2Code($a_code = '')
	{
		parent::initLanguageByIso2Code($a_code);
		$this->getLanguage()->loadLanguageModule('registration');
	}

	/**
	 * @param int $a_usr_id
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->getLanguage()->loadLanguageModule('registration');
	}

	/**
	 * @param string $rcp
	 * @throws ilMailException
	 */
	protected function handleCurrentRecipient($rcp)
	{
		require_once 'Services/Mail/exceptions/class.ilMailException.php';
		
		if(is_numeric($rcp))
		{
			/**
			 * @var $rcp ilObjUser
			 */
			$rcp = ilObjectFactory::getInstanceByObjId($rcp, false);
			if(!$rcp)
			{
				throw new ilMailException('no_recipient_found');
			}
			$this->setCurrentRecipient($rcp->getEmail());
			$this->initLanguage($rcp->getId());
		}
		else if(is_string($rcp) && ilUtil::is_email($rcp))
		{
			$this->setCurrentRecipient($rcp);
			$this->initLanguageByIso2Code();
		}
		else if($rcp instanceof ilObjUser)
		{
			/**
			 * @var $rcp ilObjUser
			 */
			$this->setCurrentRecipient($rcp->getEmail());
			$this->initLanguage($rcp->getId());
		}
		else
		{
			throw new ilMailException('no_recipient_found');
		}
	}

	/**
	 * @param string $current_recipient
	 */
	public function setCurrentRecipient($current_recipient)
	{
		$this->current_recipient = $current_recipient;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCurrentRecipient()
	{
		return $this->current_recipient;
	}

	/**
	 * @param ilMimeMail $mime_mail
	 * @return ilMimeMailNotification
	 */
	public function setMimeMail($mime_mail)
	{
		$this->mime_mail = $mime_mail;
		return $this;
	}

	/**
	 * @return ilMimeMail
	 */
	public function getMimeMail()
	{
		return $this->mime_mail;
	}
}
