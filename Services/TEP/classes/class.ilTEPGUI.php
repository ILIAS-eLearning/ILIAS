<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPView.php";
require_once "Services/TEP/classes/class.ilTEPPermissions.php";

/**
 * TEP GUI 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 * 
 * @ilCtrl_Calls ilTEPGUI: ilTEPEntryGUI, ilTEPOperationDaysGUI, ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilTEPGUI: ilParticipationStatusAdminGUI
 * @ilCtrl_Calls ilTEPGUI: gevMaillogGUI
 * @ilCtrl_Calls ilTEPGUI: ilObjCourseGUI
 * @ilCtrl_Calls ilTEPGUI: gevTrainerMailHandlingGUI
 */
class ilTEPGUI
{
	protected $permissions; // [ilTEPPermissions]
	protected $view_id; // [int]
	protected $view; // [ilTEPView]
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	public function __construct()
	{
		$perm = ilTEPPermissions::getInstance();
		$this->setPermissions($perm);
	}
	
	// 
	// properties
	//
	
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
	
	/**
	 * Get current view id
	 * 
	 * @return int
	 */
	protected function getCurrentViewId()
	{
		global $ilCtrl;
	
		$this->view_id = (int)$_REQUEST["vw"];
		
		// default
		if(!$this->view_id)
		{	
			// gev-patch start
			$default = ilTEPView::TYPE_MONTH;
			// gev-patch end
			$ilCtrl->setParameter($this, "vw", $default);
			$this->view_id = $default;					
		}
		
		// init view instance
		if(!$this->view)
		{
			$this->view = ilTEPView::getInstance($this->view_id, $this, $this->getPermissions());												
		}
		
		return $this->view_id;
	}
	
