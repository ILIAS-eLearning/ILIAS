<?php
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class ilBuildingBlockImportTableGUI extends ilTable2GUI {

	protected $parent_obj;

	public function __construct($parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilAccess;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->parent_obj = $parent_obj;
		$this->gAccess = $ilAccess;

		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		$this->setTitle($this->lng->txt("import_header"));
		$this->setSelectAllCheckbox("bb_obj_id");
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));

		$this->setRowTemplate("tpl.gev_building_block_import_row.html", $this->parent_obj->object->plugin->getDirectory());

		$this->addColumn("");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("gev_dec_building_block_content"),"content");
		$this->addColumn($this->lng->txt("gev_dec_building_block_target"), 'target');
		
		$this->addColumn($this->lng->txt("gev_dec_training_training_category"), 'training_categories');
		$this->addColumn($this->lng->txt("gev_dec_training_gdv_topic"), 'gdv_topic');
		$this->addColumn($this->lng->txt("gev_dec_training_dbv_topic"), 'dbv_topic');
		$this->addColumn($this->lng->txt("gev_dec_training_topic"), 'topic');

		$this->addColumn($this->lng->txt("gev_dec_building_block_is_wp_relevant"), "is_wp_relevant");
		$this->addColumn($this->lng->txt("gev_dec_building_block_active"), "is_active");

		$order = $this->getOrderField();
		$order_direction = $this->getOrderDirection();
		$offset = $this->getOffset();
		$limit = $this->getLimit();

		$data = gevBuildingBlockUtils::getAllBuildingBlocksForCopy($this->parent_obj->object->getId(), $order, $order_direction, $offset, $limit);

		$this->setMaxCount(gevBuildingBlockUtils::countAllBuildingBlocksForCopy($this->parent_obj->object->getId()));
		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("BB_OBJ_ID", $a_set["obj_id"]);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CONTENT", $a_set["content"]);
		$this->tpl->setVariable("TARGET", $a_set["target"]);

		$this->tpl->setVariable("TRAINING_CAT", implode("<br />",$a_set["training_categories"]));
		$this->tpl->setVariable("GDV_TOPIC", $a_set["gdv_topic"]);
		$this->tpl->setVariable("DBV_TOPIC", $a_set["dbv_topic"]);
		$this->tpl->setVariable("TOPIC", $a_set["topic"]);

		$this->tpl->setVariable("IS_WP_RELEVANT", ($a_set["is_wp_relevant"]) ? "Ja" : "Nein");
		$this->tpl->setVariable("IS_ACTIVE", ($a_set["is_active"]) ? "Ja" : "Nein");
	}

	public function importConfirmed($bb_ids) {
		gevBuildingBlockUtils::copyBuildingBlocksTo($bb_ids, $this->parent_obj->object->getId());
		$this->ctrl->redirect($this->parent_obj);
	}
}