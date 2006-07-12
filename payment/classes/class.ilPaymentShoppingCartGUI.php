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
* Class ilPaymentShoppingCartGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/

include_once './payment/classes/class.ilPurchasePaypal.php';
include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentBaseGUI.php';
include_once './payment/classes/class.ilPaypalSettings.php';

class ilPaymentShoppingCartGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $lng;
	var $user_obj;

	/*
	 * shopping cart obj
	 */
	var $psc_obj = null;

	/*
	 * paypal obj
	 */
	var $paypal_obj = null;

	var $paypalConfig;

	function ilPaymentShoppingCartGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ilPaymentBaseGUI();

		$this->user_obj =& $user_obj;

		$ppSet = new ilPaypalSettings();
		$this->paypalConfig = $ppSet->getAll();
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
					$cmd = 'showItems';
				}
				$this->$cmd();
				break;
		}
	}

	function finishPaypal()
	{
		$this->__initPaypalObject();

		if (!($fp = $this->paypal_obj->openSocket()))
		{
			sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_unreachable')."<br />".$this->lng->txt('pay_paypal_error_info'));
			$this->showItems();
		}
		else
		{
			$res = $this->paypal_obj->checkData($fp);
			if ($res == SUCCESS)
			{
				sendInfo($this->lng->txt('pay_paypal_success'), true);
				$this->ctrl->redirectByClass('ilpaymentbuyedobjectsgui');
			}
			else
			{
				switch ($res)
				{
					case ERROR_WRONG_CUSTOMER	:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_wrong_customer')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_NOT_COMPLETED	:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_not_completed')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_PREV_TRANS_ID	:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_prev_trans_id')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_VENDOR		:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_wrong_vendor')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_ITEMS		:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_wrong_items')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_FAIL				:	sendInfo($this->lng->txt('pay_paypal_failed')."<br />".$this->lng->txt('pay_paypal_error_fails')."<br />".$this->lng->txt('pay_paypal_error_info'));
													break;
				}
				$this->showItems();
			}
			fclose($fp);
		}
	}

	function cancelPaypal()
	{
		sendInfo($this->lng->txt('pay_paypal_canceled'));
		$this->showItems();
	}

	function showItems()
	{
		global $ilObjDataCache, $ilUser;

		include_once './payment/classes/class.ilPaymentPrices.php';

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_shopping_cart.html','payment');

		$this->__initShoppingCartObject();

		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		include_once './payment/classes/class.ilPayMethods.php';

		if (ilPayMethods::_enabled('pm_bmf')) $pay_methods[] = PAY_METHOD_BMF;
		if (ilPayMethods::_enabled('pm_paypal')) $pay_methods[] = PAY_METHOD_PAYPAL;

		$num_items = 0;
		if (is_array($pay_methods))
		{
			for ($p = 0; $p < count($pay_methods); $p++)
			{

				if ($pay_methods[$p] == PAY_METHOD_BMF)
					$tpl =& new ilTemplate("./payment/templates/default/tpl.pay_shopping_cart_bmf.html",true,true);
				else if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
					$tpl =& new ilTemplate("./payment/templates/default/tpl.pay_shopping_cart_paypal.html",true,true);

				if(count($items = $this->psc_obj->getEntries($pay_methods[$p])))
				{
					$counter = 0;
					foreach($items as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);
			
						$obj_id = $ilObjDataCache->lookupObjId($tmp_pobject->getRefId());
						$obj_type = $ilObjDataCache->lookupType($obj_id);
						$obj_title = $ilObjDataCache->lookupTitle($obj_id);
			
						$f_result[$counter][] = ilUtil::formCheckBox(0,'item[]',$item['psc_id']);
						$f_result[$counter][] = "<a href=\"goto.php?target=".$obj_type."_".$tmp_pobject->getRefId() . "\">".$obj_title."</a>";

						$price_arr = ilPaymentPrices::_getPrice($item['price_id']);
						$f_result[$counter][] = $price_arr['duration'].' '.$this->lng->txt('paya_months');
			
						$f_result[$counter][] = ilPaymentPrices::_getPriceString($item['price_id']);
			
						if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
						{
							$tpl->setCurrentBlock("loop_items");
							$tpl->setVariable("LOOP_ITEMS_NO", ($counter+1));
							$tpl->setVariable("LOOP_ITEMS_NAME", "[".$obj_id."]: ".$obj_title);
							$tpl->setVariable("LOOP_ITEMS_AMOUNT", $price_arr['unit_value'].".".$price_arr['sub_unit_value']);
							$tpl->parseCurrentBlock("loop_items");

#							$buttonParams["item_name_".($counter+1)] = $obj_title;
#							$buttonParams["amount_".($counter+1)] = $price_arr['unit_value'].".".$price_arr['sub_unit_value'];
						}

						unset($tmp_obj);
						unset($tmp_pobject);
						
						++$counter;
					}

					$tpl->setCurrentBlock("buy_link");
					switch($pay_methods[$p])
					{
						case PAY_METHOD_BMF:
							$tpl->setVariable("SCRIPT_LINK", './payment/start_bmf.php');
							break;
		
						case PAY_METHOD_PAYPAL:
							$tpl->setVariable("SCRIPT_LINK", "https://".$this->paypalConfig["server_host"].$this->paypalConfig["server_path"]);
							$tpl->setVariable("POPUP_BLOCKER", $this->lng->txt('popup_blocker'));
							$tpl->setVariable("VENDOR", $this->paypalConfig["vendor"]);
							$tpl->setVariable("RETURN", ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, "finishPaypal"));
							$tpl->setVariable("CANCEL_RETURN", ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, "cancelPaypal"));
							$tpl->setVariable("CUSTOM", $ilUser->getId());
							$tpl->setVariable("CURRENCY", $genSet->get("currency_unit"));
							$tpl->setVariable("PAGE_STYLE", $this->paypalConfig["page_style"]);
							
#							$buttonParams["upload"] = 1;
#							$buttonParams["charset"] = "utf-8";
#							$buttonParams["business"] = $this->paypalConfig["vendor"];
#							$buttonParams["currency_code"] = "EUR";
#							$buttonParams["return"] = "http://www.databay.de/user/jens/paypal.php";
#							$buttonParams["rm"] = 2;
#							$buttonParams["cancel_return"] = "http://www.databay.de/user/jens/paypal.php";
#							$buttonParams["custom"] = "HALLO";
#							$buttonParams["invoice"] = "0987654321";
#							if ($enc_data = $this->__encryptButton($buttonParams))
#							{
#								$tpl->setVariable("ENCDATA", $enc_data);
#							}

							break;
					}
					$tpl->setVariable("TXT_BUY", $this->lng->txt('pay_click_to_buy'));
					$tpl->parseCurrentBlock("buy_link");

					$tpl->setCurrentBlock("loop");

					$this->__showItemsTable($tpl, $f_result, $pay_methods[$p]);
					unset($f_result);

					$tpl->parseCurrentBlock("loop");

					if ($pay_methods[$p] == PAY_METHOD_BMF)
						$this->tpl->setVariable("BMF", $tpl->get());
					else if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
						$this->tpl->setVariable("PAYPAL", $tpl->get());

					$num_items += $counter;
				}

			}
		}
		
		if ($num_items == 0)
		{
			sendInfo($this->lng->txt('pay_shopping_cart_empty'));

			return false;
		}
		else
		{
			return true;
		}

	}
	
	function __showItemsTable(&$a_tpl, $a_result_set, $a_pay_method = 0)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","deleteItem");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$title = $this->lng->txt("paya_shopping_cart");
		switch($a_pay_method)
		{
			case PAY_METHOD_BMF:
				$title .= " (" . $this->lng->txt("payment_system") . ": " . $this->lng->txt("pays_bmf") . ")";
				break;

			case PAY_METHOD_PAYPAL:
				$title .= " (" . $this->lng->txt("payment_system") . ": " . $this->lng->txt("pays_paypal") . ")";
				break;
		}
		$tbl->setTitle($title,"icon_pays.gif",$this->lng->txt("paya_shopping_cart"));
		$tbl->setHeaderNames(array($this->lng->txt(""),
								   $this->lng->txt("title"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a")));

		$tbl->setHeaderVars(array("",
								  "table".$a_pay_method."_title",
								  "table".$a_pay_method."_duration",
								  "table".$a_pay_method."_price"),
							array("cmd" => "",
								  "cmdClass" => "ilpaymentshoppingcartgui",
								  "baseClass" => "ilPersonalDesktopGUI",
								  "cmdNode" => $_GET["cmdNode"]));

		$offset = $_GET["table".$a_pay_method."_offset"];
		$order = $_GET["table".$a_pay_method."_sort_by"];
		$direction = $_GET["table".$a_pay_method."_sort_order"] ? $_GET['table'.$a_pay_method.'_sort_order'] : 'desc';

		$tbl->setPrefix("table".$a_pay_method."_");
		$tbl->setOrderColumn($order,'table'.$a_pay_method.'_title');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);

		// show total amount of costs
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		$totalAmount =  $sc_obj->getTotalAmount();
		$vat = $sc_obj->getVat($totalAmount[$a_pay_method]);

		$tpl->setCurrentBlock("tbl_footer_linkbar");
		$amount = "<b>" . $this->lng->txt("pay_bmf_total_amount") . ": " . number_format($totalAmount[$a_pay_method], 2, ',', '.') . " " . $genSet->get("currency_unit") . "</b>";
		if ($vat > 0)
		{
			$amount .= "<br>\n" . $genSet->get("vat_rate") . "% " . $this->lng->txt("pay_bmf_vat_included") . ": " . number_format($vat, 2, ',', '.') . " " . $genSet->get("currency_unit");
		}

		$tpl->setVariable("LINKBAR", $amount);
		$tpl->parseCurrentBlock("tbl_footer_linkbar");
		$tpl->setCurrentBlock('tbl_footer');
		$tpl->setVariable('COLUMN_COUNT',4);
		$tpl->parseCurrentBlock();

		$tbl->render();

		$a_tpl->setVariable("ITEMS_TABLE",$tbl->tpl->get());

		return true;
	}

	function deleteItem()
	{
		if(!count($_POST['item']))
		{
			sendInfo($this->lng->txt('pay_select_one_item'));

			$this->showItems();
			return true;
		}
		$this->__initShoppingCartObject();

		foreach($_POST['item'] as $id)
		{
			$this->psc_obj->delete($id);
		}
		sendInfo($this->lng->txt('pay_deleted_items'));
		$this->showItems();

		return true;
	}
		

	function __initShoppingCartObject()
	{
		$this->psc_obj =& new ilPaymentShoppingCart($this->user_obj);
	}

	function __initPaypalObject()
	{
		$this->paypal_obj =& new ilPurchasePaypal($this->user_obj);
	}

    /**
     * Creates a new encrypted button HTML block
     *
     * @param array The button parameters as key/value pairs
     * @return mixed A string of HTML or a Paypal error object on failure
     */
    function __encryptButton($buttonParams)
    {
        $merchant_cert = $this->paypalConfig["vendor_cert"];
        $merchant_key = $this->paypalConfig["vendor_key"];
        $end_cert = $this->paypalConfig["enc_cert"];

        $tmpin_file  = tempnam('/tmp', 'paypal_');
        $tmpout_file = tempnam('/tmp', 'paypal_');
        $tmpfinal_file = tempnam('/tmp', 'paypal_');

        $rawdata = array();
        $buttonParams['cert_id'] = $this->paypalConfig["cert_id"];
        foreach ($buttonParams as $name => $value) {
            $rawdata[] = "$name=$value";
        }
        $rawdata = implode("\n", $rawdata);

        $fp = fopen($tmpin_file, 'w');
        if (!$fp) {
            echo "Could not open temporary file '$tmpin_file')";
			return false;
#            return PayPal::raiseError("Could not open temporary file '$tmpin_file')");
        }
        fwrite($fp, $rawdata);
        fclose($fp);

        if (!@openssl_pkcs7_sign($tmpin_file, $tmpout_file, $merchant_cert,
                                 array($merchant_key, $this->paypalConfig["private_key_password"]),
                                 array(), PKCS7_BINARY)) {
			echo "Could not sign encrypted data: " . openssl_error_string();
			return false;
#            return PayPal::raiseError("Could not sign encrypted data: " . openssl_error_string());
        }

        $data = file_get_contents($tmpout_file);
        $data = explode("\n\n", $data);
        $data = $data[1];
        $data = base64_decode($data);
        $fp = fopen($tmpout_file, 'w');
        if (!$fp) {
            echo "Could not open temporary file '$tmpin_file')";
			return false;
#            return PayPal::raiseError("Could not open temporary file '$tmpin_file')");
        }
        fwrite($fp, $data);
        fclose($fp);

        if (!@openssl_pkcs7_encrypt($tmpout_file, $tmpfinal_file, $enc_cert, array(), PKCS7_BINARY)) {
            echo "Could not encrypt data:" . openssl_error_string();
			return false;
#            return PayPal::raiseError("Could not encrypt data:" . openssl_error_string());
        }

        $encdata = @file_get_contents($tmpfinal_file, false);
        if (!$encdata) {
            echo "Encryption and signature of data failed.";
			return false;
#            return PayPal::raiseError("Encryption and signature of data failed.");
        }

        $encdata = explode("\n\n", $encdata);
        $encdata = trim(str_replace("\n", '', $encdata[1]));
        $encdata = "-----BEGIN PKCS7-----$encdata-----END PKCS7-----";

        @unlink($tmpfinal_file);
        @unlink($tmpin_file);
        @unlink($tmpout_file);

		return $encData;
    }

}
?>