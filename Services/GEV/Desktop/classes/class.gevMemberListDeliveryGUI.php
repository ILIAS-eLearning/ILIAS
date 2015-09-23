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
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		$cmd = $this->ctrl->getCmd();
		$ref_id = intval($_GET["ref_id"]);
		$obj_id = gevObjectUtils::getObjId($ref_id);
		$utils = gevCourseUtils::getInstance($obj_id);
		$access_roles = array("Admin-Ansicht");
		$user_utils = gevUserUtils::getInstance($this->user_id);
		$may_access	= $user_utils->hasRoleIn($access_roles);
		
		if (!$this->access->checkAccess("write", "", $ref_id, "crs", $obj_id)
			&& !$utils->hasTrainer($this->user_id) &&  !$may_access) {
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
			case "uvg":
				$utils->deliverUVGList();
				return;
			case "download_signature_list":
				$utils->deliverSignatureList();
				return;
			case "download_crs_schedule":
				$utils->deliverCrsScheduleList();
				return;
			default:
				$this->ctrl->redirectByClass("gevDesktopGUI");
				return;
		}
	}
}

?>