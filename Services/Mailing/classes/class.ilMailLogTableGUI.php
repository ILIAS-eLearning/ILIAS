<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Table/classes/class.ilTable2GUI.php");

class ilMailLogTableGUI extends ilTable2GUI {
	function __construct(ilMailLog $a_mail_log, $a_parent_gui, $a_parent_cmd) {
		$this->parent_gui = &$a_parent_gui;
		$this->mail_log = &$a_mail_log;

		global $ilCtrl, $ilLng;

		$this->ctrl = &$ilCtrl;
		$this->lng = &$ilLng;

		parent::__construct($a_parent_gui, $a_parent_cmd);

		$this->setRowTemplate("tpl.mail_log_table_row.html", "Services/Mailing");

		$this->setTitle($this->lng->txt("mail_log"));
		$this->setEnableTitle(true);

		$this->addColumn($this->lng->txt("moment"), "moment");
		$this->addColumn($this->lng->txt("occasion"), "occasion");
		$this->addColumn($this->lng->txt("recipient"), "to");
		$this->addColumn("", "_view_action");

		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(true);
		$this->setShowTemplates(false);
		//$this->setEnableTitle(false);
		$this->setOrderField("moment");
		$this->setDefaultOrderDirection("desc");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_gui, "applyFilter"));
		//$this->initFilter();
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setMaxCount($this->mail_log->countEntries());

		$this->determineOffsetAndOrder();
		$this->determineLimit();

		if($this->getOffset() === null) {
			$this->setOffset(0);
		}

		$this->insertData();
	}

	protected function insertData() {
		$data = $this->mail_log->getEntries( $this->getOffset()
										   , $this->getLimit()
										   , $this->getOrderField()
										   , $this->getOrderDirection()
										   );

		$count = count($data);
		
		for ($i = 0; $i < $count; ++$i) {
			$this->ctrl->setParameter($this->parent_gui, "mail_id", $data[$i]["id"]);
			$data[$i]["_view_action"] = $this->ctrl->getLinkTarget($this->parent_gui, "showLoggedMail");
		}
		$this->ctrl->setParameter($this, "mail_id", null);

		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("MOMENT", ilDatePresentation::formatDate($a_set["moment"], true));
		$this->tpl->setVariable("OCCASION", $a_set["occasion"]);
		$this->tpl->setVariable("RECIPIENT", $a_set["to"]);
		$this->tpl->setVariable("VIEW_LINK", $a_set["_view_action"]);
		$this->tpl->setVariable("VIEW_TEXT", $this->lng->txt("view"));
	}
}

?>