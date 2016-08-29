<?php

namespace CaT\Plugins\TalentAssessment\Observator;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilObservatorTableGUI extends \ilTable2GUI {

	const CAPTION_DELETE = "delete";

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "") {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $a_parent_obj->getTXTClosure();

		$this->setId("talent_assessment_observator");
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setShowRowsSelector(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.talent_assessment_observator_list_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$this->setEnableTitle(true);
		$this->setShowRowsSelector(false);

		$this->addColumn("", "", "1", true);
		$this->addColumn($this->txt("fullname"), null);
		$this->addColumn($this->txt("login"), null);
		$this->addColumn($this->txt("email"), null);
		$this->addColumn($this->txt("action"), null);

		foreach($this->getMultiCommands() as $cmd => $caption) {
			$this->addMultiCommand($cmd, $this->txt($caption));
		}

		$this->setSelectAllCheckbox("id[]");
		$this->setEnableAllCommand(true);
		$this->setTitle($this->txt("observations_table_title"));

		$this->setData($this->parent_obj->getActions()->getAssignedUser($this->parent_obj->getObjId()));
	}

	public function fillRow($row) {
		$this->tpl->setVariable("OBJ_ID", $row["usr_id"]);
		$this->tpl->setVariable("FULLNAME", $row["lastname"].", ".$row["firstname"]);
		$this->tpl->setVariable("LOGIN", $row["login"]);
		$this->tpl->setVariable("EMAIL", $row["email"]);
		$this->tpl->setVariable("ACTIONS", $this->getActionMenu($row["usr_id"]));
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

	/**
	 * return multicommands for requirement table gui
	 *
	 * @return array string => string
	 */
	protected function getMultiCommands() {
		return array($this->possible_cmd["CMD_DELETE_SELECTED"] => self::CAPTION_DELETE);
	}

	protected function getActionMenu($usr_id) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new \ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->txt("actions"));
		$current_selection_list->setId($usr_id);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("usr_id".$usr_id, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($usr_id) as $key => $value) {
			$current_selection_list->addItem($value["title"],"",$value["link"],$value["image"],"",$value["frame"]);
		}

		return $current_selection_list->getHTML();
	}

	protected function getActionMenuItems($usr_id) {
		$this->gCtrl->setParameter($this->parent_obj, "usr_id", $usr_id);
		$link_delete = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_DELETE"]);
		$this->gCtrl->clearParameters($this->parent_obj);

		$items = array();
		$items[] = array("title" => $this->txt("delete_observator"), "link" => $link_delete, "image" => "", "frame"=>"");

		return $items;
	}
}