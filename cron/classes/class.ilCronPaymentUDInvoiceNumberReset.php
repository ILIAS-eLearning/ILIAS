<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*/
class ilCronPaymentUDInvoiceNumberReset
{	
	public function check()
	{
		require_once 'Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php';

		$msn = new ilUserDefinedInvoiceNumber();
		$msn->cronCheck();

		return true;
	}
}
?>