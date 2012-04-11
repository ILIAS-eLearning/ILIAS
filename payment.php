<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* THIS IS ONLY USED BUY BMF-PAYMENTSYSTEM!!
* 
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
	case "start_bmf"		:	require_once "./Services/Payment/classes/class.ilPurchaseBMFGUI.php";

								$ilCtrl->setTargetScript("payment.php");
								$ilCtrl->setParameterByClass("ilpurchasebmfgui", "view", "start_bmf");
								$ilCtrl->getCallStructure("ilpurchasebmfgui");

								$pa = new ilPurchaseBMFGUI($ilias->account);
								$ilCtrl->forwardCommand($pa);
								break;
							
	case "conditions"		:	
	default					:	require_once "./Services/Payment/classes/class.ilTermsCondition.php";
								$pa = new ilTermsCondition($ilias->account);
								$pa->show();
								break;
}

$tpl->show();
?>
