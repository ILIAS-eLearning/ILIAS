<?php

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");

class ilMyObservationsTableGUI extends catTableGUI {
	public function __construct($a_parent_obj, $plugin, $a_parent_cmd = "", $a_template_context = "") {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $plugin->txtClosure();
		$this->txt_prefix = $plugin->getPrefix()."_";
		$this->values = $values;

		$this->setId("my_observations_view");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableTitle(true);
		$this->setEnableFilter(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(false);
		$this->setTitle($this->txt_prefix."my_observations");
		$this->setSubtitle($this->txt_prefix."my_observations_info");

		$this->in_progress = '<img src="'.ilUtil::getImagePath("scorm/not_attempted.png").'" />';
		$this->passed = '<img src="'.ilUtil::getImagePath("scorm/completed.png").'" />';
		$this->maybe = '<img src="'.ilUtil::getImagePath("scorm/incomplete.png").'" />';
		$this->failed = '<img src="'.ilUtil::getImagePath("scorm/failed.png").'" />';

		$this->setLegend($this->createLegend());
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));

		$this->setRowTemplate("tpl.talent_assessment_my_observations_view_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->addColumn($this->txt("career_goal"), null);
		$this->addColumn($this->txt("fullname"), null);
		$this->addColumn($this->txt("org_unit"), null);
		$this->addColumn($this->txt("org_unit_supervisor"),null);
		$this->addColumn($this->txt("venue"),null);
		$this->addColumn($this->txt("date"),null);
		$this->addColumn($this->txt("observator"),null);
		$this->addColumn($this->txt("result"),null);
		$this->addColumn($this->txt("actions"),null);
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

	protected function createLegend() {
		$legend = new \catLegendGUI();
		
		$legend->addItem($this->in_progress, $this->txt_prefix."ta_in_progress")
			   ->addItem($this->passed, $this->txt_prefix."ta_passed")
			   ->addItem($this->maybe, $this->txt_prefix."ta_maybe")
			   ->addItem($this->failed, $this->txt_prefix."ta_failed");

		return $legend;
	}

	public function fillRow($row) {
		$this->tpl->setVariable("CAREER_GOAL", $row["title"]);
		$this->tpl->setVariable("NAME", $row["lastname"]." ".$row["firstname"]);
		$this->tpl->setVariable("ORG_UNIT", $row["org_unit_title"]);
		$this->tpl->setVariable("ORG_UNIT_SUPERVISOR", $row["supervisor"]);
		$this->tpl->setVariable("VENUE", $row["venue_title"]);
		$this->tpl->setVariable("DATE", $row["start_date_text"]);
		$this->tpl->setVariable("OBSERVATOR", $row["observator"]);
		$this->tpl->setVariable("RESULT", $this->getResultImage($row["result"]));
		$this->tpl->setVariable("ACTIONS", $this->getActionMenu($row["obj_id"]));
	}

	protected function getResultImage($result) {
		switch($result) {
			case ilMyObservationsGUI::TA_IN_PROGRESS:
				return $this->in_progress;
				break;
			case ilMyObservationsGUI::TA_PASSED:
				return $this->passed;
				break;
			case ilMyObservationsGUI::TA_MAYBE:
				return $this->maybe;
				break;
			case ilMyObservationsGUI::TA_FAILED:
				return $this->failed;
				break;
		}
	}

	protected function getActionMenu($obj_id) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new \ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->txt("actions"));
		$current_selection_list->setId($obj_id);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("obj_id".$obj_id, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($obj_id) as $key => $value) {
			$current_selection_list->addItem($value["title"],"",$value["link"],$value["image"],"",$value["frame"]);
		}

		return $current_selection_list->getHTML();
	}

	protected function getActionMenuItems($obj_id) {
		$this->gCtrl->setParameter($this->parent_obj, "obj_id", $obj_id);
		//$link_edit = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_EDIT"]);
		$this->gCtrl->clearParameters($this->parent_obj);

		$items = array();
		$items[] = array("title" => $this->txt("show_pdf"), "link" => $link_edit, "image" => "", "frame"=>"");

		return $items;
	}
}
