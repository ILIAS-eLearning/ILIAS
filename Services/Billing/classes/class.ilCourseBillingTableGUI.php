
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
 * @ingroup ServicesCourseBooking
 */
class ilCourseBillingTableGUI extends ilTable2GUI {

	private $course;
	protected $status_map;
	protected $gCtrl;
	protected $gUser;
	protected $crs_finalized;
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param ilObjCourse $a_course
	 * @param ilCourseBookingPermissions $a_perm
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, ilObjCourse $a_course) {
		global $ilCtrl, $ilUser;

		$this->setId("crs_bill");
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->crs_billing = ilCourseBilling::getInstance($a_course);

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$this->gUser = $ilUser;
		$this->gCtrl = $ilCtrl;
		$this->userUtils = gevUserUtils::getInstance($this->gUser->getId());

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$this->crs_utils = gevCourseUtils::getInstanceByObj($a_course);
		$this->crs_finalized = $this->crs_utils->isFinalized();

		$this->addColumn($this->lng->txt("user_pays_fees"), "pays_fees");	
		$this->addColumn($this->lng->txt("firstname"), "firstname");
		$this->addColumn($this->lng->txt("lastname"), "lastname");
		$this->addColumn($this->lng->txt("email"), "email");
		$this->addColumn($this->lng->txt("login"), "login");
		$this->addColumn($this->lng->txt("objs_orgu"), "orgutitle");
		$this->addColumn($this->lng->txt("crsbook_admin_status"), "status");
		$this->addColumn($this->lng->txt("bill_data_complete"), "bill_data_complete");
		$this->addColumn($this->lng->txt("action"), "action");

		$this->status_map = array(
			ilCourseBooking::STATUS_BOOKED => $this->lng->txt("crsbook_admin_status_booked")
			,ilCourseBooking::STATUS_WAITING => $this->lng->txt("crsbook_admin_status_waiting")
			,ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS => $this->lng->txt("crsbook_admin_status_cancelled_without_costs")
			,ilCourseBooking::STATUS_CANCELLED_WITH_COSTS => $this->lng->txt("crsbook_admin_status_cancelled_with_costs")
		);
		
		$this->setDefaultOrderField("name");
						
		$this->setRowTemplate("tpl.members_bills_row.html", "Services/Billing");
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));	
		
		$this->setExternalSegmentation(true);
		$this->setMaxCount(count($this->crs_utils->getBookedUser()));
		$this->determineOffsetAndOrder();
		$this->setShowRowsSelector(true);
		$this->getItems($a_course);
	}

	/**
	 * Get user data
		 * 
	 * @param ilObjCourse $a_course
	 */
	protected function getItems()
	{					
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
	 * @return string
	 */
	protected function getLink($a_user_id)
	{
		$this->gCtrl->setParameter($this->getParentObject(), "user_id", $a_user_id);
		$url = $this->gCtrl->getLinkTarget($this->getParentObject(), "paymentInfo");
		$this->gCtrl->setParameter($this->getParentObject(), "user_id", null);
		return $url;
	}

	/**
	 * Fill template row
	 * 
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
			$list->addItem($this->lng->txt("edit_course_bill_data"),
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
