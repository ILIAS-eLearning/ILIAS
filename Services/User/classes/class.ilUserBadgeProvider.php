<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeProvider.php";

/**
 * Class ilUserBadgeProvider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesUser
 */
class ilUserBadgeProvider implements ilBadgeProvider
{
    public function getBadgeTypes()
    {
        include_once "Services/User/classes/Badges/class.ilUserProfileBadge.php";
        return array(
            new ilUserProfileBadge()
        );
    }
}
