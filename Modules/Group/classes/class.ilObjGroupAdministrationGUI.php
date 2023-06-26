<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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

    protected function addChildContentsTo(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        return $form;
    }

    protected function saveChildSettings(ilPropertyFormGUI $form): void
    {
    }

    protected function getChildSettingsInfo(int $a_form_id): array
    {
        return [];
    }
}
