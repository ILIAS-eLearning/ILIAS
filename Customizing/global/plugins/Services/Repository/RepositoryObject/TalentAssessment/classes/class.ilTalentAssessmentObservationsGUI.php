<?php

use CaT\Plugins\TalentAssessment;

class ilTalentAssessmentObservationsGUI {
	const CMD_OBSERVATIONS = "showObservations";
	const CMD_OBSERVATIONS_LIST = "showObservationsList";
	const CMD_OBSERVATIONS_OVERVIEW = "showObservationsOverview";
	const CMD_OBSERVATIONS_CUMULATIVE = "showObservationsCumulative";
	const CMD_OBSERVATIONS_DIAGRAMM = "showObservationsDiagramm";
	const CMD_OBSERVATIONS_REPORT = "showObservationsReport";

	const CMD_OBSERVATION_START = "startObservation";
	const CMD_OBSERVATION_SAVE_VALUES = "saveObservationValues";
	const CMD_OBSERVATION_SAVE_REPORT = "saveObservationReport";
	const CMD_OBSERVATION_PREVIEW_REPORT = "showReportPreview";
	const CMD_FINISH_TA = "finishTA";



	public function __construct($parent_obj, $actions, Closure $txt, \CaT\Plugins\TalentAssessment\Settings\TalentAssessment $settings, $obj_id) {
		global $tpl, $ilCtrl, $ilToolbar, $ilTabs;

		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gToolbar = $ilToolbar;
		$this->gTabs = $ilTabs;
		$this->parent_obj = $parent_obj;
		$this->actions = $actions;
		$this->txt = $txt;
		$this->settings = $settings;
		$this->obj_id = $obj_id;

		$this->possible_cmd = array("CMD_OBSERVATION_SAVE_VALUES"=>self::CMD_OBSERVATION_SAVE_VALUES
								  , "CMD_OBSERVATION_SAVE_REPORT"=>self::CMD_OBSERVATION_SAVE_REPORT);
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCMD(self::CMD_OBSERVATIONS);

		switch($cmd) {
			case self::CMD_OBSERVATIONS:
			case self::CMD_FINISH_TA:
				$this->$cmd();
				break;
			case self::CMD_OBSERVATION_PREVIEW_REPORT:
				$this->$cmd();
				break;
			case self::CMD_OBSERVATIONS_LIST:
			case self::CMD_OBSERVATIONS:
			case self::CMD_OBSERVATIONS_OVERVIEW:
			case self::CMD_OBSERVATIONS_CUMULATIVE:
			case self::CMD_OBSERVATIONS_DIAGRAMM:
			case self::CMD_OBSERVATIONS_REPORT:
			case self::CMD_OBSERVATION_START:
			case self::CMD_OBSERVATION_SAVE_VALUES:
			case self::CMD_OBSERVATION_SAVE_REPORT:
				$this->setSubtabs($cmd);
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentObservationsGUI:: Unknown command ".$cmd);
		}
	}

	protected function showObservations() {
		$this->setToolbarObservations();
		$gui = new TalentAssessment\Observations\ilObservationsTableGUI($this);
		$this->gTpl->setContent($gui->getHtml());
	}

	protected function showObservationsList() {
		$gui = new TalentAssessment\Observations\ilObservationsListGUI($this);
		$this->gTpl->setContent($gui->render());
	}

	protected function showObservationsOverview() {
		$gui = new TalentAssessment\Observations\ilObservationsOverviewGUI($this);
		$this->gTpl->setContent($gui->render());
	}

	protected function showObservationsCumulative() {
		$gui = new TalentAssessment\Observations\ilObservationsCumulativeGUI($this);
		$this->gTpl->setContent($gui->render());
	}

	protected function showObservationsDiagramm() {
		$gui = new TalentAssessment\Observations\ilObservationsDiagrammGUI($this);
		$this->gTpl->setContent($gui->render());
	}

	protected function showObservationsReport() {
		$this->setToolbarReport();
		$gui = new TalentAssessment\Observations\ilObservationsReportGUI($this);
		$gui->show();
	}

	protected function saveObservationReport() {
		$this->actions->saveReportData($_POST);
		$red = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_REPORT, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function showReportPreview() {

	}

	protected function finishTA() {
		$this->actions->finishTA($this->requestsMiddle());

		$red = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_REPORT, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function setToolbarObservations() {
		$start_observation_link = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATION_START);
		$this->gToolbar->addButton( $this->txt("start_observation"), $start_observation_link);
	}

	protected function setToolbarReport() {
		$start_observation_link = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATION_PREVIEW_REPORT);
		$this->gToolbar->addButton( $this->txt("preview_report"), $start_observation_link);

		if(!$this->settings->Finished()) {
			$finish_ta_link = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_FINISH_TA);
			$this->gToolbar->addButton( $this->txt("finish_ta"), $finish_ta_link);
		}
	}

	protected function startObservation() {
		$this->actions->setObservationStarted(true);
		$this->actions->copyClassificationValues($this->settings->getCareerGoalId());
		$this->actions->copyObservations($this->getObjId(), $this->settings->getCareerGoalId());
		$red = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_LIST, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function saveObservationValues() {
		if(!isset($_GET["obs_id"]) || $_GET["obs_id"] == "") {
			throw new \Exception("No observation id given");
		}

		$obs_id = $_GET["obs_id"];
		$this->actions->setNoticeFor($obs_id, $_POST["notice"]);
		$this->actions->setPoints($_POST);

		$red = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_LIST, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function setSubtabs($activate) {
		$this->gTabs->addSubTab(self::CMD_OBSERVATIONS_LIST, $this->txt("observation_list")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS_LIST));
		$this->gTabs->addSubTab(self::CMD_OBSERVATIONS_OVERVIEW, $this->txt("observation_overview")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS_OVERVIEW));
		$this->gTabs->addSubTab(self::CMD_OBSERVATIONS_CUMULATIVE, $this->txt("observation_cumultativ")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS_CUMULATIVE));
		$this->gTabs->addSubTab(self::CMD_OBSERVATIONS_DIAGRAMM, $this->txt("observation_diagramm")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS_DIAGRAMM));
		$this->gTabs->addSubTab(self::CMD_OBSERVATIONS_REPORT, $this->txt("observation_report")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS_REPORT));

		$this->gTabs->activateSubTab($activate);
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code) {
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}

	public function getTXTClosure() {
		return $this->txt;
	}

	public function getActions() {
		return $this->actions;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getPossibleCMD() {
		return $this->possible_cmd;
	}

	public function getSettings() {
		return $this->settings;
	}
}