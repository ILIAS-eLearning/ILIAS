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

declare(strict_types=0);

/**
 * Course Administration Settings
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjCourseAdministrationGUI: ilPermissionGUI, ilMemberExportSettingsGUI, ilUserActionadminGUI
 * @ingroup components\ILIASCourse
 */
class ilObjCourseAdministrationGUI extends ilMembershipAdministrationGUI
{
    private const SETTING_COURSES_AND_GROUPS_ENABLED = 'mmbr_my_crs_grp';

    protected function getType(): string
    {
        return "crss";
    }

    protected function getParentObjType(): string
    {
        return "crs";
    }

    protected function getAdministrationFormId(): int
    {
        return ilAdministrationSettingsFormHandler::FORM_COURSE;
    }

    protected function addChildContentsTo(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $checkBox = new ilCheckboxInputGUI($this->lng->txt('crs_my_courses_groups_enabled'), self::SETTING_COURSES_AND_GROUPS_ENABLED);
        $checkBox->setInfo($this->lng->txt('crs_my_courses_groups_enabled_info'));
        $checkBox->setChecked((bool) $this->settings->get(self::SETTING_COURSES_AND_GROUPS_ENABLED, 1));
        $form->addItem($checkBox);
        return $form;
    }

    protected function saveChildSettings(ilPropertyFormGUI $form): void
    {
        $this->settings->set(self::SETTING_COURSES_AND_GROUPS_ENABLED, (int) $form->getInput(self::SETTING_COURSES_AND_GROUPS_ENABLED));
    }

    protected function getChildSettingsInfo(int $a_form_id): array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_GROUP:
                $this->lng->loadLanguageModule("crs");
                $fields = [
                    'crs_my_courses_groups_enabled' => [ (bool) $this->settings->get(self::SETTING_COURSES_AND_GROUPS_ENABLED, 1), ilAdministrationSettingsFormHandler::VALUE_BOOL ]
                ];
                return [ [ "editSettings", $fields ] ];
        }
        return [];
    }
}
