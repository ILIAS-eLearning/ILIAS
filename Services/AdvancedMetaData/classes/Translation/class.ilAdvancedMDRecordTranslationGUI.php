<?php

/**
 * Class ilAdvancedMDRecordTranslationGUI
 * @ilCtrl_isCalledBy ilAdvancedMDRecordTranslationGUI: ilAdvancedMDSettingsGUI
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTranslationGUI extends ilAdvancedMDTranslationGUI
{

    /**
     * @inheritDoc
     */
    protected function translations()
    {
        $this->setTabs(self::CMD_DEFAULT);

        $language_table = new ilAdvancedMDRecordLanguageTableGUI(
            $this->record,
            $this,
            self::CMD_DEFAULT
        );
        $language_table->init();
        $language_table->parse();

        $this->tpl->setContent($language_table->getHTML());
    }

    protected function saveTranslations()
    {
        $languages = (array) $this->request->getParsedBody()['active_languages'];
        $default = (string) $this->request->getParsedBody()['default'];

        if (!in_array($default, $languages)) {
            ilUtil::sendFailure($this->language->txt('err_check_input'), true);
            ilUtil::sendInfo($this->language->txt('md_adn_int_error_no_default'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        foreach ($translations->getTranslations() as $translation) {
            if (!in_array($translation->getLangKey(), $languages)) {
                $translation->delete();
            }
        }
        foreach ($languages as $lang_key) {
            if (!$translations->isConfigured($lang_key)) {
                $translations->addTranslationEntry($lang_key);
            }
        }

        $this->record->setDefaultLanguage($default);
        $this->record->update();

        #$default = $translations->getTranslation($default);
        #$default->setTitle($this->record->getTitle());
        #$default->setDescription($this->record->getDescription());
        #$default->update();

        ilUtil::sendSuccess($this->language->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);


    }
}