<?php

use Psr\Http\Message\RequestInterface;

abstract class ilAdvancedMDTranslationGUI
{
    /**
     * Default command
     */
    protected const CMD_DEFAULT = 'translations';
    protected const CMD_ADD_TRANSLATION = 'addTranslations';
    protected const CMD_SAVE_ADDITIONAL_TRANSLATIONS = 'saveAdditionalTranslations';


    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $language;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var ilAdvancedMDRecord
     */
    protected $record;

    /**
     * ilAdvancedMDTranslationGUI constructor.
     * @param ilAdvancedMDRecord $record
     */
    public function __construct(ilAdvancedMDRecord $record)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->logger = $DIC->logger()->amet();
        $this->toolbar = $DIC->toolbar();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('obj');
        $this->request = $DIC->http()->request();
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

    /**
     * @param string $active_tab
     */
    protected function setTabs(string $active_tab)
    {
        $this->tabs->activateTab($active_tab);
    }

    /**
     * @return void
     */
    abstract protected function translations();

    /**
     * show translation creation form
     */
    protected function addTranslations(ilPropertyFormGUI $form = null)
    {
        $this->tabs->activateTab(self::CMD_DEFAULT);
        if (!$form instanceof ilPropertyFormGUI)
        {
            $form = $this->initCreateTranslationForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function initCreateTranslationForm()
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

    protected function addToolbarLanguageCreation()
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
     * @todo handle generic isConfigured
     * @return array
     */
    protected function getAvailableLanguagesOptions()
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

    protected function saveAdditionalTranslations()
    {
        $form = $this->initCreateTranslationForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            ilUtil::sendFailure($this->language->txt('err_check_input'));
            return $this->addTranslations($form);
        }
        foreach (array_unique((array) $form->getInput('languages')) as $language_code) {

            $languages = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
            $languages->addTranslationEntry($language_code);
        }

        ilUtil::sendSuccess($this->language->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }


}