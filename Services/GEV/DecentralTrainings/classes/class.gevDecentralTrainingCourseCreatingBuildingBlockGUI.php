<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseBuildingBlockTableGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseBuildingBlockGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevDecentralTrainingCourseCreatingBuildingBlockGUI extends gevDecentralTrainingCourseBuildingBlockGUI {
	
	public function __construct($a_crs_obj_id, $a_crs_request_id = null) {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilDB;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->log = $ilLog;
		$this->db = $ilDB;
		$this->search_form = null;

		$this->crs_ref_id = ($a_crs_obj_id === null) ? null : gevCourseUtils::getInstance($a_crs_obj_id)->getRefId();
		$this->crs_request_id = $a_crs_request_id;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->determineObjId();
		$this->determineCrsRef();
		$this->determineCrsRequestId();

		$cmd = $this->ctrl->getCmd();
		$in_search = true;

		switch($cmd) {
			case "delete":
				$this->renderConfirm();
				break;
			case "deleteCourseBuildingBlock":
				$this->deleteCourseBuildingBlock($this->obj_id);
				$this->render();
				break;
			case "add":
				$this->newCourseBuildingBlock();
				break;
			case "edit":
				$this->editCourseBuildingBlock();
				break;
			case "update":
				$this->updateCourseBuildingBlock();
				break;
			case "save":
				$this->saveCourseBuildingBlock();
				break;
			case "save_request":
				$this->saveRequest();
				break;
			case "delete_request":
				$this->deleteRequest();
				break;
			default:
				$this->render();
		}
	}

	protected function getTable() {
		$crs_tbl = new gevDecentralTrainingCourseBuildingBlockTableGUI($this,$this->crs_ref_id,$this->crs_request_id);
		$crs_tbl->setTitle("gev_dec_crs_creation_building_block_title")
				->setSubtitle("gev_dec_crs_creation_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				->addCommandButton("add",$this->lng->txt("add"));
				
		$crs_tbl->addCommandButton("save_request",$this->lng->txt("save"));
		$crs_tbl->addCommandButton("delete_request",$this->lng->txt("delete"));
		return $crs_tbl;
	}

	protected function saveRequest() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$crs_request = $request_db->request((int)$this->crs_request_id);
		$crs_request->request();
	}

	protected function deleteRequest() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$crs_request = $request_db->request((int)$this->crs_request_id);
		$crs_request->delete();
	}
}
?>