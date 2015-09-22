<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevDesktopGUI: gevMyCoursesGUI
* @ilCtrl_Calls gevDesktopGUI: gevCourseSearchGUI
* @ilCtrl_Calls gevDesktopGUI: ilAdminSearchGUI
* @ilCtrl_Calls gevDesktopGUI: gevBookingGUI
* @ilCtrl_Calls gevDesktopGUI: gevStaticpagesGUI
* @ilCtrl_Calls gevDesktopGUI: gevEduBiographyGUI
* @ilCtrl_Calls gevDesktopGUI: gevUserProfileGUI
* @ilCtrl_Calls gevDesktopGUI: gevWBDTPServiceRegistrationGUI
* @ilCtrl_Calls gevDesktopGUI: gevWBDTPBasicRegistrationGUI
* @ilCtrl_Calls gevDesktopGUI: gevAttendanceByEmployeeGUI
* @ilCtrl_Calls gevDesktopGUI: gevBillingReportGUI
* @ilCtrl_Calls gevDesktopGUI: gevBookingsByVenueGUI
* @ilCtrl_Calls gevDesktopGUI: gevMyTrainingsApGUI
* @ilCtrl_Calls gevDesktopGUI: gevWBDEdupointsReportedGUI
* @ilCtrl_Calls gevDesktopGUI: gevEmployeeBookingsGUI
* @ilCtrl_Calls gevDesktopGUI: gevDecentralTrainingGUI
* @ilCtrl_Calls gevDesktopGUI: gevEmployeeEduBiosGUI
* @ilCtrl_Calls gevDesktopGUI: ilFormPropertyDispatchGUI
* @ilCtrl_Calls gevDesktopGUI: gevWBDErrorsGUI
* @ilCtrl_Calls gevDesktopGUI: gevAttendanceByOrgUnitGUI
* @ilCtrl_Calls gevDesktopGUI: gevAttendanceByCourseTemplateGUI
* @ilCtrl_Calls gevDesktopGUI: gevTrainerOperationByTEPCategoryGUI
* @ilCtrl_Calls gevDesktopGUI: gevDBVReportGUI
* @ilCtrl_Calls gevDesktopGUI: gevDBVReportSuperiorGUI
* @ilCtrl_Calls gevDesktopGUI: gevDecentralTrainingBuildingBlockAdminGUI
* @ilCtrl_Calls gevDesktopGUI: gevDecentralTrainingCourseCreatingBuildingBlockGUI
* @ilCtrl_Calls gevDesktopGUI: gevDecentralTrainingCourseCreatingBuildingBlock2GUI
* @ilCtrl_Calls gevDesktopGUI: gevTrainerWorkloadGUI
* @ilCtrl_Calls gevDesktopGUI: gevTrainerOperationByOrgUnitAndTrainerGUI
*/

class gevDesktopGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->lng->loadLanguageModule("gev");
		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		global $ilLog;
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();
		$this->checkProfileComplete($cmd, $next_class);

		if ($next_class != "gevuserprofilegui" && $cmd != "toMyProfile") {
			$this->checkNeedsWBDRegistration($cmd, $next_class);
		}
			
		if($cmd == "") {
			$cmd = "toMyCourses";
		}

		global $ilMainMenu;
		switch($next_class) {
			case "gevmycoursesgui":
				$ilMainMenu->setActive("gev_me_menu");
				require_once("Services/GEV/Desktop/classes/class.gevMyCoursesGUI.php");
				$gui = new gevMyCoursesGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevcoursesearchgui":
				$ilMainMenu->setActive("gev_search_menu");
				require_once("Services/GEV/Desktop/classes/class.gevCourseSearchGUI.php");
				$gui = new gevCourseSearchGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "iladminsearchgui":
				$ilMainMenu->setActive("gev_admin_menu");
				require_once("Services/GEV/Desktop/classes/class.ilAdminSearchGUI.php");
				$gui = new ilAdminSearchGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevbookinggui":
				require_once("Services/GEV/Desktop/classes/class.gevBookingGUI.php");
				$gui = new gevBookingGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevstaticpagesgui":			
				require_once("Services/GEV/Desktop/classes/class.gevStaticPagesGUI.php");
				$gui = new gevStaticpagesGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevedubiographygui":
				$ilMainMenu->setActive("gev_me_menu");
				require_once("Services/GEV/Reports/classes/class.gevEduBiographyGUI.php");
				$gui = new gevEduBiographyGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevuserprofilegui":
				$ilMainMenu->setActive("gev_me_menu");
				require_once("Services/GEV/Desktop/classes/class.gevUserProfileGUI.php");
				$gui = new gevUserProfileGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevmytrainingsapgui":
				$ilMainMenu->setActive("gev_me_menu");
				require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApGUI.php");
				$gui = new gevMyTrainingsApGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevwbdtpserviceregistrationgui":
				require_once("Services/GEV/Registration/classes/class.gevWBDTPServiceRegistrationGUI.php");
				$gui = new gevWBDTPServiceRegistrationGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevwbdtpbasicregistrationgui":
				require_once("Services/GEV/Registration/classes/class.gevWBDTPBasicRegistrationGUI.php");
				$gui = new gevWBDTPBasicRegistrationGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevattendancebyemployeegui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevAttendanceByEmployeeGUI.php");
				$gui = new gevAttendanceByEmployeeGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevtraineroperationbytepcategorygui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevTrainerOperationByTEPCategoryGUI.php");
				$gui = new gevTrainerOperationByTEPCategoryGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevbillingreportgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevBillingReportGUI.php");
				$gui = new gevBillingReportGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevbookingsbyvenuegui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevBookingsByVenueGUI.php");
				$gui = new gevBookingsByVenueGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevwbdedupointsreportedgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevWBDEdupointsReportedGUI.php");
				$gui = new gevWBDEdupointsReportedGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevemployeebookingsgui":
				$ilMainMenu->setActive("gev_others_menu");
				require_once("Services/GEV/Reports/classes/class.gevEmployeeBookingsGUI.php");
				$gui = new gevEmployeeBookingsGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevdecentraltraininggui":
				$ilMainMenu->setActive("gev_others_menu");
				require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingGUI.php");
				$gui = new gevDecentralTrainingGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
				
			case "gevemployeeedubiosgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevEmployeeEduBiosGUI.php");
				$gui = new gevEmployeeEduBiosGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevwbderrorsgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevWBDErrorsGUI.php");
				$gui = new gevWBDErrorsGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevattendancebyorgunitgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevAttendanceByOrgUnitGUI.php");
				$gui = new gevAttendanceByOrgUnitGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevattendancebycoursetemplategui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevAttendanceByCourseTemplateGUI.php");
				$gui = new gevAttendanceByCourseTemplateGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case "gevdbvreportgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevDBVReportGUI.php");
				$gui = new gevDBVReportGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevdbvreportsuperiorgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevDBVReportSuperiorGUI.php");
				$gui = new gevDBVReportSuperiorGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevdecentraltrainingbuildingblockadmingui":
				$ilMainMenu->setActive("gev_admin_menu");
				require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingBuildingBlockAdminGUI.php");
				$gui = new gevDecentralTrainingBuildingBlockAdminGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevdecentraltrainingcoursecreatingbuildingblock2gui":
				$ilMainMenu->setActive("gev_admin_menu");
				require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
				$crs_obj_id = null;
				
				if(isset($_GET["crs_obj_id"])){
					$crs_obj_id = (int)$_GET["crs_obj_id"];
				}

				if(isset($_POST["crs_obj_id"])){
					$crs_obj_id = (int)$_POST["crs_obj_id"];
				}
				
				if(isset($_GET["crs_ref_id"])){
					require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
					$crs_obj_id = (int)gevObjectUtils::getObjId((int)$_GET["crs_ref_id"]);
				}

				if(isset($_POST["crs_ref_id"])){
					require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
					$crs_obj_id = (int)gevObjectUtils::getObjId((int)$_POST["crs_ref_id"]);
				}

				$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($crs_obj_id);
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevtrainerworkloadgui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevTrainerWorkloadGUI.php");
				$gui = new gevTrainerWorkloadGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevtraineroperationbyorgunitandtrainergui":
				$ilMainMenu->setActive("gev_reporting_menu");
				require_once("Services/GEV/Reports/classes/class.gevTrainerOperationByOrgUnitAndTrainerGUI.php");
				$gui = new gevTrainerOperationByOrgUnitAndTrainerGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			default:
				$this->dispatchCmd($cmd);
				break;
		}
		
		if (isset($ret)) {
			$this->tpl->setContent($ret);
		}
		
		$this->tpl->show();
	}
	
	public function dispatchCmd($a_cmd) {
		switch($a_cmd) {
			case "toCourseSearch":
			case "toAdmCourseSearch":
			case "toMyCourses":
			case "toMyProfile":
			case "toStaticPages":
			case "toMyTrainingsAp":
			case "toReportAttendanceByEmployee":
			case "toBillingReport":
			case "toReportBookingsByVenue":
			case "toBooking":
			case "toEmployeeBookings":
			case "toReportEmployeeEduBios":
			case "toReportAttendanceByOrgUnit":
			case "toReportAttendanceByCourseTemplate":
			case "toReportTrainerOperationByTEPCategory":
			case "toReportWBDEdupoints":
			case "toDBVReport":
			case "toDBVReportSuperior":
			case "toWBDErrors":
			case "createHAUnit":
			case "toDctBuildingBlockAdm":
			case "toTrainerWorkload":
			case "toTrainerOperationByOrgUnitAndTrainer":
			case "toSaveTrainingSettings":
			case "toAddCrsBuildingBlock":
			case "toDeleteCrsBuildingBlock":
			case "toUpdateBuildingBlock":
			case "toCancleCreation":
			case "toSaveRequest":
			case "toChangeCourseData":
			case "showOpenRequests":
				$this->$a_cmd();
			case "handleExplorerCommand":
				break;
			default:
				throw new Exception("gevDesktopGUI:Unknown command: ".$a_cmd);
		}
	}

	protected function toReportTrainerOperationByTEPCategory() {
		$this->ctrl->redirectByClass("gevTrainerOperationByTEPCategoryGUI");
	}

	protected function toDctBuildingBlockAdm() {
		$this->ctrl->redirectByClass("gevDecentralTrainingBuildingBlockAdminGUI");
	}

	protected function toCourseSearch() {
		$this->ctrl->redirectByClass("gevCourseSearchGUI");
	}
	
	protected function toAdmCourseSearch() {
		$this->ctrl->redirectByClass("ilAdminSearchGUI");
	}
	
	protected function toMyCourses() {
		$this->ctrl->redirectByClass("gevMyCoursesGUI");
	}	

	protected function toStaticPages() {
		$this->ctrl->redirectByClass("gevStaticPagesGUI", $_REQUEST['ctpl_file']);
	}
	
	protected function toMyProfile() {
		$this->ctrl->redirectByClass("gevUserProfileGUI");
	}

	protected function toMyTrainingsAp() {
		$this->ctrl->redirectByClass("gevMyTrainingsApGUI");
	}

	protected function toReportAttendanceByEmployee() {
		$this->ctrl->redirectByClass("gevAttendanceByEmployeeGUI");
	}
	
	protected function toBillingReport() {
		$this->ctrl->redirectByClass("gevBillingReportGUI");
	}
	protected function toReportBookingsByVenue() {
		$this->ctrl->redirectByClass("gevBookingsByVenueGUI");
	}
	protected function toReportWBDEdupoints() {
		$this->ctrl->redirectByClass("gevWBDEdupointsReportedGUI");
	}
	
	protected function toEmployeeBookings() {
		$this->ctrl->redirectByClass("gevEmployeeBookingsGUI");
	}

	protected function toDBVReport() {
		$this->ctrl->redirectByClass("gevDBVReportGUI");
	}

	protected function toDBVReportSuperior() {
		$this->ctrl->redirectByClass("gevDBVReportSuperiorGUI");
	}

	protected function toTrainerWorkload() {
		$this->ctrl->redirectByClass("gevTrainerWorkloadGUI");
	}

	protected function toTrainerOperationByOrgUnitAndTrainer() {
		$this->ctrl->redirectByClass("gevTrainerOperationByOrgUnitAndTrainerGUI");
	}
	
	protected function toReportEmployeeEduBios() {
		$this->ctrl->redirectByClass("gevEmployeeEduBiosGUI");
	}
	protected function toWBDErrors() {
		$this->ctrl->redirectByClass("gevWBDErrorsGUI");
	}

	protected function toReportAttendanceByOrgUnit() {
		$this->ctrl->redirectByClass("gevAttendanceByOrgUnitGUI");
	}

	protected function toReportAttendanceByCourseTemplate() {
		$this->ctrl->redirectByClass("gevAttendanceByCourseTemplateGUI");
	}

	protected function toSaveTrainingSettings() {
		$crs_request_id = (isset($_POST["crs_request_id"])) ? $_POST["crs_request_id"] : null;
		$crs_ref_id = (isset($_POST["crs_ref_id"])) ? $_POST["crs_ref_id"] : null;

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($crs_ref_id, $crs_request_id);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toAddCrsBuildingBlock() {
		$crs_request_id = (isset($_POST["crs_request_id"])) ? $_POST["crs_request_id"] : null;
		$crs_ref_id = (isset($_POST["crs_ref_id"])) ? $_POST["crs_ref_id"] : null;
		
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($crs_ref_id, $crs_request_id);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toDeleteCrsBuildingBlock() {
		$crs_request_id = (isset($_POST["crs_request_id"])) ? $_POST["crs_request_id"] : null;
		$crs_ref_id = (isset($_POST["crs_ref_id"])) ? $_POST["crs_ref_id"] : null;

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($crs_ref_id, $crs_request_id);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toUpdateBuildingBlock() {
		$crs_request_id = (isset($_POST["crs_request_id"])) ? $_POST["crs_request_id"] : null;
		$crs_ref_id = (isset($_POST["crs_ref_id"])) ? $_POST["crs_ref_id"] : null;

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($crs_ref_id, $crs_request_id);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toCancleCreation() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI(null,null);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toSaveRequest() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI(null,null);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toChangeCourseData() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingGUI.php");
		$gui = new gevDecentralTrainingGUI();
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function showOpenRequests() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI(null,null);
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function toBooking() {
		if (!$_GET["crs_id"]) {
			ilUtil::redirect("");
		}
		
		global $ilUser;
		
		$crs_id = intval($_GET["crs_id"]);
		$usr_id = $ilUser->getId();
		
		$this->ctrl->setParameterByClass("gevBookingGUI", "user_id", $usr_id);
		$this->ctrl->setParameterByClass("gevBookingGUI", "crs_id", $crs_id);
		$this->ctrl->redirectByClass("gevBookingGUI", "book");
	}
	
	protected function handleExplorerCommand() {
		
	}
	
	protected function checkProfileComplete($cmd, $next_class) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		global $ilUser;
		$utils = gevUserUtils::getInstanceByObj($ilUser);
		if (!$utils->isProfileComplete() && !($cmd == "toMyProfile" || $next_class == "gevuserprofilegui")) {
			ilUtil::sendFailure($this->lng->txt("gev_profile_incomplete"), true);
			$this->ctrl->redirect($this, "toMyProfile");
		}
	}
	
	protected function checkNeedsWBDRegistration($cmd, $next_class) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		global $ilUser;
		$utils = gevUserUtils::getInstanceByObj($ilUser);
		if ($utils->hasWBDRelevantRole() && !$utils->hasDoneWBDRegistration()) {

			//two ways: GEV is TP or  TPBasic
			if ($utils->canBeRegisteredAsTPService()) {
				if ($next_class != "gevwbdtpserviceregistrationgui") {
					$this->ctrl->redirectByClass("gevWBDTPServiceRegistrationGUI");
				}
			} else {
				if ($next_class != "gevwbdtpbasicregistrationgui") {
					$this->ctrl->redirectByClass("gevWBDTPBasicRegistrationGUI");
				}
			}
		}
	}

	protected function createHAUnit() {
		require_once("Services/GEV/Utils/classes/class.gevHAUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ha_utils = gevHAUtils::getInstance();
		
		global $ilUser;
		
		if ($ha_utils->hasHAUnit($ilUser->getId())) {
			throw new Exception("User ".$ilUser->getId()." already has an HA-Unit.");
		}
		
		$org_id = $ha_utils->createHAUnit($ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("gev_ha_org_unit_created"), true);
		
		$ref_id = gevObjectUtils::getRefId($org_id);
		$this->ctrl->setParameterByClass("ilLocalUserGUI", "ref_id", $ref_id);
		$this->ctrl->redirectByClass(array("ilAdministrationGUI","ilObjOrgUnitGUI","ilLocalUserGUI"), "index");
	}


}

?>