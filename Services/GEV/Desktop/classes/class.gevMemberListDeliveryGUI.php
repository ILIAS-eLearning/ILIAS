<?php


class gevMemberListDeliveryGUI {
	public function __construct() {
		global $ilCtrl, $ilAccess, $ilUser;
		
		$this->gCtrl = &$ilCtrl;
		$this->gAccess = &$ilAccess;
		$this->user_id = $ilUser->getId();
	}
	
	public function executeCommand() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		$cmd = $this->gCtrl->getCmd();
		$ref_id = intval($_GET["ref_id"]);
		$obj_id = gevObjectUtils::getObjId($ref_id);
		$crs_utils = gevCourseUtils::getInstance($obj_id);
		$access_roles = array("Admin-Ansicht", "Admin-dez-ID");
		$user_utils = gevUserUtils::getInstance($this->user_id);
		$may_access	= $user_utils->hasRoleIn($access_roles);

		switch($cmd) {

			case "hotel":
				if (!$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)) {
					break;
				}
				$crs_utils->deliverMemberList(gevCourseUtils::MEMBERLIST_HOTEL);
				return;

			case "trainer":
				if (!$crs_utils->userHasRightOf($this->user_id,gevSettings::LOAD_MEMBER_LIST)
					&& !$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)
					) {
					break;
				}
				$crs_utils->deliverMemberList(gevCourseUtils::MEMBERLIST_TRAINER);
				return;

			case "participant":
				if (!$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)) {
					break;
				}
				$crs_utils->deliverMemberList(gevCourseUtils::MEMBERLIST_PARTICIPANT);
				return;

			case "csn":
				if (!$crs_utils->userHasRightOf($$this->user_id, gevSettings::LOAD_CSN_LIST)
					&& !$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)
					) {
					break;
				}
				$crs_utils->deliverCSVForCSN();
				return;

			case "uvg":
				if (!$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)) {
					break;
				}
				$crs_utils->deliverUVGList();
				return;

			case "download_signature_list":
				if (!$crs_utils->userHasRightOf($this->user_id,gevSettings::LOAD_SIGNATURE_LIST)
					&& !$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)
					) {
					break;
				}
				$crs_utils->deliverSignatureList();
				return;

			case "download_crs_schedule":
				if (!$crs_utils->userHasRightOf($this->user_id,gevSettings::VIEW_SCHEDULE_PDF)
					&& !$this->gAccess->checkAccess("write", "", $ref_id, "crs", $obj_id)
					) {
					break;
				}
				$crs_utils->deliverCrsScheduleList();
				return;

			default:
				break;
		}

		$this->gCtrl->redirectByClass("gevDesktopGUI");
	}
}

?>