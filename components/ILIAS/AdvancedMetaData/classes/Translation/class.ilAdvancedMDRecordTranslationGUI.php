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

/**
 * @ilCtrl_IsCalledBy ilAdvancedMDRecordTranslationGUI: ilAdvancedMDSettingsGUI
 */
class ilAdvancedMDRecordTranslationGUI
{
    protected const string CMD_DEFAULT = 'translations';
    protected const string CMD_TABLE_ACTION = 'handleTableAction';

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
    protected ilAdvancedMDRecordTranslations $record_translations;

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
        $this->record_translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId(
            $this->record->getRecordId()
        );
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
                switch ($cmd) {
                    case self::CMD_DEFAULT:
                        $this->translations();
                        break;
                    case self::CMD_TABLE_ACTION:
                        $this->handleTableAction();
                        break;
                }
                break;
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
            $this->language,
            $this->record_translations
        );

        $table_builder = new TableBuilder(
            $data_retrieval,
            $this->language,
            $this->ui_factory,
            $this->http
        );

        [$url_builder, $id_token, $action_token] = $this->getTableActionURLBuilder();
        $table = $table_builder->get($url_builder, $id_token, $action_token);

        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    protected function getTableActionURLBuilder(): array
    {
        $link = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            ilAdvancedMDRecordTranslationGUI::class,
            $this::CMD_TABLE_ACTION
        );
        $url_builder = new URLBuilder($this->data_factory->uri($link));

        return $url_builder->acquireParameters(['advmd_ml'], 'table_ids', 'table_action');
    }

    protected function getLanguageCodesFromQuery(URLBuilderToken $id_token, DataRetrieval $data_retrieval): array
    {
        $parameter_name = $id_token->getName();
        $wrapper = $this->http->wrapper()->query();

        $raw_ids = null;
        if ($wrapper->has($parameter_name)) {
            $raw_ids = $wrapper->retrieve(
                $parameter_name,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        if ($raw_ids === ['ALL_OBJECTS']) {
            return $data_retrieval->getAllIDs();
        }

        return $raw_ids ?? [];
    }

    protected function getTableActionFromQuery(URLBuilderToken $action_token): string
    {
        $name = $action_token->getName();
        $wrapper = $this->http->wrapper()->query();

        if ($wrapper->has($name)) {
            return (string) $wrapper->retrieve(
                $name,
                $this->refinery->kindlyTo()->string()
            );
        }

        return '';
    }

    public function handleTableAction(): void
    {
        $data_retrieval = new DataRetrieval(
            $this->record,
            $this->language,
            $this->record_translations
        );
        [$url_builder, $id_token, $action_token] = $this->getTableActionURLBuilder();
        $ids = $this->getLanguageCodesFromQuery($id_token, $data_retrieval);
        $action = $this->getTableActionFromQuery($action_token);

        switch ($action) {
            case TableBuilder::ACTION_ACTIVATE:
                $this->activate($ids);
                break;
            case TableBuilder::ACTION_DEACTIVATE:
                $this->deactivate($ids);
                break;
            case TableBuilder::ACTION_SET_DEFAULT:
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
                $translations->addTranslationEntry($lang_key);
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
}
