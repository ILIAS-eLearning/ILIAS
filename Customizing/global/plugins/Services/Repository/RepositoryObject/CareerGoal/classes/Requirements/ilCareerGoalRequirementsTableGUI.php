<?php
namespace CaT\Plugins\CareerGoal\Requirements;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

class ilCareerGoalRequirementsTableGUI extends \ilTable2GUI {
	use ilFormHelper;

	const CAPTION_DELETE_SELECTED_OBSERVATIONS = "delete_selected";

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "", $sort = false) {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->sort = $sort;
		$this->txt = $a_parent_obj->getTXTClosure();

		$this->setId("career_goal_requirements");
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		if(!$sort) {
			$this->addColumn("", "", "1", true);

			foreach($this->getMultiCommands() as $cmd => $caption) {
				$this->addMultiCommand($cmd, $this->txt($caption));
			}

			$this->setSelectAllCheckbox("id[]");
			$this->setEnableAllCommand(true);
			$this->setTitle($this->txt("requirements_table_title"));
		} else {
			$this->addColumn($this->txt("position"), null, "1");

			$this->addCommandButton($this->possible_cmd["CMD_SAVE_ORDER"], $this->txt("save"));
			$this->addCommandButton($this->possible_cmd["CMD_SHOW"], $this->txt("cancel"));

			$this->setTitle($this->txt("requirements_table_sort_title"));
		}

		$this->addColumn($this->txt("title"), null);
		$this->addColumn($this->txt("description"), null);
		$this->addColumn($this->txt("observation"), null);

		if(!$sort) {
			$this->addColumn($this->txt("actions"), null);
		}

		$this->setShowRowsSelector(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.career_goal_requirement_list_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal");
		$this->setEnableTitle(true);
		$this->setShowRowsSelector(false);

		$data = $a_parent_obj->getActions()->getRequirementListData($a_parent_obj->getCareerGoalId());
		$data = $a_parent_obj->getActions()->changePositionValues($data);

		$this->setData($data);
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

	protected function fillRow($row) {
		if(!$this->sort) {
			$this->tpl->setCurrentBlock("row_selector");
			$this->tpl->setVariable("OBJ_ID", $row["obj_id"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("actions");
			$this->tpl->setVariable("ACTIONS", $this->getActionMenu($row["obj_id"]));
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("position");
			$items = $this->getFilledSortItems($row["obj_id"], $row["position"]);
			$this->tpl->setVariable("POSITION", $items[0]->render().$items[1]->getToolbarHTML());
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TITLE", $row["title"]);
		$this->tpl->setVariable("DESCRIPTION", $row["description"]);
		$this->tpl->setVariable("OBSERVATIONS", $row["observations"]);
		
	}

	/**
	 * return multicommands for requirement table gui
	 *
	 * @return array string => string
	 */
	protected function getMultiCommands() {
		return array($this->possible_cmd["CMD_DELETE_SELECTED_REQUIREMENTS"] => self::CAPTION_DELETE_SELECTED_OBSERVATIONS);
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
		$link_edit = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_EDIT"]);
		$link_delete = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_DELETE"]);
		$this->gCtrl->clearParameters($this->parent_obj);

		$items = array();
		$items[] = array("title" => $this->txt("edit_requirement"), "link" => $link_edit, "image" => "", "frame"=>"");
		$items[] = array("title" => $this->txt("delete_requirement"), "link" => $link_delete, "image" => "", "frame"=>"");

		return $items;
	}
}