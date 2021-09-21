<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * @ilCtrl_Calls ilObjLearningSequenceAdminGUI: ilUserActionadminGUI, ilPermissionGUI, ilMemberExportSettingsGUI,
 */
class ilObjLearningSequenceAdminGUI extends ilMembershipAdministrationGUI
{
    protected function getType()
    {
        return "lsos";
    }

    protected function getParentObjType()
    {
        return "lso";
    }

    protected function getAdministrationFormId()
    {
        return ilAdministrationSettingsFormHandler::FORM_MAIL;
    }
}
