<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Payment/classes/class.ilShopBaseGUI.php'; 
include_once './Services/Payment/classes/class.ilShopGUI.php'; 
include_once './Services/Payment/classes/class.ilShopAdvancedSearchGUI.php'; 

include_once './Services/Payment/classes/class.ilShopSearchResult.php';
include_once './Services/Payment/classes/class.ilShopInfoGUI.php';
include_once './Services/Payment/classes/class.ilShopNewsGUI.php';
include_once './Services/Payment/classes/class.ilTermsConditionsGUI.php';

include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
include_once './Services/Payment/classes/class.ilPaymentObject.php';
include_once './Services/Payment/classes/class.ilPaymentSettings.php';
include_once './Services/Payment/classes/class.ilPaymentVendors.php';
include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
include_once './Services/Payment/classes/class.ilPaymentBookings.php';
include_once './Services/Payment/classes/class.ilShopTopics.php';

include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
include_once './Services/Payment/classes/class.ilShopLinkBuilder.php';


/**
* Class ilShopController
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:$
*
* @defgroup ServicesPayment Services/Payment
* @ingroup ServicesPayment
*
* @ilCtrl_Calls ilShopController: ilShopGUI, ilShopAdvancedSearchGUI, ilShopShoppingCartGUI
* @ilCtrl_Calls ilShopController: ilShopBoughtObjectsGUI, ilPurchaseBMFGUI, ilShopPersonalSettingsGUI
* @ilCtrl_Calls ilShopController: ilPaymentGUI, ilPaymentAdminGUI, ilShopInfoGUI
* @ilCtrl_Calls ilShopController: ilPurchaseBillGUI, ilShopNewsGUI, ilTermsConditionsGUI, ilShopPurchaseGUI
* @ilCtrl_Calls ilShopController: ilShopLinkBuilder, ilPaymentObjectSelector
*/
class ilShopController
{	
	protected $ctrl = null;
	protected $ilErr = null;
	protected $lng = null;
	protected $tpl = null;
	
	public function __construct()
	{
		global $ilCtrl, $ilErr, $lng, $tpl;

		$this->ilErr = $ilErr;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}
	
	public function executeCommand()
	{		
		global $ilUser;

		if(!IS_PAYMENT_ENABLED)
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		}
		
		$this->buildTabs();
		
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();	
		$cmd_class = $this->ctrl->getCmdClass();
		
		if(!$next_class && $cmd_class)
		{
			$next_class = $cmd_class;
		}

		$obj = ilPaymentSettings::_getInstance();
		$allSet = $obj->getAll();

		if(($ilUser->getId() == ANONYMOUS_USER_ID) && $next_class == 'ilshopboughtobjectsgui')
		{
			$next_class = 'ilshopshoppingcartgui';
		}
		
		switch($next_class)
		{
			case 'ilpurchasebillgui':
				include_once './Services/Payment/classes/class.ilPurchaseBillGUI.php';
				$pt = new ilPurchaseBillGUI($ilUser);				
				$this->ctrl->forwardCommand($pt);
				break;
								
			case 'ilpurchasebmfgui':
				include_once './Services/Payment/classes/class.ilPurchaseBMFGUI.php';
				$pt = new ilPurchaseBMFGUI($ilUser);				
				$this->ctrl->forwardCommand($pt);
				break;
				
			case 'ilshopboughtobjectsgui':
				include_once './Services/Payment/classes/class.ilShopBoughtObjectsGUI.php';
				$this->ctrl->forwardCommand(new ilShopBoughtObjectsGUI($ilUser));
				break;
				
			case 'ilshopshoppingcartgui':
				include_once './Services/Payment/classes/class.ilShopShoppingCartGUI.php';
				$this->ctrl->forwardCommand(new ilShopShoppingCartGUI($ilUser));
				break;
				
			case 'ilshopadvancedsearchgui':
		        if ((bool) $allSet['hide_advanced_search']) 
		        {
		          $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		        }
				include_once './Services/Payment/classes/class.ilShopAdvancedSearchGUI.php';
				$this->ctrl->forwardCommand(new ilShopAdvancedSearchGUI());				
				break;
				
			case 'ilshoppersonalsettingsgui':
				include_once './Services/Payment/classes/class.ilShopPersonalSettingsGUI.php';
				$this->ctrl->forwardCommand(new ilShopPersonalSettingsGUI());
				break;
			
			case 'ilpaymentadmingui':
				include_once './Services/Payment/classes/class.ilPaymentAdminGUI.php';
				$this->ctrl->forwardCommand(new ilPaymentAdminGUI($ilUser));
				break;

			case 'ilshopinfogui':
				if ((bool) $allSet['hide_shop_info'])
		        {
		          $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		        }
				include_once './Services/Payment/classes/class.ilShopInfoGUI.php';
				$this->ctrl->forwardCommand(new ilShopInfoGUI());
				break;
				
			case 'ilshopnewsgui':
		        if ((bool) $allSet['hide_news']) 
		        {
		          $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		        }
				include_once './Services/Payment/classes/class.ilShopNewsGUI.php';
				$this->ctrl->forwardCommand(new ilShopNewsGUI());
				break;	
				
				case 'iltermsconditionsgui':

				include_once './Services/Payment/classes/class.ilTermsConditionsGUI.php';
				$this->ctrl->forwardCommand(new ilTermsConditionsGUI());
				break;

				case 'ilshoppurchasegui':
				include_once './Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$this->ctrl->forwardCommand(new ilShopPurchaseGUI($_GET['ref_id']));
				break;

			case 'ilshopgui':				
			default:
				if($cmd == 'redirect')
				{
					$this->redirect();
				}
			
				include_once './Services/Payment/classes/class.ilShopGUI.php';
				$this->ctrl->forwardCommand(new ilShopGUI());
				break;
		}		
		
