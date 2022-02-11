<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseBadgeProvider
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesCourse
 */
class ilCourseBadgeProvider implements ilBadgeProvider
{
    public function getBadgeTypes() : array
    {
        return array(
            new ilCourseMeritBadge()
            ,
            new ilCourseLPBadge()
        );
    }
}
