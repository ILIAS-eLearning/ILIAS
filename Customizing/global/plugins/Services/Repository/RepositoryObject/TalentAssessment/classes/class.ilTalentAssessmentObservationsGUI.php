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

		$this->possible_cmd = array("CMD_OBSERVATION_SAVE_VALUES"=>self::CMD_OBSERVATION_SAVE_VALUES);
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCMD(self::CMD_OBSERVATIONS);

		switch($cmd) {
			case self::CMD_OBSERVATIONS:
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
				$this->setSubtabs($cmd);
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentObservationsGUI:: Unknown command ".$cmd);
		}
	}

	protected function showObservations() {
		$this->setToolbar();
		$gui = new TalentAssessment\Observations\ilObservationsTableGUI($this);
		$this->gTpl->setContent($gui->getHtml());
	}

	protected function showObservationsList() {
		$gui = new TalentAssessment\Observations\ilObservationsListGUI($this);
		$this->gTpl->setContent($gui->render());
	}

	protected function showObservationsOverview() {

	}

	protected function showObservationsCumulative() {

	}

	protected function showObservationsDiagramm() {

	}

	protected function showObservationsReport() {

	}

	protected function setToolbar() {
		$start_observation_link = $this->gCtrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATION_START);
		$this->gToolbar->addButton( $this->txt("start_observation"), $start_observation_link);
	}

	protected function startObservation() {
		$this->actions->setObservationStarted(true);
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