<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/UICore/classes/class.ilTemplate.php");

class ilObservationsListLegendTableGUI extends \ilTable2GUI {
	use ilFormHelper;

	const SI_PREFIX = "req_id";

	public function __construct($a_parent_obj, array $values, $a_parent_cmd = "", $a_template_context = "") {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $a_parent_obj->getTXTClosure();
		$this->values = $values;
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();

		$this->setId("talent_assessment_observations_list_legend_table");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);
		$this->setEnableTitle(false);
		$this->setShowRowsSelector(false);
		$this->setEnableNumInfo(false);

		$this->gCtrl->setParameter($this->parent_obj, "obs_id", $values["obs_id"]);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->gCtrl->clearParameters($this->parent_obj);

		$this->setRowTemplate("tpl.talent_assessment_observations_legend_table_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->addColumn($this->txt("points"), null);
		$this->addColumn($this->txt("description"), null);

		$this->setData($values);
	}

	public function fillRow($row) {
		$this->tpl->setVariable("POINTS", $row["points"]);
		$this->tpl->setVariable("DESCRIPTION", $row["description"]);
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