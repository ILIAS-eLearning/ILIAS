<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatus.php";
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatusPermissions.php";
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";

/**
 * Participation status GUI 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesParticipationStatus
 * @ilCtrl_Calls ilParticipationStatusAdminGUI: 
 */
class ilParticipationStatusAdminGUI
{	
	protected $course; // [ilObjCourse]
	protected $permissions; // [ilParticipationStatusPermissions]
	protected $pstatus; // [ilParticipationStatus]
	
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public function __construct(ilObjCourse $a_course)
	{
		global $lng;
		
		$this->setCourse($a_course);	
		
		$perm = ilParticipationStatusPermissions::getInstance($this->getCourse());
		$this->setPermissions($perm);
		
		if(!$this->getPermissions()->viewParticipationStatus() &&
			!$this->getPermissions()->setParticipationStatus() &&
			!$this->getPermissions()->reviewParticipationStatus())
		{
			ilUtil::sendFailure($lng->txt("msg_no_perm_read"), true);
			$this->returnToParent();
		}
		
		$lng->loadLanguageModule("ptst");
		
		$this->setParticipationStatus(ilParticipationStatus::getInstance($this->getCourse()));
	}
	
	/**
	 * Factory
	 * 
	 * @throws ilException
	 * @param int $a_ref_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_ref_id)
	{
		global $tree;
		
		if(ilObject::_lookupType($a_ref_id, true) != "crs" ||
			$tree->isDeleted($a_ref_id))
		{
			throw new ilException("ilParticipationStatusAdminGUI - needs course ref id");
		}
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_ref_id);
		return new self($course);
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		return $this->course;
	}
	
	/**
	 * Set permissions
	 * 
	 * @param ilParticipationStatusPermissions $a_perms
	 */
	protected function setPermissions(ilParticipationStatusPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilParticipationStatusPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Set participation status
	 * 
	 * @param ilParticipationStatus $a_status
	 */
	protected function setParticipationStatus(ilParticipationStatus $a_status)
	{
		$this->pstatus = $a_status;
	}
	
	/**
	 * Get participation status
	 * 
	 * @return ilParticipationStatus 
	 */	
	protected function getParticipationStatus()
	{
		return $this->pstatus;
	}
	
			
	//
	// GUI basics
	//
	
	/**
	 * Execute request command
	 * 
	 * @return boolean
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng;
				
		// nothing can be done before certain date is reached
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		if(!$helper->isStartForParticipationStatusSettingReached())
		{
			$this->setTabs("listStatus");
			
			ilDatePresentation::setUseRelativeDates(false);
			ilUtil::sendInfo(sprintf($lng->txt("ptst_admin_start_date_not_reached"), 
				ilDatePresentation::formatDate($helper->getStartForParticipationStatusSetting())));					
		}
		else
		{
			$next_class = $ilCtrl->getNextClass($this);
			$cmd = $ilCtrl->getCmd("listStatus");

			switch($next_class)
			{						
				default:		
					$this->$cmd();
					break;
			}
		}
		
		return true;
	}
	
	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->clearTargets();
		
		/*
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTargetByClass("ilobjcoursegui", "members"));
		*/
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "returnToParent"));
		
		$ilTabs->addTab("listStatus",
			$lng->txt("ptst_admin_tab_list_status"),
			$ilCtrl->getLinkTarget($this, "listStatus"));
		
		$ilTabs->activateTab($a_active);
	}
	
	/**
	 * Return to parent GUI
	 */
	protected function returnToParent()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilObjCourseGUI"), "members");		
		// $ilCtrl->returnToParent($this);
	}
	
	
	//
	// STATUS
	// 
	
	/**
	 * Check if current user has write access
	 * 
	 * @return bool	 
	 */
	protected function mayWrite()
	{
		$state = $this->getParticipationStatus()->getProcessState();
		return (($state == ilParticipationStatus::STATE_SET &&
				$this->getPermissions()->setParticipationStatus()) ||
			($state == ilParticipationStatus::STATE_REVIEW &&
				$this->getPermissions()->reviewParticipationStatus()));
	}
	
	/**
	 * List course member status and credit points
	 * 
	 * @param array $a_invalid
	 */
	protected function listStatus(array $a_invalid = null)
	{
		global $ilToolbar, $ilCtrl, $lng, $tpl;
		
		$this->setTabs("listStatus");
					
		$may_write = $this->mayWrite();		
		if($this->getParticipationStatus()->getMode() == ilParticipationStatus::MODE_CONTINUOUS)
		{
			$may_finalize = false;
		}
		else
		{
			$may_finalize = $may_write;
		}
		
		// attendance list
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		if($helper->getCourseNeedsAttendanceList())
		{
			if($may_write)
			{
				$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "uploadAttendanceList"), true);
				
				require_once "Services/Form/classes/class.ilFileInputGUI.php";
				$file = new ilFileInputGUI($lng->txt("ptst_admin_attendance_list"), "atlst");
				$ilToolbar->addInputItem($file, true);
				
				$ilToolbar->addFormButton($lng->txt("upload"), "uploadAttendanceList");

				$ilToolbar->addSeparator();
			}
			if($this->getParticipationStatus()->getAttendanceList())
			{
				if($may_write)
				{
					$ilToolbar->addButton($lng->txt("delete"), 
						$ilCtrl->getLinkTarget($this, "deleteAttendanceList"));

					$ilToolbar->addSeparator();
				}
				
				$ilToolbar->addButton($lng->txt("ptst_admin_view_attendance_list"),
					$ilCtrl->getLinkTarget($this, "viewAttendanceList"));
			}
			else
			{
				$ilToolbar->addText($lng->txt("ptst_admin_no_attendance_list"));
			}
		}
		
		require_once "Services/ParticipationStatus/classes/class.ilParticipationStatusTableGUI.php";
		$tbl = new ilParticipationStatusTableGUI($this, "listStatus", $this->getCourse(), $may_write, $may_finalize, $a_invalid);
		$tpl->setContent($tbl->getHTML());		
	}
	
	
	//
	// TABLE GUI ACTIONS
	// 
	
	/**
	 * Save (list) form data
	 * 
	 * @param bool $a_return
	 */
	protected function saveStatusAndPoints($a_return = false)
	{
		global $ilCtrl, $lng;
		
		$status = $_POST["status"];
		$points = $_POST["cpoints"];
		
		if(!$this->mayWrite() ||
			!is_array($status) ||
			!is_array($points))
		{
			$ilCtrl->redirect($this, "listStatus");
		}
		
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		$max = $helper->getMaxCreditPoints();
		
		$invalid = array();
		
		// currently only invalid points possible
		foreach($points as $user_id => $point)
		{	
			if($point != "" && !is_numeric($point))
			{
				$invalid["points"][] = $user_id;				
				continue;
			}		
			$point = (int)$point;
			if($point < 0 || $point > $max)
			{
				$invalid["points"][] = $user_id;		
			}			
		}
		
		if(sizeof($invalid))
		{
			ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			if(!$a_return)
			{
				return $this->listStatus($invalid);
			}
			else
			{
				return $invalid;
			}
		}
		
		foreach($status as $user_id => $status)
		{
			if($status == ilParticipationStatus::STATUS_NOT_SET)
			{
				$status = null;
			}
			
			$user_points = $points[$user_id];
			if($user_points === "")
			{
				$user_points = null;
			}
			
			$this->getParticipationStatus()->setStatus($user_id, $status);
			$this->getParticipationStatus()->setCreditPoints($user_id, $user_points);
		}	
		
		if(!$a_return)
		{
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "listStatus");
		}		
	}	
	
	/**
	 * Confirm finalize
	 */
	protected function confirmFinalize()
	{
		global $tpl, $ilCtrl, $lng;
		
		$invalid = $this->saveStatusAndPoints(true);
		if(is_array($invalid))
		{
			return $this->listStatus($invalid);
		}
				
		if(!$this->getParticipationStatus()->allStatusSet())
		{
			ilUtil::sendFailure($lng->txt("ptst_admin_finalize_need_not_status_set"), true);
			$ilCtrl->redirect($this, "listStatus");
		}
				
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		if($helper->getCourseNeedsAttendanceList() &&
			!$this->getParticipationStatus()->getAttendanceList())
		{
			ilUtil::sendFailure($lng->txt("ptst_admin_finalize_need_attendance_list"), true);
			$ilCtrl->redirect($this, "listStatus");
		}
		
		
		// confirmation 
		
		$this->setTabs("listStatus");
		
		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this, "finalize"));
		$confirm->setHeaderText($lng->txt("ptst_admin_confirm_finalize"));
		$confirm->setConfirm($lng->txt("confirm"), "finalize");
		$confirm->setCancel($lng->txt("cancel"), "listStatus");				
		$tpl->setContent($confirm->getHTML());	
	}
	
	/**
	 * Finalize status
	 */
	protected function finalize()
	{		
		global $ilCtrl, $lng;
		
		if($this->getParticipationStatus()->finalizeProcessState())
		{
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);			
		}		
		$ilCtrl->redirect($this, "listStatus");
	}
	
	
	//
	// ATTENDANCE LIST ACTIONS
	//
	
	/**
	 * Upload attendance list file
	 */
	protected function uploadAttendanceList()
	{
		global $ilCtrl, $lng;
		
		if(!$_FILES["atlst"]["tmp_name"])
		{
			$ilCtrl->redirect($this, "listStatus");
		}					
		
		if($this->getParticipationStatus()->uploadAttendanceList($_FILES["atlst"]))
		{
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);			
		}		
		$ilCtrl->redirect($this, "listStatus");
	}
	
	/**
	 * View/download attendance list file
	 */
	protected function viewAttendanceList()
	{
		global $ilCtrl;
		
		$list = $this->getParticipationStatus()->getAttendanceList();
		if(!$list)
		{
			$ilCtrl->redirect($this, "listStatus");
		}	
		
		ilUtil::deliverFile($list, basename($list));	
	}
	
	/**
	 * Delete attendance list file
	 */
	protected function deleteAttendanceList()
	{
		global $ilCtrl;
		
		$list = $this->getParticipationStatus()->getAttendanceList();
		if($list)
		{
			$this->getParticipationStatus()->deleteAttendanceList();			
		}	
		
		$ilCtrl->redirect($this, "listStatus");
	}	
}