	/**
	 * Get current view
	 * 
	 * @return ilTEPView
	 */
	protected function getView()
	{
		$this->getCurrentViewId();
		return $this->view;		
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
		global $ilCtrl, $ilMainMenu, $lng, $tpl;
		$ilMainMenu->setActive("gev_others_menu");
		
		if(!$this->getPermissions()->mayView())
		{
			throw new ilException("ilTEPGUI - insufficient permissions");
		}
		
		$tpl->getStandardTemplate();
		
		$lng->loadLanguageModule("tep");
		$tpl->setTitle($lng->txt("tep_page_title"));
				
		$ilCtrl->saveParameter($this, "vw");		
		$ilCtrl->saveParameter($this, "seed");		
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("view");

		switch($next_class)
		{			
			case "iltepentrygui":
				$this->setTabs();
				$ilCtrl->setReturn($this, "view");
				
				// defaults for new entry, see ilTEPView::prepareDataForPresentation() / ilTEPViewGridBased::renderDayForUser()
				
				$entry_day = trim($_REQUEST["edt"])
					? new ilDate(trim($_REQUEST["edt"]), IL_CAL_DATE)
					: array_shift($this->getView()->getPeriod()); // 1st day of period
				
				$entry_tutor = (int)$_REQUEST["euid"] 
					? (int)$_REQUEST["euid"]
					: array_shift($this->getView()->getTutors()); // 1st tutor
				
				require_once "Services/TEP/classes/class.ilTEPEntryGUI.php";
				$gui = new ilTEPEntryGUI($this->getPermissions());
				$gui->setDefaults(						
						$entry_day
						,$entry_tutor
				);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case "iltepoperationdaysgui":
				$ref_id = $_GET["ref_id"];
				if(!$ref_id)
				{
					throw new ilException("ilTEPOperationDaysGUI - no ref_id");
				}
				$ilCtrl->saveParameterByClass("ilTEPOperationDaysGUI", "ref_id", $ref_id);			
				
				require_once "Modules/Course/classes/class.ilObjCourse.php";
				$course = new ilObjCourse($ref_id);
												
				require_once "Services/TEP/classes/class.ilTEPOperationDaysGUI.php";
				$gui = new ilTEPOperationDaysGUI($course);					
				$gui->setCoursePageTitleAndLocator();				
				$ilCtrl->forwardCommand($gui);
				break;
			case "ilobjcoursegui":
				require_once("Modules/Course/classes/class.ilObjCourseGUI.php");
				$gui = new ilObjCourseGUI();
				$ilCtrl->forwardCommand($gui);
				break;
			case "gevtrainermailhandlinggui":
				require_once("Services/GEV/Desktop/classes/class.gevTrainerMailHandlingGUI.php");
				$gui = new gevTrainerMailHandlingGUI($this);
				$ret = $ilCtrl->forwardCommand($gui);
				break;
			default:
				// gev-patch start
				switch($cmd) {
					case "finalize":
					case "confirmFinalize":
					case "saveStatusAndPoints":
					case "uploadAttendanceList":
					case "viewAttendanceList":
					case "deleteAttendanceList":
						//ilParticipationStatusTableGUI
						require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
						$crs_ref_id = $this->getCrsRefId();
						$gui = ilParticipationStatusAdminGUI::getInstanceByRefId($crs_ref_id);
						$gui->from_foreign_class = 'ilTEPGUI';
						$gui->crs_ref_id = $crs_ref_id;
						$ilCtrl->saveParameterByClass("ilParticipationStatusAdminGUI", "ref_id", $crs_ref_id);	
						$ilCtrl->saveParameterByClass("ilParticipationStatusAdminGUI", "crsrefid", $crs_ref_id);	
						//$gui->returnToList();
						//die('forwarding cmd');
						$ret = $ilCtrl->forwardCommand($gui);
						break;
					case "showMaillog":
					case "showLoggedMail":
					case "resendMail":
						require_once("Services/GEV/Mailing/classes/class.gevMaillogGUI.php");
						$gui = new gevMaillogGUI("iltepgui");
						$ret = $ilCtrl->forwardCommand($gui);
						break;
					case "listParticipationStatus":
						$this->showParticipationStatus();
						break;
					default:
						$this->$cmd();
				}
				// gev-patch end
				break;
		}

		$tpl->show();
	}
	
	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs($a_active = null)
	{
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->clearTargets();
		
		if(!$a_active)
		{
			$a_active = $this->getCurrentViewId();
		}
		
		$map = array(
			 ilTEPView::TYPE_LIST => "list"
			 ,ilTEPView::TYPE_MONTH => "month"
			 ,ilTEPView::TYPE_HALFYEAR => "halfyear"
		);		
		foreach($map as $id => $name)
		{
			$ilCtrl->setParameter($this, "vw", $id);		
			$ilTabs->addTab("view_".$name,
				$lng->txt("tep_tab_".$name),
				$ilCtrl->getLinkTarget($this, "view"));
			
			if($a_active == $id)
			{
				$ilTabs->activateTab("view_".$name);
			}
		}
		
		$ilCtrl->setParameter($this, "vw", $this->getCurrentViewId());						
	}
	
	
	//
	// presentation
	// 
	
	/**
	 * Render current view
	 */
	protected function view()
	{
		global $tpl;
		
		$this->setTabs();
				
		//$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "tep_print.css", "Services/TEP"), "print");	
		$csspath = 'Customizing/global/skin/genv/Services/TEP/tep_print.css';
		$tpl->addCss($csspath, "print");	

		
		$view = $this->getView();
		$has_data = $view->loadData();
		
		$this->renderToolbar($has_data);
	
		$tpl->setContent($view->render());
	}
	
