<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAppEventListener implements ilAppEventListener
{
	/**
	 * @param string $a_component
	 * @param string $a_event
	 * @param array  $a_parameter
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if('deleteUser' == $a_event && 'Services/User' == $a_component)
		{
			ilTermsOfServiceHelper::deleteAcceptanceHistoryByUser($a_parameter['usr_id']);
		}
	}
}
