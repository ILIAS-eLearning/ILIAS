<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Class ilPurchasePaypal
*
* @author Jens Conze
* @author Nadia Ahmad
* @version $Id: class.ilPurchasePaypal.php 22133 2009-10-16 08:09:11Z nkrzywon $
*
* @package core
*/


include_once './Services/Payment/classes/class.ilInvoiceNumberPlaceholdersPropertyGUI.php';
include_once './Services/Payment/classes/class.ilPayMethods.php';
include_once './Services/Payment/classes/class.ilPurchaseBaseGUI.php';

define('SUCCESS', 0);
define('ERROR_OPENSOCKET', 1);
define('ERROR_WRONG_CUSTOMER', 2);
define('ERROR_NOT_COMPLETED', 3);
define('ERROR_PREV_TRANS_ID', 4);
define('ERROR_WRONG_VENDOR', 5);
define('ERROR_WRONG_ITEMS', 6);
define('ERROR_FAIL', 7);

class ilPurchasePaypal  extends ilPurchaseBaseGUI
{
	/*
	 * id of vendor, admin or trustee
	 */
	public $psc_obj = null;
	public $user_obj = null;
	public $pay_method = null;
	public $currency = null;	
	public $db = null;
	public $paypalConfig;

	public function __construct($user_obj)
	{
		$this->user_obj = $user_obj;
		$this->pay_method = ilPayMethods::_getIdByTitle('paypal');		
		
		$ppSet = ilPaypalSettings::getInstance();
		$this->paypalConfig = $ppSet->getAll();

		parent::__construct($this->user_obj, $this->pay_method);
	}

	public function openSocket()
	{
		// post back to PayPal system to validate
		$fp = @fsockopen ($path = $this->paypalConfig["server_host"], 80, $errno, $errstr, 30);
		return $fp;
	}

	public function checkData($fp)
	{
		global $ilUser;
		
		//Token from paypal account
		$auth_token = $this->paypalConfig["auth_token"];

		//add 'cmd' as required
		$req = 'cmd=_notify-synch';

		//Get token
		$tx_token = $_REQUEST['tx'];

		//append both tokens as required
		$req .= "&tx=$tx_token&at=$auth_token";

		//send information back to paypal  
		// info: https required!!!
		$submiturl = 'https://'.$this->paypalConfig["server_host"].$this->paypalConfig["server_path"];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$submiturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//return into variable
		curl_setopt($ch, CURLOPT_POST, 1);//make it a post
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);//post request
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
		curl_setopt($ch, CURLOPT_HEADER , 0);  //dont return headers
		curl_setopt($ch, CURLOPT_VERBOSE, 1);//more informaiton in error
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//dont  verify
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//define timeout
		$result= @curl_exec($ch);//get result
		curl_close($ch);//close connection

// only for TEST
// echo $result;//display response

		// parse the data
		$lines = explode("\n", $result);

		$keyarray = array();
		$keyarray[0] = $lines[0]; // save payment status!

