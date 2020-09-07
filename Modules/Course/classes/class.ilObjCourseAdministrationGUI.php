<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Membership/classes/class.ilMembershipAdministrationGUI.php" ;

/**
 * Course Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjCourseAdministrationGUI: ilPermissionGUI, ilMemberExportSettingsGUI, ilUserActionadminGUI
 *
 * @ingroup ModulesCourse
 */
class ilObjCourseAdministrationGUI extends ilMembershipAdministrationGUI
{
    protected function getType()
    {
        return "crss";
    }
    
    protected function getParentObjType()
    {
        return "crs";
    }
    
    protected function getAdministrationFormId()
    {
        return ilAdministrationSettingsFormHandler::FORM_COURSE;
    }
}
