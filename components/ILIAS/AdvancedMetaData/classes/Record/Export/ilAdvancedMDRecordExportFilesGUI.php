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

namespace ILIAS\AdvancedMetaData\Record\Export;

use ilLanguage;
use ilGlobalTemplateInterface;
use ilCtrl;
use ilTabsGUI;
use ilToolbarGUI;
use ilObjUser;
use ilAccess;
use ilAdvancedMDRecordExportFiles;
use ilAdvancedMDSettingsGUI;
use ilAdministrationGUI;
use ilConfirmationGUI;
use Psr\Http\Message\RequestInterface;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Record\Export\TableBuilder;
use ILIAS\AdvancedMetaData\Record\Export\DataRetrieval;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\Data\ObjectId;

class ilAdvancedMDRecordExportFilesGUI
{
    public const string CMD_SHOW_FILES = 'showFiles';
    public const string CMD_HANDLE_EXPORT_FILES_TABLE_ACTION = 'handleExportFilesTableAction';
    public const string CMD_DELETE_FILES = 'deleteFiles';

    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected RequestInterface $request;
    protected GlobalHttpState $http;
    protected RefineryFactory $refinery;
    protected ilTabsGUI $tabs_gui;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilAccess $access;

    protected int $context;
    protected int $ref_id;
    protected ?int $obj_id;

    public function __construct(
        int $context,
        int $ref_id,
        ?int $obj_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();

        $this->refinery = $DIC->refinery();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();
        $this->user = $DIC->user();
        $this->access = $DIC->access();

        $this->context = $context;
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FILES);

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_FILES:
                        $this->showFiles();
                        break;

                    case self::CMD_HANDLE_EXPORT_FILES_TABLE_ACTION:
                        $this->handleExportFilesTableAction();
                        break;

                    case self::CMD_DELETE_FILES:
                        $this->deleteFiles();
                        break;
                }
                break;
        }
    }

    protected function showFiles(): void
    {
        if (($this->context === ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION && !$this->access->checkAccess('read', '', $this->ref_id)) ||
            ($this->context === ilAdvancedMDSettingsGUI::CONTEXT_OBJECT && !$this->access->checkAccess('write', '', $this->ref_id))) {
            $this->checkPermissionFailed();
            return;
        }
        $this->tabs_gui->setSubTabActive('md_adv_file_list');

        $data_factory = new DataFactory();
        $table_builder = new TableBuilder(
            $this->getExportFileDataRetrieval(),
            $this->lng,
            $this->ui_factory,
            $this->http,
            $this->user,
            $data_factory,
            $this->access->checkAccess('write', '', $this->ref_id)
        );

        [$url_builder, $id_token, $action_token] = $this->getExportTableActionURLBuilder();
        $table = $table_builder->get($url_builder, $id_token, $action_token);

        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    protected function getExportTableActionURLBuilder(): array
    {
        $link = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            $this::class,
            self::CMD_HANDLE_EXPORT_FILES_TABLE_ACTION
        );
        $url_builder = new URLBuilder(new URI($link));

        return $url_builder->acquireParameters(['advmd'], 'file_id', 'export_action');
    }

    public function handleExportFilesTableAction(): void
    {
        [$url_builder, $id_token, $action_token] = $this->getExportTableActionURLBuilder();

        $action = '';
        if ($this->http->wrapper()->query()->has('advmd_export_action')) {
            $action = $this->http->wrapper()->query()->retrieve(
                'advmd_export_action',
                $this->refinery->kindlyTo()->string()
            );
        }

        $ids = $this->getExportFileIdsFromQuery('advmd_file_id');

        switch ($action) {
            case TableBuilder::ACTION_DOWNLOAD:
                $this->downloadExportFile($ids);
                break;
            case TableBuilder::ACTION_DELETE:
                $this->confirmDeleteExportFiles($ids);
                break;
            default:
                $this->showFiles();
                break;
        }
    }

    /**
     * @param string[] $ids
     */
    protected function downloadExportFile(array $ids): void
    {
        if (($this->context === ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION && !$this->access->checkAccess('read', '', $this->ref_id)) ||
            ($this->context === ilAdvancedMDSettingsGUI::CONTEXT_OBJECT && !$this->access->checkAccess('write', '', $this->ref_id))) {
            $this->checkPermissionFailed();
            return;
        }
        $file_id = (string) ($ids[0] ?? '');

        if ($file_id === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showFiles();
            return;
        }

        $this->getExportFilesHandler()->download($file_id, 'ilias_meta_data_record.xml');
    }

    /**
     * @param string[] $file_ids
     */
    protected function confirmDeleteExportFiles(array $file_ids): void
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_FILES);
        }

        if (count($file_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showFiles();
            return;
        }

        $file_data = $this->getExportFilesHandler()->readFilesInfo();

        $c_gui = new ilConfirmationGUI();
        $c_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_DELETE_FILES));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_files_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), self::CMD_SHOW_FILES);
        $c_gui->setConfirm($this->lng->txt("confirm"), self::CMD_DELETE_FILES);

        foreach ($file_ids as $file_id) {
            $info = $file_data[$file_id];
            $c_gui->addItem(
                'advmd_file_id[]',
                (string) $file_id,
                is_array($info['name'] ?? false) ? implode(',', $info['name']) : 'No Records'
            );
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteFiles(): void
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_FILES);
        }

        $file_ids = $this->getExportFileIdsFromPost('advmd_file_id');
        if (count($file_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showFiles();
            return;
        }

        $files = $this->getExportFilesHandler();

        foreach ($file_ids as $file_id) {
            $files->deleteByFileId(
                $this->user->getId(),
                $file_id
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_deleted_files'));
        $this->showFiles();
    }

    protected function getExportFileIdsFromPost(string $parameter_name): array
    {
        $wrapper = $this->http->wrapper()->post();
        $ids = [];
        if ($wrapper->has($parameter_name)) {
            $ids = $wrapper->retrieve(
                $parameter_name,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        return $ids;
    }

    protected function getExportFileIdsFromQuery(string $parameter_name): array
    {
        $wrapper = $this->http->wrapper()->query();
        $ids = [];
        if ($wrapper->has($parameter_name)) {
            $ids = $wrapper->retrieve(
                $parameter_name,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        if (in_array('ALL_OBJECTS', $ids, true)) {
            return $this->getExportFileDataRetrieval()->getAllIds();
        }

        return $ids;
    }

    protected function getExportFileDataRetrieval(): DataRetrieval
    {
        return new DataRetrieval(
            $this->getExportFilesHandler(),
            new DataFactory(),
            $this->ui_factory
        );
    }

    protected function getExportFilesHandler(): ilAdvancedMDRecordExportFiles
    {
        return new ilAdvancedMDRecordExportFiles(
            $this->user->getId(),
            $this->context === ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION ? null : new ObjectId($this->obj_id)
        );
    }

    protected function checkPermissionFailed(): void
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        if ($this->context === ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION) {
            $this->ctrl->redirectByClass(ilAdministrationGUI::class);
        } else {
            $this->ctrl->returnToParent($this);
        }
    }
}