		if (strcmp ($lines[0], "SUCCESS") == 0)
		{
			for ($i=1; $i<count($lines);$i++)
			{
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
// check customer
			if ($ilUser->getId() != $keyarray["custom"]
			&& $_SESSION['shop_user_id'] != $keyarray['custom'])
			{
#echo "Wrong customer";
				return ERROR_WRONG_CUSTOMER;
			}

// check the payment_status is Completed
			if (!in_array($keyarray["payment_status"], array("Completed", "In-Progress", "Pending", "Processed")))
			{
#echo "Not completed";
				return ERROR_NOT_COMPLETED;
			}

// check that txn_id has not been previously processed
			if ($this->__checkTransactionId($keyarray["txn_id"]))
			{
				if($_SESSION['tmp_transaction']['result'] == 'success'
				&& $_SESSION['tmp_transaction']['tx_id'] == $keyarray["txn_id"])
				{
					// this is for catching the problem, if the user doubleklicks on the paypal
					// site to return to the ilias shop and his purchasings already exists in db
					return SUCCESS;
				}
				else
#echo "Prev. processed trans. id";
				return ERROR_PREV_TRANS_ID;
			}

// check that receiver_email is your Primary PayPal email
			if ($keyarray["receiver_email"] != $this->paypalConfig["vendor"])
			{
//echo "Wrong vendor";
				return ERROR_WRONG_VENDOR;
			}

// check that payment_amount/payment_currency are correct
			if (!$this->__checkItems($keyarray))
			{
//echo "Wrong items";
				return ERROR_WRONG_ITEMS;
			}

//			if($ilUser->getId() == ANONYMOUS_USER_ID)
//			{
//			    include_once './Services/Payment/classes/class.ilShopUtils.php';
//			    // anonymous user needs an account to use crs
//			    $ilUser = ilShopUtils::_createRandomUserAccount($keyarray);
//			    $user_id = $ilUser->getId();
//
//			    $_SESSION['tmp_transaction']['tx_id'] = $keyarray["txn_id"];
//			    $_SESSION['tmp_transaction']['usr_id'] = $user_id;
//
//			    if($_SESSION['is_crs_object'] && ($ilUser->getId() == ANONYMOUS_USER_ID))
//			    {
//					include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
//					foreach ($_SESSION['crs_obj_ids'] as $obj_id)
//					{
//						$members_obj = ilCourseParticipants::_getInstanceByObjId($obj_id);
//						$members_obj->add($user_id,IL_CRS_MEMBER);
//					}
//			    }
//			}

			$external_data = array();
			$external_data['transaction_extern'] = $keyarray["txn_id"];
			$external_data['street'] = $keyarray["address_street"];
			$external_data['zipcode'] = $keyarray["address_zip"];
			$external_data['city'] = $keyarray["address_city"];
			$external_data['country'] = $keyarray["address_country"];

			parent::__addBookings($external_data);
							
			$_SESSION["coupons"]["paypal"] = array();
			$_SESSION['tmp_transaction']['result'] = 'success';

			return SUCCESS;
		}
		else if (strcmp ($lines[0], "FAIL") == 0)
		{
			return ERROR_FAIL;
		}
		else
		{
			return ERROR_FAIL;
		}
	}

	private function __checkTransactionId($a_id)
	{
		global $ilDB;
	
		$res = $ilDB->queryF('SELECT * FROM payment_statistic
			WHERE transaction_extern = %s',
			array('text'), array($a_id));

		return $res->numRows() ? true : false;
	}

	private function __checkItems($a_array)
	{
		$genSet = ilPaymentSettings::_getInstance();

// Wrong currency
		if ($a_array["mc_currency"] != $genSet->get("currency_unit"))
		{
			return false;
		}
		
		$sc = $this->psc_obj->getShoppingCart($this->pay_method);		
		$this->psc_obj->clearCouponItemsSession();

		if (is_array($sc) && count($sc) > 0)
		{
			for ($i = 0; $i < count($sc); $i++)
			{
				$items[$i] = array(
					"name" => $a_array["item_name".($i+1)],
					"amount" => $a_array["mc_gross_".($i+1)]
				);			

				if (!empty($_SESSION["coupons"]["paypal"]))
				{											
					$sc[$i]["math_price"] = (float) $sc[$i]["price"];
								
					$tmp_pobject = new ilPaymentObject($this->user_obj, $sc[$i]['pobject_id']);		
													
					foreach ($_SESSION["coupons"]["paypal"] as $key => $coupon)
					{					
						$this->coupon_obj->setId($coupon["pc_pk"]);
						$this->coupon_obj->setCurrentCoupon($coupon);
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{
							$_SESSION["coupons"]["paypal"][$key]["total_objects_coupon_price"] += (float) $sc[$i]["price"];
							$_SESSION["coupons"]["paypal"][$key]["items"][] = $sc[$i];
						}								
					}					
					unset($tmp_pobject);
				}				
			}
			
			$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["paypal"]);

			$found = 0;
			$total = 0;
			for ($i = 0; $i < count($sc); $i++)
			{			
				if (array_key_exists($sc[$i]["pobject_id"], $coupon_discount_items))
				{
					$sc[$i]["price"] = round($coupon_discount_items[$sc[$i]["pobject_id"]]["discount_price"], 2);				
					if ($sc[$i]["price"] < 0) $sc[$i]["price"] = 0.0;	
				}				

				for ($j = 0; $j < count($items); $j++)
				{
					if (substr($items[$j]["name"], 0, strlen($sc[$i]["obj_id"])+2) == "[".$sc[$i]["obj_id"]."]" &&
						$items[$j]["amount"] == $sc[$i]["price"])
					{
						$total += $items[$j]["amount"];
						$found++;
					}
				}
			}

// The number of items, the items themselves and their amounts and the total amount correct
			if (number_format($total, 2, ".", "") == $a_array["mc_gross"] &&
				$found == count($sc))
			{
				return true;
			}
		}		
		return false;
	}

