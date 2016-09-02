<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilObservationsTableGUI extends \ilTable2GUI {
	const ROW_COLOR_1 = "tblrow1";
	const ROW_COLOR_2 = "tblrow2";

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "") {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $a_parent_obj->getTXTClosure();

		$this->setId("talent_assessment_observations");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.talent_assessment_observations_list_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$this->setEnableTitle(true);
		$this->setShowRowsSelector(false);

		$this->addColumn($this->txt("title"), null);
		$this->addColumn($this->txt("description"), null);
		$this->addColumn($this->txt("requirement_title"), null);
		$this->addColumn($this->txt("requirement_description"), null);

		$this->setTitle($this->txt("observations_table_title"));

		$this->tbl_row_color = self::ROW_COLOR_2;
		$this->observation_id = null;

		$this->setData($this->parent_obj->getActions()->getBaseObservations($this->parent_obj->getSettings()->getCareerGoalId()));
	}

	public function fillRow($row) {
		if($this->observation_id != $row["obj_id"]) {
			$this->tpl->setVariable("TITLE", $row["title"]);
			$this->tpl->setVariable("DESCRIPTION", $row["description"]);
			$this->toggleRowColor();
			$this->observation_id = $row["obj_id"];
		}

		$this->tpl->setVariable("CSS_ROW", $this->tbl_row_color);
		$this->tpl->setVariable("REQUIREMENT_TITLE", $row["req_title"]);
		$this->tpl->setVariable("REQUIREMENT_DESCRIPTION", $row["req_description"]);
	}

	protected function toggleRowColor() {
		$act_color = $this->tbl_row_color;

		if($act_color == self::ROW_COLOR_1) {
			$this->tbl_row_color = self::ROW_COLOR_2;
		} else if($act_color == self::ROW_COLOR_2) {
			$this->tbl_row_color = self::ROW_COLOR_1;
		}
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
}