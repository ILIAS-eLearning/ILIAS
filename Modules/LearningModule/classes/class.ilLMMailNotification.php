<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * 
 * @ingroup ModulesLearningModule
 */
class ilLMMailNotification extends ilMailNotification
{
	const TYPE_USER_BLOCKED = 10;

	/**
	 * Set question id
	 *
	 * @param int $a_val question id
	 */
	function setQuestionId($a_val)
	{
		$this->question_id = $a_val;
	}

	/**
	 * Get question id
	 *
	 * @return int question id
	 */
	function getQuestionId()
	{
		return $this->question_id;
	}

	/**
	 * Send notifications
	 * @return 
	 */
	public function send()
	{
		global $ilUser;
		
		switch($this->getType())
		{
			case self::TYPE_USER_BLOCKED:
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('cont_user_blocked'),
							$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						$this->getLanguageText('cont_user_blocked2'));
					$this->appendBody("\n");
					$this->appendBody(
						$this->getLanguageText('cont_user_blocked3')." '".$this->getLanguageText('objs_qst')."' > '".$this->getLanguageText('cont_blocked_users')."'");
					$this->appendBody("\n");
					$this->appendBody(
						$this->getLanguageText('obj_lm').": ".$this->getObjectTitle(true));
					$this->appendBody("\n");
					include_once("./Services/User/classes/class.ilUserUtil.php");
					$this->appendBody(
						$this->getLanguageText('user').": ".ilUserUtil::getNamePresentation($ilUser->getId(), false, false, ""));
					$this->appendBody("\n");

					include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
					$this->appendBody(
						$this->getLanguageText('question').": ".assQuestion::_getTitle($this->getQuestionId()));
					$this->appendBody("\n");
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('cont_lm_mail_permanent_link'));
					$this->appendBody("\n");
					$this->appendBody($this->createPermanentLink(array(), ""));
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;

		}
		return true;
	}
	
	/**
	 * Init language
	 *
	 * @param int $a_usr_id user id
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->getLanguage()->loadLanguageModule('content');
	}
}
?>