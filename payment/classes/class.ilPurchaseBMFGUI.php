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
* Class ilPurchaseBMFGUI
*
* @author Stefan Meyer, Jens Conze
* @version $Id$
*
* @package core
*/

include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentShoppingCartGUI.php';
include_once dirname(__FILE__)."/../bmf/lib/ePayment/cfg_epayment.inc.php";
include_once dirname(__FILE__)."/../bmf/lib/SOAP/class.ilBMFClient.php";

class ilPurchaseBMFGUI
{
	var $ctrl;
	var $tpl;

	var $user_obj;
	var $error;
	var $shoppingCart;

	function ilPurchaseBMFGUI(&$user_obj)
	{
		global $ilias,$ilDB,$lng,$tpl,$rbacsystem;

		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		
		// Get user object
		$this->user_obj =& $user_obj;
		if (!is_array($_SESSION["bmf"]))
		{
			$_SESSION["bmf"]["personal_data"]["vorname"] = $this->user_obj->getFirstname();
			$_SESSION["bmf"]["personal_data"]["nachname"] = $this->user_obj->getLastname();
			$_SESSION["bmf"]["personal_data"]["strasse"] = $this->user_obj->getStreet();
			$_SESSION["bmf"]["personal_data"]["hausNr"] = "";
			$_SESSION["bmf"]["personal_data"]["postfach"] = "";
			$_SESSION["bmf"]["personal_data"]["PLZ"] = $this->user_obj->getZipcode();
			$_SESSION["bmf"]["personal_data"]["ort"] = $this->user_obj->getCity();
			$_SESSION["bmf"]["personal_data"]["land"] = $this->user_obj->getCountry();
			$_SESSION["bmf"]["personal_data"]["EMailAdresse"] = $this->user_obj->getEmail();
			$_SESSION["bmf"]["personal_data"]["sprache"] = $this->user_obj->getLanguage();
		}
		
		$this->__loadTemplate();

		$this->error = "";
		
		$this->lng->loadLanguageModule("payment");
	}

