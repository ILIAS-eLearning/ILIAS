<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilShopPurchaseGUI
*
* @author Michael Jansen <mjansen@databay.de>
* 
* @ilCtrl_Calls ilShopPurchaseGUI: ilPageObjectGUI
*
* @ingroup ServicesPayment
*/
class ilShopPurchaseGUI extends ilObjectGUI
{
	var $ctrl;
	var $ilias;
	var $lng;
	var $tpl;

	var $object = null;

	public function __construct($a_ref_id)
	{
		global $ilCtrl,$lng,$ilErr,$ilias,$tpl,$tree;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));

		$this->ilErr =& $ilErr;
		$this->ilias =& $ilias;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('payment');

		$this->tpl =& $tpl;

		$this->ref_id = $a_ref_id;

		$this->object =& ilObjectFactory::getInstanceByRefId($this->ref_id);
		
		global $ilTabs;
				
		$ilTabs->clearTargets();
		$ilTabs->addTarget('buy', $this->ctrl->getLinkTarget($this, 'showDetails').'&purchasetype=buy');
		$ilTabs->addTarget('payment_demo', $this->ctrl->getLinkTarget($this, 'showDemoVersion').'&purchasetype=demo');

		$this->ctrl->setParameter($this, 'purchasetype', ($_GET['purchasetype'] == 'demo' ? 'demo' : 'buy'));
	}

	/**
	* execute command
	*/
	public function executeCommand()
	{
		switch($this->ctrl->getCmdClass())
		{
			case 'ilpageobjectgui':
				$this->__initPaymentObject();
				include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
				include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';		
				$page_gui = new ilPageObjectGUI('shop', $this->pobject->getPobjectId());		
				$this->ctrl->forwardCommand($page_gui);				
				return;
				break;
			default:
				$cmd = $this->ctrl->getCmd();
				$this->__buildHeader();
				
				if(!in_array($cmd, array('showDemoVersion', 'showDetails', 'addToShoppingCart')))
				{
					$cmd = ($_GET['purchasetype'] == 'demo' ? 'showDemoVersion' : 'showDetails');
				}
								
				if(!$cmd || !method_exists($this, $cmd))
				{
					$cmd = 'showDetails';
				}
				$this->$cmd();
				break;
		}
	}
	
	public function showDemoVersion()
	{
		global $ilMainMenu, $ilTabs, $tpl;
		
		$this->__initPaymentObject();
		$this->__initPricesObject();
		$this->__initShoppingCartObject();
		
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', 'ilias.php?baseClass=ilShopController');
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('payment_back_to_shop'));
		$this->tpl->parseCurrentBlock();
		
		
		$ilTabs->setTabActive('payment_demo');
		$ilMainMenu->setActive('shop');
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_abstract_details.html', 'Services/Payment');
		
		$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$this->object->getType().'_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$this->object->getType()));
		$this->tpl->setVariable("TITLE",$this->object->getTitle());
		
		// abstracts
		if(($abstract_html = $this->__getAbstractHTML($this->pobject->getPobjectId())) != '')
		{			
			$this->tpl->setCurrentBlock('abstract_block');
			$this->tpl->setVariable('TXT_ABSTRACT', $this->lng->txt('pay_abstract'));
			$this->tpl->setVariable('ABSTRACT_HTML', $abstract_html.$output);
			$this->tpl->parseCurrentBlock();
		}	
		
		// public content ilias lm
		global $ilObjDataCache;
		if($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->pobject->getRefId())) == 'lm')
		{			
			include_once  'Modules/LearningModule/classes/class.ilShopPublicSectionSelector.php';
			$exp = new ilShopPublicSectionSelector($this->ctrl->getLinkTarget($this, 'layout'),
				ilObjectFactory::getInstanceByRefId($this->pobject->getRefId()), get_class($this));	
			$exp->setTargetGet('obj_id');
			$exp->setOutput(0);
			$output = $exp->getOutput();
			
			if(trim($output) != '')
			{	
					
				$this->tpl->setCurrentBlock('public_content_block');
				$this->tpl->setVariable('TXT_CONTENT', $this->lng->txt('content'));
				$this->tpl->setVariable('PUBLIC_CONTENT_HTML', $output);
				$this->tpl->parseCurrentBlock();
			}
		}
		else if($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->pobject->getRefId())) == 'crs')
		{
			$oCourse = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
			$items = $oCourse->getSubItems();
			$this->__getCourseItemsHTML($items);	
		}		
	}
	
	private function getItemsByObjType($items, $type_group)
	{
		return is_array($items[$type_group]) ? $items[$type_group] : array();
	}
	
	private function __getCourseItemsHTML($container_items)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilSetting, $ilObjDataCache;
		
		$output = false;
		
		$tpl_sub_items = new ilTemplate('tpl.pay_purchase_demo_list_block.html', true, true, 'Services/Payment');
								
		$objtype_groups = $objDefinition->getGroupedRepositoryObjectTypes(
			array('cat', 'crs', 'grp', 'fold')
		);

		foreach($objtype_groups as $grp => $grpdata)
		{
			$title = $this->lng->txt('objs_'.$grp);
			$items = $this->getItemsByObjType($container_items, $grp);
			
			$item_html = array();
			$rel_header = 'th_'.$grp;

			if(count($items) > 0)
			{				
				foreach($items as $item)
				{
					if($item['title'] != '')
					{
						$item_html[] = array(
							'html' => $item['title'], 
							'item_ref_id' => $item['ref_id'],
							'item_obj_id' => $item['obj_id']
						);
					}
				}
				
				// output block for resource type
				if(count($item_html) > 0)
				{
					$output = true;
					
					// add a header for each resource type
					if($ilSetting->get('icon_position_in_lists') == 'item_rows')
					{
						$this->addHeaderRow($tpl_sub_items, $grp, false);
					}
					else
					{
						$this->addHeaderRow($tpl_sub_items, $grp);
					}
					$this->resetRowType();
					
					// content row
					foreach($item_html as $item)
					{
						if($ilSetting->get('icon_position_in_lists') == 'item_rows')
						{
							// BEGIN WebDAV: Use $item_list_gui to determine icon image type
							$this->addStandardRow($tpl_sub_items, $item['html'], $item['item_ref_id'], $item['item_obj_id'], 
								$item['item_icon_image_type'], 
								$rel_header);
							// END WebDAV: Use $item_list_gui to determine icon image type
						}
						else
						{
							$this->addStandardRow($tpl_sub_items, $item['html'], $item['item_ref_id'], $item['item_obj_id'], '', $rel_header);
						}
					}
					
					
				}
			}
		}

		if($output == true)
		{
			$this->tpl->setCurrentBlock('public_content_block');
			$this->tpl->setVariable('TXT_CONTENT', $this->lng->txt('content'));
			$this->tpl->setVariable('PUBLIC_CONTENT_HTML', $tpl_sub_items->get());
			$this->tpl->parseCurrentBlock();
		}		
	}
	
	private function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	private function addHeaderRow($a_tpl, $a_type, $a_show_image = true)
	{
		$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
		$title = $this->lng->txt("objs_".$a_type);
		$header_id = "th_".$a_type;

		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->setVariable("BLOCK_HEADER_ID", $header_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}	
	
	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	private function addStandardRow(&$a_tpl, $a_html, $a_item_ref_id = "", $a_item_obj_id = "",
	$a_image_type = "", $a_related_header = "")
	{
		global $ilSetting;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
		? "row_type_2"
		: "row_type_1";
		$a_tpl->touchBlock($this->cur_row_type);
		
		if ($a_image_type != "")
		{
			if (!is_array($a_image_type) && !in_array($a_image_type, array("lm", "dbk", "htlm", "sahs")))
			{
				$icon = ilUtil::getImagePath("icon_".$a_image_type.".gif");
				$title = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_lm.gif");
				$title = $this->lng->txt("learning_resource");
			}
			
			// custom icon
			if ($ilSetting->get("custom_icons") &&
			in_array($a_image_type, array("cat","grp","crs")))
			{
				require_once("./Services/Container/classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_item_obj_id, "small")) != "")
				{
					$icon = $path;
				}
			}
			
			$a_tpl->setCurrentBlock("block_row_image");
			$a_tpl->setVariable("ROW_IMG", $icon);
			$a_tpl->setVariable("ROW_ALT", $title);
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$rel_headers = ($a_related_header != "")
		? "th_selected_items ".$a_related_header
		: "th_selected_items";
		$a_tpl->setVariable("BLOCK_ROW_HEADERS", $rel_headers);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	public function showDetails()
	{
		global $ilMainMenu, $ilTabs;
		
		$ilTabs->setTabActive('buy');
		
		$ilMainMenu->setActive('shop');
		
		$this->__initPaymentObject();
		$this->__initPricesObject();
		$this->__initShoppingCartObject();
		
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', 'ilias.php?baseClass=ilShopController');
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('payment_back_to_shop'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pay_purchase_details.html', 'Services/Payment');

		if($this->pobject->getStatus() == $this->pobject->STATUS_EXPIRES)
		{
			ilUtil::sendInfo($this->lng->txt('pay_expires_info'));

			return false;
		}

		$prices = $this->price_obj->getPrices();
		$buyedObject = "";
		if($this->sc_obj->isInShoppingCart($this->pobject->getPobjectId()))
		{
			$buyedObject = $this->sc_obj->getEntry($this->pobject->getPobjectId());
			if (is_array($prices) &&
				count($prices) > 1)
			{
				ilUtil::sendInfo($this->lng->txt('pay_item_already_in_sc_choose_another'));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('pay_item_already_in_sc'));
			}

			$this->tpl->setCurrentBlock("shopping_cart");
			
			
			$this->tpl->setVariable("LINK_GOTO_SHOPPING_CART", 'ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilShopShoppingCartGUI');
			$this->tpl->setVariable("TXT_GOTO_SHOPPING_CART", $this->lng->txt('pay_goto_shopping_cart'));
#			$this->tpl->setVariable("TXT_BUY", $this->lng->txt('pay_click_to_buy'));
			$this->tpl->parseCurrentBlock("shopping_cart");
		}

		$this->ctrl->setParameter($this, "ref_id", $this->pobject->getRefId());

#		if (!is_array($buyedObject) ||
#			(is_array($buyedObject) && is_array($prices) && count($prices) > 1))
#		{
			$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$this->object->getType().'_b.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$this->object->getType()));
			$this->tpl->setVariable("TITLE",$this->object->getTitle());

			// payment infos
			$this->tpl->setVariable("TXT_INFO",$this->lng->txt('info'));
			switch($this->pobject->getPayMethod())
			{
				case $this->pobject->PAY_METHOD_BILL:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_bill'));
					$this->tpl->setVariable("INPUT_CMD",'getBill');
					$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_get_bill'));
					break;

				case $this->pobject->PAY_METHOD_BMF:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_info'));
					if (is_array($buyedObject))
					{
						if (is_array($prices) && count($prices) > 1)
						{
							$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
							$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_change_price'));
						}
					}
					else
					{
						$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
						$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
					}
					break;

				case $this->pobject->PAY_METHOD_PAYPAL:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_info'));
					if (is_array($buyedObject))
					{
						if (is_array($prices) && count($prices) > 1)
						{
							$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
							$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_change_price'));
						}
					}
					else
					{
						$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
						$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
					}
					break;
			}

			$this->tpl->setVariable("ROWSPAN",count($prices));
			$this->tpl->setVariable("TXT_PRICES",$this->lng->txt('prices'));
#		}

		if (is_array($prices))
		{
#			if (count($prices) > 1)
#			{
				$counter = 0;
				foreach($prices as $price)
				{
					if ($counter == 0)
					{
						$placeholderCheckbox = "CHECKBOX";
						$placeholderDuration = "DURATION";
						$placeholderPrice = "PRICE";
					}
					else
					{
						$placeholderCheckbox = "ROW_CHECKBOX";
						$placeholderDuration = "ROW_DURATION";
						$placeholderPrice = "ROW_PRICE";
					}
					$this->tpl->setCurrentBlock("price_row");
					if ($buyedObject["price_id"] == $price['price_id'])
					{
						$this->tpl->setVariable($placeholderCheckbox,ilUtil::formRadioButton(1,'price_id',$price['price_id']));
					}
					else if (count($prices) == 1)
					{
						$this->tpl->setVariable($placeholderCheckbox,ilUtil::formRadioButton(1,'price_id',$price['price_id']));
					}
					else
					{
						$this->tpl->setVariable($placeholderCheckbox,ilUtil::formRadioButton(0,'price_id',$price['price_id']));
					}
					$this->tpl->setVariable($placeholderDuration,$price['duration'].' '.$this->lng->txt('paya_months'));
					$this->tpl->setVariable($placeholderPrice,ilPaymentPrices::_getPriceString($price['price_id']));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
#			}
#			else if (!is_array($buyedObject))
#			{
#				foreach($prices as $price)
#				{
#					$this->tpl->setVariable("CHECKBOX",ilUtil::formRadioButton(0,'price_id',$price['price_id']));
#					$this->tpl->setVariable("DURATION",$price['duration'].' '.$this->lng->txt('paya_months'));
#					$this->tpl->setVariable("PRICE",ilPaymentPrices::_getPriceString($price['price_id']));
#				}
#			}
		}		
	}	
	
	private function __getAbstractHTML($a_payment_object_id)
	{		
		// page object
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

		// if page does not exist, return nothing
		if(!ilPageObject::_exists('shop', $a_payment_object_id))
		{
			return '';
		}
		
		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		// get page object
		$page_gui = new ilPageObjectGUI('shop', $a_payment_object_id);
		$page_gui->setIntLinkHelpDefault('StructureObject', $a_payment_object_id);
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass('ilPageObjectGUI', 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass('ilPageObjectGUI', 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass('ilPageObjectGUI', 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(false);
		$page_gui->setEnabledPCTabs(true);
		$page_gui->setEnabledMaps(true);

		return $page_gui->showPage();
	}

	public function addToShoppingCart()
	{
		global $ilUser, $ilTabs;
		
		$ilTabs->setTabActive('buy');		
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			ilUtil::redirect('login.php?cmd=force_login&login_to_purchase_object=1');
			exit();
		}		
		if(!isset($_POST['price_id']))
		{
			ilUtil::sendInfo($this->lng->txt('pay_select_price'));
			$this->showDetails();

			return true;
		}
		else
		{			
			$this->__initPaymentObject();
			$this->__initShoppingCartObject();
			

			$this->sc_obj->setPriceId((int) $_POST['price_id']);
			$this->sc_obj->setPobjectId($this->pobject->getPobjectId());
			$this->sc_obj->add();

			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pay_purchase_details.html', 'Services/Payment');
			$this->tpl->setCurrentBlock('shopping_cart');
			$this->tpl->setVariable('LINK_GOTO_SHOPPING_CART', 'ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilShopShoppingCartGUI');
			$this->tpl->setVariable('TXT_GOTO_SHOPPING_CART', $this->lng->txt('pay_goto_shopping_cart'));
			$this->tpl->parseCurrentBlock('shopping_cart');

			ilUtil::sendInfo($this->lng->txt('pay_added_to_shopping_cart'));

			return true;
		}
	}

	// PRIVATE
	function __initShoppingCartObject()
	{
		include_once './payment/classes/class.ilPaymentShoppingCart.php';

		$this->sc_obj =& new ilPaymentShoppingCart($this->ilias->account);

		return true;
	}

	function __initPaymentObject()
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->ilias->account,ilPaymentObject::_lookupPobjectId($this->ref_id));

		return true;
	}
	function __initPricesObject()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$this->price_obj =& new ilPaymentPrices($this->pobject->getPobjectId());

		return true;
	}

	function __buildHeader()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.payb_content.html");
		
		$this->tpl->setVariable("HEADER",$this->object->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$this->object->getDescription());

#		$this->__buildStylesheet();
#		$this->__buildStatusline();
	}

	function  __buildStatusline()
	{
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->__buildLocator();
	}	

	function __buildLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
		$this->tpl->setVariable("LINK_ITEM", "../repository.php?getlast=true");
		$this->tpl->parseCurrentBlock();

		// CHECK for new mail and info
		ilUtil::sendInfo();

		return true;
	}

	function __buildStylesheet()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilUtil::getStyleSheetLocation());
	}
}
?>