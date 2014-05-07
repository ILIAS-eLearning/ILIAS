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
	protected $subject_lang_id; // [string]
	protected $introduction; // [string]
	protected $introduction_direct; // [string]
	protected $task; // [string]
	protected $reason; // [string]
	protected $additional; // [array]
	protected $goto_caption; // [string]	
	protected $changed_by; // [int]
	protected $all_ref_ids; // [array]
	
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
	 * Set introduction text
	 * 
	 * @param string $a_text
	 */
	public function setIntroductionDirect($a_text)
	{
		$this->introduction_direct = trim($a_text);
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
	public function sendMail(array $a_user_ids, $a_goto_additional = null, $a_permission = "read")
	{		
		$this->all_ref_ids = null;
		
		// prepare object related info
		if($this->getObjId())
		{						
			if(!$this->getRefId())
			{
				// try to find ref_id(s)
				if(!$this->is_in_wsp)
				{
					$ref_ids = ilObject::_getAllReferences($this->getObjId());				
					if(sizeof($ref_ids) == 1)
					{
						$this->ref_id = array_shift($ref_ids);
					}
					else
					{
						$this->all_ref_ids = $ref_ids;
					}
				}
			}
			else if($this->is_in_wsp) // #11680
			{					
				$this->ref_id = $this->wsp_tree->lookupNodeId($this->getObjId());
			}
			
			// default values
			if(!$this->goto_caption)
			{
				$this->goto_caption = "url";			
			}
		}
		
		$recipient_ids = array();
		foreach(array_unique($a_user_ids) as $user_id)
		{				
			// author of change should not get notification
			if($this->changed_by == $user_id)
			{
				continue;
			}
			
			if($this->composeAndSendMail($user_id, $a_goto_additional, $a_permission))
			{
				$recipient_ids[] = $user_id;
			}
		}	
		
		return $recipient_ids;
	}	
	
	/**
	 * Compose notification to single recipient
	 * 
	 * @param mixed $a_rcp
	 * @param string $a_goto_additional
	 * @param string $a_permission
	 * @param bool $a_append_signature_direct
	 * @return bool
	 */
	public function compose($a_user_id, $a_goto_additional = null, $a_permission = "read", $a_append_signature_direct = false)
	{
		$this->initLanguage($a_user_id);		
		$this->initMail();

		$this->setSubject(
			sprintf($this->getLanguageText($this->subject_lang_id), $this->getObjectTitle(true))
		);

		$this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
		$this->appendBody("\n\n");

		if($this->introduction)
		{
			$this->appendBody($this->getLanguageText($this->introduction));	
			$this->appendBody("\n\n");
		}

		if($this->introduction_direct)
		{
			$this->appendBody($this->introduction_direct);	
			$this->appendBody("\n\n");
		}

		if($this->task)
		{
			$this->appendBody($this->getLanguageText($this->task));	
			$this->appendBody("\n\n");
		}

		// details table
		if($this->getObjId())
		{
			$this->appendBody($this->getLanguageText("obj_".$this->getObjType()).": ".
				$this->getObjectTitle()."\n");
		}
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
						$this->getBlockBorder().
						$item[0]."\n".
						$this->getBlockBorder()."\n");	
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

		if($this->getObjId())
		{
			// try to find accessible ref_id
			if(!$this->getRefId() && $this->all_ref_ids)
			{
				$find_ref_id = true;						
				foreach($this->all_ref_ids as $ref_id)
				{		
					if($this->isRefIdAccessible($a_user_id, $ref_id, $a_permission))						
					{
						$this->ref_id = $ref_id;
						break;
					}
				}
			}
			
			// check if initially given ref_id is accessible for current recipient
			if($this->getRefId() &&
				!$find_ref_id && 
				!$this->isRefIdAccessible($a_user_id, $this->getRefId(), $a_permission))
			{
				return false;
			}
			
			$goto = $this->createPermanentLink(array(), $a_goto_additional);							
			if($goto)
			{				
				$this->appendBody($this->getLanguageText($this->goto_caption).": ".
					$goto);
				$this->appendBody("\n\n");
			}

			if($find_ref_id)
			{
				$this->ref_id = null;
			}
		}

		if($this->reason)
		{
			$this->appendBody($this->getLanguageText($this->reason));	
			$this->appendBody("\n\n");
		}				
		
		$this->appendBody(ilMail::_getAutoGeneratedMessageString($this->language));			

		// signature will append new lines
		$this->body = trim($this->body);

		if(!$a_append_signature_direct)
		{			
			$this->getMail()->appendInstallationSignature(true);
		}
		else 
		{			
			$this->appendBody(ilMail::_getInstallationSignature());
		}	
		
		return true;
	}
	
	/**
	 * Send notification to single recipient
	 * 
	 * @param mixed $a_rcp
	 * @param string $a_goto_additional
	 * @param string $a_permission
	 * @return bool
	 */
	protected function composeAndSendMail($a_user_id, $a_goto_additional = null, $a_permission = "read")
	{						
		if($this->compose($a_user_id, $a_goto_additional, $a_permission))
		{
			parent::sendMail(array($a_user_id), array('system'), is_numeric($a_user_id));								
			return true;
		}
		return false;
	}
	
	/**
	 * Compose notification to single recipient
	 * 
	 * @param mixed $a_user_id
	 * @param string $a_goto_additional
	 * @param string $a_permission
	 * @param bool $a_append_signature_direct
	 * @return string
	 */
	public function composeAndGetMessage($a_user_id, $a_goto_additional = null, $a_permission = "read", $a_append_signature_direct = false)
	{
		if($this->compose($a_user_id, $a_goto_additional, $a_permission, $a_append_signature_direct))
		{
			return $this->body;
		}
	}
}

?>