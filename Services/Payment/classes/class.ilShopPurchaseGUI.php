<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilShopPurchaseGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id: $
* @ilCtrl_Calls ilShopPurchaseGUI: ilShopPageGUI
*
* @ingroup ServicesPayment
*/
class ilShopPurchaseGUI extends ilObjectGUI
{
	public $ctrl;
	public $lng;
	public $tpl;

	public $object = null;
	public $pobject = null;
	public $cur_row_type = null;
	public $price_obj = null;
	public $sc_obj = null;

	public function __construct($a_ref_id)
	{
		global $ilCtrl,$lng,$ilErr,$tpl,$ilTabs;

		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));

		$this->ilErr = $ilErr;

		$this->lng = $lng;
		$this->lng->loadLanguageModule('payment');

		$this->tpl = $tpl;

		$this->ref_id = $a_ref_id;

		$this->object = ilObjectFactory::getInstanceByRefId($this->ref_id, false);

		$this->tpl->getStandardTemplate();
		
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
		$cmd = $this->ctrl->getCmd();
		switch($this->ctrl->getCmdClass())
		{
			case 'ilshoppagegui':
				$this->__initPaymentObject();
				include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
				include_once 'Services/Payment/classes/class.ilShopPageGUI.php';		
				$page_gui = new ilShopPageGUI($this->pobject->getPobjectId());		
				$this->ctrl->forwardCommand($page_gui);				
				return true;
				break;
		}


		switch($cmd)
		{
			case 'addToShoppingCart':
				break;

			default:
				if(!in_array($cmd, array('showDemoVersion', 'showDetails', 'addToShoppingCart')))
				{
					$cmd = ($_GET['purchasetype'] == 'demo' ? 'showDemoVersion' : 'showDetails');
				}
				break;
		}
		$this->$cmd();
		return true;
	}
	
	public function showDemoVersion()
	{
		global $ilMainMenu, $ilTabs, $ilToolbar;
		
		$this->__initPaymentObject();
		$this->__initPricesObject();
		$this->__initShoppingCartObject();
		
		$ilToolbar->addButton($this->lng->txt('payment_back_to_shop'),'ilias.php?baseClass=ilShopController');

		$ilTabs->setTabActive('payment_demo');
		$ilMainMenu->setActive('shop');
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_abstract_details.html', 'Services/Payment');
		
		$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));

		if($this->object)
		{
			$icon = ilObject::_getIcon($this->object->getId());
			$this->tpl->setVariable("TYPE_IMG", $icon);
			$this->tpl->setVariable("ALT_IMG", $this->lng->txt('obj_'.$this->object->getType()));
			$this->tpl->setVariable("TITLE", $this->object->getTitle());
		}
		else
		{
				$this->tpl->setVariable("TITLE",$this->lng->txt('object_not_found'));
		}


		
		// abstracts
		if(($abstract_html = $this->__getAbstractHTML($this->pobject->getPobjectId())) != '')
		{			
			$this->tpl->setCurrentBlock('abstract_block');
			$this->tpl->setVariable('TXT_ABSTRACT', $this->lng->txt('pay_abstract'));
			$this->tpl->setVariable('ABSTRACT_HTML', $abstract_html);
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
		global $objDefinition, $ilSetting;
		
		$output = false;
		
		$tpl_sub_items = new ilTemplate('tpl.pay_purchase_demo_list_block.html', true, true, 'Services/Payment');
						
		$objtype_groups = $objDefinition->getGroupedRepositoryObjectTypes(
			array('cat', 'crs', 'grp', 'fold')
		);

		foreach($objtype_groups as $grp => $grpdata)
		{
//			$title = $this->lng->txt('objs_'.$grp);
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
							$this->addStandardRow($tpl_sub_items, $item['html'], $item['item_obj_id'], 
								$item['item_icon_image_type'], 
								$rel_header);
							// END WebDAV: Use $item_list_gui to determine icon image type
						}
						else
						{
							$this->addStandardRow($tpl_sub_items, $item['html'], $item['item_obj_id'], '', $rel_header);
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
		$icon = ilObject::_getIcon($this->object->getId());
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
	private function addStandardRow(&$a_tpl, $a_html, $a_item_obj_id = "",
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
				$icon = ilObject::_getIcon($this->object->getId());
				$title = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilObject::_getIcon($this->object->getId());
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
		global $ilMainMenu, $ilTabs, $ilToolbar, $ilUser;
		
		$this->__initPaymentObject();
		$this->__initPricesObject();
		$this->__initShoppingCartObject();
		
		$ilToolbar->addButton($this->lng->txt('payment_back_to_shop'),'ilias.php?baseClass=ilShopController');

		$this->tpl->getStandardTemplate();
		$ilTabs->setTabActive('buy');
		$ilMainMenu->setActive('shop');

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pay_purchase_details.html', 'Services/Payment');

		if($this->pobject->getStatus() == $this->pobject->STATUS_EXPIRES)
		{
			ilUtil::sendInfo($this->lng->txt('pay_expires_info'));

			return false;
		}

		$extension_prices = array();

		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once './Services/Payment/classes/class.ilPaymentBookings.php';
			$has_extension_price = ilPaymentBookings::_hasAccesstoExtensionPrice(
						$ilUser->getId(), $this->pobject->getPobjectId());


			if($has_extension_price)
			{
				$extension_prices = $this->price_obj->getExtensionPrices();
			}
		}

		$org_prices = $this->price_obj->getPrices();
		$tmp_prices = array_merge($org_prices, $extension_prices );

		$prices = array();
		foreach($tmp_prices as $price)
		{
			// expired prices must be filtered out
			if($price['price_type'] == ilPaymentPrices::TYPE_DURATION_DATE && $price['duration_until'] < date('Y-m-d'))
			{
				//do nothing 
			}
			else
			{
				$prices[] = $price;
			}
		}
		
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

			$this->tpl->setCurrentBlock("shopping_cart_1");
			
			$this->tpl->setVariable("LINK_GOTO_SHOPPING_CART",'ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilShopShoppingCartGUI');
			$this->tpl->setVariable("TXT_GOTO_SHOPPING_CART", $this->lng->txt('pay_goto_shopping_cart'));
			$this->tpl->parseCurrentBlock("shopping_cart_1");

		}

		$this->ctrl->setParameter($this, "ref_id", $this->pobject->getRefId());
		$subtype = '';
		if($this->object)
		{
			if($this->object->getType() == 'exc')
			{
				$subtype = ' ('.$this->lng->txt($this->pobject->getSubtype()).')';
			}

			$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TYPE_IMG", ilObject::_getIcon($this->object->getId()));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$this->object->getType()));
			$this->tpl->setVariable("TITLE",$this->object->getTitle().' '.$subtype);
		}
		else
		{
			$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TITLE",$this->lng->txt('object_not_found'));
		}
		// payment infos
		$this->tpl->setVariable("TXT_INFO",$this->lng->txt('info'));

		$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_info'));
		if (is_array($buyedObject))
		{
			if (is_array($prices) && count($prices) > 1)
			{
				$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
				$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_change_price'));
			}
			else
			{
				$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
				$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
			}
		}
		else
		{
			$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
			$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
		}

		$this->tpl->setVariable("ROWSPAN",count($prices));
		$this->tpl->setVariable("TXT_PRICES",$this->lng->txt('prices'));

		if (is_array($prices))
		{
			$counter = 0;
			foreach($prices as $price)
			{
				if ($counter == 0)
				{
					$placeholderCheckbox = "CHECKBOX";
					$placeholderDuration = "DURATION";
					$placeholderPrice = "PRICE";
					$placeholderDescription = "DESCRIPTION";
				}
				else
				{
					$placeholderCheckbox = "ROW_CHECKBOX";
					$placeholderDuration = "ROW_DURATION";
					$placeholderPrice = "ROW_PRICE";
					$placeholderDescription = "ROW_DESCRIPTION";
				}
				$this->tpl->setCurrentBlock("price_row");

				if (is_array($buyedObject) && $buyedObject["price_id"] == $price['price_id'])
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
				
				switch($price['price_type'])
                {
					case ilPaymentPrices::TYPE_DURATION_MONTH:
						$this->tpl->setVariable($placeholderDuration,$price['duration'].' '.$this->lng->txt('paya_months').': ');
						break;
					case ilPaymentPrices::TYPE_DURATION_DATE:
						ilDatePresentation::setUseRelativeDates(false);
						$this->tpl->setVariable($placeholderDuration,
						ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
						.' - '.ilDatePresentation::formatDate(new ilDate($price['duration_until'], IL_CAL_DATE)).' -> ');
						break;
					case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
						$this->tpl->setVariable($placeholderDuration, $this->lng->txt('unlimited_duration').': ');
						break;
                }

				$tmp_price = $price['price'];

				if($price['extension'] == 1)
					$extension_txt = '('.$this->lng->txt('extension_price').')';
				else $extension_txt = '';

				$this->tpl->setVariable($placeholderPrice, ilPaymentPrices::_formatPriceToString((float)$tmp_price).' '.$extension_txt );
				if($price['description'] != NULL)
				{
					$this->tpl->setVariable($placeholderDescription, $price['description']);
				}
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}		
		return true;
	}	
	
	private function __getAbstractHTML($a_payment_object_id)
	{		
		// page object
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		// if page does not exist, return nothing
		if(!ilShopPage::_exists('shop', $a_payment_object_id))
		{
			return '';
		}
		
		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		// get page object
		$page_gui = new ilShopPageGUI($a_payment_object_id);

		return $page_gui->showPage();
	}

	public function addToShoppingCart()
	{
		global $ilTabs;
		
		$ilTabs->setTabActive('buy');		
		
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
			
			$this->sc_obj->setSessionId(session_id());
			$this->sc_obj->setPriceId((int) $_POST['price_id']);
			$this->sc_obj->setPobjectId($this->pobject->getPobjectId());
			$this->sc_obj->add();
			
			ilUtil::redirect('ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilshopshoppingcartgui');

			return true;
		}
	}

	// PRIVATE
	private function __initShoppingCartObject()
	{	
		global $ilUser;
		include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
		$this->sc_obj = new ilPaymentShoppingCart($ilUser);
		return true;
	}

	private function __initPaymentObject()
	{
		global $ilUser;
		$this->pobject = new ilPaymentObject($ilUser ,ilPaymentObject::_lookupPobjectId($this->ref_id));
		return true;
	}
	private function __initPricesObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';
		$this->price_obj = new ilPaymentPrices($this->pobject->getPobjectId());
		return true;
	}

}
?>