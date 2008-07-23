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
* Class ilPaymentAdminGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @ilCtrl_Calls ilPaymentAdminGUI: ilPaymentTrusteeGUI, ilPaymentStatisticGUI, ilPaymentObjectGUI
* @ilCtrl_Calls ilPaymentAdminGUI: ilPaymentBillAdminGUI, ilPaymentCouponGUI
*
* @package ServicesPayment
*/

include_once 'payment/classes/class.ilPaymentVendors.php';
include_once 'payment/classes/class.ilPaymentBaseGUI.php';
include_once 'payment/classes/class.ilPaymentTrustees.php';
include_once 'payment/classes/class.ilPaymentBillAdminGUI.php';

class ilPaymentAdminGUI
{
	public function ilPaymentAdminGUI($user_obj)
	{
		$this->user_obj = $user_obj;
	}	
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case 'ilpaymenttrusteegui':
				include_once 'payment/classes/class.ilPaymentTrusteeGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentTrusteeGUI($this->user_obj));			
				break;

			case 'ilpaymentobjectgui':
				include_once 'payment/classes/class.ilPaymentObjectGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentObjectGUI($this->user_obj));
				break;

			case 'ilpaymentstatisticgui':
				include_once 'payment/classes/class.ilPaymentStatisticGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentStatisticGUI($this->user_obj));
				break;

			case 'ilpaymentbilladmingui':
				include_once 'payment/classes/class.ilPaymentBillAdminGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentBillAdminGUI($this->user_obj, (int)$_GET['pobject_id']));
				break;
				
			case 'ilpaymentcoupongui':
				include_once 'payment/classes/class.ilPaymentCouponGUI.php';
				$ilCtrl->forwardCommand(new ilPaymentCouponGUI($this->user_obj));
				break;

			default:
				$this->forwardToDefault();
				break;
		}
	}

	private function forwardToDefault()
	{
		global $ilCtrl;
		
		if(ilPaymentVendors::_isVendor($this->user_obj->getId()) ||
		   ilPaymentTrustees::_hasStatisticPermission($this->user_obj->getId()))
		{
			$ilCtrl->redirectByClass('ilpaymentstatisticgui');
		}
		else if(ilPaymentTrustees::_hasObjectPermission($this->user_obj->getId()))
		{
			$ilCtrl->redirectByClass('ilpaymentobjectgui');
		}
		
		echo 'No access to payment administration!';

		return false;
	}
}
?>