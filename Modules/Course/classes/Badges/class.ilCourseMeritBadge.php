<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";

/**
 * Class ilCourseMeritBadge
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesCourse
 */
class ilCourseMeritBadge implements ilBadgeType
{
    public function getId()
    {
        return "merit";
    }
    
    public function getCaption()
    {
        global $DIC;

        $lng = $DIC['lng'];
        return $lng->txt("badge_crs_merit");
    }
    
    public function isSingleton()
    {
        return true;
    }
    
    public function getValidObjectTypes()
    {
        return array("crs", "grp");
    }
    
    public function getConfigGUIInstance()
    {
        // no config
    }
}
