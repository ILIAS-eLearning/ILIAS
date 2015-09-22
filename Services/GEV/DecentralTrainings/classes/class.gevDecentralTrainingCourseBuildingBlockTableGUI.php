<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevDecentralTrainingCourseBuildingBlockTableGUI extends catAccordionTableGUI {
	const MINUTE_STEP_SIZE = 15;

	public function __construct($a_parent_obj,$a_crs_id,$a_crs_request_id=null,$no_changes = false, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->parent = $a_parent_obj;
		$this->no_changes = $no_changes;

		$this->crs_requerst_id = $a_crs_request_id;

		$this->delete_image = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';
		//$this->edit_image = '<img src="'.ilUtil::getImagePath("GEV_img/ico-edit.png").'" />';

		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.gev_course_building_block_search_row.html", "Services/GEV/DecentralTrainings");

		$this->addColumn($this->lng->txt("gev_dec_crs_building_block_from"), "","100");
		$this->addColumn($this->lng->txt("gev_dec_crs_building_block_to"),"","100");
		$this->addColumn($this->lng->txt("gev_dec_crs_building_block_block"), '');
		$this->addColumn($this->lng->txt("gev_dec_crs_building_block_content"), "");
		$this->addColumn($this->lng->txt("gev_dec_building_block_learn_dest"), "");
		$this->addColumn($this->lng->txt("gev_dec_building_wp"), "");
		$this->addColumn($this->lng->txt("gev_dec_training_dbv_topic"), "");
		$this->addColumn($this->lng->txt("action"), "");

		$data = gevCourseBuildingBlockUtils::getAllCourseBuildingBlocksRaw($a_crs_id,$a_crs_request_id);

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$start_date = new ilDateTimeInputGUI("", "start_date_".$a_set["id"]);
		$start_date->setShowDate(false);
		$start_date->setShowTime(true);
		$start_date->setDate(new ilDateTime(date("Y-m-d")." ".$a_set["start_time"], IL_CAL_DATETIME));
		$start_date->setMinuteStepSize(self::MINUTE_STEP_SIZE);
		$start_date->setDisabled($this->no_changes);

		$end_date = new ilDateTimeInputGUI("", "end_date_".$a_set["id"]);
		$end_date->setShowDate(false);
		$end_date->setShowTime(true);
		$end_date->setDate(new ilDateTime(date("Y-m-d")." ".$a_set["end_time"], IL_CAL_DATETIME));
		$end_date->setMinuteStepSize(self::MINUTE_STEP_SIZE);
		$end_date->setDisabled($this->no_changes);

		$this->tpl->setVariable("START_DATE", $start_date->render());
		$this->tpl->setVariable("END_DATE", $end_date->render());
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CONTENT", $a_set["content"]);
		$this->tpl->setVariable("LEARNING_DEST", $a_set["learning_dest"]);
		$this->tpl->setVariable("CREDIT_POINTS", $a_set["credit_points"]);
		$this->tpl->setVariable("DBV_TOPIC", $a_set["dbv_topic"]);

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
		$dct_utils = gevDecentralTrainingUtils::getInstance();
		
		
		if($a_set["crs_id"] !== null) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$obj_id = gevObjectUtils::getObjId($a_set["crs_id"]);

			if($dct_utils->userCanEditBuildingBlocks($obj_id)) {
				$action = '<a href="'.$this->getDeleteLink($a_set["id"],$a_set["crs_request_id"],$a_set["crs_id"]).'">'.$this->delete_image.'</a>&nbsp;';
				//$action .= '<a href="'.$this->getEditLink($a_set["id"],$a_set["crs_request_id"],$a_set["crs_id"]).'">'.$this->edit_image.'</a>';
				$this->tpl->setVariable("ACTION", $action);
			}
		} else {
			$action = '<a href="'.$this->getDeleteLink($a_set["id"],$a_set["crs_request_id"],$a_set["crs_id"]).'">'.$this->delete_image.'</a>&nbsp;';
			//$action .= '<a href="'.$this->getEditLink($a_set["id"],$a_set["crs_request_id"],$a_set["crs_id"]).'">'.$this->edit_image.'</a>';
			$this->tpl->setVariable("ACTION", $action);
		}
	}

	protected function getDeleteLink($a_id,$a_crs_request_id,$a_crs_ref_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameter($this->parent, "id", $a_id);
		$ilCtrl->setParameter($this->parent, "crs_ref_id", $a_crs_ref_id);
		
		if($a_crs_ref_id === null) {
			$ilCtrl->setParameter($this->parent, "crs_request_id", $a_crs_request_id);
		}
		
		$lnk = $ilCtrl->getLinkTarget($this->parent, "toDeleteCrsBuildingBlock");
		$ilCtrl->clearParameters($this->parent);
		return $lnk;
	}

	protected function getEditLink($a_id,$a_crs_request_id,$a_crs_ref_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameter($this->parent, "id", $a_id);
		$ilCtrl->setParameter($this->parent, "crs_ref_id", $a_crs_ref_id);

		if($a_crs_ref_id === null) {
			$ilCtrl->setParameter($this->parent, "crs_request_id", $a_crs_request_id);
		}
		
		$lnk = $ilCtrl->getLinkTarget($this->parent, "edit");
		$ilCtrl->clearParameters($this->parent);
		return $lnk;
	}

	protected $advice = null;

	public function setAdvice($a_advice) {
		$this->advice = $a_advice;
	}

	public function getAdvice() {
		return $this->advice;
	}

	private function renderAdvice() {
		$tpl = new ilTemplate("tpl.gev_my_advice.html", true, true, "Services/GEV/Desktop");

		$tpl->setCurrentBlock("advice");
		$tpl->setVariable("ADVICE", $this->lng->txt($this->getAdvice()));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	public function render() {
		$ret = "";

		if ($this->_title_enabled) {
			$ret .= $this->_title->render()."<br />";
		}

		if($this->advice) {
			$ret .= $this->renderAdvice()."<br />";
		}

		$ret .= ilTable2GUI::render();

		return $ret;
	}
}

?>