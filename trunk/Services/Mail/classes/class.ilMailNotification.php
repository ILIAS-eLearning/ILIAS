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
abstract class ilMailNotification
{
	const SUBJECT_TITLE_LENGTH = 60;
	
	protected $type = null;
	protected $sender = null;
	
	protected $mail = null;
	protected $subject = '';
	protected $body = '';

	protected $attachments = array();
	
	protected $language = null;
	protected $lang_modules = array();
	
	protected $recipients = array();
	
	protected $ref_id = null;
	protected $obj_id = null;
	protected $obj_type = null;
	
	protected $additional_info = array();
	
	protected $is_in_wsp;
	protected $wsp_tree; 
	protected $wsp_access_handler; 
	
	/**
	 * Constructor
	 * @param bool $a_is_personal_workspace
	 * @return
	 */
	public function __construct($a_is_personal_workspace = false)
	{
		global $lng, $ilUser;
		
		$this->is_in_wsp = (bool)$a_is_personal_workspace;

		$this->setSender(ANONYMOUS_USER_ID);
		$this->language = ilLanguageFactory::_getLanguage($lng->getDefaultLanguage());
		
		if($this->is_in_wsp)
		{			
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";			
			$this->wsp_tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
			$this->wsp_access_handler = new ilWorkspaceAccessHandler($this->wsp_tree); 					
		}
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
	 * Set attachments	 
	 * @param array $a_att
	 */
	public function setAttachments($a_att)
	{
		$this->attachments = $a_att;
	}

	/**
	 * Get attachments	 
	 * @return array
	 */
	public function getAttachments()
	{
		return (array) $this->attachments;
	}
		
	/**
	 * Set lang modules
	 * @param array $a_modules
	 */
	public function setLangModules(array $a_modules)
	{
		$this->lang_modules = $a_modules;
	}	
		
	/**
	 * Init language
	 * @param int $a_usr_id
	 */
	protected function initLanguage($a_usr_id)
	{
		$this->language = $this->getUserLanguage($a_usr_id);
	}
	
	/**
	 * Get user language
	 * 
	 * @param int $a_usr_id
	 * @return ilLanguage
	 */
	public function getUserLanguage($a_usr_id)
	{		
		$language = ilLanguageFactory::_getLanguageOfUser($a_usr_id);
		$language->loadLanguageModule('mail');
		
		if(sizeof($this->lang_modules))
		{
			foreach($this->lang_modules as $lmod)
			{
				$language->loadLanguageModule($lmod);
			}
		}
		
		return $language;
	}

	/**
	 * Init language by ISO2 code
	 * @param string $a_code
	 */
	protected function initLanguageByIso2Code($a_code = '')
	{
		$this->language = ilLanguageFactory::_getLanguage($a_code);
		$this->language->loadLanguageModule('mail');
		
		if(sizeof($this->lang_modules))
		{
			foreach($this->lang_modules as $lmod)
			{
				$this->language->loadLanguageModule($lmod);
			}
		}
	}
	
	/**
	 * A language
	 * @param ilLanguage $a_language
	 */
	protected function setLanguage($a_language)
	{
		$this->language = $a_language;
	}
	
	/**
	 * get language object
	 * @return ilLanguage
	 */
	protected function getLanguage()
	{
		return $this->language;
	}
	
	/** 
	 * Replace new lines
	 * @param string $a_keyword
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
		if(!$this->is_in_wsp)
		{
			$this->ref_id = $a_id;
			$obj_id = ilObject::_lookupObjId($this->ref_id);		
		}		
		else
		{
			$this->ref_id = (int)$a_id;			
			$obj_id = $this->wsp_tree->lookupObjectId($this->getRefId());					
		}
		
		$this->setObjId($obj_id);
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
	 * set obj id
	 * @return 
	 */
	public function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
		$this->obj_type = ilObject::_lookupType($this->obj_id);
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
		$txt = ilObject::_lookupTitle($this->getObjId());
		if((bool)$a_shorten)
		{
			$txt = ilUtil::shortenText($txt, self::SUBJECT_TITLE_LENGTH,true);
		}
		return $txt;
	}
	
	/**
	 * Send Mail
	 *  @param array recipients
	 *  @param array mail type (one 'normal', 'system', 'email')
	 * @return 
	 */
	public function sendMail($a_rcp,$a_type,$a_parse_recipients = true)
	{
		$recipients = array();
		foreach($a_rcp as $rcp)
		{
			if($a_parse_recipients)
			{
				$recipients[] = ilObjUser::_lookupLogin($rcp);
			}
			else
			{
				$recipients[] = $rcp;
			}
		}
		$recipients = implode(',',$recipients);
		$error = $this->getMail()->sendMail(
			$recipients,
			'',
			'',
			$this->getSubject(),
			$this->getBody(),
			$this->getAttachments(),
			$a_type
		);

		if(strlen($error))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': '.$error);
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
	protected function createPermanentLink($a_params = array(),$a_append = '')
	{
		include_once './Services/Link/classes/class.ilLink.php';
		
		if($this->getRefId())
		{
			if(!$this->is_in_wsp)
			{			
				return ilLink::_getLink($this->ref_id,$this->getObjType(),$a_params,$a_append);
			}
			else
			{
				return ilWorkspaceAccessHandler::getGotoLink($this->getRefId(), $this->getObjId(), $a_append);	
			}
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
		
	/**
	 * Check if ref id is accessible for user
	 * 
	 * @param int $a_user_id
	 * @param int $a_ref_id
	 * @param string $a_permission
	 * @return bool
	 */
	protected function isRefIdAccessible($a_user_id, $a_ref_id, $a_permission = "read")
	{
		global $ilAccess;
		
		// no given permission == accessible
		
		if(!$this->is_in_wsp)
		{												
			if(trim($a_permission) &&
				!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, "", $a_ref_id, $this->getObjType()))
			{
				return false;
			}
		}
		else
		{
			if(trim($a_permission) &&
				!$this->wsp_access_handler->checkAccessOfUser($this->wsp_tree, $a_user_id, $a_permission, "", $a_ref_id, $this->getObjType()))
			{
				return false;
			}								
		}
		return true;		
	}	
	
	/**
	 * Get (ascii) block border
	 * @return string
	 */
	public function getBlockBorder()
	{
		return "----------------------------------------\n";
	}
}

?>