<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission team
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * 
 * @ilCtrl_Calls ilExSubmissionTeamGUI: ilRepositorySearchGUI
 * @ingroup ModulesExercise
 */
class ilExSubmissionTeamGUI
{
	protected $exercise_id; // [int]
	protected $exercise; // [ilObjExercise]
	protected $assignment; // [ilExAssignment]
	protected $participant_id; // [int]
	
	public function __construct(ilObjExercise $a_exercise, ilExAssignment $a_ass, $a_participant_id = null)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl, $ilUser;
		
		if(!$a_participant_id)
		{
			$a_participant_id = $ilUser->getId();
		}
		
		$this->exercise_id = $a_ass->getExerciseId();
		$this->exercise = $a_exercise;
		$this->assignment = $a_ass;
		$this->participant_id = $a_participant_id;				
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;		
	}
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng, $ilUser, $ilCtrl;
		
		$no_team_yet = false;			
		
		if($a_ass->hasTeam())
		{						
			$team_members = ilExAssignment::getTeamMembersByAssignmentId($a_ass->getId(), $ilUser->getId());
			if(sizeof($team_members))
			{
				$team = array();						
				foreach($team_members as $member_id)
				{
					$team[] = ilObjUser::_lookupFullname($member_id);
				}						
				$team = implode(", ", $team);

				$button = ilLinkButton::getInstance();							
				$button->setCaption("exc_manage_team");
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "submissionScreenTeam"));							
				$team .= " ".$button->render();	

				$a_info->addProperty($lng->txt("exc_team_members"), $team);	
			}
			else
			{
				$no_team_yet = true;

				if($a_ass->beforeDeadline())
				{
					if(!sizeof($delivered_files))
					{
						 $team_info = $lng->txt("exc_no_team_yet_notice");								
					}
					else
					{
						$team_info = '<span class="warning">'.$lng->txt("exc_no_team_yet_notice").'</span>';		
					}	

					$button = ilLinkButton::getInstance();
					$button->setPrimary(true);
					$button->setCaption("exc_create_team");
					$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "createTeam"));							
					$team_info .= " ".$button->render();		

					$team_info .= '<div class="ilFormInfo">'.$lng->txt("exc_no_team_yet_info").'</div>';
				}
				else
				{
					$team_info = '<span class="warning">'.$lng->txt("exc_create_team_times_up_warning").'</span>';
				}

				$a_info->addProperty($lng->txt("exc_team_members"), $team_info);
			}
		}
		
		return $no_team_yet;
	}
	
	
	//
	// TEAM
	//
	
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("submissionScreenTeam");		
		
		switch($class)
		{		
			case 'ilrepositorysearchgui':	
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();		
				$rep_search->setTitle($this->lng->txt("exc_team_member_add"));
				$rep_search->setCallback($this,'addTeamMemberActionObject');

				// Set tabs
				$this->initTeamSubmission("submissionScreenTeam");
				$this->ctrl->setReturn($this,'submissionScreenTeam');
				
				$this->ctrl->forwardCommand($rep_search);
				break;
							
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	function returnToParentObject()
	{
		$this->ctrl->returnToParent($this);
	}
	
	public static function handleTabs()
	{		
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->addTab("team", $lng->txt("exc_team"), 
			$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "submissionScreenTeam"));

		$ilTabs->addTab("log", $lng->txt("exc_team_log"), 
			$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "submissionScreenTeamLog"));
	}
	
	protected function initTeamSubmission()
	{
		global $ilUser;
		
		self::handleTabs();
		$this->tabs_gui->activateTab("team");		

		$team_id = $this->assignment->getTeamId($ilUser->getId());

		if(!$team_id)
		{
			$team_id = $this->assignment->getTeamId($ilUser->getId(), true);

			// #12337
			if (!$this->exercise->members_obj->isAssigned($ilUser->getId()))
			{
				$this->exercise->members_obj->assignMember($ilUser->getId());
			}				
		}

		return $team_id;		
	}
	
	/**
	* Displays a form which allows members to manage team uploads
	*
	* @access public
	*/
	function submissionScreenTeamObject()
	{
		global $ilToolbar;
		
		$team_id = $this->initTeamSubmission();
						
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		// #13414
		$read_only = (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0));
				
		if ($read_only)
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		else
		{					
			$this->ctrl->setParameterByClass('ilRepositorySearchGUI', 'ctx', 1);
			$this->ctrl->setParameter($this, 'ctx', 1);
			
			// add member
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$ilToolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add'),
					'add_search'			=> true,
					'add_from_container'    => $this->exercise->getRefId()		
				)
			);
	 	}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamTableGUI.php";
		$tbl = new ilExAssignmentTeamTableGUI($this, "submissionScreenTeam",
			ilExAssignmentTeamTableGUI::MODE_EDIT, $team_id, $this->assignment, null, $read_only);
		
		$this->tpl->setContent($tbl->getHTML());				
	}
	
	public function addTeamMemberActionObject($a_user_ids = array())
	{		
		global $ilUser;
		
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$has_files = $this->assignment->getDeliveredFiles($this->exercise->getId(), 
			$this->assignment->getId(), 
			$ilUser->getId());
		$all_members = $this->assignment->getMembersOfAllTeams();
		$members = $this->assignment->getTeamMembers($team_id);
		
		foreach($a_user_ids as $user_id)
		{
			if(!in_array($user_id, $all_members))
			{
				$this->assignment->addTeamMember($team_id, $user_id, $this->ref_id);
				
				// #14277
				if (!$this->exercise->members_obj->isAssigned($user_id))
				{
					$this->exercise->members_obj->assignMember($user_id);
				}

				// see ilObjExercise::deliverFile()
				if($has_files)
				{					
					ilExAssignment::updateStatusReturnedForUser($this->assignment->getId(), $user_id, 1);
					ilExerciseMembers::_writeReturned($this->exercise->getId(), $user_id, 1);
				}

				// :TODO: log, notification
			}
			else if(!in_array($user_id, $members))
			{
				ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"), true);
			}
		}

		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "submissionScreenTeam");
	}
	
	public function confirmRemoveTeamMemberObject()
	{
		global $ilUser, $tpl;
		
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($this->lng->txt("select_one"), true);
			$this->ctrl->redirect($this, "submissionScreenTeam");
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$members = $this->assignment->getTeamMembers($team_id);
		
		$team_deleted = false;
		if(sizeof($members) <= sizeof($ids))
		{
			if(sizeof($members) == 1 && $members[0] == $ilUser->getId())
			{
				// direct team deletion - no confirmation
				return $this->removeTeamMemberObject();
			}						
			else
			{
				ilUtil::sendFailure($this->lng->txt("exc_team_at_least_one"), true);
				$this->ctrl->redirect($this, "submissionScreenTeam");
			}
		}
		
		// #11957
		
		$team_id = $this->initTeamSubmission();
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("exc_team_member_remove_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "submissionScreenTeam");
		$cgui->setConfirm($this->lng->txt("remove"), "removeTeamMember");

		$files = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), 
			$this->assignment->getId(), $ilUser->getId());
		
		include_once "Services/User/classes/class.ilUserUtil.php";
		
		foreach($ids as $id)
		{
			$details = array();
			foreach ($files as $file)
			{				
				if($file["owner_id"] == $id)
				{
					$details[] = $file["filetitle"];
				}							
			}
			$uname = ilUserUtil::getNamePresentation($id);
			if(sizeof($details))
			{
				$uname .= ": ".implode(", ", $details);
			}
			$cgui->addItem("id[]", $id, $uname);
		}

		$tpl->setContent($cgui->getHTML());		
	}
	
	public function removeTeamMemberObject()
	{
		global $ilUser;
		
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($this->lng->txt("select_one"), true);
			$this->ctrl->redirect($this, "submissionScreenTeam");
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$members = $this->assignment->getTeamMembers($team_id);
		
		$team_deleted = false;
		if(sizeof($members) <= sizeof($ids))
		{
			if(sizeof($members) == 1 && $members[0] == $ilUser->getId())
			{
				$team_deleted = true;
			}						
			else
			{
				ilUtil::sendFailure($this->lng->txt("exc_team_at_least_one"), true);
				$this->ctrl->redirect($this, "submissionScreenTeam");
			}
		}
		
		foreach($ids as $user_id)
		{
			$this->assignment->removeTeamMember($team_id, $user_id, $this->ref_id);		
			
			ilExAssignment::updateStatusReturnedForUser($this->assignment->getId(), $user_id, 0);
			ilExerciseMembers::_writeReturned($this->exercise->getId(), $user_id, 0);
			
			// :TODO: log, notification
		}
				
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		
		if(!$team_deleted)
		{
			$this->ctrl->redirect($this, "submissionScreenTeam");		
		}
		else
		{
			$this->ctrl->redirect($this, "returnToParent");	
		}		
	}
	
	function submissionScreenTeamLogObject()
	{
		$team_id = $this->initTeamSubmission();
		$this->tabs_gui->activateTab("log");
	
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamLogTableGUI.php";
		$tbl = new ilExAssignmentTeamLogTableGUI($this, "submissionScreenTeamLog",
			$team_id);
		
		$this->tpl->setContent($tbl->getHTML());						
	}
	
	function createSingleMemberTeamObject()
	{
		if(isset($_GET["lmem"]))
		{				
			$user_id = $_GET["lmem"];
			$cmd = "members";												
		}	
		else
		{
			$user_id = $_GET["lpart"];
			$cmd = "showParticipant";		
		}
		if($user_id)
		{
			$this->assignment->getTeamId($user_id, true);		
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
		$this->ctrl->redirect($this, $cmd);	
	}			
	
	function showTeamLogObject()
	{		
		$this->checkPermission("write");								
		$this->tabs_gui->activateTab("grades");	
						
		if(isset($_GET["lmem"]))
		{					
			$this->addSubmissionSubTabs("assignment");
			
			$this->tabs_gui->setBackTarget($this->lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "members"));
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->assignment->getId(), (int)$_GET["lmem"]);
			
			$this->ctrl->saveParameter($this, "lmem");
		}
		else
		{
			$this->addSubmissionSubTabs("participant");
			
			$this->tabs_gui->setBackTarget($this->lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "showParticipant"));
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->assignment->getId(), (int)$_GET["lpart"]);
			
			$this->ctrl->saveParameter($this, "lpart");
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamLogTableGUI.php";
		$tbl = new ilExAssignmentTeamLogTableGUI($this, "showTeamLog",
			$team_id);
		
		$this->tpl->setContent($tbl->getHTML());						
	}
		
	public function createTeamObject()
	{		
		global $ilCtrl, $ilUser, $ilTabs, $lng, $tpl;
		
		if($this->assignment->getDeadline() == 0 ||
			mktime() < $this->assignment->getDeadline())
		{			
			$options = ilExAssignment::getAdoptableTeamAssignments($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
			if(sizeof($options))
			{								
				$ilTabs->activateTab("content");
				$this->addContentSubTabs("content");
	
				include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
				$form = new ilPropertyFormGUI();		         
				$form->setTitle($lng->txt("exc_team_assignment_adopt_user"));
				$form->setFormAction($ilCtrl->getFormAction($this, "createAdoptedTeam"));


				$teams = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
				$teams->setValue(-1);

				$teams->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none_user"), -1));
				
				$current_map = ilExAssignment::getAssignmentTeamMap($this->assignment->getId());

				include_once "Services/User/classes/class.ilUserUtil.php";
				foreach($options as $id => $item)
				{
					$members = array();
					$free = false;
					foreach($item["user_team"] as $user_id)
					{
						$members[$user_id] = ilUserUtil::getNamePresentation($user_id);
						
						if(array_key_exists($user_id, $current_map))
						{
							$members[$user_id] .= " (".$lng->txt("exc_team_assignment_adopt_already_assigned").")";
						}
						else
						{
							$free = true;
						}
					}
					asort($members);
					$members = implode("<br />", $members);
					$option = new ilRadioOption($item["title"], $id);
					$option->setInfo($members);
					if(!$free)
					{
						$option->setDisabled(true);
					}
					$teams->addOption($option);
				}

				$form->addItem($teams);

				$form->addCommandButton("createAdoptedTeam", $lng->txt("save"));
				$form->addCommandButton("returnToParent", $lng->txt("cancel"));

				$tpl->setContent($form->getHTML());
				return;
			}			
			
			$this->assignment->getTeamId($ilUser->getId(), true);		
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);	
		}
		
		$ilCtrl->redirect($this, "returnToParent");
	}
	
	public function createAdoptedTeamObject()
	{
		global $ilCtrl, $ilUser, $lng;
		
		if($this->assignment->getDeadline() == 0 ||
			mktime() < $this->assignment->getDeadline())
		{	
			$src_ass_id = (int)$_POST["ass_adpt"];
			if($src_ass_id > 0)
			{
				$this->assignment->adoptTeams($src_ass_id, $ilUser->getId(), $this->ref_id);						
			}
			else
			{
				$this->assignment->getTeamId($ilUser->getId(), true);		
			}
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		
		$ilCtrl->redirect($this, "returnToParent");
	}	
	
	
	/**
	* Add user as member
	*/
	public function addUserFromAutoCompleteObject()
	{		
		if(!strlen(trim($_POST['user_login'])))
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
			$this->membersObject();
			return false;
		}
		$users = explode(',', $_POST['user_login']);

		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);

			if(!$user_id)
			{
				ilUtil::sendFailure($this->lng->txt('user_not_known'));								
				return $this->submissionScreenTeamObject();				
			}
			
			$user_ids[] = $user_id;
		}
	
		return $this->addTeamMemberActionObject($user_ids);								
	}
}	

