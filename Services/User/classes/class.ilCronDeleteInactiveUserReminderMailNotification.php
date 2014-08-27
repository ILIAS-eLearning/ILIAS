<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMimeMailNotification.php';
include_once './Services/Mail/classes/class.ilMimeMail.php';

/**
 * Class ilCronDeleteInactiveUserReminderMailNotification
 * @author Guido Vollbach <gvollbach@databay.de>
 * @version $Id$
 * @package Services/User
 */
class ilCronDeleteInactiveUserReminderMailNotification extends ilMimeMailNotification
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param string $a_code
	 */
	protected function initLanguageByIso2Code($a_code = '')
	{
		parent::initLanguageByIso2Code($a_code);
		$this->getLanguage()->loadLanguageModule('user');
	}

	public function send()
	{
		$additional_information = $this->getAdditionalInformation();

		foreach($this->getRecipients() as $rcp)
		{
			try
			{
				$this->handleCurrentRecipient($rcp);
			}
			catch(ilMailException $e)
			{
				continue;
			}
			$this->initMimeMail();
			$this->initLanguageByIso2Code();
			$this->setSubject($this->getLanguage()->txt('del_mail_subject'));
			$body = sprintf($this->getLanguage()->txt("del_mail_body"), $rcp->fullname,"\n\n",$additional_information["www"], $additional_information["days"]);
			$this->appendBody($body);
			$this->appendBody(ilMail::_getInstallationSignature());
			$this->sendMimeMail($this->getCurrentRecipient());
		}
	}
} 
