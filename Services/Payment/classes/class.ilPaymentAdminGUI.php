<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilPaymentAdminGUI
 * @author       Stefan Meyer
 * @version      $Id: class.ilPaymentAdminGUI.php 17010 2008-07-23 08:37:46Z mjansen $
 * @ilCtrl_Calls ilPaymentAdminGUI: ilPaymentTrusteeGUI, ilPaymentStatisticGUI, ilPaymentObjectGUI, ilPaymentCouponGUI, ilPaymentObjectSelector
 * @package      ServicesPayment
 */

include_once './Services/Payment/classes/class.ilPaymentVendors.php';
include_once './Services/Payment/classes/class.ilShopBaseGUI.php';
include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
include_once './Services/Payment/classes/class.ilPaymentObjectSelector.php';
include_once './Services/Payment/classes/class.ilPaymentSettings.php';

class ilPaymentAdminGUI
{
	public function ilPaymentAdminGUI($user_obj)
	{
		$this->user_obj = $user_obj;
	}

	protected function buildSubTabs()
	{
		global $ilUser, $ilTabs, $ilCtrl;

		$settings = ilPaymentSettings::_getInstance();

		if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
			ilPaymentTrustees::_hasStatisticPermission($ilUser->getId()))
		{
			$ilTabs->addSubTabTarget('bookings', $ilCtrl->getLinkTargetByClass('ilpaymentstatisticgui'), '', '', '');
		}
		
		if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
			ilPaymentTrustees::_hasObjectPermission($ilUser->getId()))
		{
			$ilTabs->addSubTabTarget('paya_object', $ilCtrl->getLinkTargetByClass('ilpaymentobjectgui'), '', '', '');

		}
		
		if(ilPaymentVendors::_isVendor($ilUser->getId()))
		{
			$ilTabs->addSubTabTarget('paya_trustees', $ilCtrl->getLinkTargetByClass('ilpaymenttrusteegui'), '', '', '');
		}
		
		if(!(bool)$settings->get('hide_coupons'))
		{
			if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
				ilPaymentTrustees::_hasCouponsPermission($ilUser->getId()))
			{
				$ilTabs->addSubTabTarget('paya_coupons_coupons', $ilCtrl->getLinkTargetByClass('ilpaymentcoupongui'), '', '', '');
			}
		}
	}

	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		$this->buildSubTabs();

		switch($next_class)
		{
			case 'ilpaymenttrusteegui':
				include_once './Services/Payment/classes/class.ilPaymentTrusteeGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentTrusteeGUI($this->user_obj));
				break;

			case 'ilpaymentobjectgui':
				include_once './Services/Payment/classes/class.ilPaymentObjectGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentObjectGUI($this->user_obj));
				break;

			case 'ilpaymentstatisticgui':
				include_once './Services/Payment/classes/class.ilPaymentStatisticGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentStatisticGUI($this->user_obj));
				break;

			case 'ilpaymentcoupongui':
				include_once './Services/Payment/classes/class.ilPaymentCouponGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentCouponGUI($this->user_obj));
				break;

			default:
				$this->forwardToDefault();
				break;
		}
	}

	private function forwardToDefault()
	{
		global $ilCtrl, $lng;

		$is_vendor     = ilPaymentVendors::_isVendor($this->user_obj->getId());
		$has_stat_perm = ilPaymentTrustees::_hasStatisticPermission($this->user_obj->getId());
		$has_obj_perm  = ilPaymentTrustees::_hasObjectPermission($this->user_obj->getId());
		$has_coup_perm = ilPaymentTrustees::_hasCouponsPermission($this->user_obj->getId());

		if($is_vendor || $has_stat_perm)
		{
			$ilCtrl->redirectByClass('ilpaymentstatisticgui');
		}
		else if($has_obj_perm)
		{
			$ilCtrl->redirectByClass('ilpaymentobjectgui');
		}
		else if($has_coup_perm)
		{
			$ilCtrl->redirectByClass('ilpaymentcoupongui');
		}

		ilUtil::sendInfo($lng->txt("no_permission"));

		return false;
	}
}