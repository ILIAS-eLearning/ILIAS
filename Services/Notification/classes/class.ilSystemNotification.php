<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * Wrapper classes for system notifications
 * 
 * @see FeatureWiki/Guidelines/System Notification Guideline
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjExerciseGUI.php 24003 2010-05-26 14:35:42Z akill $
 * 
 * @ingroup ServicesNotification
 */
class ilSystemNotification extends ilMailNotification
{
	protected $lang_modules; // [array]
	protected $subject_lang_id; // [string]
	protected $introduction; // [string]
	protected $task; // [string]
	protected $reason; // [string]
	protected $additional; // [array]
	protected $goto_caption; // [string]	
	protected $is_in_wsp; // [bool]	
	protected $changed_by; // [int]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_obj_id
	 * @param array $a_lang_modules
	 * @param bool $a_personal_workspace
	 */
	public function __construct($a_obj_id, array $a_lang_modules = null, $a_personal_workspace = false)
	{
		$this->setObjId($a_obj_id);				
		$this->lang_modules = $a_lang_modules;	
		$this->is_in_wsp = (bool)$a_personal_workspace;
		
		if($a_obj_id)
		{
			$this->obj_type = ilObject::_lookupType($this->getObjId());		
		}
		
		$this->setSender(ANONYMOUS_USER_ID);
	}
	
	/**
	 * Set ref_id
	 * 
	 * @param int $a_id
	 */
	public function setRefId($a_id)
	{
		if($this->is_in_wsp)
		{
			// do not try to get obj_id in personal workspace
			$this->ref_id = $a_id;
			return;
		}
		parent::setRefId($a_id);
	}
	
	/**
	 * Set subject lang id
	 * 
	 * @param string $a_lang_id
	 */
	public function setSubjectLangId($a_lang_id)
	{
		$this->subject_lang_id = (string)$a_lang_id;
	}
	
	/**
	 * Set introduction lang id
	 * 
	 * @param string $a_lang_id
	 */
	public function setIntroductionLangId($a_lang_id)
	{
		$this->introduction = (string)$a_lang_id;
	}
	
	/**
	 * Set task lang id
	 * 
	 * @param string $a_lang_id
	 */
	public function setTaskLangId($a_lang_id)
	{
		$this->task = (string)$a_lang_id;
	}
	
	/**
	 * Set reason lang id
	 * 
	 * @param string $a_lang_id
	 */
	public function setReasonLangId($a_lang_id)
	{
		$this->reason = (string)$a_lang_id;
	}
	
	/**
	 * Set goto lang id
	 * 
	 * @param string $a_lang_id
	 */
	public function setGotoLangId($a_lang_id)
	{
		$this->goto_caption = (string)$a_lang_id;
	}
	
	/**
	 * Set changed by user id
	 * 
	 * @param int $a_id
	 */
	public function setChangedByUserId($a_id)
	{
		$this->changed_by = (int)$a_id;
		
		include_once "Services/User/classes/class.ilUserUtil.php";
	}
	
	/**
	 * Add additional information
	 * 
	 * @param string $a_lang_id
	 * @param mixed $a_value
	 * @param bool $a_multiline
	 */
	public function addAdditionalInfo($a_lang_id, $a_value, $a_multiline = false)
	{
		$this->additional[$a_lang_id] = array(trim($a_value), (bool)$a_multiline);
	}
	
	/**
	 * Send notification(s)
	 * 
	 * @param array $a_user_ids
	 * @param string $a_goto_additional
	 * @param string $a_permission
	 * @return array recipient ids
	 */
	public function send(array $a_user_ids, $a_goto_additional = null, $a_permission = "read")
	{
		global $ilUser, $ilAccess;				
		
		if(!$this->getObjId())
		{
			return array();
		}
		
		// get ref_id(s)
		if(!$this->is_in_wsp)
		{
			if(!$this->getRefId())
			{
				$ref_ids = ilObject::_getAllReferences($this->getObjId());				
				if(sizeof($ref_ids) == 1)
				{
					$this->ref_id = array_shift($ref_ids);
				}
			}
		}
		else
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";			
			$wsp_tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
			$wsp_access_handler = new ilWorkspaceAccessHandler($wsp_tree); 		
			$this->ref_id = $wsp_tree->lookupNodeId($this->getObjId());					
			$goto = ilWorkspaceAccessHandler::getGotoLink($this->getRefId(), $this->getObjId(), $a_goto_additional);	
		}		
		
