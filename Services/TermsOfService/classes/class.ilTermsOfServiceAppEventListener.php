<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';

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
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		// @todo: Move to a better place
		if('deleteUser' == $a_event && 'Services/User' == $a_component)
		{
			$ilDB->manipulate("DELETE FROM tos_acceptance_track WHERE usr_id = {$ilDB->quote($a_parameter['usr_id'], 'integer')}");
		}
	}
}