	function showPersonalData()
	{
		// user_id $this->user_obj->getId()
		// all 

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_personal_data.html',true);

		$this->tpl->setVariable("PERSONAL_DATA_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_personal_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_personal_data'));

		// set plain text variables
		$this->tpl->setVariable("TXT_FIRSTNAME",$this->lng->txt('firstname'));
		$this->tpl->setVariable("TXT_LASTNAME",$this->lng->txt('lastname'));
		$this->tpl->setVariable("TXT_STREET",$this->lng->txt('street'));
		$this->tpl->setVariable("TXT_HOUSE_NUMBER",$this->lng->txt('pay_bmf_house_number'));
		$this->tpl->setVariable("TXT_OR",$this->lng->txt('pay_bmf_or'));
		$this->tpl->setVariable("TXT_PO_BOX",$this->lng->txt('pay_bmf_po_box'));
		$this->tpl->setVariable("TXT_ZIPCODE",$this->lng->txt('zipcode'));
		$this->tpl->setVariable("TXT_CITY",$this->lng->txt('city'));
		$this->tpl->setVariable("TXT_COUNTRY",$this->lng->txt('country'));
		$this->tpl->setVariable("TXT_EMAIL",$this->lng->txt('email'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		// fill defaults

		$this->error != "" && isset($_POST['country']) ? $this->__showCountries($this->tpl, $_POST['country']) : $this->__showCountries($this->tpl, $_SESSION['bmf_pd']['land']);
		$this->tpl->setVariable("FIRSTNAME",
								$this->error != "" && isset($_POST['firstname']) 
								? ilUtil::prepareFormOutput($_POST['firstname'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['vorname'],true));
		$this->tpl->setVariable("LASTNAME",
								$this->error != "" && isset($_POST['lastname']) 
								? ilUtil::prepareFormOutput($_POST['lastname'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['nachname'],true));
		$this->tpl->setVariable("STREET",
								$this->error != "" && isset($_POST['street']) 
								? ilUtil::prepareFormOutput($_POST['street'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['strasse'],true));
		$this->tpl->setVariable("HOUSE_NUMBER",
								$this->error != "" && isset($_POST['house_number']) 
								? ilUtil::prepareFormOutput($_POST['house_number'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['hausNr'],true));
		$this->tpl->setVariable("PO_BOX",
								$this->error != "" && isset($_POST['po_box']) 
								? ilUtil::prepareFormOutput($_POST['po_box'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['postfach'],true));
		$this->tpl->setVariable("ZIPCODE",
								$this->error != "" && isset($_POST['zipcode']) 
								? ilUtil::prepareFormOutput($_POST['zipcode'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['PLZ'],true));
		$this->tpl->setVariable("CITY",
								$this->error != "" && isset($_POST['city']) 
								? ilUtil::prepareFormOutput($_POST['city'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['ort'],true));
		$this->tpl->setVariable("EMAIL",
								$this->error != "" && isset($_POST['email']) 
								? ilUtil::prepareFormOutput($_POST['email'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['EMailAdresse'],true));
	}

	function getPersonalData()
	{
		if ($_POST["firstname"] == "" ||
			$_POST["lastname"] == "" ||
			($_POST["street"] == "" && $_POST["house_number"] == "" && $_POST["po_box"] == "") ||
			($_POST["street"] != "" && $_POST["house_number"] == "") ||
			($_POST["street"] == "" && $_POST["house_number"] != "") ||
			$_POST["zipcode"] == "" ||
			$_POST["city"] == "" ||
			$_POST["country"] == "" ||
			$_POST["email"] == "")
		{
			$this->error = $this->lng->txt('pay_bmf_personal_data_not_valid');
			sendInfo($this->error);
			$this->showPersonalData();
			return;
		}
		
		$_SESSION["bmf"]["personal_data"]["vorname"] = $_POST["firstname"];
		$_SESSION["bmf"]["personal_data"]["nachname"] = $_POST["lastname"];
		$_SESSION["bmf"]["personal_data"]["strasse"] = $_POST["street"];
		$_SESSION["bmf"]["personal_data"]["hausNr"] = $_POST["house_number"];
		$_SESSION["bmf"]["personal_data"]["postfach"] = $_POST["po_box"];
		$_SESSION["bmf"]["personal_data"]["PLZ"] = $_POST["zipcode"];
		$_SESSION["bmf"]["personal_data"]["ort"] = $_POST["city"];
		$_SESSION["bmf"]["personal_data"]["land"] = $_POST["country"];
		$_SESSION["bmf"]["personal_data"]["EMailAdresse"] = $_POST["email"];
		$_SESSION["bmf"]["personal_data"]["sprache"] = $this->user_obj->getLanguage();

		$this->error = "";
		$this->showPaymentType();
	}

	function showPaymentType()
	{
		// user_id $this->user_obj->getId()
		// all 

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_payment_type.html',true);

		$this->tpl->setVariable("PAYMENT_TYPE_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_payment_type'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_payment_type'));

		// set plain text variables
		$this->tpl->setVariable("TXT_DEBIT_ENTRY",$this->lng->txt('pay_bmf_debit_entry'));
		$this->tpl->setVariable("TXT_CREDIT_CARD",$this->lng->txt('pay_bmf_credit_card'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		// fill defaults

		if ($this->error != "" &&
			isset($_POST["payment_type"]))
		{
			$this->tpl->setVariable("PAYMENT_TYPE_" . $_POST['payment_type'], " checked") ;
		}
		else
		{
			$this->tpl->setVariable("PAYMENT_TYPE_" . $_SESSION["bmf"]["payment_type"], " checked") ;
		}

		// Button
		$this->tpl->setVariable("TXT_BACK",$this->lng->txt('pay_bmf_back'));
		$this->tpl->setVariable("URL_BACK",$_SERVER["PHP_SELF"] . "?cmd=showPersonalData");
	}

	function getPaymentType()
	{
		if ($_POST["payment_type"] != "credit_card" &&
			$_POST["payment_type"] != "debit_entry")
		{
			$this->error = $this->lng->txt('pay_bmf_payment_type_data_not_valid');
			sendInfo($this->error);
			$this->showPaymentType();
			return;
		}
		
		$_SESSION["bmf"]["payment_type"] = $_POST["payment_type"];

		$this->error = "";
		if ($_SESSION["bmf"]["payment_type"] == "credit_card")
		{
			$this->showCreditCard();
		}
		else
		{
			$this->showDebitEntry();
		}
	}

	function showDebitEntry()
	{
		// user_id $this->user_obj->getId()
		// all 

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_debit_entry.html',true);

		$this->__showShoppingCart();

		$this->tpl->setVariable("DEBIT_ENTRY_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_debit_entry_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_debit_entry'));

		// set plain text variables
		$this->tpl->setVariable("TXT_ACCOUNT_HOLDER",$this->lng->txt('pay_bmf_account_holder'));
		$this->tpl->setVariable("TXT_OPTIONAL",$this->lng->txt('pay_bmf_optional'));
		$this->tpl->setVariable("TXT_BANK_CODE",$this->lng->txt('pay_bmf_bank_code'));
		$this->tpl->setVariable("TXT_ACCOUNT_NUMBER",$this->lng->txt('pay_bmf_account_number'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		// fill defaults

		$this->tpl->setVariable("ACCOUNT_HOLDER",
								$this->error != "" && isset($_POST['account_holder']) 
								? ilUtil::prepareFormOutput($_POST['account_holder'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['kontoinhaber'],true));
		$this->tpl->setVariable("BANK_CODE",
								$this->error != "" && isset($_POST['bank_code']) 
								? ilUtil::prepareFormOutput($_POST['bank_code'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['BLZ'],true));
		$this->tpl->setVariable("ACCOUNT_NUMBER",
								$this->error != "" && isset($_POST['account_number']) 
								? ilUtil::prepareFormOutput($_POST['account_number'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['kontoNr'],true));

		// Button
		$this->tpl->setVariable("TXT_BACK",$this->lng->txt('pay_bmf_back'));
		$this->tpl->setVariable("URL_BACK",$_SERVER["PHP_SELF"] . "?cmd=showPaymentType");
	}

	function getDebitEntry()
	{
		if ($_POST["bank_code"] == "" ||
			$_POST["account_number"] == "")
		{
			$this->error = $this->lng->txt('pay_bmf_debit_entry_not_valid');
			sendInfo($this->error);
			$this->showDebitEntry();
			return;
		}
		
		$_SESSION["bmf"]["debit_entry"]["bank_code"] = $_POST["bank_code"];
		$_SESSION["bmf"]["debit_entry"]["kontoinhaber"] = $_POST["account_holder"];
		$_SESSION["bmf"]["debit_entry"]["kontoNr"] = $_POST["account_number"];

		$this->error = "";
		$this->sendDebitEntry();
	}

	function showCreditCard()
	{
		// user_id $this->user_obj->getId()
		// all 

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_credit_card.html',true);

		$this->__showShoppingCart();

		$this->tpl->setVariable("CREDIT_CARD_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_credit_card_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_credit_card'));

		// set plain text variables
		$this->tpl->setVariable("TXT_CARD_HOLDER",$this->lng->txt('pay_bmf_card_holder'));
		$this->tpl->setVariable("TXT_OPTIONAL",$this->lng->txt('pay_bmf_optional'));
		$this->tpl->setVariable("TXT_CARD_NUMBER",$this->lng->txt('pay_bmf_card_number'));
		$this->tpl->setVariable("TXT_VALIDITY",$this->lng->txt('pay_bmf_validity'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		// fill defaults

		$this->tpl->setVariable("CARD_HOLDER",
								$this->error != "" && isset($_POST['card_holder']) 
								? ilUtil::prepareFormOutput($_POST['card_holder'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['karteninhaber'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_1",
								$this->error != "" && isset($_POST['card_number']['block_1']) 
								? ilUtil::prepareFormOutput($_POST['card_number']['block_1'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_1'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_2",
								$this->error != "" && isset($_POST['card_number']['block_2']) 
								? ilUtil::prepareFormOutput($_POST['card_number']['block_2'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_2'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_3",
								$this->error != "" && isset($_POST['card_number']['block_3']) 
								? ilUtil::prepareFormOutput($_POST['card_number']['block_3'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_3'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_4",
								$this->error != "" && isset($_POST['card_number']['block_4']) 
								? ilUtil::prepareFormOutput($_POST['card_number']['block_4'],true) 
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_4'],true));
		for ($i = 1; $i <= 12; $i++)
		{
			$this->tpl->setCurrentBlock("loop_validity_months");
			$this->tpl->setVariable("LOOP_VALIDITY_MONTHS", $i < 10 ? "0" . $i : $i);
			$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_TXT", $i < 10 ? "0" . $i : $i);
			if ($this->error != "" &&
				isset($_POST['validity']['month']))
			{
				if ($_POST['validity']['month'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_SELECTED", " selected");
				}
			}
			else
			{
				if ($_SESSION["bmf"]["credit_card"]['validity']['month'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_SELECTED", " selected");
				}
			}
			$this->tpl->parseCurrentBlock("loop_validity_months");
		}
		for ($i = date("Y"); $i <= (date("Y")+3); $i++)
		{
			$this->tpl->setCurrentBlock("loop_validity_years");
			$this->tpl->setVariable("LOOP_VALIDITY_YEARS", $i);
			$this->tpl->setVariable("LOOP_VALIDITY_YEARS_TXT", $i);
			if ($this->error != "" &&
				isset($_POST['validity']['year']))
			{
				if ($_POST['validity']['year'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_YEARS_SELECTED", " selected");
				}
			}
			else
			{
				if ($_SESSION["bmf"]["credit_card"]['validity']['year'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_YEARS_SELECTED", " selected");
				}
			}
			$this->tpl->parseCurrentBlock("loop_validity_years");
		}

		// Button
		$this->tpl->setVariable("TXT_BACK",$this->lng->txt('pay_bmf_back'));
		$this->tpl->setVariable("URL_BACK",$_SERVER["PHP_SELF"] . "?cmd=showPaymentType");
	}

	function getCreditCard()
	{
		if ($_POST["card_number"]["block_1"] == "" ||
			$_POST["card_number"]["block_2"] == "" ||
			$_POST["card_number"]["block_3"] == "" ||
			$_POST["card_number"]["block_4"] == "" ||
			$_POST["validity"]["month"] == "" ||
			$_POST["validity"]["year"] == "" ||
			$_POST["validity"]["year"]."-".$_POST["validity"]["month"] < date("Y-m"))
		{
			$this->error = $this->lng->txt('pay_bmf_credit_card_not_valid');
			sendInfo($this->error);
			$this->showCreditCard();
			return;
		}
		
		$_SESSION["bmf"]["credit_card"]["gueltigkeit"]["monat"] = $_POST["validity"]["month"];
		$_SESSION["bmf"]["credit_card"]["gueltigkeit"]["jahr"] = $_POST["validity"]["year"];
		$_SESSION["bmf"]["credit_card"]["karteninhalber"] = $_POST["card_holder"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_1"] = $_POST["card_number"]["block_1"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_2"] = $_POST["card_number"]["block_2"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_3"] = $_POST["card_number"]["block_3"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_4"] = $_POST["card_number"]["block_4"];

		$this->error = "";
		$this->sendCreditCard();
	}

	function sendCreditCard()
	{
		$customer = new Customer();
		$customer->create();

		$credit_card = new CreditCard();
		$credit_card->create();
		
		vd($customer);
		vd($credit_card);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree;

		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{

			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showPersonalData';
				}
				$this->$cmd();
				break;
		}
	}

	// PRIVATE
	function __loadTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.payb_content.html");
		
		$this->__buildStylesheet();
		$this->__buildStatusline();
	}

	function  __buildStatusline()
	{
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}	

	function __buildStylesheet()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_JAVASCRIPT",ilUtil::getJSPath('functions.js'));
	}

	/**
	* shows select box für countries
	*/
	function __showCountries(&$tpl, $value = "")
	{
		$countries = $this->__getCountries();
		foreach($countries as $code => $text)
		{
			$tpl->setCurrentBlock("loop_countries");
			$tpl->setVariable("LOOP_COUNTRIES", $code);
			$tpl->setVariable("LOOP_COUNTRIES_TXT", $text);
			if ($value != "" &&
				$value == $code)
			{
				$tpl->setVariable("LOOP_COUNTRIES_SELECTED", " selected");
			}
			$tpl->parseCurrentBlock("loop_countries");
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("pay_bmf_please_select"));
		return;
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

	function __showShoppingCart()
	{

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries()))
		{
			sendInfo($this->lng->txt('pay_shopping_cart_empty'));
		}

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			$price_arr = ilPaymentPrices::_getPrice($item['price_id']);

			$f_result[$counter][] = $tmp_obj->getTitle();
			$f_result[$counter][] = $price_arr['duration'];

			$price = $price_arr['unit_value'].' '.$this->lng->txt('paya_euro');

			if($price_arr['sub_unit_value'])
			{
				$price .= ' '.$price_arr['sub_unit_value'].' '.$this->lng->txt('paya_cent');
			}
			$f_result[$counter][] = $price;

			unset($tmp_obj);
			unset($tmp_pobject);
			
			++$counter;
		}
			
		return $this->__showItemsTable($f_result);
	}

	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __showItemsTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("paya_statistic"),"icon_pays_b.gif",$this->lng->txt("paya_statistic"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a")));

		$tbl->setHeaderVars(array("title",
								  "duration",
								  "price"),
							array("cmd" => "",
								  "cmdClass" => "ilpurchasebmfgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->disable("footer");
		$tbl->disable("sort");
		$tbl->disable("linkbar");

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'title');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);


		$tbl->render();

		$this->tpl->setVariable("ITEMS_TABLE",$tbl->tpl->get());

		return true;
	}

}


class Customer
{
	function Customer ($customerNumber = 0)
    {
		if ($customerNumber > 0)
		{
			$this->setCustomerNumber($customerNumber);
		}
		else
		{
			$this->__createCustomerNumber();
		}
	}
	
	function create($values = "")
	{
		if ($value == "")
		{
			$values = $_SESSION["bmf"]["personal_data"];
		}
		if (is_array($values))
		{
			$this->setLanguage($values["sprache"]);
			$this->setFirstname($values["vorname"]);
			$this->setLastname($values["nachname"]);
			$this->setEmail($values["EMailAdresse"]);

			$address = new Address();
			$address->create();

			$this->setAddress($address);
		}
	}

	function setCustomerNumber($customerNumber = 0)
	{
		$this->EShopKundenNr = $customerNumber;
	}
	function setLanguage($language = "")
	{
		$this->sprache = $language;
	}
	function setFirstname($firstname = "")
	{
		$this->vorname = $firstname;
	}
	function setLastname($lastname = "")
	{
		$this->nachname = $lastname;
	}
	function setEmail($email = "")
	{
		$this->EMailAdresse = $email;
	}
	function setAddress($address = NULL)
	{
		$this->rechnungsAdresse = $address;
	}

	function __createCustomerNumber()
	{
		$this->EShopKundenNr = 1;
	}
}

class Address
{
	function Address ()
    {
	}
	
	function create($values = "")
	{
		if ($value == "")
		{
			$values = $_SESSION["bmf"]["personal_data"];
		}
		if (is_array($values))
		{
			$this->setStreet($values["strasse"]);
			$this->setHouseNumber($values["hausNr"]);
			$this->setPOBox($values["postfach"]);
			$this->setCountry($values["land"]);
			$this->setZipCode($values["PLZ"]);
			$this->setCity($values["ort"]);
		}
	}

	function setStreet($street)
	{
		$this->strasse = $street;
	}
	function setHouseNumber($houseNumber)
	{
		$this->hausNr = $houseNumber;
	}
	function setPOBox($POBox)
	{
		$this->postfach = $POBox;
	}
	function setCountry($country)
	{
		$this->land = $country;
	}
	function setZipCode($zipCode)
	{
		$this->PLZ = $zipCode;
	}
	function setCity($city)
	{
		$this->ort = $city;
	}
}

class CreditCard
{
	function CreditCard ()
    {
	}
	
	function create($values = "")
	{
		if ($value == "")
		{
			$values = $_SESSION["bmf"]["credit_card"];
		}
		if (is_array($values))
		{
			$this->setCardHolder($values["karteninhaber"]);
			$this->setCardNumber($values["kreditkartenNr"]);
			$this->setValidity($values["gueltigkeit"]);
		}
	}

	function setCardHolder($cardHolder = "")
	{
		$this->karteninhaber = $cardHolder;
	}
	function setCardNumber($cardNumber = array())
	{
		if (count($cardNumber) == 4)
		{
			for ($i = 1; $i <= count($cardNumber); $i++)
			{
				$this->kreditkartenNr .= $cardNumber["block_".$i] . "-";
			}
			$this->kreditkartenNr = substr($this->kreditkartenNr, 0, strlen($this->kreditkartenNr)-1);
		}
		else
		{
			$this->kreditkartenNr = "";
		}
	}
	function setValidity($validity = array())
	{
		if ($validity["monat"] != "" &&
			$validity["jahr"] != "")
		{
			$this->gueltigkeit = $validity["monat"];
			$this->gueltigkeit .= $validity["jahr"];
		}
		else
		{
			$this->gueltigkeit = "";
		}
	}
}

class DebitEntry
{
	function DebitEntry ()
    {
	}
	
	function create($values = "")
	{
		if ($value == "")
		{
			$values = $_SESSION["bmf"]["debit_entry"];
		}
		if (is_array($values))
		{
			$this->setBankCode($values["BLZ"]);
			$this->setAccountNumber($values["kontoNr"]);
			$this->setAccountHolder($values["kontoinhaber"]);
		}
	}

	function setAccountHolder($accountHolder = "")
	{
		$this->kontoinhaber = $accountHolder;
	}
	function setAccountNumber($accountNumber = "")
	{
		$this->kontonr = $accountNumber;
	}
	function setBankCode($bankCode = "")
	{
		$this->BLZ = $bankCode;
	}
}

class BookEntry
{
    function BookEntry()
    {
    }

	function setHaushaltstelle()
	{
		global $bmfConfig;
		$this->haushaltsstelle = $bmfConfig["haushaltsstelle"];
	}
	function setObjectNumber()
	{
		global $bmfConfig;
		$this->objektnummer = $bmfConfig["objektNr"];
	}
	function setPostingText($postingText = "")
	{
		$this->buchungstext = $postingText;
	}
	function setAmount($amount = "")
	{
		$this->betrag = $amount;
	}
	function setVoucherNumber($voucherNumber = "")
	{
		$this->belegNr = $voucherNumber;
	}
	function getVoucherNumber()
	{
		return $this->belegNr;
	}
}

class BookingList
{
    function BookingList()
    {
    }
}

?>