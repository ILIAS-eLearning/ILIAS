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

use ilLanguage;
use ilSelectInputGUI;
use Psr\Http\Message\RequestInterface;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\AdvancedMetaData\Record\Multilingualism\DataRetrieval;
use ILIAS\AdvancedMetaData\Record\Multilingualism\TableBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class ilAdvancedMDRecordTranslationGUI
{
    protected const CMD_DEFAULT = 'translations';
    protected const CMD_ADD_TRANSLATION = 'addTranslations';
    protected const CMD_SAVE_ADDITIONAL_TRANSLATIONS = 'saveAdditionalTranslations';
    protected const CMD_ACTIVATE = 'activate';
    protected const CMD_DEACTIVATE = 'deactivate';
    protected const CMD_SET_DEFAULT = 'setDefault';

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    protected ilLanguage $language;
    protected RequestInterface $request;
    protected ilAdvancedMDRecord $record;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected HTTP $http;
    protected RefineryFactory $refinery;
    protected DataFactory $data_factory;

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
        $this->record = $record;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->data_factory = new DataFactory();
    }

    /**
     * Execute command and save parameter record id
     */
    public function executeCommand(): void
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

    protected function translations(): void
    {
        $this->setTabs(self::CMD_DEFAULT);

        $data_retrieval = new DataRetrieval(
            $this->record,
            $this->language
        );

        $table_builder = new TableBuilder(
            $data_retrieval,
            $this->language,
            $this->ui_factory,
            $this->http,
            $this->ctrl,
            $this->data_factory
        );

        [$url_builder, $id_token, $action_token] = $this->getTableActionURLBuilder();
        $table = $table_builder->get($url_builder, $id_token, $action_token);

        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    protected function getTableActionURLBuilder(): array
    {
        $link = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            ilAdvancedMDRecordTranslationGUI::class,
            'handleTableAction'
        );
        $url_builder = new URLBuilder($this->data_factory->uri($link));

        return $url_builder->acquireParameters(['advmd_ml'], 'table_ids', 'table_action');
    }

    protected function getLanguageCodes(URLBuilderToken $id_token): array
    {
        $parameter_name = $id_token->getName();
        $raw_ids = $this->getPostParameter($parameter_name, true);
        if ($raw_ids === null) {
            $raw_ids = $this->getQueryParameter($parameter_name, true);
        }

        if ($raw_ids === ['ALL_OBJECTS'] || $raw_ids === 'ALL_OBJECTS') {
            return DataRetrieval::getAllIDs();
        }

        return (array) ($raw_ids ?? []);
    }

    protected function getPostParameter(string $name, bool $is_list = false): mixed
    {
        $wrapper = $this->http->wrapper()->post();

        if ($wrapper->has($name)) {
            $refinery = $is_list ?
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()) :
                $this->refinery->kindlyTo()->string();
            return $wrapper->retrieve($name, $refinery);
        }

        return null;
    }

    protected function getQueryParameter(string $name, bool $is_list = false): mixed
    {
        $wrapper = $this->http->wrapper()->query();

        if ($wrapper->has($name)) {
            $refinery = $is_list ?
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()) :
                $this->refinery->kindlyTo()->string();
            return $wrapper->retrieve($name, $refinery);
        }

        return null;
    }

    public function handleTableAction(): void
    {
        [$url_builder, $id_token, $action_token] = $this->getTableActionURLBuilder();
        $ids = $this->getLanguageCodes($id_token);
        $action = $this->getPostParameter($action_token->getName());
        if ($action === null) {
            $action = $this->getQueryParameter($action_token->getName());
        }

        switch ($action) {
            case 'activate':
                $this->activate($ids);
                break;
            case 'deactivate':
                $this->deactivate($ids);
                break;
            case 'setDefault':
                $this->setDefault($ids);
                break;
        }
    }


    protected function activate(array $lang_keys): void
    {
        $record_id = $this->record->getRecordId();
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);

        foreach ($lang_keys as $lang_key) {
            if (!$translations->isConfigured($lang_key)) {
                $translations->addTranslationEntry($lang_key, false);
            }
        }

        if (count($lang_keys) > 0) {
            $this->tpl->setOnScreenMessage('success', $this->language->txt('settings_saved'), true);
        }

        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function deactivate(array $lang_keys): void
    {
        $record_id = $this->record->getRecordId();
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);
        $success = false;

        foreach ($lang_keys as $lang_key) {
            if ($lang_key === $this->record->getDefaultLanguage()) {
                $this->tpl->setOnScreenMessage('failure', $this->language->txt('md_adn_int_error_no_default'), true);
            } else {
                $translation = $translations->getTranslation($lang_key);
                if ($translation) {
                    $translation->delete();
                    $success = true;
                }
            }
        }

        if ($success) {
            $this->tpl->setOnScreenMessage('success', $this->language->txt('settings_saved'), true);
        }

        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function setDefault(array $lang_keys): void
    {
        $lang_key = reset($lang_keys);

        if (!$lang_key) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        if (!$translations->isConfigured($lang_key)) {
            $this->tpl->setOnScreenMessage('failure', $this->language->txt('err_check_input'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->record->setDefaultLanguage($lang_key);
        $this->record->update();

        $this->tpl->setOnScreenMessage('success', $this->language->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    /**
     * show translation creation form
     */
    protected function addTranslations(?ilPropertyFormGUI $form = null)
    {
        $this->tabs->activateTab(self::CMD_DEFAULT);
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initCreateTranslationForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function initCreateTranslationForm(): ilPropertyFormGUI
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

    /**
     * @return array<string, string>
     * @todo handle generic isConfigured
     */
    protected function getAvailableLanguagesOptions(): array
    {
        $languages = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());

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

    protected function saveAdditionalTranslations(): void
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
