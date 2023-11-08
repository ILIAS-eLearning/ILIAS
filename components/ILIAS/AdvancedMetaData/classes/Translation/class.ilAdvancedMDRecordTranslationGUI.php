<?php

declare(strict_types=1);

/**
 * Class ilAdvancedMDRecordTranslationGUI
 * @ilCtrl_isCalledBy ilAdvancedMDRecordTranslationGUI: ilAdvancedMDSettingsGUI
 * @ingroup           ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTranslationGUI extends ilAdvancedMDTranslationGUI
{
    protected function translations(): void
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

    /**
     * @todo use kindlyTo for input parameters
     */
    protected function saveTranslations(): void
    {
        $languages = (array) $this->request->getParsedBody()['active_languages'];
        $default = (string) $this->request->getParsedBody()['default'];

        if (!in_array($default, $languages)) {
            $this->tpl->setOnScreenMessage('failure', $this->language->txt('err_check_input'), true);
            $this->tpl->setOnScreenMessage('info', $this->language->txt('md_adn_int_error_no_default'), true);
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

        $this->tpl->setOnScreenMessage('success', $this->language->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }
}
