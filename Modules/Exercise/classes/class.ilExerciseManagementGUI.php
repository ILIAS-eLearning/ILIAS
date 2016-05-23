<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExSubmission.php";
include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";

/**
* Class ilExerciseManagementGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExerciseManagementGUI: ilFileSystemGUI, ilRepositorySearchGUI
* @ilCtrl_Calls ilExerciseManagementGUI: ilExSubmissionTeamGUI, ilExSubmissionFileGUI
* @ilCtrl_Calls ilExerciseManagementGUI: ilExSubmissionTextGUI, ilExPeerReviewGUI
* 
* @ingroup ModulesExercise
*/
class ilExerciseManagementGUI
{
	protected $exercise; // [ilObjExercise]
	protected $assignment; // [ilExAssignment]
	
	const VIEW_ASSIGNMENT = 1;
	const VIEW_PARTICIPANT = 2;	
	const VIEW_GRADES = 3;
	
	/**
	 * Constructor
	 * 
	 * @param int $a_exercise_id
	 * @return object
	 */
	public function __construct(ilObjExercise $a_exercise, ilExAssignment $a_ass = null)
	{		
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$this->exercise = $a_exercise;
		$this->assignment = $a_ass;
		
		$ilCtrl->saveParameter($this, array("vw", "member_id"));
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;				
	}
	
	public function executeCommand()
	{
		global $ilCtrl, $lng, $ilTabs;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listPublicSubmissions");		
		
		switch($class)
		{			
			case "ilfilesystemgui":							
				$ilTabs->clearTargets();				
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, $this->getViewBack()));
				
				ilUtil::sendInfo($lng->txt("exc_fb_tutor_info"));

				include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
				$fstorage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
				$fstorage->create();
								
				$submission = new ilExSubmission($this->assignment, (int)$_GET["member_id"]);
				$feedback_id = $submission->getFeedbackId();
				$noti_rec_ids = $submission->getUserIds();
				
				include_once("./Services/User/classes/class.ilUserUtil.php");																	
				$fs_title = array();
				foreach($noti_rec_ids as $rec_id)
				{
					$fs_title[] = ilUserUtil::getNamePresentation($rec_id, false, false, "", true);
				}
				$fs_title = implode(" / ", $fs_title);
					
				include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
				$fs_gui = new ilFileSystemGUI($fstorage->getFeedbackPath($feedback_id));
				$fs_gui->setTableId("excfbfil".$this->assignment->getId()."_".$feedback_id);
				$fs_gui->setAllowDirectories(false);					
				$fs_gui->setTitle($lng->txt("exc_fb_files")." - ".
					$this->assignment->getTitle()." - ".
					$fs_title);
				$pcommand = $fs_gui->getLastPerformedCommand();					
				if (is_array($pcommand) && $pcommand["cmd"] == "create_file")
				{
					$this->exercise->sendFeedbackFileNotification($pcommand["name"], 
						$noti_rec_ids, $this->assignment->getId());
				}					 
				$this->ctrl->forwardCommand($fs_gui);
				break;
				
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();			
				$rep_search->setTitle($this->lng->txt("exc_add_participant"));
				$rep_search->setCallback($this,'addMembersObject');

				// Set tabs
				$this->addSubTabs("assignment");
				$this->ctrl->setReturn($this,'members');
				
				$this->ctrl->forwardCommand($rep_search);
				break;
			
			case "ilexsubmissionteamgui":										
				include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
				$gui = new ilExSubmissionTeamGUI($this->exercise, $this->initSubmission());
				$ilCtrl->forwardCommand($gui);				
				break;		
				
			case "ilexsubmissionfilegui":													
				include_once "Modules/Exercise/classes/class.ilExSubmissionFileGUI.php";
				$gui = new ilExSubmissionFileGUI($this->exercise, $this->initSubmission());
				$ilCtrl->forwardCommand($gui);				
				break;
				
			case "ilexsubmissiontextgui":															
				include_once "Modules/Exercise/classes/class.ilExSubmissionTextGUI.php";
				$gui = new ilExSubmissionTextGUI($this->exercise, $this->initSubmission());
				$ilCtrl->forwardCommand($gui);				
				break;
			
