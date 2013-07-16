<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesMembership
 */
class ilCourseMembershipMailNotification extends ilMailNotification
{
	const TYPE_ADMISSION_MEMBER = 20;
	const TYPE_DISMISS_MEMBER 	= 21;
	
	const TYPE_ACCEPTED_SUBSCRIPTION_MEMBER = 22;
	const TYPE_REFUSED_SUBSCRIPTION_MEMBER = 23;
	
	const TYPE_STATUS_CHANGED = 24;
	
	const TYPE_BLOCKED_MEMBER = 25;
	const TYPE_UNBLOCKED_MEMBER = 26;
	
	const TYPE_UNSUBSCRIBE_MEMBER = 27;
	const TYPE_SUBSCRIBE_MEMBER = 28;
	const TYPE_WAITING_LIST_MEMBER = 29;
	
	const TYPE_NOTIFICATION_REGISTRATION = 30;
	const TYPE_NOTIFICATION_REGISTRATION_REQUEST = 31;
	const TYPE_NOTIFICATION_UNSUBSCRIBE = 32;
	

	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Send notifications
	 * @return 
	 */
	public function send()
	{
		global $ilSetting;

		if( (int) $this->getRefId() &&
			in_array($this->getType(), array(self::TYPE_ADMISSION_MEMBER)) )
		{
			$obj = ilObjectFactory::getInstanceByRefId( (int) $this->getRefId() );

			if( $obj->getAutoNotification() == false )
			{
				return false;
			}
		}

		// #11359
		// parent::send();
		
		switch($this->getType())
		{
			case self::TYPE_ADMISSION_MEMBER:
				
				// automatic mails about status change disabled
				if(!$ilSetting->get('mail_crs_member_notification',true))
				{
					return;
				}

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_added_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_added_member_body'),$this->getObjectTitle())
					);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink());
					$this->getMail()->appendInstallationSignature(true);
										
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
				
			case self::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_accept_subscriber'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_accept_subscriber_body'),$this->getObjectTitle())
					);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink());
					$this->getMail()->appendInstallationSignature(true);
										
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_REFUSED_SUBSCRIPTION_MEMBER:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_reject_subscriber'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_reject_subscriber_body'),$this->getObjectTitle())
					);

					$this->getMail()->appendInstallationSignature(true);
										
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_STATUS_CHANGED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_status_changed'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_status_changed_body'),$this->getObjectTitle())
					);
					
					$this->appendBody("\n\n");
					$this->appendBody($this->createCourseStatus($rcp));

					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink());

					$this->getMail()->appendInstallationSignature(true);
										
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				

			case self::TYPE_DISMISS_MEMBER:

				// automatic mails about status change disabled
				if(!$ilSetting->get('mail_crs_member_notification',true))
				{
					return;
				}
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_dismiss_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_dismiss_member_body'),$this->getObjectTitle())
					);
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
				
			case self::TYPE_BLOCKED_MEMBER:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_blocked_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_blocked_member_body'),$this->getObjectTitle())
					);
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_UNBLOCKED_MEMBER:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_unblocked_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_unblocked_member_body'),$this->getObjectTitle())
					);
					
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink());
					$this->getMail()->appendInstallationSignature(true);
										
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_NOTIFICATION_REGISTRATION:
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_new_subscription'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					
					$info = $this->getAdditionalInformation();
					$this->appendBody(
						sprintf($this->getLanguageText('crs_new_subscription_body'),
							$this->userToString($info['usr_id']),
							$this->getObjectTitle()
						)
					);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink(array(),'_mem'));
					
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));
					
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;

			case self::TYPE_NOTIFICATION_REGISTRATION_REQUEST:
				
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_new_subscription_request'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					
					$info = $this->getAdditionalInformation();
					$this->appendBody(
						sprintf($this->getLanguageText('crs_new_subscription_request_body'),
							$this->userToString($info['usr_id']),
							$this->getObjectTitle()
						)
					);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_new_subscription_request_body2'));
					$this->appendBody("\n");
					$this->appendBody($this->createPermanentLink(array(),'_mem'));
					
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));
					
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_NOTIFICATION_UNSUBSCRIBE:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_cancel_subscription'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					
					$info = $this->getAdditionalInformation();
					$this->appendBody(
						sprintf($this->getLanguageText('crs_cancel_subscription_body'),
							$this->userToString($info['usr_id']),
							$this->getObjectTitle()
						)
					);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_cancel_subscription_body2'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink(array(),'_mem'));
					
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));
					
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_UNSUBSCRIBE_MEMBER:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_unsubscribe_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_unsubscribe_member_body'),$this->getObjectTitle())
					);
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_SUBSCRIBE_MEMBER:

				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_subscribe_member'),$this->getObjectTitle(true))
					);
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_subscribe_member_body'),$this->getObjectTitle())
					);
					
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
					$this->appendBody("\n\n");
					$this->appendBody($this->createPermanentLink());
					$this->getMail()->appendInstallationSignature(true);

					$this->sendMail(array($rcp),array('system'));
				}
				break;
				
			case self::TYPE_WAITING_LIST_MEMBER:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(
						sprintf($this->getLanguageText('crs_subscribe_wl'),$this->getObjectTitle(true))
					);
					
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					
					$info = $this->getAdditionalInformation();
					$this->appendBody("\n\n");
					$this->appendBody(
						sprintf($this->getLanguageText('crs_subscribe_wl_body'),
							$this->getObjectTitle(),
							$info['position']
							)
					);
					$this->getMail()->appendInstallationSignature(true);
					$this->sendMail(array($rcp),array('system'));
				}
				break;
		}
		return true;
	}
	
	/**
	 * Add language module crs
	 * @param object $a_usr_id
	 * @return 
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->getLanguage()->loadLanguageModule('crs');
	}
	
	/**
	 * Get course status body
	 * @param int $a_usr_id
	 * @return string
	 */
	protected function createCourseStatus($a_usr_id)
	{
		$part = ilCourseParticipants::_getInstanceByObjId($this->getObjId());
		
		$body = $this->getLanguageText('crs_new_status')."\n";
		$body .= $this->getLanguageText('role').': ';
		
		
		if($part->isAdmin($a_usr_id))
		{
			$body .= $this->getLanguageText('crs_admin')."\n";
			
		}
		elseif($part->isTutor($a_usr_id))
		{
			$body .= $this->getLanguageText('crs_tutor')."\n";
		}
		else
		{
			$body .= $this->getLanguageText('crs_member')."\n";
		}

		if($part->isAdmin($a_usr_id) or $part->isTutor($a_usr_id))
		{
			$body .= $this->getLanguageText('crs_status').': ';
			
			if($part->isNotificationEnabled($a_usr_id))
			{
				$body .= $this->getLanguageText('crs_notify')."\n";
			}
			else
			{
				$body .= $this->getLanguageText('crs_no_notify')."\n";
			}
		}
		else
		{
			$body .= $this->getLanguageText('crs_access').': ';
			
			if($part->isBlocked($a_usr_id))
			{
				$body .= $this->getLanguageText('crs_blocked')."\n";
			}
			else
			{
				$body .= $this->getLanguageText('crs_unblocked')."\n";
			}
		}

		$body .= $this->getLanguageText('crs_passed').': ';
		
		if($part->hasPassed($a_usr_id))
		{
			$body .= $this->getLanguageText('yes');
		}
		else
		{
			$body .= $this->getLanguageText('no');
		}
		return $body;
	}
}
?>