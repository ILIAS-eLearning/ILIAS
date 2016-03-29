<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeProvider.php";

/**
 * Class ilGroupBadgeProvider
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesGroup
 */
class ilGroupBadgeProvider implements ilBadgeProvider
{
	public function getBadgeTypes() 
	{
		include_once "Modules/Group/classes/Badges/class.ilGroupMeritBadge.php";
		return array(
			new ilGroupMeritBadge()
		);
	}	
}