<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesRegistration
 */

class ilRegistrationMailNotification extends ilMailNotification
{
	const TYPE_NOTIFICATION_APPROVERS = 30;
	const TYPE_NOTIFICATION_CONFIRMATION = 31;
	
	
	/**
	 * Default constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Parse and send mail
	 * @return 
	 */
	public function send()
	{
		switch($this->getType())
		{
			case self::TYPE_NOTIFICATION_APPROVERS:
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						$this->getLanguageText('reg_mail_new_user')
					);

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					
					$this->appendBody($this->getLanguageText('reg_mail_new_user_body'));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('reg_mail_body_profile'));
					
					$info = $this->getAdditionalInformation();
					
					$this->appendBody("\n\n");
					$this->appendBody($info['usr']->getProfileAsString($this->getLanguage()));

					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('reg_mail_body_reason'));

					$this->getMail()->appendInstallationSignature(true);
					$this->getMail()->enableSoap(false);
					$this->sendMail(array($rcp),array('system'));
				}
				break;

			case self::TYPE_NOTIFICATION_CONFIRMATION:
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						$this->getLanguageText('reg_mail_new_user_confirmation')
					);

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					
					$this->appendBody($this->getLanguageText('reg_mail_new_user_body'));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('reg_mail_body_profile'));
					
					$info = $this->getAdditionalInformation();
					
					$this->appendBody("\n\n");
					$this->appendBody($info['usr']->getProfileAsString($this->getLanguage()));

					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('reg_mail_body_confirmation'));				
					$this->appendBody("\n"); // #4527
					include_once "Services/Link/classes/class.ilLink.php";
					$this->appendBody(ilLink::_getStaticLink($info['usr']->getId(), "usrf"));

					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('reg_mail_body_reason'));
					
					$this->getMail()->appendInstallationSignature(true);
					$this->getMail()->enableSoap(false);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
		}
	}
	
	/**
	 * Add language module registration
	 * @param object $a_usr_id
	 * @return 
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->getLanguage()->loadLanguageModule('registration');
	}
	
}