			case "ilexpeerreviewgui":															
				include_once "Modules/Exercise/classes/class.ilExPeerReviewGUI.php";
				$gui = new ilExPeerReviewGUI($this->assignment, $this->initSubmission());
				$ilCtrl->forwardCommand($gui);				
				break;
			
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	protected function getViewBack()
	{
		switch($_REQUEST["vw"])
		{			
			case self::VIEW_PARTICIPANT:
				$back_cmd = "showParticipant";
				break;
			
			case self::VIEW_GRADES:
				$back_cmd = "showGradesOverview";
				break;
			
			default:
			// case self::VIEW_ASSIGNMENT:
				$back_cmd = "members";
				break;			
 		}
		return $back_cmd;
	}
	
	protected function initSubmission()
	{
		$back_cmd = $this->getViewBack();
		$this->ctrl->setReturn($this, $back_cmd);
		
		$this->tabs_gui->clearTargets();		
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
			$this->ctrl->getLinkTarget($this, $back_cmd));	

		include_once "Modules/Exercise/classes/class.ilExSubmission.php";
		return new ilExSubmission($this->assignment, $_REQUEST["member_id"], null, true);		
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function addSubTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		$ilCtrl->setParameter($this, "vw", "");
		$ilCtrl->setParameter($this, "member_id", "");
		$ilTabs->addSubTab("assignment", $lng->txt("exc_assignment_view"),
			$ilCtrl->getLinkTarget($this, "members"));	
		$ilTabs->addSubTab("participant", $lng->txt("exc_participant_view"),
			$ilCtrl->getLinkTarget($this, "showParticipant"));		
		$ilTabs->addSubTab("grades", $lng->txt("exc_grades_overview"),
			$ilCtrl->getLinkTarget($this, "showGradesOverview"));
		$ilTabs->activateSubTab($a_activate);
	}
	
	/**
	 * All participants and submission of one assignment
	 */
	function membersObject()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng;

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
	
		$this->addSubTabs("assignment");
		
		// assignment selection
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getInstancesByExercise($this->exercise->getId());
		
		if (!$this->assignment)
		{
			$this->assignment = current($ass);				
		}
		
		reset($ass);
		if (count($ass) > 1)
		{
			$options = array();
			foreach ($ass as $a)
			{
				$options[$a->getId()] = $a->getTitle();
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->lng->txt(""), "ass_id");
			$si->setOptions($options);
			$si->setValue($this->assignment->getId());
			$ilToolbar->addStickyItem($si);
					
			include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
			$button = ilSubmitButton::getInstance();
			$button->setCaption("exc_select_ass");
			$button->setCommand("selectAssignment");			
			$ilToolbar->addStickyItem($button);
			
			$ilToolbar->addSeparator();
		}
		// #16165 - if only 1 assignment dropdown is not displayed;
		else if($this->assignment)
		{
			$ilCtrl->setParameter($this, "ass_id", $this->assignment->getId());
		}
		
		// add member
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('add'),
				'add_search'			=> true,
				'add_from_container'    => $this->exercise->getRefId()
			)
		);
		
		// #16168 - no assignments
		if (count($ass) > 0)
		{	
			$ilToolbar->addSeparator();

			// we do not want the ilRepositorySearchGUI form action		
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

			$ilCtrl->setParameter($this, "ass_id", $this->assignment->getId());

			if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");
				if(ilExAssignmentTeam::getAdoptableGroups($this->exercise->getRefId()))
				{
					// multi-feedback
					$ilToolbar->addButton($this->lng->txt("exc_adopt_group_teams"),
						$this->ctrl->getLinkTarget($this, "adoptTeamsFromGroup"));
				}
			}		
			else
			{	
				// multi-feedback
				$ilToolbar->addButton($this->lng->txt("exc_multi_feedback"),
					$this->ctrl->getLinkTarget($this, "showMultiFeedback"));
			}
								
			if(ilExSubmission::hasAnySubmissions($this->assignment->getId()))
			{
				$ilToolbar->addSeparator();			
				if($this->assignment->getType() == ilExAssignment::TYPE_TEXT)
				{
					$ilToolbar->addFormButton($lng->txt("exc_list_text_assignment"), "listTextAssignment");					
				}		
				else 
				{			
					$ilToolbar->addFormButton($lng->txt("download_all_returned_files"), "downloadAll");			
				}		
			}
			$this->ctrl->setParameter($this, "vw", self::VIEW_ASSIGNMENT);
			
			include_once("./Modules/Exercise/classes/class.ilExerciseMemberTableGUI.php");
			$exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->exercise, $this->assignment);
			$tpl->setContent($exc_tab->getHTML());
		}
		else
		{
			ilUtil::sendInfo($lng->txt("exc_no_assignments_available"));
		}
		
		$ilCtrl->setParameter($this, "ass_id", "");

		return;		
	}
	
	

	
	/**
	 * Save grades
	 */
	function saveGradesObject()
	{
		global $ilCtrl, $lng;
				
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		
		if (is_array($_POST["lcomment"]))
		{
			foreach ($_POST["lcomment"] as $k => $v)
			{
				$marks_obj = new ilLPMarks($this->exercise->getId(), (int) $k);
				$marks_obj->setComment(ilUtil::stripSlashes($v));
				$marks_obj->setMark(ilUtil::stripSlashes($_POST["mark"][$k]));
				$marks_obj->update();
			}
		}
		ilUtil::sendSuccess($lng->txt("exc_msg_saved_grades"), true);
		$ilCtrl->redirect($this, "showGradesOverview");
	}
	
	
	// TEXT ASSIGNMENT ?!
	
	function listTextAssignmentWithPeerReviewObject()
	{
		$this->listTextAssignmentObject(true);
	}
	
	function listTextAssignmentObject($a_show_peer_review = false)
	{
		global $tpl, $ilCtrl, $ilTabs, $lng;
				
		if(!$this->assignment || $this->assignment->getType() != ilExAssignment::TYPE_TEXT)
		{
			$ilCtrl->redirect($this, "members");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "members"));
		
		if($a_show_peer_review)
		{
			$cmd = "listTextAssignmentWithPeerReview";
		}
		else
		{
			$cmd = "listTextAssignment";
		}
		include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
		$tbl = new ilExAssignmentListTextTableGUI($this, $cmd, $this->assignment, $a_show_peer_review);		
		$tpl->setContent($tbl->getHTML());		
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
				return $this->membersObject();		
			}
			
			$user_ids[] = $user_id;
		}

		if(!$this->addMembersObject($user_ids));
		{
			$this->membersObject();
			return false;
		}
		return true;
	}

	/**
	 * Add new partipant
	 */
	function addMembersObject($a_user_ids = array())
	{
		global $ilAccess,$ilErr;

		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}

		if(!$this->exercise->members_obj->assignMembers($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"));
			return false;
		}
		else
		{
			/* #16921
			// #9946 - create team for new user(s) for each team upload assignment
			foreach(ilExAssignment::getInstancesByExercise($this->exercise->getId()) as $ass)
			{
				if($ass->hasTeam())
				{
					include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";									
					foreach($a_user_ids as $user_id)
					{						
						// #15915
						ilExAssignmentTeam::getTeamId($ass->getId(), $user_id, true);
					}
				}
			}						
			*/ 
			
			ilUtil::sendSuccess($this->lng->txt("exc_members_assigned"),true);
		}

		$this->ctrl->redirect($this, "members");
		return true;
	}
	

	/**
	 * Select assignment
	 */
	function selectAssignmentObject()
	{
		global $ilTabs;

		$_GET["ass_id"] = ilUtil::stripSlashes($_POST["ass_id"]);
		$this->membersObject();
	}
	
	/**
	 * Show Participant
	 */
	function showParticipantObject()
	{
		global $rbacsystem, $tree, $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;

		$this->addSubTabs("participant");
		
		// participant selection
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->exercise->getId());
		$members = $this->exercise->members_obj->getMembers();
		
		if (count($members) == 0)
		{
			ilUtil::sendInfo($lng->txt("exc_no_participants"));
			return;
		}
		
		$mems = array();
		foreach ($members as $mem_id)
		{
			if (ilObject::_lookupType($mem_id) == "usr")
			{
				include_once("./Services/User/classes/class.ilObjUser.php");
				$name = ilObjUser::_lookupName($mem_id);
				$mems[$mem_id] = $name;
			}
		}
		
		$mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);
		
		if ($_GET["part_id"] == "" && count($mems) > 0)
		{
			$_GET["part_id"] = key($mems);
		}
		
		$current_participant = $_GET["part_id"];
		
		reset($mems);
		if (count($mems) > 1)
		{
			$options = array();
			foreach ($mems as $k => $m)
			{
				$options[$k] =
					$m["lastname"].", ".$m["firstname"]." [".$m["login"]."]";
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->lng->txt(""), "part_id");
			$si->setOptions($options);
			$si->setValue($current_participant);
			$ilToolbar->addStickyItem($si);
			
			include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
			$button = ilSubmitButton::getInstance();
			$button->setCaption("exc_select_part");
			$button->setCommand("selectParticipant");			
			$ilToolbar->addStickyItem($button);
			
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));		
		}

		if (count($mems) > 0)
		{
			$this->ctrl->setParameter($this, "vw", self::VIEW_PARTICIPANT);
			$this->ctrl->setParameter($this, "part_id", $current_participant);
			
			include_once("./Modules/Exercise/classes/class.ilExParticipantTableGUI.php");
			$part_tab = new ilExParticipantTableGUI($this, "showParticipant",
				$this->exercise, $current_participant);
			$tpl->setContent($part_tab->getHTML());
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("exc_no_assignments_available"));
		}
	}
	
	/**
	 * Select participant
	 */
	function selectParticipantObject()
	{
		global $ilTabs;

		$_GET["part_id"] = ilUtil::stripSlashes($_POST["part_id"]);
		$this->showParticipantObject();
	}

	/**
	 * Show grades overview
	 */
	function showGradesOverviewObject()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng;
		
		$this->addSubTabs("grades");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$mem_obj = new ilExerciseMembers($this->exercise);
		$mems = $mem_obj->getMembers();

		if (count($mems) > 0)
		{
			$ilToolbar->addButton($lng->txt("exc_export_excel"),
				$ilCtrl->getLinkTarget($this, "exportExcel"));
		}
		
		$this->ctrl->setParameter($this, "vw", self::VIEW_GRADES);

		include_once("./Modules/Exercise/classes/class.ilExGradesTableGUI.php");
		$grades_tab = new ilExGradesTableGUI($this, "showGradesOverview",
			$this->exercise, $mem_obj);
		$tpl->setContent($grades_tab->getHTML()); 
	}

	/**
	* set feedback status for member and redirect to mail screen
	*/
	function redirectFeedbackMailObject()
	{		
		$members = array();
						
		if ($_GET["member_id"] != "")
		{				
			$submission = new ilExSubmission($this->assignment, $_GET["member_id"]);
			$members = $submission->getUserIds();				
		}
		else if($members = $this->getMultiActionUserIds())
		{						
			$members = array_keys($members);			
		}
		
		if($members)
		{
			$logins = array();
			foreach($members as $user_id)
			{				
				$member_status = $this->assignment->getMemberStatus($user_id);
				$member_status->setFeedback(true);
				$member_status->update();

				$logins[] = ilObjUser::_lookupLogin($user_id);
			}
			$logins = implode($logins, ",");
			
			// #16530 - see ilObjCourseGUI::createMailSignature
			$sig = chr(13).chr(10).chr(13).chr(10);
			$sig .= $this->lng->txt('exc_mail_permanent_link');
			$sig .= chr(13).chr(10).chr(13).chr(10);
			include_once './Services/Link/classes/class.ilLink.php';
			$sig .= ilLink::_getLink($this->exercise->getRefId());
			$sig = rawurlencode(base64_encode($sig));
						
			require_once 'Services/Mail/classes/class.ilMailFormCall.php';
			ilUtil::redirect(ilMailFormCall::getRedirectTarget(
				$this, 
				$this->getViewBack(), 
				array(), 
				array(
					'type' => 'new', 
					'rcp_to' => $logins, 
					ilMailFormCall::SIGNATURE_KEY => $sig
				)
			));
		}

		ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	* Download all submitted files (of all members).
	*/
	function downloadAllObject()
	{		
		$members = array();
		
		foreach($this->exercise->members_obj->getMembers() as $member_id)
		{
			$submission = new ilExSubmission($this->assignment, $member_id);
			$submission->updateTutorDownloadTime();
			
			// get member object (ilObjUser)
			if (ilObject::_exists($member_id))
			{				
				// adding file metadata
				foreach($submission->getFiles() as $file)
				{
					$members[$file["user_id"]]["files"][$file["returned_id"]] = $file;
				}			
			
				$tmp_obj =& ilObjectFactory::getInstanceByObjId($member_id);
				$members[$member_id]["name"] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
				unset($tmp_obj);
			}
		}
		
		ilExSubmission::downloadAllAssignmentFiles($this->assignment, $members);		
	}
	
	protected function getMultiActionUserIds($a_keep_teams = false)
	{				
		if (!is_array($_POST["member"]) || 
			count($_POST["member"]) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);			
		}
		else
		{						
			$members = array();
			foreach(array_keys($_POST["member"]) as $user_id)
			{					
				$submission = new ilExSubmission($this->assignment, $user_id);				
				$tmembers = $submission->getUserIds();
				if(!(bool)$a_keep_teams)
				{
					foreach($tmembers as $tuser_id)
					{
						$members[$tuser_id] = 1;
					}
				}
				else
				{
					if($tmembers)
					{
						$members[] = $tmembers;
					}
					else
					{
						// no team yet
						$members[] = $user_id;
					}
				}
			}		
			return $members;
		}
	}
			
	/**
	* Send assignment per mail to participants
	*/
	function sendMembersObject()
	{
		global $ilCtrl;
		
		$members = $this->getMultiActionUserIds();
		if(is_array($members))
		{
			$this->exercise->sendAssignment($this->assignment, $members);			
			ilUtil::sendSuccess($this->lng->txt("exc_sent"),true);
		}
		$ilCtrl->redirect($this, "members");
	}

	/**
	* Confirm deassigning members
	*/
	function confirmDeassignMembersObject()
	{
		global $ilCtrl, $tpl, $lng;
			
		$members = $this->getMultiActionUserIds();
		if(is_array($members))
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("exc_msg_sure_to_deassign_participant"));
			$cgui->setCancel($lng->txt("cancel"), "members");
			$cgui->setConfirm($lng->txt("remove"), "deassignMembers");
			
			include_once("./Services/User/classes/class.ilUserUtil.php");
			foreach ($members as $k => $m)
			{								
				$cgui->addItem("member[$k]", $m,
					ilUserUtil::getNamePresentation((int) $k, false, false, "", true));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Deassign members from exercise 
	 */
	function deassignMembersObject()
	{
		global $ilCtrl, $lng;
		
		$members = $this->getMultiActionUserIds();
		if($members)
		{		
			foreach(array_keys($members) as $usr_id)
			{
				$this->exercise->members_obj->deassignMember((int) $usr_id);
			}
			ilUtil::sendSuccess($lng->txt("exc_msg_participants_removed"), true);
		}
		$ilCtrl->redirect($this, "members");		
	}

	function saveCommentsObject() 
	{	
		if(!isset($_POST['comments_value']))
		{
			return;
		}
  
		$this->exercise->members_obj->setNoticeForMember($_GET["member_id"],
			ilUtil::stripSlashes($_POST["comments_value"]));
		ilUtil::sendSuccess($this->lng->txt("exc_members_comments_saved"));
		$this->membersObject();
	}


	/**
	 * Save assignment status (participant view)
	 */
	function saveStatusParticipantObject()
	{
		global $ilCtrl;
		
		$member_id = (int)$_GET["member_id"];
		$data = array();
		foreach(array_keys($_POST["id"]) as $ass_id)
		{
			$data[$ass_id][$member_id] = array(
				"status" => ilUtil::stripSlashes($_POST["status"][$ass_id])
				,"notice" => ilUtil::stripSlashes($_POST["notice"][$ass_id])			
				,"mark" => ilUtil::stripSlashes($_POST["mark"][$ass_id])
			);
		}
		
		$ilCtrl->setParameter($this, "part_id", $member_id); // #17629
		$this->saveStatus($data);
	}
	
	function saveStatusAllObject()
	{		
		$data = array();
		foreach(array_keys($_POST["id"]) as $user_id)
		{
			$data[-1][$user_id] = array(
				"status" => ilUtil::stripSlashes($_POST["status"][$user_id])
				,"notice" => ilUtil::stripSlashes($_POST["notice"][$user_id])			
				,"mark" => ilUtil::stripSlashes($_POST["mark"][$user_id])
			);
		}		
		$this->saveStatus($data);
	}
	
	function saveStatusSelectedObject()
	{		
		$members = $this->getMultiActionUserIds();
		if(!$members)
		{
			$this->ctrl->redirect($this, "members");
		}
		
		// #18408 - saveStatus() will rollout teams, we need raw (form) data here 
		$data = array();				
		foreach(array_keys($_POST["member"]) as $user_id)
		{
			$data[-1][$user_id] = array(
				"status" => ilUtil::stripSlashes($_POST["status"][$user_id])
				,"notice" => ilUtil::stripSlashes($_POST["notice"][$user_id])			
				,"mark" => ilUtil::stripSlashes($_POST["mark"][$user_id])
			);
		}				
		$this->saveStatus($data);
	}
	
	/**
	 * Save status of selecte members 
	 */
	protected function saveStatus(array $a_data)
	{
		global $ilCtrl;
				
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		$saved_for = array();		
		foreach($a_data as $ass_id => $users)
		{					
			$ass = ($ass_id < 0)
				? $this->assignment
				: new ilExAssignment($ass_id);
			
			foreach($users as $user_id => $values)
			{				
				// this will add team members if available
				$submission = new ilExSubmission($ass, $user_id);				
				foreach($submission->getUserIds() as $sub_user_id)
				{			
					$uname = ilObjUser::_lookupName($sub_user_id);
					$saved_for[$sub_user_id] = $uname["lastname"].", ".$uname["firstname"];					

					$member_status = $ass->getMemberStatus($sub_user_id);
					$member_status->setStatus($values["status"]);
					$member_status->setNotice($values["notice"]);			
					$member_status->setMark($values["mark"]);
					$member_status->update();	
				}
			}
		}
		
		if (count($saved_for) > 0)
		{
			$save_for_str = "(".implode($saved_for, " - ").")";
		}
		
		ilUtil::sendSuccess($this->lng->txt("exc_status_saved")." ".$save_for_str, true);		
		$ilCtrl->redirect($this, $this->getViewBack());	
	}

	/**
	 * Save comment for learner (asynch)
	 */
	function saveCommentForLearnersObject()
	{		
		$res = array("result"=>false);
		
		if($this->ctrl->isAsynch())
		{
			$ass_id = (int)$_POST["ass_id"];
			$user_id = (int)$_POST["mem_id"];
			$comment = trim($_POST["comm"]);
			
			if($ass_id && $user_id)
			{				
				$submission = new ilExSubmission($this->assignment, $user_id);
				$user_ids = $submission->getUserIds();
				
				$all_members = new ilExerciseMembers($this->exercise);
				$all_members = $all_members->getMembers();
				
				$reci_ids = array();
				foreach($user_ids as $user_id)
				{
					if(in_array($user_id, $all_members))
					{
						$member_status = $this->assignment->getMemberStatus($user_id);
						$member_status->setComment(ilUtil::stripSlashes($comment));
						$member_status->update();
						
						if(trim($comment))
						{
							$reci_ids[] = $user_id;
						}
					}
				}
				
				if(sizeof($reci_ids))
				{
					// send notification
					$this->exercise->sendFeedbackFileNotification(null, $reci_ids, 
						$ass_id, true);
				}
				
				$res = array("result"=>true, "snippet"=>ilUtil::shortenText($comment, 25, true));
			}						
		}				
		
		echo(json_encode($res));		
		exit();
	}	
		
	/**
	 * Export as excel
	 */
	function exportExcelObject()
	{
		$this->exercise->exportGradesExcel();
		exit;
	}
	
	
	//
	// TEAM
	//
	
	function createTeamsObject()
	{
		global $ilCtrl;
		
		$members = $this->getMultiActionUserIds(true);
		if($members)
		{			
			$new_members = array();
			
			include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
			foreach($members as $group)
			{
				if(is_array($group))
				{
					$new_members = array_merge($new_members, $group);
					
					$first_user = $group;
					$first_user = array_shift($first_user);
					$team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user);	
					foreach($group as $user_id)
					{
						$team->removeTeamMember($user_id);
					}
				}
				else
				{
					$new_members[] = $group;
				}
			}
			
			if(sizeof($new_members))
			{
				// see ilExSubmissionTeamGUI::addTeamMemberActionObject()
				
				$first_user = array_shift($new_members);
				$team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user, true);
				if(sizeof($new_members))
				{
					foreach($new_members as $user_id)
					{
						$team->addTeamMember($user_id);
					}
				}
				
				// re-evaluate complete team, as some members might have had submitted				
				$submission = new ilExSubmission($this->assignment, $first_user);				
				$this->exercise->processExerciseStatus(
					$this->assignment,
					$team->getMembers(),
					$submission->hasSubmitted(),
					$submission->validatePeerReviews()
				);	
			}
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "members");					
	}
	
	function dissolveTeamsObject()
	{
		global $ilCtrl;
		
		$members = $this->getMultiActionUserIds(true);
		if($members)
		{					
			include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
			foreach($members as $group)
			{
				// if single member - nothing to do
				if(is_array($group))
				{					
					// see ilExSubmissionTeamGUI::removeTeamMemberObject()
					
					$first_user = $group;
					$first_user = array_shift($first_user);
					$team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user);	
					foreach($group as $user_id)
					{
						$team->removeTeamMember($user_id);
					}
					
					// reset ex team members, as any submission is not valid without team									
					$this->exercise->processExerciseStatus(
						$this->assignment,
						$group,
						false
					);	
				}					
			}
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "members");		
	}
	
	function adoptTeamsFromGroupObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$ilTabs->clearTargets();				
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, $this->getViewBack()));
		
		if(!$a_form)
		{
			$a_form = $this->initGroupForm();
		}
		$tpl->setContent($a_form->getHTML());		
	}
	
	protected function initGroupForm()
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();				
		$form->setTitle($lng->txt("exc_adopt_group_teams")." - ".$this->assignment->getTitle());
		$form->setFormAction($this->ctrl->getFormAction($this, "createTeamsFromGroups"));		
			
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
		include_once "Services/User/classes/class.ilUserUtil.php";
		$all_members = array();
		foreach(ilExAssignmentTeam::getGroupMembersMap($this->exercise->getRefId()) as $grp_id => $group)
		{
			if(sizeof($group["members"]))
			{
				$grp_team = new ilCheckboxGroupInputGUI($lng->txt("obj_grp")." \"".$group["title"]."\"", "grpt_".$grp_id);				
				$grp_value = $options = array();				
				foreach($group["members"] as $user_id)
				{
					$user_name = ilUserUtil::getNamePresentation($user_id, false, false, "", true);		
					$options[$user_id] = $user_name;
					if(!in_array($user_id, $all_members))
					{
						$grp_value[] = $user_id;
						$all_members[] = $user_id;
					}
				}
				asort($options);
				foreach($options as $user_id => $user_name)
				{
					$grp_team->addOption(new ilCheckboxOption($user_name, $user_id));
				}				
				$grp_team->setValue($grp_value);
				$form->addItem($grp_team);
			}
			else
			{
				$grp_team = new ilNonEditableValueGUI($group["title"]);
				$grp_team->setValue($lng->txt("exc_adopt_group_teams_no_members"));
				$form->addItem($grp_team);
			}
		}
		
		if(sizeof($all_members))
		{
			$form->addCommandButton("createTeamsFromGroups", $lng->txt("save"));
		}
		$form->addCommandButton("members", $lng->txt("cancel"));
		
		return $form;		
	}
	
	function createTeamsFromGroupsObject()
	{
		global $lng;
		
		$form = $this->initGroupForm();
		if($form->checkInput())
		{
			include_once "Services/User/classes/class.ilUserUtil.php";
			$map = ilExAssignmentTeam::getGroupMembersMap($this->exercise->getRefId());
			$all_members = $teams = array();
			$valid = true;
			foreach(array_keys($map) as $grp_id)
			{
				$postvar = "grpt_".$grp_id;
				$members = $_POST[$postvar];
				if(is_array($members))
				{
					$teams[] = $members;
					$invalid_team_members = array();
					
					foreach($members as $user_id)
					{
						if(!array_key_exists($user_id, $all_members))
						{
							$all_members[$user_id] = $grp_id;
						}
						else
						{				
							// user is selected in multiple groups							
							$invalid_team_members[] = $user_id;							
						}
					}
					
					if(sizeof($invalid_team_members))
					{
						$valid = false;
						
						$alert = array();
						foreach($invalid_team_members as $user_id)
						{							
							$user_name = ilUserUtil::getNamePresentation($user_id, false, false, "", true);		
							$grp_title = $map[$all_members[$user_id]]["title"];
							$alert[] = sprintf($lng->txt("exc_adopt_group_teams_conflict"), $user_name, $grp_title);
						}
						$input = $form->getItemByPostVar($postvar);
						$input->setAlert(implode("<br/>", $alert));
					}
				}
			}
			if($valid)
			{				
				if(sizeof($teams))
				{				
					$existing_users = array_keys(ilExAssignmentTeam::getAssignmentTeamMap($this->assignment->getId()));
					
					// create teams from group selections
					$sum = array("added"=>0, "blocked"=>0);										
					foreach($teams as $members)
					{						
						foreach($members as $user_id)
						{
							if(!$this->exercise->members_obj->isAssigned($user_id))
							{
								$this->exercise->members_obj->assignMember($user_id);
							}
							
							if(!in_array($user_id, $existing_users))
							{
								$sum["added"]++;
							}
							else
							{
								$sum["blocked"]++;
							}
						}
						
						$first = array_shift($members);
						$team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first, true);
						
						// getTeamId() does NOT send notification
						// $team->sendNotification($this->exercise->getRefId(), $first, "add");
						
						if(sizeof($members))
						{
							foreach($members as $user_id)
							{
								$team->addTeamMember($user_id);								
							}
						}					
					}					
					
					$mess = array();
					if($sum["added"])
					{
						$mess[] = sprintf($lng->txt("exc_adopt_group_teams_added"), $sum["added"]);
					}
					if($sum["blocked"])
					{
						$mess[] = sprintf($lng->txt("exc_adopt_group_teams_blocked"), $sum["blocked"]);
					}
					if($sum["added"])
					{
						ilUtil::sendSuccess(implode(" ", $mess), true);
					}
					else
					{
						ilUtil::sendFailure(implode(" ", $mess), true);
					}
				}
				$this->ctrl->redirect($this, "members");
			}
			else
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
		}
		
		$form->setValuesByPost();
		$this->adoptTeamsFromGroupObject($form);
	}
	
	
	////
	//// Multi Feedback
	////
	
	function initMultiFeedbackForm($a_ass_id)
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("uploadMultiFeedback", $lng->txt("upload"));
		$form->addCommandButton("members", $lng->txt("cancel"));
		
		// multi feedback file
		$fi = new ilFileInputGUI($lng->txt("exc_multi_feedback_file"), "mfzip");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);
				
		$form->setTitle(ilExAssignment::lookupTitle($a_ass_id));
		$form->setFormAction($this->ctrl->getFormAction($this, "uploadMultiFeedback"));		
		
		return $form;
	}
	
	/**
	 * Show multi-feedback screen
	 *
	 * @param
	 * @return
	 */
	function showMultiFeedbackObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilToolbar, $lng, $tpl;
		
		ilUtil::sendInfo($lng->txt("exc_multi_feedb_info"));
		
		$this->addSubTabs("assignment");
		
		// #13719
		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
		$button = ilLinkButton::getInstance();				
		$button->setCaption("exc_download_zip_structure");
		$button->setUrl($this->ctrl->getLinkTarget($this, "downloadMultiFeedbackZip"));							
		$button->setOmitPreventDoubleSubmission(true);
		$ilToolbar->addButtonInstance($button);
		
		if(!$a_form)
		{
			$a_form = $this->initMultiFeedbackForm($this->assignment->getId());
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Download multi-feedback structrue file
	 */
	function downloadMultiFeedbackZipObject()
	{		
		$this->assignment->sendMultiFeedbackStructureFile($this->exercise);
	}
	
	/**
	 * Upload multi feedback file
	 */
	function uploadMultiFeedbackObject()
	{				
		// #11983
		$form = $this->initMultiFeedbackForm($this->assignment->getId());
		if($form->checkInput())
		{
			try
			{
				$this->assignment->uploadMultiFeedbackFile(ilUtil::stripSlashesArray($_FILES["mfzip"]));
				$this->ctrl->redirect($this, "showMultiFeedbackConfirmationTable");
			}
			catch (ilExerciseException $e)
			{
				ilUtil::sendFailure($e->getMessage(), true);
				$this->ctrl->redirect($this, "showMultiFeedback");
			}
		}
		
		$form->setValuesByPost();
		$this->showMultiFeedbackObject($form);
	}
	
	/**
	 * Show multi feedback confirmation table
	 *
	 * @param
	 * @return
	 */
	function showMultiFeedbackConfirmationTableObject()
	{
		global $tpl;
		
		$this->addSubTabs("assignment");
				
		include_once("./Modules/Exercise/classes/class.ilFeedbackConfirmationTable2GUI.php");
		$tab = new ilFeedbackConfirmationTable2GUI($this, "showMultiFeedbackConfirmationTable", $this->assignment);
		$tpl->setContent($tab->getHTML());		
	}
	
	/**
	 * Cancel Multi Feedback
	 */
	function cancelMultiFeedbackObject()
	{
		$this->assignment->clearMultiFeedbackDirectory();		
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	 * Save multi feedback
	 */
	function saveMultiFeedbackObject()
	{
		$this->assignment->saveMultiFeedbackFiles($_POST["file"], $this->exercise);
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "members");
	}
}

