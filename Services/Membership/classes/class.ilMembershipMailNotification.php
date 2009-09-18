<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Language/classes/class.ilLanguageFactory.php';
include_once './Services/Mail/classes/class.ilMail.php';

/**
 * Base class for course/group mail notifications
 * 
 * @version $Id$
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * 
 * @ingroup ServicesMembership
 */
abstract class ilMembershipMailNotification
{
	const SUBJECT_TITLE_LENGTH = 60;
	
	private $type = null;
	private $sender = null;
	
	private $mail = null;
	private $subject = '';
	private $body = '';
	
	private $language = null;
	
	private $recipients = array();
	
	private $ref_id = null;
	private $obj_id = null;
	private $obj_type = null;
	
	private $additional_info = array();
	
	/**
	 * Constructor
	 * @return
	 */
	public function __construct()
	{
		$this->setSender(ANONYMOUS_USER_ID);
		$this->language = ilLanguageFactory::_getLanguage('en');
	}
	
	/**
	 * Set notification type
	 * @param int $a_type
	 * @return 
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	/**
	 * Get notification type
	 * @return 
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Set sender of mail
	 * @param  int $a_usr_id
	 * @return 
	 */
	public function setSender($a_usr_id)
	{
		$this->sender = $a_usr_id;
	}
	
	/**
	 * get sender of mail
	 * @return 
	 */
	public function getSender()
	{
		return $this->sender;
	}
	
	/**
	 * Set mail subject
	 * @param string $a_subject
	 * @return string body
	 */
	protected function setSubject($a_subject)
	{
		return $this->subject = $a_subject;
	}
	
	/**
	 * Get mail subject
	 * @return string
	 */
	protected function getSubject()
	{
		return $this->subject;
	}
	
	/**
	 * Set mail body
	 * @param string $a_body
	 * @return 
	 */
	protected function setBody($a_body)
	{
		$this->body = $a_body;
	}
	
	/**
	 * Append body text
	 * @param string $a_body
	 * @return string body
	 */
	protected function appendBody($a_body)
	{
		return $this->body .= $a_body;
	}
	
	/**
	 * Get body
	 * @return string 
	 */
	protected function getBody()
	{
		return $this->body;
	}
	
	/**
	 * set mail recipients
	 * @param array $a_rcp user ids
	 * @return 
	 */
	public function setRecipients($a_rcp)
	{
		$this->recipients = $a_rcp;
	}
	
	/**
	 * get array of recipients
	 * @return 
	 */
	public function getRecipients()
	{
		return $this->recipients;
	}
	
	/**
	 * Init language
	 * @param int $a_usr_id
	 * @return 
	 */
	protected function initLanguage($a_usr_id)
	{
		$this->language = ilLanguageFactory::_getLanguageOfUser($a_usr_id);
		$this->language->loadLanguageModule('mail');
	}
	
	/**
	 * get language object
	 * @return 
	 */
	protected function getLanguage()
	{
		return $this->language;
	}
	
	/** 
	 * Replace new lines
	 * @param object $a_keyword
	 * @return 
	 */
	protected function getLanguageText($a_keyword)
	{
		return str_replace('\n', "\n", $this->getLanguage()->txt($a_keyword));
	}
	
	/**
	 * Set ref id
	 * @param int $a_id
	 * @return 
	 */
	public function setRefId($a_id)
	{
		$this->ref_id = $a_id;
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
		$this->obj_type = ilObject::_lookupType($this->obj_id);
	}
	
	/**
	 * get reference id
	 * @return 
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * get object id
	 * @return 
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * Get object type
	 * @return 
	 */
	public function getObjType()
	{
		return $this->obj_type;
	}
	
	/**
	 * Additional information for creating notification mails
	 * @param array $a_info
	 * @return 
	 */
	public function setAdditionalInformation($a_info)
	{
		$this->additional_info = $a_info;
	}
	
	/**
	 * Get additional information for generating notification mails
	 * @return 
	 */
	public function getAdditionalInformation()
	{
		return (array) $this->additional_info; 
	}
	
	/**
	 * Get object title
	 * @param bool shorten title
	 * @return 
	 */
	protected function getObjectTitle($a_shorten = false)
	{
		if(!$this->getObjId())
		{
			return '';
		}
		return ilUtil::shortenText(ilObject::_lookupTitle($this->getObjId()), self::SUBJECT_TITLE_LENGTH,true);
	}
	
	
	/**
	 * Send notifications
	 * @return 
	 */
	public function send()
	{
		switch($this->getType())
		{
			
		}
	}
	
	/**
	 * Send Mail
	 *  @param array recipients
	 *  @param array mail type (one 'normal', 'system', 'email')
	 * @return 
	 */
	public function sendMail($a_rcp,$a_type)
	{
		$recipients = array();
		foreach($a_rcp as $rcp)
		{
			$recipients[] = ilObjUser::_lookupLogin($rcp);
		}
		$recipients = implode(',',$recipients);
		$error = $this->getMail()->sendMail(
			$recipients,
			'',
			'',
			$this->getSubject(),
			$this->getBody(),
			array(),
			$a_type
		);
		
		if(strlen($error))
		{
			ilUtil::sendFailure($error,true);
		}
	}
	
	/**
	 * Init mail
	 * @return 
	 */
	protected function initMail()
	{
		return $this->mail = new ilMail($this->getSender());
	}
	
	/**
	 * Get mail object
	 * @return 
	 */
	protected function getMail()
	{
		return is_object($this->mail) ? $this->mail : $this->initMail();
	}
	
	/**
	 * Create a permanent link for an object
	 * @return 
	 */
	protected function createPermanentLink()
	{
		include_once './classes/class.ilLink.php';
		
		if($this->getRefId())
		{
			return ilLink::_getLink($this->ref_id,$this->getObjType());
		}
		else
		{
			// Return root
			return ilLink::_getLink(ROOT_FOLDER_ID,'root');
		}
	}
	
	/**
	 * Utility function 
	 * @param int $a_usr_id
	 * @return 
	 */
	protected function userToString($a_usr_id)
	{
		$name = ilObjUser::_lookupName($a_usr_id);
		return ($name['title'] ? $name['title'].' ' : '').
			($name['firstname'] ? $name['firstname'].' ' : '').
			($name['lastname'] ? $name['lastname'].' ' : '');
	}
}
?>