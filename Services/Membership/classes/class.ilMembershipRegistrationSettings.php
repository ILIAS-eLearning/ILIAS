<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Registration settings 
 * Currently only some constants used in sessions (@todo course, groups)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesMembership
 */
abstract class ilMembershipRegistrationSettings
{
	const TYPE_NONE = 0;
	const TYPE_DIRECT = 1;
	const TYPE_PASSWORD = 2;
	const TYPE_REQUEST = 3;
	
	const REGISTRATION_LINK = 5;
	
	const REGISTRATION_LIMITED_DURATION = 6;
	const REGISTRATION_LIMITED_USERS = 7;
	
}
?>
