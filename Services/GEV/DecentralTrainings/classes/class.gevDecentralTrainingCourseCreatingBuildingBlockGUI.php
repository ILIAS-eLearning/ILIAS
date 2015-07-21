<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseBuildingBlockTableGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseBuildingBlockGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");


class gevDecentralTrainingCourseCreatingBuildingBlockGUI extends gevDecentralTrainingCourseBuildingBlockGUI {

	const AUTO_RELOAD_TIMEOUT_MS = 5000;
	
	public function __construct($a_crs_obj_id, $a_crs_request_id = null) {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilDB, $ilUser;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->log = $ilLog;
		$this->db = $ilDB;
		$this->search_form = null;

		$this->current_user = $ilUser;
		$this->crs_obj_id = $a_crs_obj_id;

		$this->crs_ref_id = ($a_crs_obj_id === null) ? null : gevCourseUtils::getInstance($a_crs_obj_id)->getRefId();
		$this->crs_request_id = $a_crs_request_id;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->determineObjId();
		$this->determineCrsRef();
		$this->determineCrsRequestId();

		$cmd = $this->ctrl->getCmd();
		$in_search = true;

		switch($cmd) {
			case "delete":
				$this->renderConfirm();
				break;
			case "deleteCourseBuildingBlock":
				$this->deleteCourseBuildingBlock($this->obj_id);
				break;
			case "add":
				$this->newCourseBuildingBlock();
				break;
			case "edit":
				$this->editCourseBuildingBlock();
				break;
			case "update":
				$this->updateCourseBuildingBlock();
				break;
			case "save":
				$this->saveCourseBuildingBlock();
				break;
			case "save_request":
				$this->saveRequest();
				break;
			case "update_request":
				$this->updateRequest();
				break;
			case "delete_request":
				$this->deleteRequest();
				break;
			case "showOpenRequests":
				$this->showOpenRequests();
				break;
			case "updateBuildingBlock":
				$this->updateBuildingBlock();
				break;
			case "redirect_to_tep":
				$this->redirectToTep();
				break;
			case "cancel":
			case "cancelDelete":
				$this->cancel();
				break;
			default:
				$this->render();
		}
	}

	protected function cancel() {
		if($this->crs_obj_id != null) {
			$this->updateBuildingBlock();
			return;
		}

		$this->render();
	}

	protected function updateBuildingBlock() {
		$spacer = new catHSpacerGUI();
		$spacer_out = $spacer->render();
		
		$this->ctrl->setParameter($this,"crs_request_id",$this->crs_request_id);
		$this->ctrl->setParameter($this, "crs_obj_id", $this->crs_obj_id);
		$crs_tbl = $this->getTableUpdate();

		$this->tpl->setContent($crs_tbl->getHTML());
		$this->ctrl->setParameter($this,"crs_request_id",null);
		$this->ctrl->setParameter($this, "crs_ref_id", null);
	}

	protected function getTableUpdate() {
		$crs_tbl = new gevDecentralTrainingCourseBuildingBlockTableGUI($this,$this->crs_ref_id,$this->crs_request_id);
		$crs_tbl->setTitle("gev_dec_crs_creation_building_block_title")
				->setSubtitle("gev_dec_crs_creation_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				->addCommandButton("add",$this->lng->txt("add"));
				
		$crs_tbl->addCommandButton("redirect_to_tep",$this->lng->txt("update"));

		return $crs_tbl;
	}

	protected function getTable() {
		$crs_tbl = new gevDecentralTrainingCourseBuildingBlockTableGUI($this,$this->crs_ref_id,$this->crs_request_id);
		$crs_tbl->setTitle("gev_dec_crs_creation_building_block_title")
				->setSubtitle("gev_dec_crs_creation_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				->addCommandButton("add",$this->lng->txt("add"));
				
		$crs_tbl->addCommandButton("save_request",$this->lng->txt("save"));
		$crs_tbl->addCommandButton("delete_request",$this->lng->txt("delete"));
		return $crs_tbl;
	}

	protected function saveRequest() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$crs_request = $request_db->request((int)$this->crs_request_id);
		$crs_request->request();

		return $this->ctrl->redirect($this, "showOpenRequests");
	}

	protected function deleteRequest() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$crs_request = $request_db->request((int)$this->crs_request_id);
		$crs_request->delete();

		$this->ctrl->redirectByClass(array("ilTEPGUI"));
	}

