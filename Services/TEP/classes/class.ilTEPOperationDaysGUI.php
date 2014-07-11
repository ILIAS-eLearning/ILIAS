<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/TEP/classes/class.ilTEPPermissions.php";
		
/**
 * TEP operation days GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 * @ilCtrl_Calls ilTEPOperationDaysGUI: 
 */
class ilTEPOperationDaysGUI
{
	protected $course; // [ilObjCourse]
	protected $op_days; // [ilTEPOperationDays]
	protected $permissions; // [ilCourseBookingPermissions]
	
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
		
		$perm = ilTEPPermissions::getInstance();
		$this->setPermissions($perm);
		
		if(!$this->getPermissions()->mayView() &&
			!$this->getPermissions()->mayEdit())
		{
			ilUtil::sendFailure($lng->txt("msg_no_perm_read"), true);
			$this->returnToParent();
		}
		
		$lng->loadLanguageModule("tep");
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
			throw new ilException("ilTEPOperationDaysGUI - needs course ref id");
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
			
		require_once "Services/TEP/classes/class.ilTEPCourseEntries.php";
		$course_entries = ilTEPCourseEntries::getInstance($this->getCourse());	
		$this->setOperationDays($course_entries->getOperationsDaysInstance());
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
	 * Set operation days
	 * 
	 * @param ilTEPOperationDays $a_opdays
	 */
	protected function setOperationDays(ilTEPOperationDays $a_opdays)
	{
		$this->op_days = $a_opdays;				
	}
	
	/**
	 * Get operation days
	 *  
	 * @return ilTEPOperationDays 
	 */	
	protected function getOperationDays()
	{
		return $this->op_days;
	}
	
	/**
	 * Set permissions
	 * 
	 * @param ilTEPPermissions $a_perms
	 */
	protected function setPermissions(ilTEPPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilTEPPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
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
		$cmd = $ilCtrl->getCmd("listOperationDays");
		
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
		
		$ilTabs->addTab("listOperationDays",
			$lng->txt("tep_op_tab_list_operation_days"),
			$ilCtrl->getLinkTarget($this, "listOperationDays"));
		
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
	
	/**
	 * Set page title, description and locator
	 * 
	 * @param ilObjCourse $a_course
	 */
	public function setCoursePageTitleAndLocator()
	{
		global $tpl, $ilLocator, $lng;
		
		// see ilObjectGUI::setTitleAndDescription()
		
		$course = $this->getCourse();
		$tpl->setTitle($course->getPresentationTitle());
		$tpl->setDescription($course->getLongDescription());
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_crs_b.png"),
			$lng->txt("obj_crs"));

		include_once './Services/Object/classes/class.ilObjectListGUIFactory.php';
		$lgui = ilObjectListGUIFactory::_getListGUIByType("crs");
		$lgui->initItem($course->getRefId(), $course->getId());
		$tpl->setAlertProperties($lgui->getAlertProperties());	

		// see ilObjectGUI::setLocator()

		$ilLocator->addRepositoryItems($course->getRefId());
		$tpl->setLocator();
	}
	
	
	//
	// operation days
	//
	
	/**
	 * List operation days of course tutors
	 */
	protected function listOperationDays()
	{
		global $lng, $tpl;
		
		$this->setTabs("listOperationDays");
		
		require_once "Services/TEP/classes/class.ilTEPOperationDaysTableGUI.php";
		$tbl = new ilTEPOperationDaysTableGUI(
			$this
			, "listOperationDays"
			,$this->getOperationDays()
			,$this->getCourse()->getMembersObject()->getTutors()
			,!$this->getPermissions()->mayEdit()
		);
		return $tpl->setContent($tbl->getHTML());
	}
	
	/**
	 * Update operation days of course tutors
	 */
	protected function saveOperationDaysList()
	{
		global $ilCtrl, $lng;
		
		foreach($this->getCourse()->getMembersObject()->getTutors() as $tutor_id)
		{			
			if(!is_array($_POST["op"][$tutor_id]))
			{
				$this->getOperationDays()->setNoDaysForUser($tutor_id);
			}					
			else 
			{
				$op_days = array_keys($_POST["op"][$tutor_id]);
				$this->getOperationDays()->setDaysForUser($tutor_id, $op_days);
			}
		}
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "listOperationDays");
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
		$form->setFormAction($ilCtrl->getFormAction($this, "saveUserOperationDays"));
		$form->setTitle($lng->txt("tep_edit_user_operation_days"));				
		
		$uname = ilObjUser::_lookupName($a_user_id);
		
		$name = new ilNonEditableValueGUI($lng->txt("name"));
		$name->setValue($uname["lastname"].", ".$uname["firstname"]);
		$form->addItem($name);
		
		$user_days = array();
		foreach($this->getOperationDays()->getDaysForUser($a_user_id) as $day)
		{
			$user_days[] = $day->get(IL_CAL_DATE);
		}
		
		require_once "Services/TEP/classes/class.ilTEPPeriodInputGUI.php";
		$days = new ilTEPPeriodInputGUI($lng->txt("tep_edit_operation_days"), "op_days");		
		$days->setPeriod(
			$this->getOperationDays()->getStart()->get(IL_CAL_DATE)
			,$this->getOperationDays()->getEnd()->get(IL_CAL_DATE) 
		);
		$days->setValue($user_days);
		$form->addItem($days);

		$form->addCommandButton("saveUserOperationDays", $lng->txt("save"));
		$form->addCommandButton("returnToParent", $lng->txt("cancel"));
		
		return $form;
	}
	
	/**
	 * Edit user
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editUserOperationDays(ilPropertyFormGUI $a_form = null)
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
	protected function saveUserOperationDays()
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
			$days = $form->getInput("op_days");
			if(!is_array($days))
			{
				$this->getOperationDays()->setNoDaysForUser($user_id);
			}					
			else 
			{				
				$this->getOperationDays()->setDaysForUser($user_id, $days);
			}
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$this->returnToParent();
		}
		
		$form->setValuesByPost();
		$this->editUserOperationDays($form);
	}	
}
