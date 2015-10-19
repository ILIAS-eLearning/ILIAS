<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/Billing/classes/class.ilCourseBilling.php';
require_once 'Services/CourseBooking/classes/class.ilCourseBooking.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * List all users from course
 *
 * @ingroup ServicesBilling
 */
class ilCourseBillingTableGUI extends ilTable2GUI {
	private $course;
	protected $status_map;
	protected $gCtrl;
	protected $gLng;
	protected $crs_finalized;
	protected $crs_utils;
	protected $crs_billing;

	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param ilObjCourse $a_course
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, ilObjCourse $a_course) {
		global $ilCtrl, $lng;

		$this->setId("crs_bill");
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->crs_billing = ilCourseBilling::getInstance($a_course);

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$this->gCtrl = $ilCtrl;

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$this->crs_utils = gevCourseUtils::getInstanceByObj($a_course);
		$this->crs_finalized = $this->crs_utils->isFinalized();
		$this->gLng = $lng;
		$this->gLng->loadLanguageModule("crsbook");
		$this->addColumn($this->gLng->txt("user_pays_fees"), "pays_fees");	
		$this->addColumn($this->gLng->txt("firstname"), "firstname");
		$this->addColumn($this->gLng->txt("lastname"), "lastname");
		$this->addColumn($this->gLng->txt("email"), "email");
		$this->addColumn($this->gLng->txt("login"), "login");
		$this->addColumn($this->gLng->txt("objs_orgu"), "orgutitle");
		$this->addColumn($this->gLng->txt("crsbook_admin_status"), "status");
		$this->addColumn($this->gLng->txt("bill_data_complete"), "bill_data_complete");
		$this->addColumn($this->gLng->txt("action"), "action");

		$this->status_map = array(
			ilCourseBooking::STATUS_BOOKED => $this->gLng->txt("crsbook_admin_status_booked")
			,ilCourseBooking::STATUS_WAITING => $this->gLng->txt("crsbook_admin_status_waiting")
			,ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS => $this->gLng->txt("crsbook_admin_status_cancelled_without_costs")
			,ilCourseBooking::STATUS_CANCELLED_WITH_COSTS => $this->gLng->txt("crsbook_admin_status_cancelled_with_costs")
		);
		
		$this->setDefaultOrderField("name");
						
		$this->setRowTemplate("tpl.members_bills_row.html", "Services/Billing");
		$this->setFormAction($this->gCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));	
		
		$this->setExternalSegmentation(true);
		$this->setMaxCount(count($this->crs_utils->getBookedUser()));
		$this->determineOffsetAndOrder();
		$this->setShowRowsSelector(true);

		$this->getItems($a_course);
	}

	/**
	 * Get user data
	 */
	protected function getItems() {					
		$data = array();
		foreach($this->crs_billing->getCourseTableData($this->getOffset(),$this->getLimit()) as $item) {	
			$data[]  = $item;
		}
		$this->setData($data);
	}
	
	/**
	 * Get user action link
	 * 
	 * @param int $a_user_id
	 * @return url-string
	 */
	protected function getLink($a_user_id) {
		$this->gCtrl->setParameter($this->getParentObject(), "user_id", $a_user_id);
		$url = $this->gCtrl->getLinkTarget($this->getParentObject(), "paymentInfo");
		$this->gCtrl->setParameter($this->getParentObject(), "user_id", null);
		return $url;
	}

	/**
	 * Fill template row
	 * @param array $a_set
	 */
	protected function fillRow(&$a_set) {
		$pays_fees = gevUserUtils::getInstance($a_set["user_id"])->paysFees();
		$a_set["pays_fees"] = $pays_fees ? $this->lng->txt("yes") : $this->lng->txt("no");
		$a_set["status"] = $this->status_map[$a_set["status"]];
		$a_set["bill_data_complete"] = $pays_fees ? ($a_set["bill_pk"] ? $this->lng->txt("yes") : $this->lng->txt("no")) : null;
		if($pays_fees && ($a_set["bill_pk"] or !$this->crs_finalized)) {
			$list = new ilAdvancedSelectionListGUI();
			$list->setId("crsbill_".$a_set["user_id"]);
			$list->addItem($this->gLng->txt("edit_course_bill_data"),
								"", $this->getLink($a_set["user_id"]));
		}
		$a_set["action_link"] = $list ? $list->getHTML() : null; 

		$this->tpl->setVariable("TXT_PAYS_FEES", $a_set["pays_fees"]);		
		$this->tpl->setVariable("TXT_LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("TXT_FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("TXT_EMAIL", $a_set["email"]);		
		$this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);		
		$this->tpl->setVariable("TXT_ORG", $a_set["orgutitle"]);	
		$this->tpl->setVariable("TXT_STATUS", $a_set["status"]);
		$this->tpl->setVariable("TXT_BILL_DATA_COMPLETE", $a_set["bill_data_complete"]);
		$this->tpl->setVariable("TXT_ACTION",  $a_set["action_link"]);
	}
}
