<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* My Trainings Appointments GUI for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevMyTrainingsApGUI: ilParticipationStatusAdminGUI
*
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApTableGUI.php");
//require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
//require_once("Services/GEV/Desktop/classes/class.gevPeriodSelectorGUI.php");

class gevMyTrainingsApGUI {

	public $crs_ref_id;

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->user = &$ilUser;
		$this->log = &$ilLog;
	
		$this->crs_ref_id = false;

	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		if (!$cmd) {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
			case "memberList":
				$cont = $this->$cmd();
				break;

			//participation-status commands	
			//case "setParticipationStatus":
			case "listStatus":
			case  "listParticipationStatus":
				$cont = $this->listParticipationStatus();
				break;

			case "finalize":
			case "confirmFinalize":
			case "saveStatusAndPoints":
			case "uploadAttendanceList":
				//ilParticipationStatusTableGUI
				require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
				$crs_ref_id = $this->getCrsRefId();
				$gui = ilParticipationStatusAdminGUI::getInstanceByRefId($crs_ref_id);
				
				$gui->from_foreign_class = 'gevMyTrainingsApGUI';
				$gui->crs_ref_id = $crs_ref_id;

				//$gui->returnToList();
				//die('forwarding cmd');
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			default:
				$errstr = "gevMyTrainingsApGUI: Unknown command '".$cmd."'";
				$this->log->write($errstr);
				throw new ilException($errstr);
		}
		
		if ($cont) {
			$this->tpl->setContent($cont);
		}
	}
	


	public function getCrsRefId() {
		$crs_ref_id = $_GET['crsrefid'];

		if(! $crs_ref_id){
			throw new ilException("gevMyTrainingsApGUI - needs course-refid");
		}
		return $crs_ref_id;
	}





	// std-view, my trainings-ap-table;
	public function view() {
		$trainings_table = new gevMyTrainingsApTableGUI($this->user->getId(), $this);
		return (
			$trainings_table->getHTML()
		);
	}


	public function memberList() {
		
		$crs_ref_id = $this->getCrsRefId();
		$this->ctrl->redirect($this, "view");
	}


	public function listParticipationStatus() {
		global $lng;
			
		$crs_ref_id = $this->getCrsRefId();

		$title = new catTitleGUI("gev_set_course_status_title", "gev_set_course_status_title_desc", "GEV_img/ico-head-edubio.png");
		$spacer = new catHSpacerGUI();
		

		global $ilTabs, $ilCtrl, $lng;
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "view"));
		
		//ilParticipationStatusTableGUI
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusTableGUI.php");
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$crs_obj = new ilObjCourse($crs_ref_id);
		
		$lng->loadLanguageModule("ptst");

		$ptstatus_admingui =  ilParticipationStatusAdminGUI::getInstanceByRefId($crs_ref_id);
		//$ptstatus_admingui =  new ilParticipationStatusAdminGUI($crs_obj);
		$may_write = $ptstatus_admingui->mayWrite();
		if($ptstatus_admingui->getParticipationStatus()->getMode() == ilParticipationStatus::MODE_CONTINUOUS)
		{
			$may_finalize = false;
		}
		else
		{
			$may_finalize = $may_write;
		}
		$ptstatusgui = new ilParticipationStatusTableGUI($this, 'listParticipationStatus', $crs_obj, $may_write, $may_finalize);
		
		$form_action = $ptstatusgui->getFormAction();
		$form_action .= '&crsrefid=' .$crs_ref_id;
		$ptstatusgui->setFormAction($form_action);

		return (
				$title->render()
			   .$spacer->render()
			   .$ptstatusgui->getHTML()
			   );
	}


}

?>