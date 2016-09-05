<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/UICore/classes/class.ilTemplate.php");

class ilObservationsListTableGUI extends \ilTable2GUI {
	use ilFormHelper;

	public function __construct($a_parent_obj, array $values, $a_parent_cmd = "", $a_template_context = "") {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $a_parent_obj->getTXTClosure();
		$this->values = $values;
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();
		$this->settings = $a_parent_obj->getSettings();

		$this->setId("talent_assessment_observations_list_table");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);

		$this->gCtrl->setParameter($this->parent_obj, "obs_id", $values["obs_id"]);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->gCtrl->clearParameters($this->parent_obj);

		$this->setRowTemplate("tpl.talent_assessment_observations_table_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$this->setEnableTitle(true);
		$this->setShowRowsSelector(false);
		$this->addButtomCMDButton($this->possible_cmd["CMD_OBSERVATION_SAVE_VALUES"], $this->txt("save"));

		$this->addColumn($this->txt("title_of_req"), null);
		$this->addColumn($this->txt("description_of_req"), null);
		$this->addColumn($this->txt("points"), null);

		$this->setTitle($this->values["title"]);
		$this->setDescription($this->values["description"]);

		$this->setData($values["requirements"]);
		$this->tpl = new \ilTemplate("tpl.talent_assessment_observations_list_table.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$this->setEnableNumInfo(false);
	}

	public function fillRow($row) {
		$this->tpl->setVariable("TITLE", $row["title"]);
		$this->tpl->setVariable("DESCRIPTION", $row["description"]);
		$this->tpl->setVariable("POINTS", $this->getDropDown($row["obj_id"], $row["value"], $this->settings->Finished()));
	}

	protected function addButtomCMDButton($cmd, $value) {
		$this->buttom_cmd_btn[$cmd] = $value;
	}

	public function render() {
		$this->tpl->setCurrentBlock("notice_input");
		$this->tpl->setVariable("NOTICE_DESCRIPTION", $this->txt("notice_description"));
		$this->tpl->setVariable("NOTICE_TEXT", $this->values["notice"]);

		if($this->settings->Finished()) {
			$this->tpl->setVariable("READONLY", 'readonly="readonly"');
		}

		$this->tpl->parseCurrentBlock();

		foreach ($this->buttom_cmd_btn as $key => $value) {
			$this->tpl->setCurrentBlock("bottom_cmd_button");
			$this->tpl->setVariable("CMD", $key);
			$this->tpl->setVariable("CMD_VALUE", $value);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->touchBlock("begin_bottom_cmd_button");

		return parent::render();
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