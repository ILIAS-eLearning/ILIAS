<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* base script for payment_shopping_cart and buyed objects
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id: payment.php 5054 2004-09-27 14:00:01Z smeyer $
*
* @package ilias-core
*/
define('ILIAS_MODULE','payment');

require_once "include/inc.header.php";

switch ($_GET["view"])
{
	case "payment_admin"	:	require_once "./payment/classes/class.ilPaymentAdminGUI.php";

								$ilCtrl->setTargetScript("payment.php");
								$ilCtrl->setParameterByClass("ilpaymentadmingui", "view", "payment_admin");
								$ilCtrl->getCallStructure("ilpaymentadmingui");

								$pa =& new ilPaymentAdminGUI($ilias->account);
								$ilCtrl->forwardCommand($pa);
								break;
	case "start_purchase"	:	require_once "./payment/classes/class.ilPaymentPurchaseGUI.php";

								$ilCtrl->setTargetScript("payment.php");
								$ilCtrl->setParameterByClass("ilpaymentpurchasegui", "view", "start_purchase");
								$ilCtrl->getCallStructure("ilpaymentpurchasegui");

								$pa =& new ilPaymentPurchaseGUI((int) $_GET['ref_id']);
								$ilCtrl->forwardCommand($pa);
								break;
	case "start_bmf"		:	require_once "./payment/classes/class.ilPurchaseBMFGUI.php";

								$ilCtrl->setTargetScript("payment.php");
								$ilCtrl->setParameterByClass("ilpurchasebmfgui", "view", "start_bmf");
								$ilCtrl->getCallStructure("ilpurchasebmfgui");

								$pa =& new ilPurchaseBMFGUI($ilias->account);
								$ilCtrl->forwardCommand($pa);
								break;
	case "conditions"		:	require_once "./payment/classes/class.ilTermsCondition.php";

								$pa =& new ilTermsCondition($ilias->account);
								$pa->show();
								break;
	default					:	require_once "./payment/classes/class.ilPaymentGUI.php";

								$ilCtrl->setTargetScript("payment.php");
								$ilCtrl->setParameterByClass("ilpaymentgui", "view", "payment");
								$ilCtrl->getCallStructure("ilpaymentgui");

								$pa =& new ilPaymentGUI($ilias->account);
								$ilCtrl->forwardCommand($pa);
								break;
								
}

$tpl->show();
?>