	/**
	 * Render toolbar
	 *
	 * @param bool $a_has_content
	 */
	protected function renderToolbar($a_has_content)
	{
		global $ilToolbar, $ilCtrl, $lng;
		
		if($this->getPermissions()->mayEdit())
		{
			$ilToolbar->addButton($lng->txt("tep_add_new_entry"),
				$ilCtrl->getLinkTargetByClass("ilTEPEntryGUI", "createEntry"));
			// gev-patch start
			require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
			global $ilUser;
			if (gevDecentralTrainingUtils::getInstance()->canCreate($ilUser->getId())) {
				$ilToolbar->addButton($lng->txt("gev_create_decentral_training"),
					$ilCtrl->getLinkTargetByClass(array("gevDesktopGUI", "gevDecentralTrainingGUI"), "chooseTemplateAndTrainers"));
			}
			// gev-patch end
		}
		
		if($a_has_content)
		{
			$ilToolbar->addButton($lng->txt("tep_export"),
				$ilCtrl->getLinkTarget($this, "export"));
			
			//$ilToolbar->addButton($lng->txt("tep_print"), "#", "", "", 
			//	' onClick="self.print(); return false;"');
		}
	}
	
	
	//
	// export
	// 
	
	/**
	 * Export current events as XLS
	 */
	protected function export()
	{
		global $lng, $ilCtrl;

		$view = $this->getView();		
		if($view->loadData())
		{
			$view->exportXLS();
			exit();
		}

		$ilCtrl->redirect($this, "view");
	}
	
	// gev-patch start
	public function getCrsRefId() {
		$crs_ref_id = $_GET['ref_id'];

		if (! $crs_ref_id) {
			$crs_ref_id = $_GET['crsrefid'];
		}

		if(! $crs_ref_id) {
			throw new ilException("ilTEPGUI - needs course ref_id");
		}
		return $crs_ref_id;
	}

	public function getCrsId() {
		$crs_id = $_GET['crs_id'];

		if(! $crs_id){
			throw new ilException("ilTEPGUI - needs course crs_id");
		}
		return $crs_id;
	}
	
	protected function checkAccomodation($crs_utils) {
		if (!$crs_utils->isWithAccomodations()) {
			ilUtil::sendFailure($this->lng->txt("gev_mytrainingsap_no_accomodations"), true);
			$this->ctrl->redirect($this, "view");
		}
	}
	
	protected function listStatus() {
		$this->showParticipationStatus();
	}
	
	protected function showParticipationStatus() {
		global $ilCtrl, $tpl;
		$ref_id = $this->getCrsRefId();
		require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApGUI.php");
		$tpl->setTitle(null);
		$tpl->setContent(
			gevMyTrainingsApGUI::renderListParticipationStatus(
					$this, 
					$ilCtrl->getLinkTarget($this, "view"),
					$ref_id));
	}

	
	protected function showOvernights($a_form = null) {
		global $ilCtrl, $tpl, $ilUser;
		$crs_id = $this->getCrsId();
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		
		$ilCtrl->setParameter($this, "crs_id", $crs_id);
		
		require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$tpl->setTitle(null);
		$tpl->setContent(
			gevMyTrainingsApGUI::renderShowOvernights(
					$this, 
					$ilCtrl->getLinkTarget($this, "view"),
					$ilUser->getId(),
					$crs_utils,
					$a_form
				)
			);
	}
	
	protected function saveOvernights() {
		global $ilCtrl, $tpl, $ilUser, $lng;
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = $this->getCrsId();
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		
		$ilCtrl->setParameter($this, "crs_id", $crs_id);

		require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$form = gevMyTrainingsApGUI::buildOvernightsForm($crs_id, $crs_utils, $ilCtrl->getFormAction($this));
		if ($form->checkInput()) {
			ilSetAccomodationsGUI::importAccomodationsFromForm($form, $crs_id, $ilUser->getId());
			ilUtil::sendSuccess($lng->txt("gev_mytrainingsap_saved_overnights"));
		}
		return $this->showOvernights($form);
	}
	
	protected function showBookings() {
		global $ilCtrl;
		$ref_id = $this->getCrsRefId();
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::setBackTarget(
			$ilCtrl->getLinkTargetByClass("ilTEPGUI", "backFromBookings")
			);
		
		$ilCtrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $ref_id);
		$ilCtrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}
	
	protected function backFromBookings() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::removeBackTarget();
		return $this->view();
	}
	
	// gev-patch end
}