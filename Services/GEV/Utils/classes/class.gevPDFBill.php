<?php

/** 
 *   Generali specific configuration of ilPDFBill
 */

require_once("Services/Billing/classes/class.ilPDFBill.php");

class gevPDFBill extends ilPDFBill {
	static $instance = null;
	
	public function __construct() {
		parent::__construct();
		
		// Set specs here
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevPDFBill();
		}
		
		return self::$instance;
	}
}

?>