<?php
require_once(__DIR__."/class.ilMyObservationsTableGUI.php");
require_once(__DIR__."/class.ilAllObservationsTableGUI.php");
require_once("./Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

use \CaT\Plugins\TalentAssessment;

/**
 * @ilCtrl_isCalledBy ilMyObservationsGUI: gevDesktopGUI
 */
class ilMyObservationsGUI {
	const FILTER_SESSION_VAR_MY = "my_assessments";
	const FILTER_SESSION_VAR_ALL = "all_assessments";

	const MODE_MY = "mode_my";
	const MODE_ALL = "mode_all";

	const TA_IN_PROGRESS = 1;
	const TA_PASSED = 2;
	const TA_MAYBE = 3;
	const TA_FAILED = 4;

	public function __construct($parent_obj, $mode) {
		global $tpl, $ilCtrl, $rbacreview, $ilUser;

		$this->parent_obj = $parent_obj;
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->mode = $mode;
		$this->gRbacreview = $rbacreview;
		$this->gUser = $ilUser;

		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
							ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", "xtas"));
		$this->txt = $this->plugin->txtClosure();
		$this->filter_settings = false;
		
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();

		switch($cmd) {
			case "toMyAssessments";
				$this->mode = self::MODE_MY;
				$this->myAssessments();
				break;
			case "toAllAssessments";
				$this->mode = self::MODE_ALL;
				$this->allAssessments();
				break;
			case "performMyAssFilter":
				$this->mode = self::MODE_MY;
				$this->performFilter();
				break;
			case "performAllAssFilter":
				$this->mode = self::MODE_ALL;
				$this->performFilter();
			case "showPDF":
				$this->showPDF();
				break;
		}
	}

	protected function showPDF() {
		$this->mode = $_GET["mode"];

		require_once($this->plugin->getDirectory()."/classes/class.ilObjTalentAssessment.php");
		$ta_obj = new ilObjTalentAssessment($_GET["xtas_ref_id"]);
		$settings = $ta_obj->getSettings();
		$actions = $ta_obj->getActions();

		$pdf = new TalentAssessment\Observations\ilResultPDF($settings, $actions, $this->txt);
		$file_name = "TA_".$settings->getFirstname()."_".$settings->getLastname();
		try {
			$pdf->show($file_name, "D");
		} catch(\Exception $e) {
			throw new \Exception($this->txt("pdf_to_long"));
		}

		$this->render();
	}

	public function loadFilterSettings() {
		if ($this->filter_settings !== false) {
			return $this->filter_settings;
		}

		$tmp = ilSession::get($this->getSessionVar());
		if ($tmp !== null) {
			$this->filter_settings =  unserialize($tmp);
		}
		else {
			$this->filter_settings = null;
		}

		return $this->filter_settings;
	}

	protected function performFilter() {

		$display = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory(), new \CaT\Filter\TypeFactory());

		$filter = $this->myAssessmentsFilter();

		$settings = $display->buildFilterValues($filter, $_POST["filter"]);
		$this->saveFilterSettings($_POST["filter"]);

		if($this->mode == self::MODE_MY) {
			$this->gCtrl->redirect($this, "toMyAssessments");
		} else if($this->mode == self::MODE_ALL) {
			$this->gCtrl->redirect($this, "toAllAssessments");
		}
	}

	protected function saveFilterSettings($settings) {
		ilSession::set($this->getSessionVar(), serialize($settings));
		$this->filter_settings = $settings;
	}

	public function flushFilterSettings() {
		ilSession::clear($this->getSessionVar());
		$this->filter_settings = null;
	}

	public function render() {
		switch($this->mode) {
			case self::MODE_MY:
				$this->myAssessments();
				break;
			case self::MODE_ALL:
				$this->allAssessments();
		}
	}

	protected function myAssessments() {
		$this->filter_settings = $this->loadFilterSettings();

		$gui = new \ilMyObservationsTableGUI($this, $this->plugin);

		$fs = $this->myAssessmentsFilter();
		$df = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory(), new \CaT\Filter\TypeFactory());
		$ff = new catFilterFlatViewGUI($this, $fs, $df, $this->mode, "performMyAssFilter");

		$gui->setFilter($ff);
		$gui->setFilterVals($this->filter_settings);

		$filter_values = array();
		if($this->filter_settings) {
			$filter_values = $df->buildFilterValues($fs, $this->filter_settings);
		}

		$base_data = $this->plugin->getObservationsDB()->getAssessmentsData($fs, $filter_values);
		$data = array();

		foreach ($base_data as $key => $row) {
			$row = $this->addVenueName($row);
			$row = $this->addOrgUnitTitle($row);
			$row = $this->calcStartDate($row);
			$row = $this->addObservator($row);
			$row = $this->addOrgUnitSupervisor($row);

			if ($this->observatorIn(array($this->gUser->getId()), $row)) {
				$data[$key] = $row;
			}
		}

		$gui->setData($data);

		$this->gTpl->setContent($gui->getHtml());
	}

	protected function addVenueName($data) {
		$org_unit_utils = gevOrgUnitUtils::getInstance($data["venue"]);
		$data["venue_title"] = $org_unit_utils->getTitle();

		return $data;
	}

	protected function addObservator($data) {
		$role_id = $this->gRbacreview->roleExists(TalentAssessment\ilActions::OBSERVATOR_ROLE_NAME."_".$data["obj_id"]);
		$usrs = $this->gRbacreview->assignedUsers($role_id, array("usr_id", "firstname", "lastname"));
		$usrs_names = array();
		$usrs_ids = array();
		foreach ($usrs as $key => $value) {
			$usrs_names[] = $value["lastname"]." ".$value["firstname"];
			$usrs_ids[] = $value["usr_id"];
		}

		$data["observator"] = implode(", ", $usrs_names);
		$data["observator_ids"] = $usrs_ids;

		return $data;
	}

	protected function addOrgUnitTitle($data) {
		$org_unit_utils = gevOrgUnitUtils::getInstance($data["org_unit"]);
		$data["org_unit_title"] = $org_unit_utils->getTitle();

		return $data;
	}

	protected function addOrgUnitSupervisor($data) {
		$supervisor = gevOrgUnitUtils::getSuperiorsIn(array(gevObjectUtils::getRefId($data["org_unit"])));

		$names = array();
		foreach ($supervisor as $key => $value) {
			$user_utils = gevUserUtils::getInstance((int)$value);
			$names[] = $user_utils->getLastname()." ".$user_utils->getFirstname();
		}

		$data["supervisor"] = implode(", ",$names);

		return $data;
	}

	protected function calcStartDate($row) {
		$start_date = explode(" ", $row["start_date"]);
		$end_date = explode(" ", $row["end_date"]);

		if($start_date[0] == $end_date[0]) {
			$row["start_date_text"] = $start_date[0];
		} else {
			$row["start_date_text"] = $start_date[0]." - ".$end_date[0];
		}

		return $row;
	}

	protected function myAssessmentsFilter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);

		return $f->sequence($f->sequence
			( $f->dateperiod
				( $this->txt("dateperiod_choice_label")
				, ""
				)
			  , $f->multiselect
				( $this->txt("result")
				 , ""
				 , array(self::TA_IN_PROGRESS=>$this->txt("ta_in_progress")
				 		,self::TA_PASSED=>$this->txt("ta_passed")
				 		,self::TA_MAYBE=>$this->txt("ta_maybe")
				 		,self::TA_FAILED=>$this->txt("ta_failed")
				   )
				)
			  , $f->multiselect
				( $this->txt("career_goal")
				 , ""
				 , $this->plugin->getSettingsDB()->getCareerGoalsOptions()
				)
			  , $f->multiselect
				( $this->txt("org_unit")
				 , ""
				 , $this->getOrgUnitOptions()
				)
			));
	}

	protected function allAssessments() {
		$this->filter_settings = $this->loadFilterSettings();

		$gui = new \ilAllObservationsTableGUI($this, $this->plugin, $this->mode);

		$fs = $this->allAssessemntsFilter();
		$df = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory(), new \CaT\Filter\TypeFactory());
		$ff = new catFilterFlatViewGUI($this, $fs, $df, "performAllAssFilter");

		$gui->setFilter($ff);
		$gui->setFilterVals($this->filter_settings);

		$filter_values = array();
		if($this->filter_settings) {
			$filter_values = $df->buildFilterValues($fs, $this->filter_settings);
		}

		$base_data = $this->plugin->getObservationsDB()->getAssessmentsData($fs, $filter_values);

		foreach ($base_data as $key => $row) {
			$row = $this->addVenueName($row);
			$row = $this->addOrgUnitTitle($row);
			$row = $this->calcStartDate($row);
			$row = $this->addObservator($row);
			$row = $this->addOrgUnitSupervisor($row);

			if(empty($filter_values[5])) {
				$data[$key] = $row;
			} else if($this->observatorIn($filter_values[5], $row)) {
				$data[$key] = $row;
			}
		}

		$gui->setData($data);
		$this->gTpl->setContent($gui->getHtml());
	}

	public function observatorIn(array $observators, $row) {
		return count(array_intersect($observators, $row["observator_ids"])) > 0;
	}

	protected function allAssessemntsFilter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);

		return $f->sequence($f->sequence
			( $f->dateperiod
				( $this->txt("dateperiod_choice_label")
				, ""
				)
			  ,$f->multiselect
				( $this->txt("result")
				 , ""
				 , array(self::TA_IN_PROGRESS=>$this->txt("ta_in_progress")
				 		,self::TA_PASSED=>$this->txt("ta_passed")
				 		,self::TA_MAYBE=>$this->txt("ta_maybe")
				 		,self::TA_FAILED=>$this->txt("ta_failed")
				   )
				)
			  , $f->multiselect
				( $this->txt("career_goal")
				 , ""
				 , $this->plugin->getSettingsDB()->getCareerGoalsOptions()
				)
			  , $f->multiselect
				( $this->txt("org_unit")
				 , ""
				 , $this->getOrgUnitOptions()
				)
			  , $f->multiselect
				( $this->txt("observator")
				 , ""
				 , $this->plugin->getSettingsDB()->getAllObservator(TalentAssessment\ilActions::OBSERVATOR_ROLE_NAME)
				)
			));
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

	protected function getSessionVar() {
		switch($this->mode) {
			case self::MODE_MY:
				return self::FILTER_SESSION_VAR_MY;
				break;
			case self::MODE_ALL:
				return self::FILTER_SESSION_VAR_ALL;
		}
	}

	protected function getOrgUnitOptions() {
		$evg_id = gevOrgUnitUtils::getEVGOrgUnitRefId();
		$org_unit_utils = gevOrgUnitUtils::getAllChildren(array($evg_id));

		$ret = array();
		foreach($org_unit_utils as $key => $value) {
			$ret[$value["obj_id"]] = ilObject::_lookupTitle($value["obj_id"]);
		}

		return $ret;
	}
}
