<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Course Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjCourseAdministrationGUI: ilPermissionGUI, ilMemberExportSettingsGUI, ilUserActionadminGUI
 * @ingroup ModulesCourse
 */
class ilObjCourseAdministrationGUI extends ilMembershipAdministrationGUI
{
    protected function getType() : string
    {
        return "crss";
    }
    
    protected function getParentObjType() : string
    {
        return "crs";
    }
    
    protected function getAdministrationFormId() : int
    {
        return ilAdministrationSettingsFormHandler::FORM_COURSE;
    }
}
