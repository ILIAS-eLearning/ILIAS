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

use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Button\Standard as Button;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundtripModal;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Filesystem;
use ILIAS\MetaData\Vocabularies\Manager\ManagerInterface as VocabManager;
use ILIAS\MetaData\Settings\Vocabularies\Presentation;
use ILIAS\MetaData\Settings\Vocabularies\Import\Importer;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Settings\Vocabularies\DataRetrieval;
use JetBrains\PhpStorm\NoReturn;

/**
 * @ilCtrl_Calls ilMDVocabulariesGUI: ilMDVocabularyUploadHandlerGUI
 */
class ilMDVocabulariesGUI
{
    protected const MAX_CONFIRMATION_VALUES = 5;

    protected ilCtrl $ctrl;
    protected HTTP $http;
    protected Filesystem $temp_files;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilObjMDSettingsGUI $parent_obj_gui;
    protected ilMDSettingsAccessService $access_service;
    protected Refinery $refinery;

    protected VocabManager $vocab_manager;
    protected Presentation $presentation;
    protected Importer $importer;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $services = new InternalServices($DIC);

        $this->vocab_manager = $services->vocabularies()->manager();
        $this->presentation = new Presentation(
            $services->presentation()->elements(),
            $services->presentation()->utilities(),
            $services->vocabularies()->presentation(),
            $services->vocabularies()->slotHandler(),
            $services->structure()->structure(),
            $services->paths()->navigatorFactory(),
            $services->paths()->pathFactory()
        );
        $this->importer = new Importer(
            $services->paths()->pathFactory(),
            $this->vocab_manager->controlledVocabularyCreator(),
            $services->vocabularies()->slotHandler()
        );

        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->temp_files = $DIC->filesystem()->temp();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();

