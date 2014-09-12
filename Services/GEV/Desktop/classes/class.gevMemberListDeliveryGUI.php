<?php


class gevMemberListDeliveryGUI {
	public function __construct() {
		global $ilCtrl, $ilAccess, $ilUser;
		
		$this->ctrl = &$ilCtrl;
		$this->access = &$ilAccess;
		$this->user_id = $ilUser->getId();
	}
	
	public function executeCommand() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

		$cmd = $this->ctrl->getCmd();
		$ref_id = intval($_GET["ref_id"]);
		$obj_id = gevObjectUtils::getObjId($ref_id);
		$utils = gevCourseUtils::getInstance($obj_id);
		
		
		if (!$this->access->checkAccess("write", "", $ref_id, "crs", $obj_id)
			&& !$utils->hasTrainer($this->user_id)) {
			$this->ctrl->redirectByClass("gevDesktopGUI");
			return;
		}
		
		switch($cmd) {
			case "hotel":
				$utils->deliverMemberList(gevCourseUtils::MEMBERLIST_HOTEL);
				return;
			case "trainer":
				$utils->deliverMemberList(gevCourseUtils::MEMBERLIST_TRAINER);
				return;
			case "participant":
				$utils->deliverMemberList(gevCourseUtils::MEMBERLIST_PARTICIPANT);
				return;
			case "csn":
				$utils->deliverCSVForCSN();
				return;
			default:
				$this->ctrl->redirectByClass("gevDesktopGUI");
				return;
		}
	}
}

?>