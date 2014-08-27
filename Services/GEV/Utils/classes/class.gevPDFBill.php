<?php

/** 
 *   Generali specific configuration of ilPDFBill
 */

require_once("Services/Billing/classes/class.ilPDFBill.php");

class gevPDFBill extends ilPDFBill {
	static $instance = null;
	
	public function __construct() {
		parent::__construct();
		
		global $ilDB;
		
		$this->db = &$ilDB;
		
		$this->setSpaceAddress(5.2);
		$this->setAddressFont("Arial", 10, false, false);
		$this->setSpaceLeft(2.5);
		$this->setSpaceAbout(9.0);
		$this->setDateFont("Arial", 10, false, false);
		$this->setAboutFont("Arial", 10, true, false);
		$this->setSpaceBillNumber(10.5);
		$this->setBillNumberFont("Arial", 10, false, false);
		$this->setSpaceTitle(11.0);
		$this->setTitleFont("Arial", 10, false, false);
		$this->setSpaceText(12.5);
		$this->setTextFont("Arial", 10, false, false);
		$this->setCalculationFont("Arial", 8, false, false);
		$this->setSpaceRight(3.5);
		// This is bad, but i don't know how to do that differently.
		$this->setBackground(ILIAS_ABSOLUTE_PATH."/Customizing/global/skin/genv/bill_background.png");
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevPDFBill();
		}
		
		return self::$instance;
	}
	
	public function setBill(ilBill $a_bill) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		parent::setBill($a_bill);

		$crs_utils = gevCourseUtils::getInstance($a_bill->getContextId());
		$user_utils = gevUserUtils::getInstance($a_bill->getUserId());
		$booking_status = $crs_utils->getBookingStatusOf($a_bill->getUserId());
		
		$this->setAbout("Rechnung");
		$this->setTitle("Veranstaltungstitel: ".$crs_utils->getTitle().", ".$crs_utils->getCustomId()
					   .", ".$crs_utils->getFormattedAppointment());
		$this->setPretext("Für die Weiterbildung des Teilnehmers ".$user_utils->getFirstname()." ".$user_utils->getLastname().
						  " erlauben wir uns folgende Rechnung zu stellen:");
		$posttext = "Der Rechnungsbetrag wird in dem Agenturkonto ".$a_bill->getCostCenter()." belastet.";
		if ($booking_status == ilCourseBooking::STATUS_CANCELLED_WITH_COSTS) {
			$res = $this->db->query("SELECT coupon_code FROM gev_bill_coupon WHERE bill_pk = ".$a_bill->getId());
			if ($rec = $this->db->fetchAssoc($res)) {
				$posttext .= " Sie erhalten von uns den Gutscheincode ".$rec["coupon_code"]." in Höhe von "
						     .number_format($a_bill->getAmount(), 2, ",", "")." EUR, welchen sie für Folgebuchungen "
						     ."einlösen können. Der Gutschein ist ein Jahr gültig.";
			}
		}
		$this->setPosttext($posttext);
		
		$this->setGreetings(" \nMit freundlichen Grüßen\nGenerali Versicherung AG\nPersonalentwicklung Vertrieb");
		//TODO: title and stuff needs to be set here
	}
}

?>