		// default values
		if(!$this->goto_caption)
		{
			$this->goto_caption = "url";			
		}
		
		$recipient_ids = array();
		foreach(array_unique($a_user_ids) as $user_id)
		{				
			// author of change should not get notification
			if($this->changed_by == $user_id)
			{
				continue;
			}
			
			$this->initLanguage($user_id);
			if(sizeof($this->lang_modules))
			{
				foreach($this->lang_modules as $lmod)
				{
					$this->language->loadLanguageModule($lmod);
				}
			}
			
			$this->initMail();
			
			$this->setSubject(
				sprintf($this->getLanguageText($this->subject_lang_id), $this->getObjectTitle(true))
			);
			
			$this->setBody(ilMail::getSalutation($user_id, $this->getLanguage()));
			$this->appendBody("\n\n");
			
			if($this->introduction)
			{
				$this->appendBody($this->getLanguageText($this->introduction));	
				$this->appendBody("\n\n");
			}
			
			if($this->task)
			{
				$this->appendBody($this->getLanguageText($this->task));	
				$this->appendBody("\n\n");
			}
			
			// details table
			$this->appendBody($this->getLanguageText("obj_".$this->getObjType()).": ".
				$this->getObjectTitle()."\n");
			if(sizeof($this->additional))
			{
				foreach($this->additional as $lang_id => $item)
				{
					if(!$item[1])
					{
						$this->appendBody($this->getLanguageText($lang_id).": ".
							$item[0]."\n");						
					}				
					else
					{
						$this->appendBody("\n".$this->getLanguageText($lang_id)."\n".
							"----------------------------------------\n".
							$item[0]."\n".
							"----------------------------------------\n\n");	
					}
				}
			}
			$this->body = trim($this->body);
			$this->appendBody("\n\n");
			
			if($this->changed_by)
			{				
				$this->appendBody($this->getLanguageText("system_notification_installation_changed_by").": ".
					ilUserUtil::getNamePresentation($this->changed_by));
				$this->appendBody("\n\n");
			}
				
			// repository (for personal workspace see above)
			if(!$this->is_in_wsp)
			{				
				$goto = null;
				
				// try to find accessible ref_id
				if(!$this->getRefId())
				{
					$find_ref_id = true;						
					foreach($ref_ids as $ref_id)
					{						
						if($ilAccess->checkAccessOfUser($user_id, $a_permission, "", $ref_id, $this->getObjType()))
						{
							$this->ref_id = $ref_id;
							break;
						}
					}
				}
					
				if($this->getRefId())
				{
					if(!$ilAccess->checkAccessOfUser($user_id, $a_permission, "", $this->getRefId(), $this->getObjType()))
					{
						continue;
					}
					
					$goto = $this->createPermanentLink();
				}
			}	
			else
			{
				if(!$wsp_access_handler->checkAccessOfUser($wsp_tree, $user_id, $a_permission, "", $this->getRefId(), $this->getObjType()))
				{
					continue;
				}								
			}
			if($goto)
			{				
				$this->appendBody($this->getLanguageText($this->goto_caption).": ".
					$goto);
				$this->appendBody("\n\n");
			}
			
			if(!$this->is_in_wsp && $find_ref_id)
			{
				$this->ref_id = null;
			}
			
			if($this->reason)
			{
				$this->appendBody($this->getLanguageText($this->reason));	
				$this->appendBody("\n\n");
			}				
			
			// signature will append new lines
			$this->body = trim($this->body);
								
			$this->getMail()->appendInstallationSignature(true, 
				$this->getLanguageText("system_notification_installation_signature"));
			
			$this->sendMail(array($user_id),array('system'));
			
			$recipient_ids[] = $user_id;
		}	
		
		return $recipient_ids;
	}	
}

?>