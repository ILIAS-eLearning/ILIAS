<?php
require_once 'Services/GEV/Utils/classes/class.gevBillingUtils.php';

class ilCourseBillingHelper {
	protected static $instance;
	protected $crs_id;

	protected function __construct() {
		$this->billing_utils = gevBillingUtils::getInstance();
	}

	public function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public  function extractRelevantDataFromBill(ilBill $a_bill) {
		$res = array();
		$aux = $a_bill->getRecipientName();
		$aux = explode(", ",$aux);

		$res["agency"] = $aux[0];
		$res["recipient"] = implode(", ",array_slice($aux,1));
		$res["street"] = $a_bill->getRecipientStreet();
		$res["housenumber"] = $a_bill->getRecipientHousenumber();
		$res["zipcode"] = $a_bill->getRecipientZipcode();
		$res["city"] = $a_bill->getRecipientCity();
		$res["costcenter"] = $a_bill->getCostcenter();
		$res["email"] = $a_bill->getRecipientEmail();
		return $res;
	}

	public function updateBillDataByArray(ilBill &$a_bill, array $a_new_data) {
		if($a_new_data["agency"] && $a_new_data["recipient"] ) {
			$a_bill->setRecipientName($a_new_data["agency"].", ".$a_new_data["recipient"]);
		}
		if($a_new_data["street"] ) {
			$a_bill->setRecipientStreet($a_new_data["street"]);
		}
		if($a_new_data["housenumber"] ) {
			$a_bill->setRecipientHousenumber($a_new_data["housenumber"] );
		}
		if($a_new_data["zipcode"] ) {
			$a_bill->setRecipientZipcode($a_new_data["zipcode"]);
		}
		if($a_new_data["city"] ) {
			$a_bill->setRecipientCity($a_new_data["city"]);
		}
		if($a_new_data["costcenter"] ) {
			$a_bill->setCostcenter($a_new_data["costcenter"]);
		}
		if($a_new_data["email"]) {
			$a_bill->setRecipientEmail($a_new_data["email"]);
		}
		$a_bill->update();
	}

	public function getCouponCodesAssociatedWithBill(ilBill $a_bill) {
		$bill_items = $a_bill->getItems();
		$codes = array();
		foreach($bill_items as $bill_item) {
			$bill_item_amount = $bill_item->getAmount();
			if( $bill_item_amount >= 0 ) {
				continue;
			}
			$bill_item_title = $bill_item->getTitle();
			$bill_item_title = explode(" ", $bill_item_title);
			$code = implode("",array_slice($bill_item_title,1));
			
			$codes[] = $code;
		}
		return $codes;
	}

}