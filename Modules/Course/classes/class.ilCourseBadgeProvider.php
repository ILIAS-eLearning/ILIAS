<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeProvider.php";

/**
 * Class ilCourseBadgeProvider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesCourse
 */
class ilCourseBadgeProvider implements ilBadgeProvider
{
    public function getBadgeTypes()
    {
        include_once "Modules/Course/classes/Badges/class.ilCourseMeritBadge.php";
        include_once "Modules/Course/classes/Badges/class.ilCourseLPBadge.php";
        return array(
            new ilCourseMeritBadge()
            ,new ilCourseLPBadge()
        );
    }
}
