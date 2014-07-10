<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilUserCourseBookings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");

class gevBillingUtils {
	static protected $instance = null;

	protected function __construct() {
	}
	
	static public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevBillingUtils();
		}
		
		return self::$instance;
	}
	
	public function isValidCouponCode($a_code) {
		// TODO: implement
		return true;
	}
	
	public function createBill( $a_user_id
							  , $a_crs_id
							  , $a_recipient
							  , $a_agency
							  , $a_street
							  , $a_housenumber
							  , $a_zipcode
							  , $a_city
							  , $a_costcenter
							  , $a_coupons
							  , $a_email
							  ) {
		// TODO: implement
		return;
	}
}

?>