        $this->parent_obj_gui = $parent_obj_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_obj_gui->getRefId(),
            $DIC->access()
        );

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDVocabularyUploadHandlerGUI::class):
                $handler = new ilMDVocabularyUploadHandlerGUI();
                $this->ctrl->forwardCommand($handler);

                // no break
            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showVocabularies';
                }

                $this->$cmd();
                break;
        }
    }

    public function showVocabularies(): void
    {
        $import_modal = $this->getImportModal();
        $this->toolbar->addComponent($this->getImportButton($import_modal->getShowSignal()));

        $table = $this->getTable();

        $this->tpl->setContent(
            $this->ui_renderer->render([
                $import_modal,
                $table
            ])
        );
    }

    public function tableAction(): void
    {
        $action = $this->fetchTableAction();
        $vocab_id = $this->fetchVocabID();

        if (
            $vocab_id === '' ||
            ($action !== 'show_all' && !$this->access_service->hasCurrentUserWriteAccess())
        ) {
            $this->ctrl->redirect($this, 'showVocabularies');
        }

        switch ($action) {
            case 'delete':
                $this->confirmDeleteVocabulary($vocab_id);
                return;

            case 'activate':
                $this->activateVocabulary($vocab_id);
                return;

            case 'deactivate':
                $this->deactivateVocabulary($vocab_id);
                return;

            case 'allow_custom_input':
                $this->allowCustomInputForVocabulary($vocab_id);
                return;

            case 'disallow_custom_input':
                $this->disallowCustomInputForVocabulary($vocab_id);
                return;

            case 'show_all':
                $this->showAllValuesModalForVocabulary($vocab_id);
                return;

            default:
                $this->ctrl->redirect($this, 'showVocabularies');
        }
    }

    public function importVocabulary(): void
    {
        if (!$this->access_service->hasCurrentUserWriteAccess()) {
            $this->ctrl->redirect($this, 'showVocabularies');
        }

        $message_type = 'failure';
        $message_text = $this->lng->txt('md_vocab_import_upload_failed');

        $modal = $this->getImportModal()->withRequest($this->http->request());

        $upload_folder = null;
        if ($modal->getData()) {
            $upload_folder = (string) ($modal->getData()['file'][0] ?? null);
            if (!$this->temp_files->hasDir($upload_folder)) {
                $upload_folder = null;
            }
        }

        $file_content = null;
        if (!is_null($upload_folder)) {
            $files = $files = $this->temp_files->listContents($upload_folder);
            if (count($files) === 1 && ($files[0] ?? null)?->isFile()) {
                $file_content = $this->temp_files->read($files[0]->getPath());
            }
            $this->temp_files->deleteDir($upload_folder);
        }

        if (!is_null($file_content)) {
            $result = $this->importer->import($file_content);

            if ($result->wasSuccessful()) {
                $message_type = 'success';
                $message_text = $this->lng->txt('md_vocab_import_successful');
            } else {
                $message_type = 'failure';
                $message_text = sprintf(
                    $this->lng->txt('md_vocab_import_invalid'),
                    implode("<br/>", $result->getErrors())
                );
            }
        }

        $this->tpl->setOnScreenMessage($message_type, $message_text, true);
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    #[NoReturn] protected function confirmDeleteVocabulary(string $vocab_id): void
    {
        list($url_builder, $action_parameter_token, $vocabs_id_token) = $this->getTableURLBuilderAndParameters();
        $key = $vocabs_id_token->getName();

        $vocab = $this->vocab_manager->getVocabulary($vocab_id);
        $value_items = [];
        foreach ($this->presentation->makeValuesPresentable(
            $vocab,
            self::MAX_CONFIRMATION_VALUES
        ) as $value) {
            $value_items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                '',
                $value,
            );
        }

        $this->ctrl->setParameter($this, $key, $vocab_id);
        $link = $this->ctrl->getLinkTarget($this, 'deleteVocabulary');
        $this->ctrl->clearParameters($this);

        $modal = $this->ui_factory->modal()->interruptive(
            $this->presentation->txt('md_vocab_delete_confirmation_title'),
            $this->presentation->txtFill(
                'md_vocab_delete_confirmation_text',
                $this->presentation->makeSlotPresentable($vocab->slot()),
                $vocab->source()
            ),
            $link
        )->withAffectedItems($value_items);
        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    protected function deleteVocabulary(): void
    {
        $vocab_id = $this->fetchVocabID();
        if ($vocab_id !== '') {
            $this->vocab_manager->actions()->delete(
                $this->vocab_manager->getVocabulary($vocab_id)
            );
        }
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('md_vocab_deletion_successful'),
            true
        );
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    protected function activateVocabulary(string $vocab_id): void
    {
        $this->vocab_manager->actions()->activate(
            $this->vocab_manager->getVocabulary($vocab_id)
        );
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('md_vocab_update_successful'),
            true
        );
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    protected function deactivateVocabulary(string $vocab_id): void
    {
        $this->vocab_manager->actions()->deactivate(
            $this->vocab_manager->getVocabulary($vocab_id)
        );
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('md_vocab_update_successful'),
            true
        );
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    protected function allowCustomInputForVocabulary(string $vocab_id): void
    {
        $this->vocab_manager->actions()->allowCustomInput(
            $this->vocab_manager->getVocabulary($vocab_id)
        );
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('md_vocab_update_successful'),
            true
        );
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    protected function disallowCustomInputForVocabulary(string $vocab_id): void
    {
        $this->vocab_manager->actions()->disallowCustomInput(
            $this->vocab_manager->getVocabulary($vocab_id)
        );
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('md_vocab_update_successful'),
            true
        );
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    #[NoReturn] protected function showAllValuesModalForVocabulary(string $vocab_id): void
    {
        $vocab = $this->vocab_manager->getVocabulary($vocab_id);
        $values = $this->ui_factory->listing()->unordered(
            $this->presentation->makeValuesPresentable($vocab)
        );
        $modal = $this->ui_factory->modal()->roundtrip(
            $this->presentation->txtFill(
                'md_vocab_all_values_title',
                $this->presentation->makeSlotPresentable($vocab->slot()),
                $vocab->source()
            ),
            [$values]
        );
        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    protected function getTable(): DataTable
    {
        $column_factory = $this->ui_factory->table()->column();
        $columns = [
            'element' => $column_factory->text($this->lng->txt('md_vocab_element_column'))->withIsSortable(false),
            'type' => $column_factory->status($this->lng->txt('md_vocab_type_column'))->withIsSortable(false),
            'source' => $column_factory->text($this->lng->txt('md_vocab_source_column'))->withIsSortable(false),
            'preview' => $column_factory->text($this->lng->txt('md_vocab_preview_column'))->withIsSortable(false),
            'active' => $column_factory->statusIcon($this->lng->txt('md_vocab_active_column'))->withIsSortable(false),
            'custom_input' => $column_factory->statusIcon($this->lng->txt('md_vocab_custom_input_column'))->withIsSortable(false)
        ];

        list($url_builder, $action_parameter_token, $row_id_token) = $this->getTableURLBuilderAndParameters();
        $actions_factory = $this->ui_factory->table()->action();
        $actions = [
            'delete' => $actions_factory->single(
                $this->lng->txt('md_vocab_delete_action'),
                $url_builder->withParameter($action_parameter_token, 'delete'),
                $row_id_token
            )->withAsync(true),
            'activate' => $actions_factory->single(
                $this->lng->txt('md_vocab_activate_action'),
                $url_builder->withParameter($action_parameter_token, 'activate'),
                $row_id_token
            ),
            'deactivate' => $actions_factory->single(
                $this->lng->txt('md_vocab_deactivate_action'),
                $url_builder->withParameter($action_parameter_token, 'deactivate'),
                $row_id_token
            ),
            'allow_custom_input' => $actions_factory->single(
                $this->lng->txt('md_vocab_allow_custom_input_action'),
                $url_builder->withParameter($action_parameter_token, 'allow_custom_input'),
                $row_id_token
            ),
            'disallow_custom_input' => $actions_factory->single(
                $this->lng->txt('md_vocab_disallow_custom_input_action'),
                $url_builder->withParameter($action_parameter_token, 'disallow_custom_input'),
                $row_id_token
            ),
            'show_all' => $actions_factory->single(
                $this->lng->txt('md_vocab_show_all_action'),
                $url_builder->withParameter($action_parameter_token, 'show_all'),
                $row_id_token
            )->withAsync(true)
        ];

        return $this->ui_factory->table()->data(
            $this->lng->txt('md_vocab_table_title'),
            $columns,
            new DataRetrieval(
                $this->vocab_manager,
                $this->presentation,
                $this->ui_factory
            )
        )->withActions($actions)->withRequest($this->http->request());
    }

    protected function getImportModal(): RoundtripModal
    {
        $file_input = $this->ui_factory->input()->field()->file(
            new ilMDVocabularyUploadHandlerGUI(),
            $this->lng->txt('md_import_file_vocab')
        )->withAcceptedMimeTypes([MimeType::TEXT__XML])->withMaxFiles(1);

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('md_import_vocab_modal'),
            null,
            ['file' => $file_input],
            $this->ctrl->getLinkTarget($this, 'importVocabulary')
        );
    }

    protected function getImportButton(Signal $signal): Button
    {
        return $this->ui_factory->button()->standard(
            $this->lng->txt('md_import_vocab'),
            $signal
        );
    }

    protected function fetchTableAction(): string
    {
        list($url_builder, $action_parameter_token, $vocabs_id_token) = $this->getTableURLBuilderAndParameters();
        $key = $action_parameter_token->getName();
        if ($this->http->wrapper()->query()->has($key)) {
            return $this->http->wrapper()->query()->retrieve(
                $key,
                $this->refinery->identity()
            );
        }
        return '';
    }

    protected function fetchVocabID(): string
    {
        list($url_builder, $action_parameter_token, $vocabs_id_token) = $this->getTableURLBuilderAndParameters();
        $key = $vocabs_id_token->getName();
        if ($this->http->wrapper()->query()->has($key)) {
            return $this->http->wrapper()->query()->retrieve(
                $key,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            )[0] ?? '';
        }
        return '';
    }

    protected function getTableURLBuilderAndParameters(): array
    {
        $url_builder = new URLBuilder(new URI(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $this->ctrl->getLinkTarget($this, 'tableAction')
        ));
        return $url_builder->acquireParameters(
            ['metadata', 'vocab'],
            'table_action',
            'ids'
        );
    }
}
