<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseMeritBadge
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesCourse
 */
class ilCourseMeritBadge implements ilBadgeType
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId() : string
    {
        return "merit";
    }

    public function getCaption() : string
    {
        return $this->lng->txt("badge_crs_merit");
    }

    public function isSingleton() : bool
    {
        return true;
    }

    public function getValidObjectTypes() : array
    {
        return array("crs", "grp");
    }

    public function getConfigGUIInstance() : ?ilBadgeTypeGUI
    {
        return null;
    }
}
