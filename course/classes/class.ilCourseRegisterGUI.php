<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* class ilobjcourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseRegisterGUI
{
	var $ctrl;
	var $ilias;
	var $tree;
	var $ilErr;
	var $lng;
	var $tpl;

	var $course_obj;
	var $course_id;
	var $user_id;
	
	function ilCourseRegisterGUI($a_course_id)
	{
		global $ilCtrl,$lng,$ilErr,$ilias,$tpl,$tree;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id"));

		$this->ilErr =& $ilErr;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tree =& $tree;

		$this->user_id = $ilias->account->getId();

		$this->course_id = $a_course_id;
		$this->__initCourseObject();
		$this->__initWaitingList();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		switch($cmd = $this->ctrl->getCmd())
		{
			case 'archive':
			case 'join':
			case 'view':
			case '':
				$cmd = "showRegistrationForm";
				break;
		}
		$this->$cmd();
	}

	function cancel()
	{
		sendInfo($this->lng->txt("action_aborted"),true);

		ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
	}

	function subscribe()
	{
		if($this->course_obj->getSubscriptionMaxMembers() <= $this->course_obj->members_obj->getCountMembers())
		{
			include_once 'course/classes/class.ilCourseWaitingList.php';

			if(!$this->waiting_list->isOnList($this->user_id))
			{
				$this->waiting_list->addToList($this->user_id);
				
				$info = sprintf($this->lng->txt('crs_added_to_list'),$this->waiting_list->getPosition($this->user_id));
				sendInfo($info,true);

				ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
			}
			else
			{
				sendInfo($this->lng->txt('crs_already_assigned_to_list'),true);
				ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
			}				
		}				
		switch($this->course_obj->getSubscriptionType())
		{
			case $this->course_obj->SUBSCRIPTION_DEACTIVATED:
				$this->ilErr->raiseError($this->lng->txt("err_unknown_error"),$this->ilErr->MESSAGE);
				exit;

			case $this->course_obj->SUBSCRIPTION_DIRECT:
				
				$tmp_obj =& ilObjectFactory::getInstanceByObjId($this->user_id);

				if($this->course_obj->members_obj->add($tmp_obj,$this->course_obj->members_obj->ROLE_MEMBER))
				{
					$this->course_obj->members_obj->sendNotification($this->course_obj->members_obj->NOTIFY_ADMINS,$this->user_id);
					ilObjUser::updateActiveRoles($this->user_id);
					sendInfo($this->lng->txt("crs_subscription_successful"),true);
					
					ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
				}
				else
				{
					sendInfo("err_unknown_error");
					$this->showRegistrationForm();
				}
				break;

			case $this->course_obj->SUBSCRIPTION_CONFIRMATION:

				if($this->course_obj->members_obj->addSubscriber($this->user_id))
				{
					$this->course_obj->members_obj->sendNotification($this->course_obj->members_obj->NOTIFY_ADMINS,$this->user_id);
					sendInfo($this->lng->txt("crs_subscription_successful"),true);
					$this->ctrl->setParameterByClass("ilRepositoryGUI","ref_id",$this->tree->getParentId($this->course_id));

					ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
				}
				else
				{
					sendInfo("err_unknown_error");
					$this->showRegistrationForm();
				}
				break;

			case $this->course_obj->SUBSCRIPTION_PASSWORD:

				$tmp_obj =& ilObjectFactory::getInstanceByObjId($this->user_id);

				if($this->course_obj->getSubscriptionPassword() != $_POST["password"])
				{
					sendInfo($this->lng->txt("crs_password_not_valid"),true);
					$this->showRegistrationForm();
				}
				else if($this->course_obj->members_obj->add($tmp_obj,$this->course_obj->members_obj->ROLE_MEMBER))
				{
					$this->course_obj->members_obj->sendNotification($this->course_obj->members_obj->NOTIFY_ADMINS,$this->user_id);
					ilObjUser::updateActiveRoles($this->user_id);
					sendInfo($this->lng->txt("crs_subscription_successful"),true);

					ilUtil::redirect('repository.php?ref_id='.$this->tree->getParentId($this->course_id));
				}
				else
				{
					sendInfo("err_unknown_error");
					$this->showRegistrationForm();
				}
				break;
		}
	}

	function showRegistrationForm()
	{
		$really_submit = $this->__validateStatus();

		if($this->course_obj->getMessage())
		{
			sendInfo($this->course_obj->getMessage());
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_subscription.html","course");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass("ilObjCourseGUI"));
		
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_crs.gif"));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_crs"));
		$this->tpl->setVariable("TITLE",$this->lng->txt("crs_registration"));

		$this->tpl->setVariable("TXT_SYLLABUS",$this->lng->txt("crs_syllabus"));
		$this->tpl->setVariable("SYLLABUS",nl2br($this->course_obj->getSyllabus()));

		$this->tpl->setVariable("TXT_INFO_REG",$this->lng->txt("crs_info_reg"));


		// Waiting list
		if($this->course_obj->getSubscriptionMaxMembers())
		{
			$this->tpl->setCurrentBlock("waiting_list");
			$this->tpl->setVariable("TXT_WAITING_LIST",$this->lng->txt('crs_free_places'));
			$free_places = $this->course_obj->getSubscriptionMaxMembers() - $this->course_obj->members_obj->getCountMembers();
			$this->tpl->setVariable("FREE_PLACES",$free_places);
			$this->tpl->parseCurrentBlock();

			if($this->waiting_list->isOnList($this->user_id))
			{
				$this->tpl->setCurrentBlock("waiting_list_info");
				$this->tpl->setVariable("TXT_WAITING_LIST_INFO",$this->lng->txt('crs_youre_position'));
				$this->tpl->setVariable("POSITION",$this->waiting_list->getPosition($this->user_id));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("waiting_list_info");
				$this->tpl->setVariable("TXT_WAITING_LIST_INFO",$this->lng->txt('crs_persons_on_waiting_list'));
				$this->tpl->setVariable("POSITION",$this->waiting_list->getCountUsers());
				$this->tpl->parseCurrentBlock();
			}

		}

		if($courses = $this->__getGroupingCourses())
		{
			$this->tpl->setVariable("INFO_REG_PRE",$this->lng->txt('crs_grp_info_reg').$courses.'<br>');
		}

		switch($this->course_obj->getSubscriptionType())
		{
			case $this->course_obj->SUBSCRIPTION_DEACTIVATED:
				$this->tpl->setVariable("INFO_REG",$this->lng->txt("crs_info_reg_deactivated"));
				break;
			case $this->course_obj->SUBSCRIPTION_CONFIRMATION:
				$this->tpl->setVariable("INFO_REG",$this->lng->txt("crs_info_reg_confirmation"));
				break;
			case $this->course_obj->SUBSCRIPTION_DIRECT:
				$this->tpl->setVariable("INFO_REG",$this->lng->txt("crs_info_reg_direct"));
				break;
			case $this->course_obj->SUBSCRIPTION_PASSWORD:
				$this->tpl->setVariable("INFO_REG",$this->lng->txt("crs_info_reg_password"));
				break;
		}

		if($this->course_obj->getSubscriptionType() != $this->course_obj->SUBSCRIPTION_DEACTIVATED)
		{
			$this->tpl->setCurrentBlock("reg_until");
			$this->tpl->setVariable("TXT_REG_UNTIL",$this->lng->txt("crs_reg_until"));

			if($this->course_obj->getSubscriptionUnlimitedStatus())
			{
				$this->tpl->setVariable("REG_UNTIL",$this->lng->txt("crs_unlimited"));
			}
			else if($this->course_obj->getSubscriptionStart() < time())
			{
				$this->tpl->setVariable("FROM",$this->lng->txt("crs_to"));
				$this->tpl->setVariable("REG_UNTIL",strftime("%c",$this->course_obj->getSubscriptionEnd()));
			}
			else if($this->course_obj->getSubscriptionStart() > time())
			{
				$this->tpl->setVariable("FROM",$this->lng->txt("crs_from"));
				$this->tpl->setVariable("REG_UNTIL",strftime("%c",$this->course_obj->getSubscriptionStart()));
			}
			$this->tpl->parseCurrentBlock();
		}

		if($this->course_obj->getSubscriptionType() == $this->course_obj->SUBSCRIPTION_PASSWORD and
		   $this->course_obj->inSubscriptionTime())
		{
			$this->tpl->setCurrentBlock("pass");
			$this->tpl->setVariable("TXT_PASSWORD",$this->lng->txt("crs_access_password"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));

		if($really_submit)
		{
			$this->tpl->setCurrentBlock("go");
			$this->tpl->setVariable("CMD_SUBMIT","subscribe");
			$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("register"));
			$this->tpl->parseCurrentBlock();
		}
			

		return true;
	}


	// PRIVATE
	function __initCourseObject()
	{
		if(!$this->course_obj =& ilObjectFactory::getInstanceByRefId($this->course_id,false))
		{
			$this->ilErr->raiseError("ilCourseRegisterGUI: cannot create course object",$this->ilErr->MESSAGE);
			exit;
		}
		$this->course_obj->initCourseMemberObject();

		return true;
	}

	function __initWaitingList()
	{
		include_once 'course/classes/class.ilCourseWaitingList.php';

		$this->waiting_list =& new ilCourseWaitingList($this->course_obj->getId());

		return true;
	}

	function __validateStatus()
	{
		$allow_subscription = true;

		$this->course_obj->setMessage('');

		if($this->course_obj->members_obj->isAssigned($this->user_id))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_user_already_assigned"));
			$allow_subscription = false;
		}
		if($this->course_obj->members_obj->isBlocked($this->user_id))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_user_blocked"));
			$allow_subscription = false;
		}
		if($this->course_obj->members_obj->isSubscriber($this->user_id))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_user_already_subscribed"));
			$allow_subscription = false;
		}
		if($this->course_obj->getSubscriptionType() == $this->course_obj->SUBSCRIPTION_DEACTIVATED)
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_subscription_deactivated"));
			$allow_subscription = false;

			return false;
		}
		if(!$this->course_obj->getSubscriptionUnlimitedStatus() and
		   ( time() < $this->course_obj->getSubscriptionStart()))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_subscription_start_later"));
			$allow_subscription = false;
		}
		if(!$this->course_obj->getSubscriptionUnlimitedStatus() and
		   ( time() > $this->course_obj->getSubscriptionEnd()))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_subscription_end_earlier"));
			$allow_subscription = false;
		}
		if($this->waiting_list->isOnList($this->user_id))
		{
			$this->course_obj->appendMessage($this->lng->txt('crs_already_assigned_to_list'));
			$allow_subscription = false;
		}
		elseif($this->course_obj->getSubscriptionMaxMembers() and 
		   ($this->course_obj->members_obj->getCountMembers() >= $this->course_obj->getSubscriptionMaxMembers()))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_reg_subscription_max_members_reached"));
			$this->course_obj->appendMessage($this->lng->txt('crs_set_on_waiting_list'));
		}
		if(!$this->__checkGroupingDependencies())
		{
			$allow_subscription = false;
		}

		return $allow_subscription;
	}
	function __checkGroupingDependencies()
	{
		global $ilUser;

		include_once './classes/class.ilConditionHandler.php';
		include_once './course/classes/class.ilCourseMembers.php';

		$trigger_ids = array();
		foreach(ilConditionHandler::_getConditionsOfTarget($this->course_obj->getId(),'crs') as $condition)
		{
			if($condition['operator'] == 'not_member')
			{
				$trigger_ids[] = $condition['trigger_obj_id'];
				break;
			}
		}
		if(!count($trigger_ids))
		{
			return true;
		}

		foreach($trigger_ids as $trigger_id)
		{
			foreach(ilConditionHandler::_getConditionsOfTrigger('crsg',$trigger_id) as $condition)
			{
				if($condition['operator'] == 'not_member')
				{
					switch($condition['value'])
					{
						case 'matriculation':
							if(!strlen($ilUser->getMatriculation()))
							{
								if(!$matriculation_message)
								{
									$matriculation_message = $this->lng->txt('crs_grp_matriculation_required');
								}
							}
					}
					if(ilCourseMembers::_isMember($ilUser->getId(),$condition['target_obj_id'],$condition['value']))
					{
						if(!$assigned_message)
						{
							$assigned_message = $this->lng->txt('crs_grp_already_assigned');
						}
					}
				}
			}
		}
		if($matriculation_message)
		{
			$this->course_obj->appendMessage($matriculation_message);
			return false;
		}
		elseif($assigned_message)
		{
			$this->course_obj->appendMessage($assigned_message);
			return false;
		}
		return true;
	}
	function __getGroupingCourses()
	{
		global $tree;

		include_once './classes/class.ilConditionHandler.php';
		include_once './course/classes/class.ilCourseMembers.php';

		$trigger_ids = array();
		foreach(ilConditionHandler::_getConditionsOfTarget($this->course_obj->getId(),'crs') as $condition)
		{
			if($condition['operator'] == 'not_member')
			{
				$trigger_ids[] = $condition['trigger_obj_id'];
			}
		}
		if(!count($trigger_ids))
		{
			return false;
		}
		foreach($trigger_ids as $trigger_id)
		{
			foreach(ilConditionHandler::_getConditionsOfTrigger('crsg',$trigger_id) as $condition)
			{
				if($condition['operator'] == 'not_member')
				{
					if(!$hash_table[$condition['target_ref_id']])
					{
						$tmp_obj =& ilObjectFactory::getInstanceByRefId($condition['target_ref_id']);
						$courses .= (' <br/>'.$this->__formatPath($tree->getPathFull($tmp_obj->getRefId())));
					}
					$hash_table[$condition['target_ref_id']] = true;
				}
			}
		}
		return $courses;
	}

	function __formatPath($a_path_arr)
	{
		$counter = 0;
		foreach($a_path_arr as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}

		if(strlen($path) > 40)
		{
			return '...'.substr($path,-40);
		}
		return $path;
	}
}
?>