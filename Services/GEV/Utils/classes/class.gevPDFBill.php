<?php

/** 
 *   Generali specific configuration of ilPDFBill
 */

require_once("Services/Billing/classes/class.ilPDFBill.php");

class gevPDFBill extends ilPDFBill {
	static $instance = null;
	
	public function __construct() {
		parent::__construct();
		
		// TODO: set specs here
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevPDFBill();
		}
		
		return self::$instance;
	}
	
	public function setBill(ilBill $a_bill) {
		//require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		parent::setBill($a_bill);
		//$crs_utils = 
		//TODO: title and stuff needs to be set here
	}
}

?>