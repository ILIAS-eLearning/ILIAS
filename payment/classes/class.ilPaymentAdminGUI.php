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
* @package core
*/
include_once "./payment/classes/class.ilPaymentVendors.php";
include_once "./payment/classes/class.ilPaymentBaseGUI.php";
include_once "./payment/classes/class.ilPaymentTrustees.php";
include_once "./payment/classes/class.ilPaymentBillAdminGUI.php";


class ilPaymentAdminGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $user_obj;

	function ilPaymentAdminGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		
		$this->ilPaymentBaseGUI();
		$this->setMainSection($this->ADMIN);
		
		// Get user object
		$this->user_obj =& $user_obj;
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree,$ilTabs;

		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{
			case 'ilpaymenttrusteegui':
				$this->setSection($this->SECTION_TRUSTEE);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentTrusteeGUI.php';

				$pt =& new ilPaymentTrusteeGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($pt);
			
				break;

			case 'ilpaymentobjectgui':
				$this->setSection($this->SECTION_OBJECT);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentObjectGUI.php';

				$po =& new ilPaymentObjectGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($po);
				break;

			case 'ilpaymentstatisticgui':
				$this->setSection($this->SECTION_STATISTIC);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentStatisticGUI.php';

				$ps =& new ilPaymentStatisticGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($ps);
				break;

			case 'ilpaymentbilladmingui':
				$this->setSection($this->SECTION_OBJECT);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentBillAdminGUI.php';

				$po =& new ilPaymentBillAdminGUI($this->user_obj,$_GET['pobject_id']);
				
				$this->ctrl->forwardCommand($po);
				break;
				
			case 'ilpaymentcoupongui':
				$this->setSection($this->SECTION_COUPONS);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentCouponGUI.php';

				$po =& new ilPaymentCouponGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($po);
				break;

			default:
				$this->__forwardToDefault();
				break;
		}
	}

	function __forwardToDefault()
	{
		
		if(ilPaymentVendors::_isVendor($this->user_obj->getId()) or
		   ilPaymentTrustees::_hasStatisticPermission($this->user_obj->getId()))
		{
			$this->ctrl->redirectByClass('ilpaymentstatisticgui');
		}
		else if(ilPaymentTrustees::_hasObjectPermission($this->user_obj->getId()))
		{
			$this->ctrl->redirectByClass('ilpaymentobjectgui');
		}
		
		echo 'No access to payment admin';

		return false;
	}

}
?>