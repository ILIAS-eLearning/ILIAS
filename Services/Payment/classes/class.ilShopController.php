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

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'Services/Payment/classes/class.ilShopGUI.php';
include_once 'Services/Payment/classes/class.ilShopAdvancedSearchGUI.php';
include_once 'Services/Payment/classes/class.ilShopTopics.php';
include_once 'Services/Payment/classes/class.ilShopSearchResult.php';
include_once 'payment/classes/class.ilPaymentObject.php';
include_once 'payment/classes/class.ilGeneralSettings.php';
include_once 'payment/classes/class.ilPaymentVendors.php';
include_once 'payment/classes/class.ilPaymentTrustees.php';
include_once 'payment/classes/class.ilPaymentShoppingCart.php';
include_once 'payment/classes/class.ilPaymentBookings.php';
include_once 'Services/Payment/classes/class.ilShopInfoGUI.php';
include_once 'Services/Payment/classes/class.ilShopNewsGUI.php';

/**
* Class ilShopController
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @defgroup ServicesPayment Services/Payment
* @ingroup ServicesPayment
*
* @ilCtrl_Calls ilShopController: ilShopGUI, ilShopAdvancedSearchGUI, ilShopShoppingCartGUI
* @ilCtrl_Calls ilShopController: ilShopBoughtObjectsGUI, ilPurchaseBMFGUI, ilShopPersonalSettingsGUI
* @ilCtrl_Calls ilShopController: ilPaymentGUI, ilPaymentAdminGUI, ilShopInfoGUI
* @ilCtrl_Calls ilShopController: ilPurchaseBillGUI, ilShopNewsGUI 
*/
class ilShopController
{	
	protected $ctrl = null;
	protected $ilias = null;
	protected $lng = null;
	protected $tpl = null;
	
	public function __construct()
	{
		global $ilCtrl, $ilias, $lng, $tpl;

		$this->ilias = $ilias;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}
	
	public function executeCommand()
	{		
		global $ilUser;
		
		if(!(bool)ilGeneralSettings::_getInstance()->get('shop_enabled'))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->buildTabs();
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();	
			
		switch($next_class)
		{
			case 'ilpurchasebillgui':
				include_once 'payment/classes/class.ilPurchaseBillGUI.php';
				$pt = new ilPurchaseBillGUI($ilUser);				
				$this->ctrl->forwardCommand($pt);
				break;
								
			case 'ilpurchasebmfgui':
				include_once 'payment/classes/class.ilPurchaseBMFGUI.php';
				$pt = new ilPurchaseBMFGUI($ilUser);				
				$this->ctrl->forwardCommand($pt);
				break;
				
			case 'ilshopboughtobjectsgui':
				include_once 'Services/Payment/classes/class.ilShopBoughtObjectsGUI.php';
				$this->ctrl->forwardCommand(new ilShopBoughtObjectsGUI($ilUser));
				break;
				
			case 'ilshopshoppingcartgui':
				include_once 'Services/Payment/classes/class.ilShopShoppingCartGUI.php';
				$this->ctrl->forwardCommand(new ilShopShoppingCartGUI($ilUser));
				break;
				
			case 'ilshopadvancedsearchgui':
				include_once 'Services/Payment/classes/class.ilShopAdvancedSearchGUI.php';
				$this->ctrl->forwardCommand(new ilShopAdvancedSearchGUI());
				break;
				
			case 'ilshoppersonalsettingsgui':
				include_once 'Services/Payment/classes/class.ilShopPersonalSettingsGUI.php';
				$this->ctrl->forwardCommand(new ilShopPersonalSettingsGUI());
				break;
			
			case 'ilpaymentadmingui':
				include_once 'payment/classes/class.ilPaymentAdminGUI.php';
				$this->ctrl->forwardCommand(new ilPaymentAdminGUI($ilUser));
				break;

			case 'ilshopinfogui':
				include_once 'Services/Payment/classes/class.ilShopInfoGUI.php';
				$this->ctrl->forwardCommand(new ilShopInfoGUI());
				break;
				
			case 'ilshopnewsgui':
				include_once 'Services/Payment/classes/class.ilShopNewsGUI.php';
				$this->ctrl->forwardCommand(new ilShopNewsGUI());
				break;	
				
			case 'ilshopgui':				
			default:
				if($cmd == 'redirect')
				{
					$this->redirect();
				}
			
				include_once 'Services/Payment/classes/class.ilShopGUI.php';
				$this->ctrl->forwardCommand(new ilShopGUI());
				break;
		}		
		
		
		$this->tpl->show();		
		
		return true;
	}
	
	private function buildTabs()
	{
		global $ilTabs, $ilUser;
				
		$ilTabs->addTarget('content', $this->ctrl->getLinkTargetByClass('ilshopgui'), '', '', '');
		$ilTabs->addTarget('advanced_search', $this->ctrl->getLinkTargetByClass('ilshopadvancedsearchgui'), '', '', '');
		$ilTabs->addTarget('shop_info',$this->ctrl->getLinkTargetByClass('ilshopinfogui') ,'' , '', '');  
		$ilTabs->addTarget('payment_news',$this->ctrl->getLinkTargetByClass('ilshopnewsgui'),'' , '', '');
		if(ANONYMOUS_USER_ID != $ilUser->getId())
		{
			if((bool)ilGeneralSettings::_getInstance()->get('topics_allow_custom_sorting'))
			{
				$ilTabs->addTarget('pay_personal_settings', $this->ctrl->getLinkTargetByClass('ilshoppersonalsettingsgui'), '', '', '');
			}
			$ilTabs->addTarget('paya_shopping_cart', $this->ctrl->getLinkTargetByClass('ilshopshoppingcartgui'), '', '', '');
			$ilTabs->addTarget('paya_buyed_objects', $this->ctrl->getLinkTargetByClass('ilshopboughtobjectsgui'), '', '', '');
					
			if(ilPaymentVendors::_isVendor($ilUser->getId()) ||
			   ilPaymentTrustees::_hasAccess($ilUser->getId()))
			{
				$ilTabs->addTarget('paya_header', $this->ctrl->getLinkTargetByClass('ilpaymentadmingui'), '', '', '');
			}
		}
	}
	
	public function redirect()
	{
		global $ilUser;
		
		switch(strtolower(ilUtil::stripSlashes($_GET['redirect_class'])))
		{
			case 'ilshopshoppingcartgui':			
				ilUtil::redirect($this->ctrl->getLinkTargetByClass('ilshopshoppingcartgui'));
				break;
			
			default:
				break;
		}
	}
}
?>