		$this->tpl->show();		
		
		return true;
	}
	
	private function buildTabs()
	{
		global $ilTabs, $ilUser;

//		$shop_obj = new ilPaymentShoppingCart($ilUser);
		
		$obj = ilPaymentSettings::_getInstance();
		$allSet = $obj->getAll();
				
		$ilTabs->addTarget('content', $this->ctrl->getLinkTargetByClass('ilshopgui','firstpage'), '', '', '');

		if (!(bool)$allSet['hide_advanced_search']) { 
		  $ilTabs->addTarget('advanced_search', $this->ctrl->getLinkTargetByClass('ilshopadvancedsearchgui'), '', '', '');
		 }
		if (!(bool)$allSet['hide_shop_info'])
		{
		  $ilTabs->addTarget('shop_info',$this->ctrl->getLinkTargetByClass('ilshopinfogui') ,'' , '', '');
		}
		if (!(bool)$allSet['hide_news'])
		{
		  $ilTabs->addTarget('payment_news',$this->ctrl->getLinkTargetByClass('ilshopnewsgui'),'' , '', '');
		}
		if(ANONYMOUS_USER_ID != $ilUser->getId())
		{
			if((bool)$allSet['topics_allow_custom_sorting'] && (bool)$allSet['enable_topics'])
			{
				$ilTabs->addTarget('pay_personal_settings', $this->ctrl->getLinkTargetByClass('ilshoppersonalsettingsgui'), '', '', '');
			}

			// Only show if not empty
			$ilTabs->addTarget('paya_buyed_objects', $this->ctrl->getLinkTargetByClass('ilshopboughtobjectsgui'), '', '', '');
			
			// Only show if user is vendor
			if(ilPaymentVendors::_isVendor($ilUser->getId()) ||
			   ilPaymentTrustees::_hasAccess($ilUser->getId()))
			{
				$ilTabs->addTarget('paya_header', $this->ctrl->getLinkTargetByClass('ilpaymentadmingui'), '', '', '');
			}
		}
		
		// Only show cart if not empty
		$ilTabs->addTarget('paya_shopping_cart', $this->ctrl->getLinkTargetByClass('ilshopshoppingcartgui'), '', '', '');
		
		$ilTabs->addTarget('terms_conditions',$this->ctrl->getLinkTargetByClass(array('iltermsconditionsgui'), ''));
	}
	
	public function redirect()
	{
		global $ilCtrl;
		
		switch(strtolower(ilUtil::stripSlashes($_GET['redirect_class'])))
		{
			case 'ilshopshoppingcartgui':			
				$ilCtrl->redirectByClass('ilshopshoppingcartgui','','',false, false);
				break;
			case 'ilshopadvancedsearchgui':
				$ilCtrl->redirectByClass('ilshopadvancedsearchgui','','',false, false);
				break;
			case 'ilshopboughtobjectsgui':
				$ilCtrl->redirectByClass('ilshopboughtobjectsgui','','',false, false);
				break;
			
			default:
				break;
		}
	}
}
?>
