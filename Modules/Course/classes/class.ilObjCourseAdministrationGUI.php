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
    private const SETTING_COURSES_AND_GROUPS_ENABLED = 'mmbr_my_crs_grp';

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

    protected function addChildContentsTo($form)
    {
        $checkBox = new ilCheckboxInputGUI($this->lng->txt('crs_my_courses_groups_enabled'), self::SETTING_COURSES_AND_GROUPS_ENABLED);
        $checkBox->setInfo($this->lng->txt('crs_my_courses_groups_enabled_info'));
        $checkBox->setChecked((bool) $this->settings->get(self::SETTING_COURSES_AND_GROUPS_ENABLED, 1));
        $form->addItem($checkBox);
        return $form;
    }

    protected function saveChildSettings($form)
    {
        $this->settings->set(self::SETTING_COURSES_AND_GROUPS_ENABLED, (int) $form->getInput(self::SETTING_COURSES_AND_GROUPS_ENABLED));
    }

    protected function getChildSettingsInfo($a_form_id)
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
