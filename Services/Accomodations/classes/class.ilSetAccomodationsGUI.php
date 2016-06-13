<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Accomodations/classes/class.ilAccomodationsPermissions.php";
require_once "./Services/Accomodations/classes/class.ilAccomodations.php";
		
/**
 * Accomodations admin GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAccomodations
 * @ilCtrl_Calls ilSetAccomodationsGUI: 
 */
class ilSetAccomodationsGUI
{
	protected $course; // [ilObjCourse]	
	protected $permissions; // [ilAccomodationsPermissions]
	protected $accomodations; // [ilAccomodations]
	
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
		
		$perm = ilAccomodationsPermissions::getInstance($this->getCourse());
		$this->setPermissions($perm);
		
		if(!$this->getPermissions()->viewOwnAccomodations() &&
			!$this->getPermissions()->viewOthersAccomodations())
		{
			ilUtil::sendFailure($lng->txt("msg_no_perm_read"), true);
			$this->returnToParent();
		}
		
		$lng->loadLanguageModule("acco");
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
			throw new ilException("ilSetAccomodationsGUI - needs course ref id");
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
	 * @throws ilException
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;		
			
		$this->setAccomodations(ilAccomodations::getInstance($this->course));
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
	 * @param ilAccomodationsPermissions $a_perms
	 */
	protected function setPermissions(ilAccomodationsPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilAccomodationsPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Set accomodations
	 * 
	 * @param ilAccomodations $a_acco
	 */
	protected function setAccomodations(ilAccomodations $a_acco)
	{
		$this->accomodations = $a_acco;
	}
	
	/**
	 * Get accomodations
	 * 
	 * @return ilAccomodations 
	 */	
	protected function getAccomodations()
	{
		return $this->accomodations;
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

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listAccomodations");
		//gev patch start #2351
		global $ilUser, $log;
		if($this->course) {
			$crs = $this->course->getId();
		} else {
			$crs = " course without id";
		}
		$log->write("####course accomodations of ".$crs.":".$ilUser->getId()." performing command ".$cmd);

		//gev patch end
		switch($next_class)
		{						
			default:				
				$this->$cmd();
				break;
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
		
		$ilTabs->addTab("listAccomodations",
			$lng->txt("acco_tab_list_accomodations"),
			$ilCtrl->getLinkTarget($this, "listAccomodations"));
		
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
	// accomodations
	//
	
	/**
	 * List accomodations of course members
	 */
	protected function listAccomodations()
	{
		global $tpl;
		
		$this->setTabs("listAccomodations");
		
		require_once "Services/Accomodations/classes/class.ilSetAccomodationsTableGUI.php";
		$tbl = new ilSetAccomodationsTableGUI(
			$this
			,"listAccomodations"
			,"saveAccomodationsList"
			,"returnToParent"
			,$this->getCourse()
			,$this->getAccomodations()	
			,null
			,$this->getPermissions()
		);
		return $tpl->setContent($tbl->getHTML());
	}
	
	/**
	 * Update operation days of course tutors
	 */
	protected function saveAccomodationsList()
	{
		global $ilCtrl, $lng;

		require_once "Services/Accomodations/classes/class.ilSetAccomodationsTableGUI.php";
		$tbl = new ilSetAccomodationsTableGUI(
			$this
			,"listAccomodations"
			,"saveAccomodationsList"
			,"returnToParent"
			,$this->getCourse()
			,$this->getAccomodations()	
			,null
			,$this->getPermissions()
		);
		if($tbl->processPostVars())
		{
			//gev-patch start
			require_once("Services/UICore/classes/class.ilTemplate.php");
			$ilCtrl->setParameterByClass("gevCrsMailingGUI","ref_id", $this->getCourse()->getRefId());
			$ilCtrl->setParameterByClass("gevCrsMailingGUI","auto_mail_id", "invitation");
			$link = $ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjCourseGUI", "gevCrsMailingGUI"),"sendAutoMail");
			$ilCtrl->clearParametersByClass("gevCrsMailingGUI");

			$tpl = new ilTemplate("tpl.gev_resend_mail_info.html", true, true, "Services/GEV/Course");
			$tpl->setVariable("MESSAGE", $lng->txt("gev_crs_resend_invitation_accomodation_info"));
			$tpl->setVariable("HREF_LINK", $link);
			$tpl->setVariable("HREF_TEXT", $lng->txt("gev_crs_resend_invitation"));

			ilUtil::sendInfo($tpl->get(), true);
			//gev-patch end

			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		$ilCtrl->redirect($this, "listAccomodations");
	}
	
	
	//
	// single user
	// 
	
	/**
	 * Init user form
	 * 
	 * @param int $a_user_id
	 * @return ilPropertyFormGUI
	 */
	protected function initUserForm($a_user_id)
	{
		global $ilCtrl, $lng;
		
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveUserAccomodations"));
		$form->setTitle($lng->txt("acco_edit_user_accomodations"));				
		
		$uname = ilObjUser::_lookupName($a_user_id);
		
		$name = new ilNonEditableValueGUI($lng->txt("name"));
		$name->setValue($uname["lastname"].", ".$uname["firstname"]);
		$form->addItem($name);
		
		/*
		$user_nights = array();
		foreach($this->getAccomodations()->getAccomodationsOfUser($a_user_id) as $night)
		{
			$user_nights[] = $night->get(IL_CAL_DATE);
		}
		
		require_once "Services/Accomodations/classes/class.ilAccomodationsPeriodInputGUI.php";
		$nights = new ilAccomodationsPeriodInputGUI($lng->txt("acco_accomodations"), "acco");		
		$nights->setPeriod(
			$this->getAccomodations()->getCourseStart()->get(IL_CAL_DATE)
			,$this->getAccomodations()->getCourseEnd()->get(IL_CAL_DATE)
		);
		$nights->setMode(ilAccomodationsPeriodInputGUI::MODE_OVERNIGHT);
		$nights->setValue($user_nights);
		$form->addItem($nights);
		*/
		
		self::addAccomodationsToForm($form, $this->getCourse()->getId(), $a_user_id);

		$form->addCommandButton("saveUserAccomodations", $lng->txt("save"));
		$form->addCommandButton("returnToParent", $lng->txt("cancel"));
		
		return $form;
	}
	
	/**
	 * Edit user
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editUserAccomodations(ilPropertyFormGUI $a_form = null)
	{
		global $ilCtrl, $tpl;
		
		$user_id = (int)$_REQUEST["uid"];
		if(!$user_id)
		{
			$this->returnToParent();
		}
		
		$ilCtrl->setParameter($this, "uid", $user_id);
		
		if(!$a_form)
		{
			$a_form = $this->initUserForm($user_id);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Update user
	 */
	protected function saveUserAccomodations()
	{
		global $lng;
		
		$user_id = (int)$_REQUEST["uid"];
		if(!$user_id)
		{
			$this->returnToParent();
		}
		
		$form = $this->initUserForm($user_id);
		if($form->checkInput())
		{
				/*
			$nights = $form->getInput("acco");
			
			if(!is_array($nights))
			{
				$this->getAccomodations()->deleteAccomodations($user_id);
			}					
			else 
			{				
				foreach($nights as $idx => $night)
				{
					$nights[$idx] = new ilDate($night, IL_CAL_DATE);
				}
				$this->getAccomodations()->setAccomodationsOfUser($user_id, $nights);
			}
			*/
			
			self::importAccomodationsFromForm($form, $this->getCourse()->getId(), $user_id);
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$this->returnToParent();
		}
		
		$form->setValuesByPost();
		$this->editUserOperationDays($form);
	}	
	
	
	//
	// form gui helper	
	// 
	
	/**
	 * Add accomodations of user for course to form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 * @param string $a_field_name
	 */
	public static function addAccomodationsToForm(ilPropertyFormGUI $a_form, $a_course_obj_id, $a_user_id, $a_field_name = "acco", $a_fill_default = false)
	{		
		global $lng;
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_obj_id, false);		
		$accomodations = ilAccomodations::getInstance($course);
	
		$user_nights = array();

		// gev-patch start
		if (!$a_fill_default) {
			foreach($accomodations->getAccomodationsOfUser($a_user_id) as $night)
			{
				$user_nights[] = $night->get(IL_CAL_DATE);
			}
		}
		else {
			$start = $accomodations->getCourseStart();
			$end = $accomodations->getCourseEnd();
			
			while (ilDate::_before($start, $end)) {
				$user_nights[] = $start->get(IL_CAL_DATE);
				$start->increment(IL_CAL_DAY, 1);
			}
		}
		// gev-patch end
		
		require_once "Services/Accomodations/classes/class.ilAccomodationsPeriodInputGUI.php";
		$nights = new ilAccomodationsPeriodInputGUI($lng->txt("acco_accomodations"), $a_field_name);
		$nights->setPeriod(
			$accomodations->getCourseStart()->get(IL_CAL_DATE)
			,$accomodations->getCourseEnd()->get(IL_CAL_DATE)
		);
		$nights->setMode(ilAccomodationsPeriodInputGUI::MODE_OVERNIGHT);
		$nights->setValue($user_nights);
		$a_form->addItem($nights);		
	}
	
	/**
	 * Import accomodations of user from form input
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 * @param string $a_field_name
	 */
	public static function importAccomodationsFromForm(ilPropertyFormGUI $a_form, $a_course_obj_id, $a_user_id, $a_field_name = "acco")
	{
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_obj_id, false);		
		$accomodations = ilAccomodations::getInstance($course);
				
		$nights = $a_form->getInput($a_field_name);					
		if(!is_array($nights))
		{
			$accomodations->deleteAccomodations($a_user_id);
		}					
		else 
		{
			$nights = self::formInputToAccomodationsArray($nights);
			$accomodations->setAccomodationsOfUser($a_user_id, $nights);
		}		
	}
	
	public static function formInputToAccomodationsArray($a_nights) {
		foreach($a_nights as $idx => $night)
		{
			$a_nights[$idx] = new ilDate($night, IL_CAL_DATE);
		}
		return $a_nights;
	}
}
