<?php


class gevMemberListDeliveryGUI {
	public function __construct() {
		global $ilCtrl, $ilAccess;
		
		$this->ctrl = &$ilCtrl;
		$this->access = &$ilAccess;
	}
	
	public function executeCommand() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

		$cmd = $this->ctrl->getCmd();
		$ref_id = intval($_GET["ref_id"]);
		$obj_id = gevObjectUtils::getObjId($ref_id);
		
		if (!$this->access->checkAccess("write", "", $ref_id, "crs", $obj_id)) {
			$this->ctrl->redirectByClass("gevDesktopGUI");
			return;
		}
		
		$utils = gevCourseUtils::getInstance($obj_id);
		
		switch($cmd) {
			case "hotel":
				$utils->deliverMemberList(gevCourseUtils::MEMBERLIST_HOTEL);
				return;
			case "trainer":
				$utils->deliverMemberList(gevCourseUtils::MEMBERLIST_TRAINER);
				return;
			default:
				$this->ctrl->redirectByClass("gevDesktopGUI");
				return;
		}
	}
}

?>