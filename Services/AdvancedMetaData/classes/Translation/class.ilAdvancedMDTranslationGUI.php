<?php declare(strict_types=1);

use Psr\Http\Message\RequestInterface;

abstract class ilAdvancedMDTranslationGUI
{
    protected const CMD_DEFAULT = 'translations';
    protected const CMD_ADD_TRANSLATION = 'addTranslations';
    protected const CMD_SAVE_ADDITIONAL_TRANSLATIONS = 'saveAdditionalTranslations';

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    protected ilLanguage $language;
    protected RequestInterface $request;
    protected ilLogger $logger;
    protected ilAdvancedMDRecord $record;

    public function __construct(ilAdvancedMDRecord $record)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('obj');
        $this->request = $DIC->http()->request();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->amet();
        $this->record = $record;
    }

    /**
     * Execute command and save parameter record id
     */
    public function executeCommand()
    {
        $this->ctrl->setParameterByClass(
            strtolower(get_class($this)),
            'record_id',
            $this->record->getRecordId()
        );

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);
        switch ($next_class) {
            default:
                $this->$cmd();
        }
    }

    protected function setTabs(string $active_tab)
    {
        $this->tabs->activateTab($active_tab);
    }

    /**
     * @return void
     */
    abstract protected function translations() : void;

    /**
     * show translation creation form
     */
    protected function addTranslations(ilPropertyFormGUI $form = null)
    {
        $this->tabs->activateTab(self::CMD_DEFAULT);
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initCreateTranslationForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function initCreateTranslationForm() : ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->language->txt('obj_add_languages'));

        $language_options = $this->getAvailableLanguagesOptions();
        $languages = new ilSelectInputGUI(
            $this->language->txt('obj_additional_langs'),
            'languages'
        );
        $languages->setOptions($language_options);
        $languages->setMulti(true);
        $languages->setRequired(true);
        $form->addItem($languages);

        $form->addCommandButton(
            self::CMD_SAVE_ADDITIONAL_TRANSLATIONS,
            $this->language->txt('save')
        );
        $form->addCommandButton(
            self::CMD_DEFAULT,
            $this->language->txt('cancel')
        );
        return $form;
    }

    protected function addToolbarLanguageCreation() : void
    {
        $button = ilLinkButton::getInstance();
        $button->setCaption('obj_add_languages');
        $button->setUrl(
            $this->ctrl->getLinkTargetByClass(
                strtolower(get_class($this)),
                self::CMD_ADD_TRANSLATION
            )
        );
        $this->toolbar->addButtonInstance($button);
    }

    /**
     * @return array<string, string>
     * @todo handle generic isConfigured
     */
    protected function getAvailableLanguagesOptions() : array
    {
        $languages = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());

        $this->language->loadLanguageModule('meta');
        $installed_languages = ilLanguage::_getInstalledLanguages();
        $options = [];
        $options[''] = $this->language->txt('select_one');
        foreach ($installed_languages as $key => $language) {
            if ($languages->isConfigured($language)) {
                continue;
            }
            $options[$language] = $this->language->txt('meta_l_' . $language);
        }
        return $options;
    }

    protected function saveAdditionalTranslations() : void
    {
        $form = $this->initCreateTranslationForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->language->txt('err_check_input'));
            $this->addTranslations($form);
            return;
        }
        foreach (array_unique((array) $form->getInput('languages')) as $language_code) {
            $languages = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
            $languages->addTranslationEntry($language_code);
        }

        $this->tpl->setOnScreenMessage('success', $this->language->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }
}
