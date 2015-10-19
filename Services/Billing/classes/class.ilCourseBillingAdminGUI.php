<?php
require_once "Services/CourseBooking/classes/class.ilCourseBookingPermissions.php";
require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
require_once "Services/GEV/Utils/classes/class.gevUserUtils.php";
require_once "Services/GEV/Utils/classes/class.gevBillingUtils.php";
require_once "Services/Billing/classes/class.ilCourseBillingHelper.php";
require_once 'Services/Billing/classes/class.ilCourseBilling.php';
/**
 * Course booking administration GUI
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBillingAdminGUI {
	protected $course; // [ilObjCourse]
	protected $permissions; // [ilCourseBookingPermissions]

	protected $crs_utils;
	protected $user_utils;

	protected $crs_finalized;

	protected $gTabs;
	protected $gToolbar;
	protected $gLng;
	protected $gCtrl;
	protected $gTpl;
	protected $gUser;

	
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public function __construct(ilObjCourse $a_course) {
		global $lng, $ilUser, $ilCtrl, $tpl, $ilTabs, $ilToolbar;
		$this->gTabs = $ilTabs;
		$this->gToolbar = $ilToolbar;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;

		$this->course = $a_course;

		$this->setTabs("listBills");
		
		$this->permissions = ilCourseBookingPermissions::getInstance($this->course);
		
		if(!$this->permissions->bookCourseForOthers()) {
			ilUtil::sendFailure($this->gLng->txt("msg_no_perm_book"), true);
			$this->returnToParent();
		}

		$this->crs_utils = gevCourseUtils::getInstanceByObj($a_course);
		if(!$this->crs_utils->getFee()) {
			ilUtil::sendFailure($this->gLng->txt("no_bills_for_free_training"), true);
			$this->returnToParent();
		}

		$this->crs_finalized = $this->crs_utils->isFinalized();
		$this->crs_billing = ilCourseBilling::getInstance($a_course);
		$this->bill_utils = gevBillingUtils::getInstance();
		$this->crs_billing_helper = ilCourseBillingHelper::getInstance();
		$this->gLng->loadLanguageModule("crsbook");
	}
	
	/**
	 * Execute request command
	 * 
	 * @return boolean
	 */
	public function executeCommand() {

		$next_class = $this->gCtrl->getNextClass($this);
		$cmd = $this->gCtrl->getCmd("listParticipationBills");
		$this->setTabs();
		
		switch($cmd) {	
			case "listParticipationBills":
			case "paymentInfo":
			case "savePaymentInfo":
				$this->$cmd();
				break;
			default:				
				$this->listParticipationBills();
		}
		
		return true;
	}


	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs() {
		$this->gTabs->clearTargets();
		$this->gCtrl->setParameterByClass('ilobjcoursegui', 'ref_id', $this->course->getRefId());
		$link = $this->gCtrl->getLinkTargetByClass(array('ilrepositorygui', 'ilobjcoursegui'),'members');
		$this->gCtrl->setParameterByClass('ilobjcoursegui', 'ref_id', null);
		$this->gTabs->setBackTarget(
			$this->gLng->txt("back")
			,$link
			);
	}
	

	/**
	 * Return to parent GUI
	 */
	protected function returnToParent()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilObjCourseGUI"), "members");		
		// $ilCtrl->returnToParent($this);
	}
	
	/**
	 * List current course participants and past course participants having bills associated to them.
	 */
	protected function listParticipationBills() {
		$this->setTabs("listParticipationBills");
		require_once "Services/CourseBooking/classes/class.ilCourseBookings.php";
		
		require_once "Services/Billing/classes/class.ilCourseBillingTableGUI.php";
		$tbl = new ilCourseBillingTableGUI($this, "listParticipationBills", $this->course, $this->permissions);
		return $this->gTpl->setContent($tbl->getHTML());
	}

	/**
	* Build form containing bill-data of an user.
	* @param array $a_bill_data
	*/

	protected function buildPaymentForm($a_bill_data = null) {

		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->crs_utils->getTitle());
		$this->gCtrl->saveParameter($this,array('user_id'));
		$form->setFormAction($this->gCtrl->getFormAction($this));
		
		$recipient = new ilTextInputGUI($this->gLng->txt("gev_bill_recipient"), "recipient");
		$recipient->setRequired(true);
		$form->addItem($recipient);
		
		$agency = new ilTextInputGUI($this->gLng->txt("gev_bill_agency_name"), "agency");
		$agency->setRequired(true);
		$form->addItem($agency);
		
		$street = new ilTextInputGUI($this->gLng->txt("street"), "street");
		$street->setRequired(true);
		$form->addItem($street);
		
		$housenumber = new ilTextInputGUI($this->gLng->txt("housenumber"), "housenumber");
		$housenumber->setRequired(true);
		$form->addItem($housenumber);
		
		$zipcode = new ilTextInputGUI($this->gLng->txt("zipcode"), "zipcode");
		$zipcode->setRequired(true);
		$form->addItem($zipcode);
		
		$city = new ilTextInputGUI($this->gLng->txt("city"), "city");
		$city->setRequired(true);
		$form->addItem($city);
		
		$costcenter = new ilTextInputGUI($this->gLng->txt("gev_bill_costcenter"), "costcenter");
		$costcenter->setRequired(true);
		$form->addItem($costcenter);
		
		$coupons = new ilTextInputGUI($this->gLng->txt("gev_use_coupon_codes"), "coupons");
		$coupons->setMulti(true);
		$form->addItem($coupons);
		
		$email = new ilEMailInputGUI($this->gLng->txt("gev_bill_email"), "email");
		$email->setRequired(true);
		$form->addItem($email);

		$coupons_used = new ilNonEditableValueGUI($this->gLng->txt("coupon_codes_used_sofar"), "coupons_used");
		$form->addItem($coupons_used);
			
		if ($a_bill_data !== null) {
			$form->getItemByPostVar("recipient")->setValue($a_bill_data["recipient"]);
			$form->getItemByPostVar("agency")->setValue($a_bill_data["agency"]);
			$form->getItemByPostVar("street")->setValue($a_bill_data["street"]);
			$form->getItemByPostVar("housenumber")->setValue($a_bill_data["housenumber"]);
			$form->getItemByPostVar("zipcode")->setValue($a_bill_data["zipcode"]);
			$form->getItemByPostVar("city")->setValue($a_bill_data["city"]);
			$form->getItemByPostVar("costcenter")->setValue($a_bill_data["costcenter"]);
			$form->getItemByPostVar("email")->setValue($a_bill_data["email"]);
			if(count($a_bill_data["coupons"])) {
				$form->getItemByPostVar("coupons")->setMultiValues($a_bill_data["coupons"]);
			}
			$form->getItemByPostVar("coupons")->setValue($a_bill_data["coupons"][0]);
		}
		return $form;
	}

	/**
	* Build form containing bill-data of an user. Which represents finalized bills.
	* @param array $a_bill_data
	*/

	protected function buildPaymentFormNoneditable($a_bill_data = null) {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->crs_utils->getTitle());
		$this->gCtrl->saveParameter($this,array('user_id'));
		$form->setFormAction($this->gCtrl->getFormAction($this));
		
		$recipient = new ilNonEditableValueGUI($this->gLng->txt("gev_bill_recipient"), "recipient");
		$form->addItem($recipient);
		
		$agency = new ilNonEditableValueGUI($this->gLng->txt("gev_bill_agency_name"), "agency");
		$form->addItem($agency);
		
		$street = new ilNonEditableValueGUI($this->gLng->txt("street"), "street");
		$form->addItem($street);
		
		$housenumber = new ilNonEditableValueGUI($this->gLng->txt("housenumber"), "housenumber");
		$form->addItem($housenumber);
		
		$zipcode = new ilNonEditableValueGUI($this->gLng->txt("zipcode"), "zipcode");
		$form->addItem($zipcode);
		
		$city = new ilNonEditableValueGUI($this->gLng->txt("city"), "city");
		$form->addItem($city);
		
		$costcenter = new ilNonEditableValueGUI($this->gLng->txt("gev_bill_costcenter"), "costcenter");
		$form->addItem($costcenter);
		
		$email = new ilNonEditableValueGUI($this->gLng->txt("gev_bill_email"), "email");
		$form->addItem($email);

		$coupons_used = new ilNonEditableValueGUI($this->gLng->txt("coupon_codes_used_sofar"), "coupons_used");
		$form->addItem($coupons_used);

		if ($a_bill_data !== null) {
			$form->getItemByPostVar("recipient")->setValue($a_bill_data["recipient"]);
			$form->getItemByPostVar("agency")->setValue($a_bill_data["agency"]);
			$form->getItemByPostVar("street")->setValue($a_bill_data["street"]);
			$form->getItemByPostVar("housenumber")->setValue($a_bill_data["housenumber"]);
			$form->getItemByPostVar("zipcode")->setValue($a_bill_data["zipcode"]);
			$form->getItemByPostVar("city")->setValue($a_bill_data["city"]);
			$form->getItemByPostVar("costcenter")->setValue($a_bill_data["costcenter"]);
			$form->getItemByPostVar("email")->setValue($a_bill_data["email"]);
		}
		return $form;
	}

	/**
	*	$cmd = paymentInfo. Loads User bill data associated with course fpr editing.
	*/

	protected function paymentInfo() {
		if($this->crs_finalized) {																							
			$this->showFinalBill();
			return;
		}

		$this->user_utils = gevUserUtils::getInstance($_GET["user_id"]);
		if(!$this->crs_billing->userMayHaveBill($this->user_utils)) {
			ilUtil::sendFailure("User pays no fees or is not a member of the course.", true);
			$this->returnToParent();
		}

		$bill_db = $this->bill_utils->getNonFinalizedBillForCourseAndUser($_GET["user_id"], $this->crs_utils->getId());
		$bill_db_data = $bill_db ? $this->crs_billing_helper->extractRelevantDataFromBill($bill_db) : array();				//get current bill data for this course, if exists
		$bill_last_data = $this->user_utils->getLastBillingDataMaybe(); 													//try to guess bill data from bills for other courses


		$show_bill_data = count($bill_db_data) ? $bill_db_data : 
				(count($bill_last_data) ? $bill_last_data : array());

		$form = $this->buildPaymentForm($show_bill_data);
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->gLng->txt("bill_data_of_user")." ".$this->user_utils->getFullName());

		$this->gCtrl->setParameter($this, "user_id", null);
		$form->addCommandButton("listParticipationBills", $this->gLng->txt("back"));
		$this->gCtrl->setParameter($this, "user_id", $_GET["user_id"]);
		$form->addCommandButton("savePaymentInfo", $this->gLng->txt("save"));
		$this->gCtrl->setParameter($this, "user_id", null);
		
		$bill_db_coupons = array();

		if($bill_db = $this->bill_utils->getNonFinalizedBillForCourseAndUser($this->crs_utils->getId(),$_GET["user_id"])) {
			$bill_db_coupons = $this->crs_billing_helper->getCouponCodesAssociatedWithBill($bill_db);
		}
		if(count($bill_db_coupons)) {
			$form->getItemByPostVar("coupons_used")->setValue(implode(", ",$bill_db_coupons));
		}
		$this->gTpl->setContent($form->getHTML());
	}

	/**
	*	$cmd = savePaymentInfo. Stores edited user bill data to databse.
	*/

	protected function savePaymentInfo() {
		if($this->crs_finalized) {
			$this->showFinalBill();
			return;
		}
		$this->user_utils = gevUserUtils::getInstance($_GET["user_id"]);
		if(!$this->crs_billing->userMayHaveBill($this->user_utils)) {
			ilUtil::sendFailure("User pays no fees or is not a member of the course.", true);
			$this->returnToParent();
		}
		$form = $this->buildPaymentForm();
		$form->setValuesByPost();

		if($form->checkInput()) {

			$coupon_codes = array_unique($form->getItemByPostVar("coupons")->getMultiValues());

			$form->getItemByPostVar("coupons")->setValue(null);
			$form->getItemByPostVar("coupons")->setMultiValues(array());

			$bill_db = $this->bill_utils->getNonFinalizedBillForCourseAndUser($this->crs_utils->getId(),$_GET["user_id"]);
			$new_bill = ($bill_db) ? false : true;

			if($new_bill) {
				$this->bill_utils->createCourseBill(
					$this->user_utils->getId()
					,$this->crs_utils->getId()
					,$form->getItemByPostVar("recipient")->getValue()
					,$form->getItemByPostVar("agency")->getValue()
					,$form->getItemByPostVar("street")->getValue()
					,$form->getItemByPostVar("housenumber")->getValue()
					,$form->getItemByPostVar("zipcode")->getValue()
					,$form->getItemByPostVar("city")->getValue()
					,$form->getItemByPostVar("costcenter")->getValue()
					,$coupon_codes
					,$form->getItemByPostVar("email")->getValue()
				);
				ilUtil::sendSuccess($this->gLng->txt("new_bill_saved"), true);

			} else {
				$bill_db_data = $this->crs_billing_helper->extractRelevantDataFromBill($bill_db);
				$bill_db_coupons = $this->crs_billing_helper->getCouponCodesAssociatedWithBill($bill_db);
				$bill_data_same = true;
				foreach ($bill_db_data as $key => $value) {
					if($value != $form->getItemByPostVar($key)->getValue()) {
						$bill_data_same = false;
						break;
					}
				}
				if(!$bill_data_same) {
					$this->crs_billing_helper->updateBillDataByArray($bill_db,$_POST);

					ilUtil::sendSuccess($this->gLng->txt("bill_data_saved"), true);
				} else {
					ilUtil::sendSuccess($this->gLng->txt("bill_data_not_changed"), true);
				}
				$count = 0;
				foreach($coupon_codes as $coupon_code) {
					if($this->bill_utils->chargeCouponAgainstBill($coupon_code,$bill_db)) {
						$count++;
					}
				}
				if($count) {
					ilUtil::sendInfo($count." ".$this->gLng->txt("coupon_codes_used"));
				}
			}
		}

		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->gLng->txt("bill_data_of_user")." ".$this->user_utils->getFullName());

		$this->gCtrl->setParameter($this, "user_id", null);
		$form->addCommandButton("listParticipationBills", $this->gLng->txt("back"));
		$this->gCtrl->setParameter($this, "user_id", $_GET["user_id"]);
		$form->addCommandButton("savePaymentInfo", $this->gLng->txt("save"));
		$this->gCtrl->setParameter($this, "user_id", null);

		$bill_db_coupons = array();

		if($bill_db = $this->bill_utils->getNonFinalizedBillForCourseAndUser($this->crs_utils->getId(),$_GET["user_id"])) {
			$bill_db_coupons = $this->crs_billing_helper->getCouponCodesAssociatedWithBill($bill_db);
		}
		if(count($bill_db_coupons)) {
			$form->getItemByPostVar("coupons_used")->setValue(implode(", ",$bill_db_coupons));
		}
		$this->gTpl->setContent($form->getHTML());
	}

	protected function showFinalBill() {
		$this->user_utils = gevUserUtils::getInstance($_GET["user_id"]);
		if(!$this->crs_billing->userMayHaveBill($this->user_utils)) {
			ilUtil::sendFailure("This user may not have a bill for that particular course.", true);
			$this->returnToParent();
		}


		$bill = $this->bill_utils->getBillsForCourseAndUser((int) $_GET["user_id"],(int) $this->crs_utils->getId())[0];
		if($bill) {
			if(!$bill->getFinal()) {
				ilUtil::sendFailure("This user has a nonfinal bill, allthough the course is final. Something is wrong...", true);
				return;
			}
		}

		$show_bill_data = $bill ? $this->crs_billing_helper->extractRelevantDataFromBill($bill) : array();
		$form = $this->buildPaymentFormNoneditable($show_bill_data);
		
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");

		$form->setTitle($this->gLng->txt("bill_data_of_user")." ".$this->user_utils->getFullName());
		$this->gCtrl->setParameter($this, "user_id", null);
		$form->addCommandButton("listParticipationBills", $this->gLng->txt("back"));
		if($bill) {
			$bill_db_coupons = $this->crs_billing_helper->getCouponCodesAssociatedWithBill($bill);
		}
		if(count($bill_db_coupons)) {
			$form->getItemByPostVar("coupons_used")->setValue(implode(", ",$bill_db_coupons));
		}
		$this->gTpl->setContent($form->getHTML());
	}	
}
