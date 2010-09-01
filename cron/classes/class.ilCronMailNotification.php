<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Michael Jansen <mjansen@databay.de>
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* @package ilias
*/
class ilCronMailNotification
{	
	public function sendNotifications()
	{
		require_once 'Services/Mail/classes/class.ilMailSummaryNotification.php';

		$msn = new ilMailSummaryNotification();
		$msn->send();

		return true;
	}
}
?>
