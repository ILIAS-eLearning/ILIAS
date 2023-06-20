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
    const SETTING_FAVOURITES_ENABLED = 'rep_favourites';

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
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('crs_course_section'));
        $form->addItem($section);

        $checkBox = new ilCheckboxInputGUI($this->lng->txt('crs_favourites_enabled'), 'rep_favourites');
        $checkBox->setInfo($this->lng->txt('crs_favourites_enabled_info'));
        $checkBox->setChecked((bool) $this->ilSettings->get(self::SETTING_FAVOURITES_ENABLED, 0));
        $form->addItem($checkBox);
        return $form;
    }

    protected function saveChildSettings($form)
    {
        $this->ilSettings->set(self::SETTING_FAVOURITES_ENABLED, (int) $form->getInput(self::SETTING_FAVOURITES_ENABLED));
    }

    protected function getChildSettingsInfo($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_GROUP:
                $this->lng->loadLanguageModule("crs");
                $fields = [
                    'crs_favourites_enabled' => [ (bool) $this->ilSettings->get(self::SETTING_FAVOURITES_ENABLED, 0), ilAdministrationSettingsFormHandler::VALUE_BOOL ]
                ];
                return [ [ "editSettings", $fields ] ];
        }
        return [];
    }
}
