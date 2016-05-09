<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Table/classes/class.ilTable2GUI.php");

class gevMailLogTableGUI extends ilTable2GUI {
	function __construct(ilMailLog $a_mail_log, $a_parent_gui, $a_parent_cmd) {
		$this->parent_gui = &$a_parent_gui;
		$this->mail_log = &$a_mail_log;

		global $ilCtrl, $lng;

		$this->ctrl = &$ilCtrl;
		$this->lng = &$lng;

		parent::__construct($a_parent_gui, $a_parent_cmd);

		$this->setRowTemplate("tpl.gev_mail_log_table_row.html", "Services/GEV/Mailing");

		$this->setTitle($this->lng->txt("mail_log"));
		$this->setEnableTitle(true);

		$this->addColumn($this->lng->txt("moment"), "moment");
		$this->addColumn($this->lng->txt("occasion"), "occasion");
		$this->addColumn($this->lng->txt("recipient"), "to");
		$this->addColumn("", "_view_action");
		$this->addColumn("", "_send_action");

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
			$this->ctrl->setParameter($this->parent_gui, "crs_id", $this->mail_log->getObjectId());

			$data[$i]["_view_action"] = $this->ctrl->getLinkTarget($this->parent_gui, "showLoggedMail");

			if($data[$i]["to"] !== NULL && $data[$i]["to"] != "") {
				$data[$i]["_send_action"] = $this->ctrl->getLinkTarget($this->parent_gui, "resendMail");
			} else {
				$data[$i]["_send_action"] = "";
			}
		}
		$this->ctrl->setParameter($this, "mail_id", null);
		$this->ctrl->setParameter($this->parent_gui, "obj_id", null);

		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("MOMENT", ilDatePresentation::formatDate($a_set["moment"], true));
		$this->tpl->setVariable("OCCASION", $a_set["occasion"]);
		$this->tpl->setVariable("RECIPIENT", $a_set["to"]);
		$this->tpl->setVariable("VIEW_LINK", $a_set["_view_action"]);
		$this->tpl->setVariable("VIEW_TEXT", $this->lng->txt("view"));
		$this->tpl->setVariable("SEND_LINK", $a_set["_send_action"]);
		$this->tpl->setVariable("SEND_TEXT", $this->lng->txt("gev_resend_mail"));
	}
}

?>