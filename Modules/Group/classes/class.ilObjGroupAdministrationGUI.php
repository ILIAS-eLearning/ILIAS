<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Group Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjGroupAdministrationGUI: ilPermissionGUI, ilMemberExportSettingsGUI, ilUserActionadminGUI
 *
 * @ingroup ModulesGroup
 */
class ilObjGroupAdministrationGUI extends ilMembershipAdministrationGUI
{
    protected function getType(): string
    {
        return "grps";
    }

    protected function getParentObjType(): string
    {
        return "grp";
    }

    protected function getAdministrationFormId(): int
    {
        return ilAdministrationSettingsFormHandler::FORM_GROUP;
    }
}
