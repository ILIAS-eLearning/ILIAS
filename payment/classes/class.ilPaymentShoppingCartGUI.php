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

include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentBaseGUI.php';

class ilPaymentShoppingCartGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $lng;
	var $user_obj;

	/*
	 * shopping cart obj
	 */
	var $psc_obj = null;

	function ilPaymentShoppingCartGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ilPaymentBaseGUI();

		$this->user_obj =& $user_obj;

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

	function showItems()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_shopping_cart.html',true);

		$this->__initShoppingCartObject();
		if(!count($items = $this->psc_obj->getEntries()))
		{
			sendInfo($this->lng->txt('pay_shopping_cart_empty'));

			return false;
		}
		else
		{
			$this->tpl->setCurrentBlock("buy_link");
			$this->tpl->setVariable("LINK_SCRIPT", './start_bmf.php');
			$this->tpl->setVariable("TXT_BUY", $this->lng->txt('pay_click_to_buy'));
			$this->tpl->parseCurrentBlock("buy_link");
		}

		

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			$price_arr = ilPaymentPrices::_getPrice($item['price_id']);

			$f_result[$counter][] = ilUtil::formCheckBox(0,'item[]',$item['psc_id']);
			$numPrices = ilPaymentPrices::_countPrices($item['pobject_id']);
			if ($numPrices > 1)
			{
				if ($tmp_obj->getType() == "crs")
				{
					$f_result[$counter][] = "<a href=\"" . ILIAS_HTTP_PATH . "/repository.php?ref_id=" . $tmp_pobject->getRefId() . "&cmdClass=ilobjcoursegui&cmdNode=4\">" . $tmp_obj->getTitle() . "</a>";
				}
				else if ($tmp_obj->getType() == "lm")
				{
					$f_result[$counter][] = "<a href=\"" . ILIAS_HTTP_PATH . "/content/lm_presentation.php?ref_id=" . $tmp_pobject->getRefId() . "\">" . $tmp_obj->getTitle() . "</a>";
				}
				else
				{
					$f_result[$counter][] = $tmp_obj->getTitle();
				}
			}
			else
			{
				$f_result[$counter][] = $tmp_obj->getTitle();
			}
			$f_result[$counter][] = $price_arr['duration'].' '.$this->lng->txt('paya_months');

			$f_result[$counter][] = ilPaymentPrices::_getPriceString($item['price_id']);

			unset($tmp_obj);
			unset($tmp_pobject);
			
			++$counter;
		}
			
		return $this->__showItemsTable($f_result);
	}
	
	function __showItemsTable($a_result_set)
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

		$tbl->setTitle($this->lng->txt("paya_shopping_cart"),"icon_pays_b.gif",$this->lng->txt("paya_shopping_cart"));
		$tbl->setHeaderNames(array($this->lng->txt(""),
								   $this->lng->txt("title"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a")));

		$tbl->setHeaderVars(array("",
								  "title",
								  "duration",
								  "price"),
							array("cmd" => "",
								  "cmdClass" => "ilpaymentshoppingcartgui",
								  "cmdNode" => $_GET["cmdNode"]));

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

		// show total amount of costs
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		$totalAmount =  $sc_obj->getTotalAmount();
		$vat = $sc_obj->getVat($totalAmount);

		$tpl->setCurrentBlock("tbl_footer_linkbar");
		$amount = "<b>" . $this->lng->txt("pay_bmf_total_amount") . ": " . number_format($totalAmount, 2, ',', '.') . " " . $genSet->get("currency_unit") . "</b>";
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

		$this->tpl->setVariable("ITEMS_TABLE",$tbl->tpl->get());

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
}
?>