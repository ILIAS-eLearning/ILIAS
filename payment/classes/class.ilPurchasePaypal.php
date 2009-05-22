<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/**
* Class ilPurchasePaypal
*
* @author Jens Conze
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentObject.php';
include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaypalSettings.php';
include_once './payment/classes/class.ilPaymentCoupons.php';
include_once 'Services/Payment/classes/class.ilShopVatsList.php';

define('SUCCESS', 0);
define('ERROR_OPENSOCKET', 1);
define('ERROR_WRONG_CUSTOMER', 2);
define('ERROR_NOT_COMPLETED', 3);
define('ERROR_PREV_TRANS_ID', 4);
define('ERROR_WRONG_VENDOR', 5);
define('ERROR_WRONG_ITEMS', 6);
define('ERROR_FAIL', 7);

class ilPurchasePaypal
{
	/*
	 * id of vendor, admin or trustee
	 */
	var $psc_obj = null;
	var $user_obj = null;
	var $db = null;

	var $paypalConfig;
	
	private $totalVat = 0;

	function ilPurchasePaypal(&$user_obj)
	{
		global $ilDB,$lng;

		$this->user_obj =& $user_obj;
		$this->db =& $ilDB;
		$this->lng =& $lng;
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);

		$this->__initShoppingCartObject();

		$ppSet = ilPaypalSettings::getInstance();
		$this->paypalConfig = $ppSet->getAll();

		$this->lng->loadLanguageModule("payment");
		
		if (!is_array($_SESSION["coupons"]["paypal"]))
		{
			$_SESSION["coupons"]["paypal"] = array();
		}
	}

	function openSocket()
	{
		// post back to PayPal system to validate
		$fp = @fsockopen ($this->paypalConfig["server_host"], 80, $errno, $errstr, 30);
		return $fp;
	}

	function checkData($fp)
	{
		global $ilUser;

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-synch';

		$tx_token = $_REQUEST['tx'];		

		$auth_token = $this->paypalConfig["auth_token"];

		$req .= "&tx=$tx_token&at=$auth_token";
		$header .= "POST " . $this->paypalConfig["server_path"] . " HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		fputs ($fp, $header . $req);
		// read the body data
		$res = '';
		$headerdone = false;
		while (!feof($fp))
		{
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0)
			{
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0)
		{
			for ($i=1; $i<count($lines);$i++)
			{
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
// check customer
			if ($ilUser->getId() != $keyarray["custom"])
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
#echo "Prev. processed trans. id";
				return ERROR_PREV_TRANS_ID;
			}

// check that receiver_email is your Primary PayPal email
			if ($keyarray["receiver_email"] != $this->paypalConfig["vendor"])
			{
#echo "Wrong vendor";
				return ERROR_WRONG_VENDOR;
			}

// check that payment_amount/payment_currency are correct
			if (!$this->__checkItems($keyarray))
			{
#echo "Wrong items";
				return ERROR_WRONG_ITEMS;
			}

			$bookings = $this->__saveTransaction($keyarray["txn_id"]);
			$this->__sendBill($bookings, $keyarray);
			$_SESSION["coupons"]["paypal"] = array();

			return SUCCESS;
		}
		else if (strcmp ($lines[0], "FAIL") == 0)
		{
			return ERROR_FAIL;
		}
	}

	function __checkTransactionId($a_id)
	{
/*
 			$query = "SELECT * FROM payment_statistic ".
			"WHERE transaction_extern = '".$a_id."'";
*/
		$res = $this->db->query('SELECT * FROM payment_statistic WHERE transaction_extern = '.$ilDB->quote($a_id, 'integer'));

		return $res->numRows() ? true : false;
	}

	function __checkItems($a_array)
	{
		$genSet = new ilGeneralSettings();

		include_once './payment/classes/class.ilPayMethods.php';

// Wrong currency
		if ($a_array["mc_currency"] != $genSet->get("currency_unit"))
		{
			return false;
		}
		
		$sc = $this->psc_obj->getShoppingCart(PAY_METHOD_PAYPAL);		
		$this->psc_obj->clearCouponItemsSession();

		if (is_array($sc) &&
			count($sc) > 0)
		{
			for ($i = 0; $i < count($sc); $i++)
			{
				$items[$i] = array(
					"name" => $a_array["item_name".($i+1)],
					"amount" => $a_array["mc_gross_".($i+1)]
				);			

				if (!empty($_SESSION["coupons"]["paypal"]))
				{											
					$sc[$i]["math_price"] = (float) $sc[$i]["betrag"];
								
					$tmp_pobject =& new ilPaymentObject($this->user_obj, $sc[$i]['pobject_id']);	
													
					foreach ($_SESSION["coupons"]["paypal"] as $key => $coupon)
					{					
						$this->coupon_obj->setId($coupon["pc_pk"]);
						$this->coupon_obj->setCurrentCoupon($coupon);
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{
							$_SESSION["coupons"]["paypal"][$key]["total_objects_coupon_price"] += (float) $sc[$i]["betrag"];
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
					$sc[$i]["betrag"] = round($coupon_discount_items[$sc[$i]["pobject_id"]]["discount_price"], 2);				
					if ($sc[$i]["betrag"] < 0) $sc[$i]["betrag"] = 0.0;	
				}				

				for ($j = 0; $j < count($items); $j++)
				{
					if (substr($items[$j]["name"], 0, strlen($sc[$i]["obj_id"])+2) == "[".$sc[$i]["obj_id"]."]" &&
						$items[$j]["amount"] == $sc[$i]["betrag"])
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

	function __saveTransaction($a_id)
	{
		global $ilias, $ilUser, $ilObjDataCache;
		
		$sc = $this->psc_obj->getShoppingCart(PAY_METHOD_PAYPAL);
		$this->psc_obj->clearCouponItemsSession();		

		if (is_array($sc) &&
			count($sc) > 0)
		{
			include_once './payment/classes/class.ilPaymentBookings.php';
			$book_obj =& new ilPaymentBookings($this->usr_obj);
			
			for ($i = 0; $i < count($sc); $i++)
			{
				if (!empty($_SESSION["coupons"]["paypal"]))
				{									
					$sc[$i]["math_price"] = (float) $sc[$i]["betrag"];
								
					$tmp_pobject =& new ilPaymentObject($this->user_obj, $sc[$i]['pobject_id']);	
													
					foreach ($_SESSION["coupons"]["paypal"] as $key => $coupon)
					{					
						$this->coupon_obj->setId($coupon["pc_pk"]);
						$this->coupon_obj->setCurrentCoupon($coupon);
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{
							$_SESSION["coupons"]["paypal"][$key]["total_objects_coupon_price"] += (float) $sc[$i]["betrag"];
							$_SESSION["coupons"]["paypal"][$key]["items"][] = $sc[$i];
						}								
					}
					
					unset($tmp_pobject);
				}
			}
			
			$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["paypal"]);			

			for ($i = 0; $i < count($sc); $i++)
			{
				$pobjectData = ilPaymentObject::_getObjectData($sc[$i]["pobject_id"]);
				$pobject =& new ilPaymentObject($this->user_obj,$sc[$i]['pobject_id']);

				$inst_id_time = $ilias->getSetting('inst_id').'_'.$ilUser->getId().'_'.substr((string) time(),-3);
				
				$price = $sc[$i]["betrag"];
				$bonus = 0.0;
				
				if (array_key_exists($sc[$i]["pobject_id"], $coupon_discount_items))
				{
					$bonus = $coupon_discount_items[$sc[$i]["pobject_id"]]["math_price"] - $coupon_discount_items[$sc[$i]["pobject_id"]]["discount_price"];	
				}				

				$book_obj->setTransaction($inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4));
				$book_obj->setPobjectId($sc[$i]["pobject_id"]);
				$book_obj->setCustomerId($ilUser->getId());
				$book_obj->setVendorId($pobjectData["vendor_id"]);
				$book_obj->setPayMethod($pobjectData["pay_method"]);
				$book_obj->setOrderDate(time());
				$book_obj->setDuration($sc[$i]["dauer"]);
				$book_obj->setPrice($sc[$i]["betrag_string"]);
				//$book_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount($bonus * (-1)) : "");
				$book_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount($bonus * (-1)) : 0);
				$book_obj->setPayed(1);
				$book_obj->setAccess(1);
				$book_obj->setVoucher('');
				$book_obj->setTransactionExtern($a_id);
				
				$booking_id = $book_obj->add();
				
				if (!empty($_SESSION["coupons"]["paypal"]) && $booking_id)
				{				
					foreach ($_SESSION["coupons"]["paypal"] as $coupon)
					{	
						$this->coupon_obj->setId($coupon["pc_pk"]);				
						$this->coupon_obj->setCurrentCoupon($coupon);																
							
						if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
						{						
							$this->coupon_obj->addCouponForBookingId($booking_id);																					
						}				
					}			
				}
	
				unset($booking_id);
				unset($pobject);				

				$obj_id = $ilObjDataCache->lookupObjId($pobjectData["ref_id"]);
				$obj_type = $ilObjDataCache->lookupType($obj_id);
				$obj_title = $ilObjDataCache->lookupTitle($obj_id);

				$bookings["list"][] = array(
					"type" => $obj_type,
					"title" => "[".$obj_id."]: " . $obj_title,
					"duration" => $sc[$i]["dauer"],
					"vat_rate" => $sc[$i]["vat_rate"],				
					"vat_unit" => $sc[$i]["vat_unit"],
					"price" => $sc[$i]["betrag_string"],
					"betrag" => $sc[$i]["betrag"]
				);

				$total += $sc[$i]["betrag"];
				$total_vat += $sc[$i]['vat_unit'];
				
				if ($sc[$i]["psc_id"]) $this->psc_obj->delete($sc[$i]["psc_id"]);				
			}
			
			if (!empty($_SESSION["coupons"]["paypal"]))
			{				
				foreach ($_SESSION["coupons"]["paypal"] as $coupon)
				{	
					$this->coupon_obj->setId($coupon["pc_pk"]);				
					$this->coupon_obj->setCurrentCoupon($coupon);
					$this->coupon_obj->addTracking();			
				}			
			}
		}

		$bookings["total"] = $total;
		$bookings['total_vat'] = $total_vat;

		return $bookings;
	}

	function __sendBill($bookings, $a_array)
	{
		global $ilUser, $ilias;

		$transaction = $a_array["txn_id"];

		include_once './classes/class.ilTemplate.php';
		include_once './Services/Utilities/classes/class.ilUtil.php';
		include_once './payment/classes/class.ilGeneralSettings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once 'Services/Mail/classes/class.ilMimeMail.php';

		$genSet = new ilGeneralSettings();

		$tpl = new ilTemplate("./payment/templates/default/tpl.pay_paypal_bill.html", true, true, true);
  
		$tpl->setVariable("VENDOR_ADDRESS", nl2br(utf8_decode($genSet->get("address"))));
		$tpl->setVariable("VENDOR_ADD_INFO", nl2br(utf8_decode($genSet->get("add_info"))));
		$tpl->setVariable("VENDOR_BANK_DATA", nl2br(utf8_decode($genSet->get("bank_data"))));
		$tpl->setVariable("TXT_BANK_DATA", utf8_decode($this->lng->txt("pay_bank_data")));

		$tpl->setVariable("CUSTOMER_FIRSTNAME", $a_array["first_name"]);
		$tpl->setVariable("CUSTOMER_LASTNAME", $a_array["last_name"]);
		$tpl->setVariable("CUSTOMER_STREET", $a_array["address_street"]);
		$tpl->setVariable("CUSTOMER_ZIPCODE", $a_array["address_zip"]);
		$tpl->setVariable("CUSTOMER_CITY", $a_array["address_city"]);
		$tpl->setVariable("CUSTOMER_COUNTRY", $a_array["address_country"]);

		$tpl->setVariable("BILL_NO", $transaction);
		$tpl->setVariable("DATE", date("d.m.Y"));

		$tpl->setVariable("TXT_BILL", utf8_decode($this->lng->txt("pays_bill")));
		$tpl->setVariable("TXT_BILL_NO", utf8_decode($this->lng->txt("pay_bill_no")));
		$tpl->setVariable("TXT_DATE", utf8_decode($this->lng->txt("date")));

		$tpl->setVariable("TXT_ARTICLE", utf8_decode($this->lng->txt("pay_article")));
		$tpl->setVariable('TXT_VAT_RATE', utf8_decode($this->lng->txt('vat_rate')));
		$tpl->setVariable('TXT_VAT_UNIT', utf8_decode($this->lng->txt('vat_unit')));				
		$tpl->setVariable("TXT_PRICE", utf8_decode($this->lng->txt("price_a")));

		for ($i = 0; $i < count($bookings["list"]); $i++)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj, $bookings["list"][$i]['pobject_id']);
			
			$assigned_coupons = '';					
			if (!empty($_SESSION["coupons"]["paypal"]))
			{											
				foreach ($_SESSION["coupons"]["paypal"] as $key => $coupon)
				{
					$this->coupon_obj->setId($coupon["pc_pk"]);
					$this->coupon_obj->setCurrentCoupon($coupon);

					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon["pcc_code"];
					}
				}
			}
			
			$tpl->setCurrentBlock("loop");
			$tpl->setVariable("LOOP_OBJ_TYPE", utf8_decode($this->lng->txt($bookings["list"][$i]["type"])));
			$tpl->setVariable("LOOP_TITLE", utf8_decode($bookings["list"][$i]["title"]) . $assigned_coupons);
			$tpl->setVariable("LOOP_TXT_ENTITLED_RETRIEVE", utf8_decode($this->lng->txt("pay_entitled_retrieve")));
			$tpl->setVariable("LOOP_DURATION", $bookings["list"][$i]["duration"] . " " . utf8_decode($this->lng->txt("paya_months")));
			$tpl->setVariable("LOOP_VAT_RATE", ilShopUtils::_formatVAT($bookings["list"][$i]["vat_rate"]));
			$tpl->setVariable('LOOP_VAT_UNIT', ilShopUtils::_formatFloat($bookings['list'][$i]['vat_unit']).' '.$genSet->get('currency_unit'));			
			$tpl->setVariable("LOOP_PRICE", $bookings["list"][$i]["price"]);
			$tpl->parseCurrentBlock("loop");
			
			unset($tmp_pobject);
		}
		
		if (!empty($_SESSION["coupons"]["paypal"]))
		{
			if (count($items = $bookings["list"]))
			{
				$sub_total_amount = $bookings["total"];							

				foreach ($_SESSION["coupons"]["paypal"] as $coupon)
				{
					$this->coupon_obj->setId($coupon["pc_pk"]);
					$this->coupon_obj->setCurrentCoupon($coupon);					
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;
					
					foreach ($bookings["list"] as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{						
							$total_object_price += $item["betrag"];																					
						}			
						
						unset($tmp_pobject);
					}					
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);	

					$bookings["total"] += $current_coupon_bonus * (-1);
					
					$tpl->setCurrentBlock("cloop");
					$tpl->setVariable("TXT_COUPON", utf8_decode($this->lng->txt("paya_coupons_coupon") . " " . $coupon["pcc_code"]));
					$tpl->setVariable("BONUS", number_format($current_coupon_bonus * (-1), 2, ',', '.') . " " . $genSet->get("currency_unit"));
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setVariable("TXT_SUBTOTAL_AMOUNT", utf8_decode($this->lng->txt("pay_bmf_subtotal_amount")));
				$tpl->setVariable("SUBTOTAL_AMOUNT", number_format($sub_total_amount, 2, ",", ".") . " " . $genSet->get("currency_unit"));
			}
		}
		
		if ($bookings["total"] < 0)
		{			
			$bookings["total"] = 0.0;
			$bookings["total_vat"] = 0.0;
		}

		$tpl->setVariable("TXT_TOTAL_AMOUNT", utf8_decode($this->lng->txt("pay_bmf_total_amount")));
		$tpl->setVariable("TOTAL_AMOUNT", number_format($bookings["total"], 2, ",", ".") . " " . $genSet->get("currency_unit"));
		if ($bookings["total_vat"] > 0)
		{
			$tpl->setVariable("TOTAL_VAT", ilShopUtils::_formatFloat($bookings["total_vat"]). " " . $genSet->get("currency_unit"));			
			$tpl->setVariable("TXT_TOTAL_VAT", utf8_decode($this->lng->txt("pay_bmf_vat_included")));
		}

		$tpl->setVariable("TXT_PAYMENT_TYPE", utf8_decode($this->lng->txt("pay_payed_paypal")));

		if (!@file_exists($genSet->get("pdf_path")))
		{
			ilUtil::makeDir($genSet->get("pdf_path"));
		}

		if (@file_exists($genSet->get("pdf_path")))
		{
			ilUtil::html2pdf($tpl->get(), $genSet->get("pdf_path") . "/" . $transaction . ".pdf");
		}

		if (@file_exists($genSet->get("pdf_path") . "/" . $transaction . ".pdf") &&
			$ilUser->getEmail() != "" &&
			$ilias->getSetting("admin_email") != "")
		{
			$m= new ilMimeMail; // create the mail
			$m->From( $ilias->getSetting("admin_email") );
			$m->To( $ilUser->getEmail() );
			$m->Subject( $this->lng->txt("pay_message_subject") );	
			$message = $this->lng->txt("pay_message_hello") . " " . $ilUser->getFirstname() . " " . $ilUser->getLastname() . ",\n\n";
			$message .= $this->lng->txt("pay_message_thanks") . "\n\n";
			$message .= $this->lng->txt("pay_message_attachment") . "\n\n";
			$message .= $this->lng->txt("pay_message_regards") . "\n\n";
			$message .= strip_tags($genSet->get("address"));
			$m->Body( $message );	// set the body
			$m->Attach( $genSet->get("pdf_path") . "/" . $transaction . ".pdf", "application/pdf" ) ;	// attach a file of type image/gif
			$m->Send();	// send the mail
		}

		@unlink($genSet->get("pdf_path") . "/" . $transaction . ".html");
		@unlink($genSet->get("pdf_path") . "/" . $transaction . ".pdf");

	}

	function __initShoppingCartObject()
	{
		$this->psc_obj =& new ilPaymentShoppingCart($this->user_obj);
	}

	function __getCountries()
	{
		global $lng;

		$lng->loadLanguageModule("meta");

		$cntcodes = array ("DE","ES","FR","GB","AT","CH","AF","AL","DZ","AS","AD","AO",
			"AI","AQ","AG","AR","AM","AW","AU","AT","AZ","BS","BH","BD","BB","BY",
			"BE","BZ","BJ","BM","BT","BO","BA","BW","BV","BR","IO","BN","BG","BF",
			"BI","KH","CM","CA","CV","KY","CF","TD","CL","CN","CX","CC","CO","KM",
			"CG","CK","CR","CI","HR","CU","CY","CZ","DK","DJ","DM","DO","TP","EC",
			"EG","SV","GQ","ER","EE","ET","FK","FO","FJ","FI","FR","FX","GF","PF",
			"TF","GA","GM","GE","DE","GH","GI","GR","GL","GD","GP","GU","GT","GN",
			"GW","GY","HT","HM","HN","HU","IS","IN","ID","IR","IQ","IE","IL","IT",
			"JM","JP","JO","KZ","KE","KI","KP","KR","KW","KG","LA","LV","LB","LS",
			"LR","LY","LI","LT","LU","MO","MK","MG","MW","MY","MV","ML","MT","MH",
			"MQ","MR","MU","YT","MX","FM","MD","MC","MN","MS","MA","MZ","MM","NA",
			"NR","NP","NL","AN","NC","NZ","NI","NE","NG","NU","NF","MP","NO","OM",
			"PK","PW","PA","PG","PY","PE","PH","PN","PL","PT","PR","QA","RE","RO",
			"RU","RW","KN","LC","VC","WS","SM","ST","SA","CH","SN","SC","SL","SG",
			"SK","SI","SB","SO","ZA","GS","ES","LK","SH","PM","SD","SR","SJ","SZ",
			"SE","SY","TW","TJ","TZ","TH","TG","TK","TO","TT","TN","TR","TM","TC",
			"TV","UG","UA","AE","GB","UY","US","UM","UZ","VU","VA","VE","VN","VG",
			"VI","WF","EH","YE","ZR","ZM","ZW");
		$cntrs = array();
		foreach($cntcodes as $cntcode)
		{
			$cntrs[$cntcode] = $lng->txt("meta_c_".$cntcode);
		}
		asort($cntrs);
		return $cntrs;
	}

	function __getCountryCode($value = "")
	{
		$countries = $this->__getCountries();
		foreach($countries as $code => $text)
		{
			if ($text == $value)
			{
				return $code;
			}
		}
		return;
	}

	function __getCountryName($value = "")
	{
		$countries = $this->__getCountries();
		return $countries[$value];
	}

}
?>