/**
 * content of $keyarray
 *
 * 

 array(42) {
  [0]=>
  string(7) "SUCCESS"
  ["mc_gross"]=>
  string(4) "0.12"
  ["protection_eligibility"]=>
  string(10) "Ineligible"
  ["address_status"]=>
  string(11) "unconfirmed"
  ["item_number1"]=>
  string(0) ""
  ["payer_id"]=>
  string(13) "K35VQU3ZS4NH6"
  ["tax"]=>
  string(4) "0.00"
  ["address_street"]=>
  string(12) "ESpachstr. 1"
  ["payment_date"]=>
  string(25) "03:40:26 Dec 01, 2011 PST"
  ["payment_status"]=>
  string(9) "Completed"
  ["charset"]=>
  string(12) "windows-1252"
  ["address_zip"]=>
  string(5) "79111"
  ["mc_shipping"]=>
  string(4) "0.00"
  ["mc_handling"]=>
  string(4) "0.00"
  ["first_name"]=>
  string(4) "Test"
  ["mc_fee"]=>
  string(4) "0.12"
  ["address_country_code"]=>
  string(2) "DE"
  ["address_name"]=>
  string(9) "Test User"
  ["custom"]=>
  string(3) "191"
  ["payer_status"]=>
  string(8) "verified"
  ["business"]=>
  string(32) "nkrzyw_1269340517_biz@databay.de"
  ["address_country"]=>
  string(7) "Germany"
  ["num_cart_items"]=>
  string(1) "1"
  ["mc_handling1"]=>
  string(4) "0.00"
  ["address_city"]=>
  string(8) "Freiburg"
  ["payer_email"]=>
  string(32) "nahmad_1304490818_pre@databay.de"
  ["mc_shipping1"]=>
  string(4) "0.00"
  ["txn_id"]=>
  string(17) "1V7779519K0212052"
  ["payment_type"]=>
  string(7) "instant"
  ["last_name"]=>
  string(4) "User"
  ["address_state"]=>
  string(5) "Empty"
  ["item_name1"]=>
  string(27) "[205]: SCORM Diagnostic SCO"
  ["receiver_email"]=>
  string(32) "nkrzyw_1269340517_biz@databay.de"
  ["payment_fee"]=>
  string(0) ""
  ["quantity1"]=>
  string(1) "1"
  ["receiver_id"]=>
  string(13) "D9SADU4UX7EFJ"
  ["txn_type"]=>
  string(4) "cart"
  ["mc_gross_1"]=>
  string(4) "0.12"
  ["mc_currency"]=>
  string(3) "EUR"
  ["residence_country"]=>
  string(2) "DE"
  ["transaction_subject"]=>
  string(3) "191"
  ["payment_gross"]=>
  string(0) ""
}
 */
 
}
?>