	protected function showOpenRequests() {
		$requests = $this->getOpenCreationRequests();
		if ($this->userCanOpenNewCreationRequest() && !$this->userCanOpenMultipleRequests()) {
			return $this->redirectToBookingFormOfLastCreatedTraining();
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$view = $this->getOpenRequestsView($requests, !$this->userCanOpenNewCreationRequest());
		
		$this->tpl->setContent( $title->render()
			  . $view);
	}

	protected function redirectToTep() {
		$this->ctrl->redirectByClass(array("ilTEPGUI"));
	}

	protected function getOpenCreationRequests() {
		if ($this->open_creation_requests === null) {
			$db = $this->getRequestDB();
			$this->open_creation_requests = $db->openRequestsOfUser((int)$this->current_user->getId());
		}
		return $this->open_creation_requests;
	}

	protected function userCanOpenNewCreationRequest() {
		if ($this->userCanOpenMultipleRequests()) {
			return true;
		}
		return count($this->getOpenCreationRequests()) === 0;
	}

	protected function userCanOpenMultipleRequests() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		return $user_utils->isAdmin();
	}

	protected function redirectToBookingFormOfLastCreatedTraining() {
		$obj_id = $this->lastCreatedCourseId();
		if (!$obj_id) {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ref_id = gevObjectUtils::getRefId($obj_id);
		
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		
		$this->ctrl->setParameter($this, "obj_id", $obj_id);
		ilCourseBookingAdminGUI::setBackTarget($this->ctrl->getLinkTarget($this, "backFromBooking"));
		$this->ctrl->setParameter($this, "obj_id", null);
		
		$this->ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $ref_id);
		$this->ctrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}

	protected function getOpenRequestsView(array $a_requests, $a_do_autoload = false) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$tpl = new ilTemplate("tpl.open_requests.html", true, true, "Services/GEV/DecentralTrainings");
		
		$tpl->setCurrentBlock("header");
		$tpl->setVariable("HEADER", $this->lng->txt("gev_dec_training_open_requests_header"));
		$tpl->parseCurrentBlock();
		
		if (count($a_requests) > 0) {
			$tpl->setCurrentBlock("requests");
			foreach ($a_requests as $request) {
				$tpl->setCurrentBlock("request");
				$tpl->setVariable("TITLE", ilObject::_lookupTitle($request->templateObjId()));
				$settings = $request->settings();
				$start = explode(", ", ilDatePresentation::formatDate($settings->start()));
				$tpl->setVariable("DATE", $start[0]);
				$tpl->setVariable("START_TIME", $start[1]);
				$end = explode(" ", ilDatePresentation::formatDate($settings->end()));
				$tpl->setVariable("END_TIME", $end[1]);
				$tpl->parseCurrentBlock();
			}
			$tpl->parseCurrentBlock();
		}
		else {
			$tpl->touchBlock("no_requests");
		}

		$tpl->setCurrentBlock("footer");
		$wait_m = $this->getWaitingTime();
		$time_info = sprintf($this->lng->txt("gev_dec_training_open_requests_time_info"), $wait_m);
		$tpl->setVariable("FOOTER", $time_info);
		$tpl->parseCurrentBlock();

		if ($a_do_autoload) {
			$tpl->setCurrentBlock("autoreload");
			$tpl->setVariable("TIMEOUT", self::AUTO_RELOAD_TIMEOUT_MS);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}

	protected function getRequestDB() {
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		return $dec_utils->getCreationRequestDB();
	}

