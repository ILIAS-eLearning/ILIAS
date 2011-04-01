<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*/
class ilCronPaymentNotification
{	
	public function sendNotifications()
	{
		require_once 'Services/Payment/classes/class.ilPaymentNotification.php';

		$msn = new ilPaymentNotification();
		$msn->send();

		return true;
	}
}
?>
