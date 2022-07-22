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

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
abstract class ilTestSettingsGUI
{
    protected ilObjTest $testOBJ;
    protected ?ilSettingsTemplate $settingsTemplate = null;

    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;

        $templateId = $this->testOBJ->getTemplate();

        if ($templateId) {
            $this->settingsTemplate = new ilSettingsTemplate($templateId, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
        }
    }

    protected function getTemplateSettingValue($settingName)
    {
        if (!$this->settingsTemplate) {
            return null;
        }

        $templateSettings = $this->settingsTemplate->getSettings();

        if (!isset($templateSettings[$settingName])) {
            return false;
        }

        return $templateSettings[$settingName]['value'];
    }

    protected function isHiddenFormItem($formFieldId) : bool
    {
        if (!$this->settingsTemplate) {
            return false;
        }

        $settings = $this->settingsTemplate->getSettings();

        if (!isset($settings[$formFieldId])) {
            return false;
        }

        if (!$settings[$formFieldId]['hide']) {
            return false;
        }

        return true;
    }

    protected function isSectionHeaderRequired($fields) : bool
    {
        foreach ($fields as $field) {
            if (!$this->isHiddenFormItem($field)) {
                return true;
            }
        }

        return false;
    }

    protected function formPropertyExists(ilPropertyFormGUI $form, $propertyId) : bool
    {
        return $form->getItemByPostVar($propertyId) instanceof ilFormPropertyGUI;
    }

    protected function removeHiddenItems(ilPropertyFormGUI $form)
    {
        if ($this->settingsTemplate) {
            foreach ($this->settingsTemplate->getSettings() as $id => $item) {
                if ($item["hide"]) {
                    $form->removeItemByPostVar($id);
                }
            }
        }
    }
}
