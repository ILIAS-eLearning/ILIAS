<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListener implements \ilAppEventListener
{
	/**
	 * @inheritdoc
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if ('deleteUser' == $a_event && 'Services/User' == $a_component) {
			ilTermsOfServiceHelper::deleteAcceptanceHistoryByUser($a_parameter['usr_id']);
		}
	}
}
