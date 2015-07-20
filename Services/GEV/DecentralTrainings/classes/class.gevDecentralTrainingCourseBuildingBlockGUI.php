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
require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevDecentralTrainingCourseBuildingBlockGUI {
	const NEW_UNIT = "new";
	const EDIT_UNIT = "edit";
	const MINUTE_STEP_SIZE = 15;
	protected $obj_id = null;
	protected $crs_ref_id = -1;
	protected $crs_request_id = null;

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilDB;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->log = $ilLog;
		$this->db = $ilDB;
		$this->search_form = null;

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
				$this->render();
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
			default:
				$this->render();
		}
		
	}

	private function deleteCourseBuildingBlock($a_obj_id) {
		
		$bb_utils = gevCourseBuildingBlockUtils::getInstance($a_obj_id);
		$bb_utils->loadData();
		$bb_utils->delete();

		return;
	}

	private function renderConfirm() {
		include_once "./Services/User/classes/class.ilUserUtil.php";
		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this, "assignMembers"));
		$confirm->setHeaderText($this->lng->txt("gev_dec_building_block_delete_confirm"));
		$confirm->setConfirm($this->lng->txt("confirm"), "deleteCourseBuildingBlock");
		$confirm->setCancel($this->lng->txt("cancel"), "view");
		
		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevCourseBuildingBlockUtils::getInstance($this->obj_id);
		$bu_utils->loadData();

		$confirm->addItem("id",
				$this->obj_id,
				$bu_utils->getBuildingBlock()->getTitle()
			);
		
		$this->tpl->setContent($confirm->getHTML());
	}

	protected function render() {


		$spacer = new catHSpacerGUI();
		$spacer_out = $spacer->render();
		//die("sdsd");
		
		$this->ctrl->setParameter($this,"crs_request_id",$this->crs_request_id);
		$crs_tbl = new gevDecentralTrainingCourseBuildingBlockTableGUI($this,$this->crs_ref_id,$this->crs_request_id);
		$crs_tbl->setTitle("gev_dec_crs_building_block_title")
				->setSubtitle("gev_dec_crs_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				->addCommandButton("add",$this->lng->txt("add"));

		$this->tpl->setContent($crs_tbl->getHTML());
		$this->ctrl->setParameter($this,"crs_request_id",null);
	}

	protected function determineObjId() {
		if(isset($_GET["id"])) {
			$this->obj_id = $_GET["id"];
		}

		if(isset($_POST["id"])) {
			$this->obj_id = $_POST["id"];
		}
	}

	protected function determineCrsRef() {
		if(isset($_GET["ref_id"])) {
			$this->crs_ref_id = $_GET["ref_id"];
		}
	}

	protected function determineCrsRequestId() {
		if(isset($_POST["crs_request_id"])) {
			$this->crs_request_id = $_POST["crs_request_id"];
		}

		if(isset($_GET["crs_request_id"])) {
			$this->crs_request_id = $_GET["crs_request_id"];
		}
	}

	protected function newCourseBuildingBlock($a_form = null) {
		$form = ($a_form === null) ? $this->initForm(self::NEW_UNIT) : $a_form;
		$this->tpl->setContent($form->getHtml());
	}

	protected function editCourseBuildingBlock($a_form = null) {
		$form = ($a_form === null) ? $this->initForm(self::EDIT_UNIT) : $a_form;
		$this->tpl->setContent($form->getHtml());
	}

	protected function initForm($a_mode) {

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this));

		if($a_mode == self::EDIT_UNIT) {
			$bu_utils = gevCourseBuildingBlockUtils::getInstance($this->obj_id);
			$bu_utils->loadData();

			$vals = array(
					 "id" => $bu_utils->getId()
					,"time" => $bu_utils->getTime()
					,"methods" => $bu_utils->getMethods()
					,"media" => $bu_utils->getMedia()
					,"build_block" => $bu_utils->getBuildingBlock()->getId()
				);

			$form_gui->setTitle($this->lng->txt("gev_dec_crs_building_block_edit"));

			$tmplt_id = new ilHiddenInputGUI("id");
			$tmplt_id->setValue($vals["id"]);
			$form_gui->addItem($tmplt_id);

		}else {
			
			$tmplt_id = new ilHiddenInputGUI("id");
			$form_gui->addItem($tmplt_id);

			$form_gui->setTitle($this->lng->txt("gev_dec_crs_building_block_new"));
		}

		$crs_request_id = new ilHiddenInputGUI("crs_request_id");
		if($this->crs_request_id !== null) {
			$crs_request_id->setValue($this->crs_request_id);
		}
		$form_gui->addItem($crs_request_id);

		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->lng->txt("gev_dec_crs_building_block_base_data"));
		$form_gui->addItem($sec_l);

		require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($no_changes_allowed);
		$time->setMinuteStepSize(self::MINUTE_STEP_SIZE);


		if($a_mode == self::EDIT_UNIT) {
			$time->setValueByArray($vals);
		}
		$form_gui->addItem($time);

		
		$bb_sets = gevBuildingBlockUtils::getPossibleBuildingBlocks();
		$bb_options = array("-1"=>"-");
		$building_block = new ilSelectInputGUI("Baustein","build_block");
		$building_block->setOptions($bb_options + $bb_sets);
		$building_block->setRequired(true);
		if($a_mode == self::EDIT_UNIT) {
			$building_block->setValue($vals["build_block"]);
		}

		$form_gui->addItem($building_block);

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$amd_utils = gevAMDUtils::getInstance();
		$method_options = $amd_utils->getOptions(gevSettings::CRS_AMD_METHODS);
		$cbx_group_methods = new ilCheckBoxGroupInputGUI("Methoden","methods");
		$cbx_group_methods->setRequired(true);

		foreach($method_options as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_methods->addOption($option);
		}

		if($a_mode == self::EDIT_UNIT) {
			$cbx_group_methods->setValue($vals["methods"]);
		}
		$form_gui->addItem($cbx_group_methods);

		$media_options = $amd_utils->getOptions(gevSettings::CRS_AMD_MEDIA);
		$cbx_group_media = new ilCheckBoxGroupInputGUI("Medien","media");
		$cbx_group_media->setRequired(true);

		foreach($media_options as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_media->addOption($option);
		}
		
		if($a_mode == self::EDIT_UNIT) {
			$cbx_group_media->setValue($vals["media"]);
		}

		$form_gui->addItem($cbx_group_media);

		
		if($this->obj_id !== null && $this->obj_id != "") {
			$form_gui->addCommandButton("update", $this->lng->txt("save"));
		} else {
			$form_gui->addCommandButton("save", $this->lng->txt("save"));
		}

		$form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		

		return $form_gui;
	}

	protected function updateCourseBuildingBlock() {
		$form = $this->initForm(self::NEW_UNIT);

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->editCourseBuildingBlock($form);
		}

		$building_bock_value = $form->getInput("build_block");

		if($building_bock_value == "-1") {
			ilUtil::sendFailure($this->lng->txt("gev_dct_please_select_building_block"),false);
			return $this->editCourseBuildingBlock($form);
		}

		//did you passed ne max duration for a day (12 hours)
		if(gevCourseBuildingBlockUtils::getMaxDurationReached($this->crs_ref_id, $crs_request_id, $form->getInput("time"))){
			$message = $this->lng->txt("gev_dct_max_duration_reached_part1");
			$message .= $this->getFormattedRemainingTime(gevCourseBuildingBlockUtils::getRemainingTime($this->crs_ref_id, $this->crs_request_id));
			$message .= $this->lng->txt("gev_dct_max_duration_reached_part2");

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

		$this->render($in_search);
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
			ilUtil::sendFailure($this->lng->txt("gev_dct_please_select_building_block"),false);
			return $this->newCourseBuildingBlock($form);
		}

		//did you passed ne max duration for a day (12 hours)
		if(gevCourseBuildingBlockUtils::getMaxDurationReached($this->crs_ref_id, $crs_request_id, $form->getInput("time"))){
			$message = $this->lng->txt("gev_dct_max_duration_reached_part1");
			$message .= $this->getFormattedRemainingTime(gevCourseBuildingBlockUtils::getRemainingTime($this->crs_ref_id, $this->crs_request_id));
			$message = $this->lng->txt("gev_dct_max_duration_reached_part2");

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

		$this->render();
	}

	public function setCourseRequestId($a_crs_request_id) {
		$this->crs_request_id = $a_crs_request_id;
	}

	/*
	* @return string
	*/
	private function getFormattedRemainingTime($a_minutes) {
		if($a_minutes < 60) {
			return "0 Stunden und $a_minutes Minuten ";
		}

		$hours = $a_minutes / 60;
		$r_minutes = $a_minutes - $hours * 60;

		return "$hours Stunden und $r_minutes Minuten ";
	}
}

?>