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

	function ilPurchasePaypal(&$user_obj)
	{
		global $ilDB,$lng;

		$this->user_obj =& $user_obj;
		$this->db =& $ilDB;
		$this->lng =& $lng;

		$this->__initShoppingCartObject();

		$ppSet = new ilPaypalSettings();
		$this->paypalConfig = $ppSet->getAll();

		$this->lng->loadLanguageModule("payment");
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

		$tx_token = $_GET['tx'];

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

			return SUCCESS;
		}
		else if (strcmp ($lines[0], "FAIL") == 0)
		{
			return ERROR_FAIL;
		}
	}

	function __checkTransactionId($a_id)
	{
		$query = "SELECT * FROM payment_statistic ".
			"WHERE transaction_extern = '".$a_id."'";

		$res = $this->db->query($query);

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

		if (is_array($sc = $this->psc_obj->getShoppingCart(PAY_METHOD_PAYPAL)) &&
			count($sc) > 0)
		{
			for ($i = 0; $i < count($sc); $i++)
			{
				$items[$i] = array(
					"name" => $a_array["item_name".($i+1)],
					"amount" => $a_array["mc_gross_".($i+1)]
				);
			}

			$found = 0;
			$total = 0;
			for ($i = 0; $i < count($sc); $i++)
			{
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

		if (is_array($sc = $this->psc_obj->getShoppingCart(PAY_METHOD_PAYPAL)) &&
			count($sc) > 0)
		{
			include_once './payment/classes/class.ilPaymentBookings.php';
			$book_obj =& new ilPaymentBookings($this->usr_obj);

			for ($i = 0; $i < count($sc); $i++)
			{

				$pobjectData = ilPaymentObject::_getObjectData($sc[$i]["pobject_id"]);

				$inst_id_time = $ilias->getSetting('inst_id').'_'.$ilUser->getId().'_'.substr((string) time(),-3);

				$book_obj->setTransaction($inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4));
				$book_obj->setPobjectId($sc[$i]["pobject_id"]);
				$book_obj->setCustomerId($ilUser->getId());
				$book_obj->setVendorId($pobjectData["vendor_id"]);
				$book_obj->setPayMethod($pobjectData["pay_method"]);
				$book_obj->setOrderDate(time());
				$book_obj->setDuration($sc[$i]["dauer"]);
				$book_obj->setPrice($sc[$i]["betrag_string"]);
				$book_obj->setPayed(1);
				$book_obj->setAccess(1);
				$book_obj->setVoucher('');
				$book_obj->setTransactionExtern($a_id);
				$book_obj->add();

				$obj_id = $ilObjDataCache->lookupObjId($pobjectData["ref_id"]);
				$obj_type = $ilObjDataCache->lookupType($obj_id);
				$obj_title = $ilObjDataCache->lookupTitle($obj_id);

				$bookings["list"][] = array(
					"type" => $obj_type,
					"title" => "[".$obj_id."]: " . $obj_title,
					"duration" => $sc[$i]["dauer"],
					"price" => $sc[$i]["betrag_string"]
				);

				$total += $sc[$i]["betrag"];

				$query = "DELETE FROM payment_shopping_cart ".
					"WHERE pobject_id = '".$sc[$i]["pobject_id"]."'";
			
				$res = $this->db->query($query);
			}
		}

		$bookings["total"] = $total;
		$bookings["vat"] = $this->psc_obj->getVat($total);

		return $bookings;
	}

	function __sendBill($bookings, $a_array)
	{
		global $ilUser, $ilias;

		$transaction = $a_array["txn_id"];

		include_once './classes/class.ilTemplate.php';
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once './payment/classes/class.ilGeneralSettings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once 'Services/Mail/classes/class.ilMimeMail.php';
		
		$genSet = new ilGeneralSettings();

		$tpl = new ilTemplate("./payment/templates/default/tpl.pay_paypal_bill.html", true, true, true);
  
		$tpl->setVariable("VENDOR_ADDRESS", nl2br(utf8_decode($genSet->get("address"))));
		$tpl->setVariable("VENDOR_ADD_INFO", nl2br(utf8_decode($genSet->get("add_info"))));
		$tpl->setVariable("VENDOR_BANK_DATA", nl2br(utf8_decode($genSet->get("bank_data"))));
		$tpl->setVariable("TXT_BANK_DATA", utf8_decode($this->lng->txt("pay_bank_data")));

#		$tpl->setVariable("CUSTOMER_FIRSTNAME", utf8_decode($ilUser->getFirstname()));
#		$tpl->setVariable("CUSTOMER_LASTNAME", utf8_decode($ilUser->getLastname()));
#		$tpl->setVariable("CUSTOMER_STREET", utf8_decode($ilUser->getStreet()));
#		$tpl->setVariable("CUSTOMER_ZIPCODE", utf8_decode($ilUser->getZipcode()));
#		$tpl->setVariable("CUSTOMER_CITY", utf8_decode($ilUser->getCity()));
#		$tpl->setVariable("CUSTOMER_COUNTRY", utf8_decode($ilUser->getCountry()));
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
		$tpl->setVariable("TXT_PRICE", utf8_decode($this->lng->txt("price_a")));

		for ($i = 0; $i < count($bookings["list"]); $i++)
		{
			$tpl->setCurrentBlock("loop");
			$tpl->setVariable("LOOP_OBJ_TYPE", utf8_decode($this->lng->txt($bookings["list"][$i]["type"])));
			$tpl->setVariable("LOOP_TITLE", utf8_decode($bookings["list"][$i]["title"]));
			$tpl->setVariable("LOOP_TXT_ENTITLED_RETRIEVE", utf8_decode($this->lng->txt("pay_entitled_retrieve")));
			$tpl->setVariable("LOOP_DURATION", $bookings["list"][$i]["duration"] . " " . utf8_decode($this->lng->txt("paya_months")));
			$tpl->setVariable("LOOP_PRICE", $bookings["list"][$i]["price"]);
			$tpl->parseCurrentBlock("loop");
		}

		$tpl->setVariable("TXT_TOTAL_AMOUNT", utf8_decode($this->lng->txt("pay_bmf_total_amount")));
		$tpl->setVariable("TOTAL_AMOUNT", number_format($bookings["total"], 2, ",", ".") . " " . $genSet->get("currency_unit"));
		if ($bookings["vat"] > 0)
		{
			$tpl->setVariable("VAT", number_format($bookings["vat"], 2, ",", ".") . " " . $genSet->get("currency_unit"));
			$tpl->setVariable("TXT_VAT", $genSet->get("vat_rate") . "% " . utf8_decode($this->lng->txt("pay_bmf_vat_included")));
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