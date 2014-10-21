<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPurchaseBillGUI
*
* @author Nadia Ahmad
* @version $Id: class.ilPurchaseBillGUI.php 
*
* @package core
*/

include_once './Services/Payment/classes/class.ilPurchaseBaseGUI.php';
include_once './Services/Payment/classes/class.ilPayMethods.php';

class ilPurchaseBillGUI extends ilPurchaseBaseGUI
{
	var $user_obj;
	var $pay_method;
	
	public function __construct($user_obj)
	{
		$this->user_obj = $user_obj;
		$this->pay_method =	ilPayMethods::_getIdByTitle('bill');
		
		parent::__construct($user_obj,$this->pay_method);
		$this->setAccess(0);
		$this->setPayed(0);
	}		
}
?>