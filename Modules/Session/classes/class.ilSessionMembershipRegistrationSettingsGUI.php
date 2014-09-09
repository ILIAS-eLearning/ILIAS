<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipRegistrationSettingsGUI.php';

/**
* Registration settings 
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
class ilSessionMembershipRegistrationSettingsGUI extends ilMembershipRegistrationSettingsGUI
{
	public function __construct(\ilObjectGUI $gui_object, \ilObject $object, $a_options)
	{
		parent::__construct($gui_object, $object, $a_options);
		$GLOBALS['lng']->loadLanguageModule('sess');
	}
}
?>