	protected function getWaitingTime() {
		$db = $this->getRequestDB();
		return $db->waitingTimeInMinuteEstimate();
	}

	protected function lastCreatedCourseId() {
		$db = $this->getRequestDB();
		return $db->lastCreatedTrainingOfUser($this->current_user->getId());
	}

	protected function updateCourseBuildingBlock() {
		$form = $this->initForm(self::NEW_UNIT);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->editCourseBuildingBlock($form);
		}

		$building_bock_value = $form->getInput("build_block");

		if($building_bock_value == "-1") {
			ilUtil::sendFailure($this->lng->txt("gev_dec_please_select_building_block"),false);
			return $this->editCourseBuildingBlock($form);
		}

		//did you passed ne max duration for a day (12 hours)
		if(gevCourseBuildingBlockUtils::getMaxDurationReached($this->crs_ref_id, $this->crs_request_id, $form->getInput("time"))){
			$message = $this->lng->txt("gev_dec_max_duration_reached_part");

			ilUtil::sendFailure($message,false);
			return $this->newCourseBuildingBlock($form);
		}

		$time = $form->getInput("time");
		require_once ("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
		$bu_utils = gevCourseBuildingBlockUtils::getInstance($form->getInput("id"));
		$bu_utils->loadData();
		
		$bu_utils->setBuildingBlock($form->getInput("build_block"));
		$bu_utils->setStartDate($time["start"]["date"]." ".$time["start"]["time"]);
		$bu_utils->setEndDate($time["end"]["date"]." ".$time["end"]["time"]);
		$bu_utils->setMethods($form->getInput("methods"));
		$bu_utils->setMedia($form->getInput("media"));

		$bu_utils->update();

		if($this->crs_obj_id != null) {
			$this->updateBuildingBlock();
			return;
		}

		$this->render();
		
	}

	protected function saveCourseBuildingBlock() {
		$form = $this->initForm(self::NEW_UNIT);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->newCourseBuildingBlock($form);
		}
		
		//is a buldingblock selected
		$building_bock_value = $form->getInput("build_block");
		if($building_bock_value == -1) {
			ilUtil::sendFailure($this->lng->txt("gev_dec_please_select_building_block"),false);
			return $this->newCourseBuildingBlock($form);
		}
		$time = $form->getInput("time");
		//did you passed ne max duration for a day (12 hours)
		if(gevCourseBuildingBlockUtils::getMaxDurationReached($this->crs_ref_id, $this->crs_request_id, $time)){
			$message = $this->lng->txt("gev_dec_max_duration_reached_part");

			ilUtil::sendFailure($message,false);
			return $this->newCourseBuildingBlock($form);
		}

		$time = $form->getInput("time");
		$newId = $this->db->nextId("dct_crs_building_block");

		$bu_utils = gevCourseBuildingBlockUtils::getInstance($newId);

		$bu_utils->setCrsId($this->crs_ref_id);
		$bu_utils->setBuildingBlock($form->getInput("build_block"));
		$bu_utils->setStartDate($time["start"]["date"]." ".$time["start"]["time"]);
		$bu_utils->setEndDate($time["end"]["date"]." ".$time["end"]["time"]);
		$bu_utils->setMethods($form->getInput("methods"));
		$bu_utils->setMedia($form->getInput("media"));

		if($this->crs_request_id) {
			$bu_utils->setCourseRequestId($this->crs_request_id);
		}

		$bu_utils->save();

		if($this->crs_obj_id != null) {
			$this->updateBuildingBlock();
			return;
		}

		$this->render();
	}

	public function deleteCourseBuildingBlock($a_obj_id) {
		
		$bb_utils = gevCourseBuildingBlockUtils::getInstance($a_obj_id);
		$bb_utils->loadData();
		$bb_utils->delete();

		if($this->crs_obj_id != null) {
			$this->updateBuildingBlock();
			return;
		}

		$this->render();
	}
}
?>