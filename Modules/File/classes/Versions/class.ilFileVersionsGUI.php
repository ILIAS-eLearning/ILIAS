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

use ILIAS\HTTP\Services;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Implementation\Component\Modal\Interruptive;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\ResourceStorage\Revision\RevisionStatus;
use ILIAS\Services\WOPI\Discovery\ActionDBRepository;
use ILIAS\Services\WOPI\Discovery\ActionRepository;
use ILIAS\Services\WOPI\Embed\EmbeddedApplication;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Modal\Modal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @ilCtrl_Calls ilFileVersionsGUI: ilWOPIEmbeddedApplicationGUI
 */
class ilFileVersionsGUI
{
    use ilObjFileCopyrightInput;

    public const KEY_FILE_RID = 'file_rid';
    public const KEY_FILE_EXTRACT = 'file_extract';
    public const KEY_FILE_STRUCTURE = 'file_structure';
    public const KEY_COPYRIGHT_OPTION = "copyright_option";
    public const KEY_INHERIT_COPYRIGHT = "inherit_copyright";
    public const KEY_SELECT_COPYRIGHT = "select_copyright";
    public const KEY_COPYRIGHT_ID = "copyright_id";

    public const CMD_DEFAULT = 'index';
    public const CMD_DELETE_VERSIONS = "deleteVersions";
    public const CMD_ROLLBACK_VERSION = "rollbackVersion";
    public const CMD_DOWNLOAD_VERSION = "sendFile";
    public const HIST_ID = 'hist_id';
    public const CMD_CANCEL_DELETE = "cancelDeleteFile";
    public const CMD_CONFIRMED_DELETE_FILE = "confirmDeleteFile";
    public const CMD_CONFIRMED_DELETE_VERSIONS = 'confirmDeleteVersions';
    public const CMD_ADD_NEW_VERSION = 'addNewVersion';
    public const CMD_CREATE_NEW_VERSION = 'saveVersion';
    public const CMD_ADD_REPLACING_VERSION = 'addReplacingVersion';
    public const CMD_CREATE_REPLACING_VERSION = 'createReplacingVersion';
    public const CMD_UNZIP_CURRENT_REVISION = 'unzipCurrentRevision';
    public const CMD_PROCESS_UNZIP = 'processUnzip';
    public const CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL = 'renderDeleteSelectedVersionsModal';
    public const CMD_PUBLISH = 'publish';
    public const CMD_UNPUBLISH = 'unpublish';

    private ilToolbarGUI $toolbar;
    private \ILIAS\ResourceStorage\Services $storage;
    private ActionRepository $action_repo;
    private ?Revision $current_revision;
    protected \ILIAS\DI\UIServices $ui;
    private ilAccessHandler $access;
    private \ilWorkspaceAccessHandler $wsp_access;
    private int $ref_id;
    protected ilLanguage $lng;
    private Services $http;
    private ilTabsGUI $tabs;
    protected ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilFileServicesSettings $file_service_settings;
    private ilObjFileComponentBuilder $file_component_builder;
    protected ?int $version_id = null;
    protected ilTree $tree;
    protected int $parent_id;
    protected Refinery $refinery;

    /**
     * ilFileVersionsGUI constructor.
     */
    public function __construct(private ilObjFile $file)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->storage = $DIC->resourceStorage();
        $this->file_service_settings = $DIC->fileServiceSettings();
        $this->ui = $DIC->ui();
        $this->tree = $this->isWorkspaceContext() ? new ilWorkspaceTree($DIC->user()->getId()) : $DIC->repositoryTree();
        $this->file_component_builder = new ilObjFileComponentBuilder($this->lng, $this->ui);
        $this->refinery = $DIC->refinery();

        $this->parent_id = $this->tree->getParentId($this->file->getRefId()) ?? $this->getParentIdType();
        $this->wsp_access = new ilWorkspaceAccessHandler($this->tree);
        $this->version_id = $this->http->wrapper()->query()->has(self::HIST_ID)
            ? $this->http->wrapper()->query()->retrieve(self::HIST_ID, $DIC->refinery()->kindlyTo()->int())
            : null;
        $this->action_repo = new ActionDBRepository($DIC->database());
        $this->current_revision = $this->getCurrentFileRevision();
    }



    /**
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    protected function performCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);
        switch ($cmd) {
            case self::CMD_DEFAULT:
                $this->index();
                break;
            case self::CMD_DOWNLOAD_VERSION:
                $this->downloadVersion();
                break;
            case self::CMD_DELETE_VERSIONS:
                $this->deleteVersions();
                break;
            case self::CMD_ROLLBACK_VERSION:
                $this->rollbackVersion();
                break;
            case self::CMD_ADD_NEW_VERSION:
                $this->addVersion(ilFileVersionFormGUI::MODE_ADD);
                break;
            case self::CMD_ADD_REPLACING_VERSION:
                $this->addVersion(ilFileVersionFormGUI::MODE_REPLACE);
                break;
            case self::CMD_CREATE_NEW_VERSION:
                $this->saveVersion(ilFileVersionFormGUI::MODE_ADD);
                // no break
            case self::CMD_CREATE_REPLACING_VERSION:
                $this->saveVersion(ilFileVersionFormGUI::MODE_REPLACE);
                break;
            case self::CMD_CONFIRMED_DELETE_VERSIONS:
                $this->confirmDeleteVersions();
                break;
            case self::CMD_CONFIRMED_DELETE_FILE:
                $this->confirmDeleteFile();
                break;
            case self::CMD_UNZIP_CURRENT_REVISION:
                $this->unzipCurrentRevision();
                break;
            case self::CMD_PROCESS_UNZIP:
                $this->processUnzip();
                break;
            case self::CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL:
                $this->renderDeleteSelectedVersionsModal();
                break;
            case self::CMD_PUBLISH:
                $this->publish();
                break;
            case self::CMD_UNPUBLISH:
                $this->unpublish();
                break;
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function setBackTab(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    public function executeCommand(): void
    {
        // bugfix mantis 26007: use new function hasPermission to ensure that the check also works for workspace files
        if (!$this->hasPermission('write')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilFileVersionsUploadHandlerGUI::class):
                $this->ctrl->forwardCommand(
                    new ilFileVersionsUploadHandlerGUI(
                        $this->file
                    )
                );
                return;
            case strtolower(ilWOPIEmbeddedApplicationGUI::class):
                $action = $this->action_repo->getActionForSuffix(
                    $this->current_revision->getInformation()->getSuffix()
                );

                $embeded_application = new EmbeddedApplication(
                    $this->current_revision->getIdentification(),
                    $action,
                    new ilObjFileStakeholder(),
                    new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT))
                );

                $this->ctrl->forwardCommand(
                    new ilWOPIEmbeddedApplicationGUI(
                        $embeded_application
                    )
                );
                break;
            default:
                $this->performCommand();
                break;
        }
    }

    private function unzipCurrentRevision(): void
    {
        $this->setBackTab();
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->getFileZipOptionsForm()
            )
        );
    }

    private function publish(): void
    {
        $this->storage->manage()->publish($this->getIdentification());
        $this->file->updateObjectFromCurrentRevision();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function unpublish(): void
    {
        if ($this->current_revision->getVersionNumber() === 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_cant_unpublish'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->storage->manage()->unpublish($this->getIdentification());
        $this->file->updateObjectFromCurrentRevision();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function processUnzip(): void
    {
        $form = $this->getFileZipOptionsForm()->withRequest($this->http->request());
        $data = $form->getData();

        if (!empty($data)) {
            $file_rid = $this->storage->manage()->find($data[self::KEY_FILE_RID]);
            if (null !== $file_rid) {
                $copyright_id = $data[self::KEY_COPYRIGHT_OPTION][1][self::KEY_COPYRIGHT_ID] ?? null;
                $processor = $this->getFileProcessor($data[self::KEY_FILE_STRUCTURE]);
                $processor->process($file_rid, null, null, $copyright_id);

                if ($processor->getInvalidFileNames() !== []) {
                    $this->ui->mainTemplate()->setOnScreenMessage(
                        'info',
                        sprintf(
                            $this->lng->txt('file_upload_info_file_with_critical_extension'),
                            implode(', ', $processor->getInvalidFileNames())
                        ),
                        true
                    );
                }

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_unzip_success'), true);
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $this->parent_id);
                $this->ctrl->redirectByClass(ilRepositoryGUI::class);
            }

            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('file_not_found'));
        }

        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->getFileZipOptionsForm()
            )
        );
    }

    private function index(): void
    {
        // Buttons
        $status = $this->current_revision?->getStatus();

        $btn_add_version = $this->ui->factory()->button()->standard(
            $this->lng->txt('file_new_version'),
            $this->ctrl->getLinkTarget($this, self::CMD_ADD_NEW_VERSION)
        );
        if ($status === RevisionStatus::DRAFT) {
            $btn_add_version = $btn_add_version->withUnavailableAction();
        }
        $this->toolbar->addComponent($btn_add_version);

        $btn_replace_version = $this->ui->factory()->button()->standard(
            $this->lng->txt('replace_file'),
            $this->ctrl->getLinkTarget($this, self::CMD_ADD_REPLACING_VERSION)
        );
        if ($status === RevisionStatus::DRAFT) {
            $btn_replace_version = $btn_replace_version->withUnavailableAction();
        }
        $this->toolbar->addComponent($btn_replace_version);

        // only add unzip button if the current revision is a zip.
        if (null !== $this->current_revision &&
            ilObjFileAccess::isZIP($this->current_revision->getInformation()->getMimeType())
        ) {
            $btn_unzip = $this->ui->factory()->button()->standard(
                $this->lng->txt('unzip'),
                $this->ctrl->getLinkTarget($this, self::CMD_UNZIP_CURRENT_REVISION)
            );
            $this->toolbar->addComponent($btn_unzip);
        }

        // Editor
        $suffix = $this->current_revision?->getInformation()?->getSuffix();

        if ($this->action_repo->hasActionForSuffix(
            $this->current_revision->getInformation()->getSuffix()
        )) {
            $external_editor = $this->ui->factory()
                                        ->button()
                                        ->standard(
                                            $this->lng->txt('open_external_editor'),
                                            $this->ctrl->getLinkTargetByClass(
                                                [self::class, ilWOPIEmbeddedApplicationGUI::class],
                                                \ilWOPIEmbeddedApplicationGUI::CMD_INDEX
                                            )
                                        );
            $this->toolbar->addComponent($external_editor);
        }


        // Publish
        if ($status === RevisionStatus::DRAFT) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('file_version_draft_info'));

            $btn_publish = $this->ui->factory()->button()->standard(
                $this->lng->txt('file_publish'),
                $this->ctrl->getLinkTarget($this, self::CMD_PUBLISH)
            );
            $this->toolbar->addComponent($btn_publish);
        }

        $table = new ilFileVersionsTableGUI($this, self::CMD_DEFAULT);
        $this->tpl->setContent($table->getHTML());
    }

    private function addVersion(int $mode = ilFileVersionFormGUI::MODE_ADD): void
    {
        $this->setBackTab();

        $form = new ilFileVersionFormGUI($this, $mode);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    private function saveVersion(int $mode = ilFileVersionFormGUI::MODE_ADD): void
    {
        $form = new ilFileVersionFormGUI($this, $mode);
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $this->tpl->setContent($form->getHTML());
    }

    private function downloadVersion(): void
    {
        try {
            $this->file->sendFile($this->version_id);
        } catch (FileNotFoundException) {
        }
    }

    private function rollbackVersion(): void
    {
        $version_ids = $this->getVersionIdsFromRequest();

        // more than one entry selected?
        if (count($version_ids) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("file_rollback_select_exact_one"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        if($this->current_revision->getStatus() === RevisionStatus::DRAFT) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("file_rollback_rollback_first"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        // rollback the version
        $version_id = $version_ids[0];
        if($version_id === $this->current_revision->getVersionNumber()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("file_rollback_same_version"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->file->rollback($version_id);

        $this->tpl->setOnScreenMessage('success', sprintf($this->lng->txt("file_rollback_done"), ''), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function confirmDeleteVersions(): void
    {
        // delete versions after confirmation
        $versions_to_delete = $this->getVersionIdsFromRequest();
        if (is_array($versions_to_delete) && $versions_to_delete !== []) {
            $this->file->deleteVersions($versions_to_delete);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("file_versions_deleted"), true);
        }

        $this->ctrl->setParameter($this, self::HIST_ID, "");
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function confirmDeleteFile(): void
    {
        $parent_id = $this->tree->getParentId($this->ref_id);

        ilRepUtil::deleteObjects($parent_id, [$this->ref_id]);

        // redirect to parent object
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $parent_id);
        $this->ctrl->redirectByClass(ilRepositoryGUI::class);
    }

    public function getFile(): ilObjFile
    {
        return $this->file;
    }

    private function getVersionIdsFromRequest(): array
    {
        if ('GET' === $this->http->request()->getMethod() &&
            $this->http->wrapper()->query()->has(self::HIST_ID)
        ) {
            return [
                $this->http->wrapper()->query()->retrieve(self::HIST_ID, $this->refinery->kindlyTo()->int()),
            ];
        }

        /** in case request is triggered by @see self::CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL */
        if ($this->http->wrapper()->post()->has('interruptive_items')) {
            return $this->http->wrapper()->post()->retrieve(
                'interruptive_items',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if ($this->http->wrapper()->post()->has(self::HIST_ID)) {
            return $this->http->wrapper()->post()->retrieve(
                self::HIST_ID,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        return [];
    }

    private function getVersionsToKeep(array $version_ids): array
    {
        $versions_to_keep = $this->file->getVersions();
        array_udiff($versions_to_keep, $version_ids, static function ($v1, $v2): bool {
            if (is_array($v1) || $v1 instanceof ilObjFileVersion) {
                $v1 = (int) $v1["hist_entry_id"];
            } elseif (!is_numeric($v1)) {
                $v1 = (int) $v1;
            }

            if (is_array($v2) || $v2 instanceof ilObjFileVersion) {
                $v2 = (int) $v2["hist_entry_id"];
            } elseif (!is_numeric($v2)) {
                $v2 = (int) $v2;
            }

            return $v1 === $v2;
        });

        return $versions_to_keep;
    }

    /**
     * bugfix mantis 26007:
     * this function was created to ensure that the access check not only works for repository objects
     * but for workspace objects too
     */
    private function hasPermission(string $a_permission): bool
    {
        // determine if the permission check concerns a workspace- or repository-object
        if ($this->isWorkspaceContext()) {
            // permission-check concerning a workspace object
            if ($this->wsp_access->checkAccess($a_permission, "", $this->ref_id)) {
                return true;
            }
        } elseif ($this->access->checkAccess($a_permission, '', $this->ref_id)) {
            // permission-check concerning a repository object
            return true;
        }

        return false;
    }

    protected function renderDeleteSelectedVersionsModal(): void
    {
        $delete_selected_versions_modal = $this->getDeleteSelectedVersionsModal();

        $this->http->saveResponse(
            $this->http->response()->withBody(
                \ILIAS\Filesystem\Stream\Streams::ofString(
                    (null !== $delete_selected_versions_modal) ?
                        $this->ui->renderer()->renderAsync([$delete_selected_versions_modal]) :
                        ''
                )
            )->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    protected function getDeleteSelectedVersionsModal(): ?Interruptive
    {
        $deletion_version_ids = $this->getVersionIdsFromRequest();
        $existing_versions = $this->file->getVersions();

        $non_deletion_versions = array_udiff(
            $existing_versions,
            $deletion_version_ids,
            static function ($a, $b) {
                if ($a instanceof ilObjFileVersion) {
                    $a = $a->getHistEntryId();
                }
                if ($b instanceof ilObjFileVersion) {
                    $b = $b->getHistEntryId();
                }
                return $a - $b;
            }
        );
        $this->checkSanityOfDeletionRequest($deletion_version_ids, false);

        // no version will remain after deletion, so we can delete the whole file
        if (count($non_deletion_versions) < 1) {
            return $this->file_component_builder->buildConfirmDeleteAllVersionsModal(
                $this->ctrl->getFormActionByClass(self::class, self::CMD_CONFIRMED_DELETE_FILE),
                $this->file
            );
        }

        // confirm the deletion of the selected versions
        if (count($non_deletion_versions) >= 1) {
            return $this->file_component_builder->buildConfirmDeleteSpecificVersionsModal(
                $this->ctrl->getFormActionByClass(self::class, self::CMD_CONFIRMED_DELETE_VERSIONS),
                $this->file,
                $deletion_version_ids
            );
        }
        return null;
    }

    protected function checkSanityOfDeletionRequest(array $requested_deletion_version, bool $redirect): void
    {
        // Check Sanity of request
        // Cant delete version if current is a DRAFT
        if (
            $this->current_revision->getStatus() === RevisionStatus::DRAFT
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('publish_before_delete'), $redirect);
            if($redirect) {
                $this->ctrl->redirect($this, self::CMD_DEFAULT);
            }
        }

        // no checkbox has been selected
        if (count($requested_deletion_version) < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), $redirect);
            if($redirect) {
                $this->ctrl->redirect($this, self::CMD_DEFAULT);
            }
        }
    }

    //TODO: Remove this function and replace its calls with calls to "getDeleteSelectedVersionsModal" as soon as the new table gui is introduced.
    // This function and its deprecated ilConfirmationGUI are only needed because the old ilTable2GUI doesn't support calling modals from its MultiCommands
    private function deleteVersions(): void
    {
        $version_ids = $this->getVersionIdsFromRequest();
        $existing_versions = $this->file->getVersions();
        $remaining_versions = array_udiff(
            $existing_versions,
            $version_ids,
            static function ($a, $b) {
                if ($a instanceof ilObjFileVersion) {
                    $a = $a->getHistEntryId();
                }
                if ($b instanceof ilObjFileVersion) {
                    $b = $b->getHistEntryId();
                }
                return $a - $b;
            }
        );

        $this->checkSanityOfDeletionRequest($version_ids, true);

        $conf_gui = new ilConfirmationGUI();
        $conf_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_DEFAULT));
        $conf_gui->setCancel($this->lng->txt("cancel"), self::CMD_DEFAULT);

        $icon = ilObject::_getIcon($this->file->getId(), "small", $this->file->getType());
        $alt = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->file->getType());

        // only one version left, delete the whole file
        if (count($remaining_versions) < 1) {
            // Ask
            $conf_gui->setHeaderText($this->lng->txt('file_confirm_delete_all_versions'));
            $conf_gui->setConfirm($this->lng->txt("confirm"), self::CMD_CONFIRMED_DELETE_FILE);
            $conf_gui->addItem(
                "id[]",
                $this->ref_id,
                $this->file->getTitle(),
                $icon,
                $alt
            );
        } else {
            // Ask to delete version
            $conf_gui->setHeaderText($this->lng->txt('file_confirm_delete_versions'));
            $conf_gui->setConfirm($this->lng->txt("confirm"), self::CMD_CONFIRMED_DELETE_VERSIONS);

            foreach ($this->file->getVersions($version_ids) as $version) {
                $a_text = $version['filename'] ?? $version->getFilename() ?? $this->file->getTitle();
                $version_string = $version['hist_id'] ?? $version->getVersion();
                $a_text .= " (v" . $version_string . ")";
                $conf_gui->addItem(
                    "hist_id[]",
                    $version['hist_entry_id'],
                    $a_text,
                    $icon,
                    $alt
                );
            }
        }

        $this->tpl->setContent($conf_gui->getHTML());
    }

    private function getFileZipOptionsForm(): Form
    {
        $inputs = [];
        $copyright_options = [];
        $form_action = $this->ctrl->getFormActionByClass(self::class, self::CMD_PROCESS_UNZIP);

        $inputs[self::KEY_FILE_RID] = $this->ui->factory()->input()->field()->hidden()->withValue(
            $this->file->getResourceId()
        );
        $inputs[self::KEY_FILE_STRUCTURE] = $this->ui->factory()->input()->field()->checkbox(
            $this->lng->txt('take_over_structure'),
            $this->lng->txt('take_over_structure_info'),
        );

        // return form at this point if copyright selection is not enabled
        if (!ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
            return $this->ui->factory()->input()->container()->form()->standard($form_action, $inputs);
        }

        // add the option for letting all unzipped files inherit the copyright of their parent zip (if a copyright has been set for the zip)
        $zip_md = new ilMD($this->file->getId(), 0, $this->file->getType());
        $rights = $zip_md->getRights();
        if ($rights !== null) {
            $zip_copyright_description = $zip_md->getRights()->getDescription();
            $zip_copyright_id = ilMDCopyrightSelectionEntry::_extractEntryId($zip_copyright_description);
            $copyright_inheritance_input = $this->ui->factory()->input()->field()->hidden()->withValue(
                (string) $zip_copyright_id
            );
            $copyright_options[self::KEY_INHERIT_COPYRIGHT] = $this->ui->factory()->input()->field()->group(
                [self::KEY_COPYRIGHT_ID => $copyright_inheritance_input],
                $this->lng->txt("copyright_inherited"),
                sprintf(
                    $this->lng->txt("copyright_inherited_info"),
                    ilMDCopyrightSelectionEntry::lookupCopyyrightTitle($zip_copyright_description)
                )
            );
        }

        // add the option to collectively select the copyright for all unzipped files independent of the original copyright of the zip
        $copyright_selection_input = $this->getCopyrightSelectionInput('set_license_for_all_files');
        $copyright_options[self::KEY_SELECT_COPYRIGHT] = $this->ui->factory()->input()->field()->group(
            [self::KEY_COPYRIGHT_ID => $copyright_selection_input],
            $this->lng->txt("copyright_custom"),
            $this->lng->txt("copyright_custom_info")
        );

        $inputs[self::KEY_COPYRIGHT_OPTION] = $this->ui->factory()->input()->field()->switchableGroup(
            $copyright_options,
            $this->lng->txt("md_copyright")
        )->withValue(self::KEY_SELECT_COPYRIGHT);

        return $this->ui->factory()->input()->container()->form()->standard($form_action, $inputs);
    }

    private function getFileProcessor(bool $keep_structure): ilObjFileProcessorInterface
    {
        $context = $this->getParentIdType();

        if ($keep_structure) {
            return new ilObjFileUnzipRecursiveProcessor(
                new ilObjFileStakeholder(),
                new ilObjFileGUI(
                    $this->file->getId(),
                    $context,
                    $this->parent_id
                ),
                $this->storage,
                $this->file_service_settings,
                $this->tree
            );
        }

        return new ilObjFileUnzipFlatProcessor(
            new ilObjFileStakeholder(),
            new ilObjFileGUI(
                $this->file->getId(),
                $context,
                $this->parent_id
            ),
            $this->storage,
            $this->file_service_settings,
            $this->tree
        );
    }

    private function getIdentification(): ?\ILIAS\ResourceStorage\Identification\ResourceIdentification
    {
        return $this->storage->manage()->find($this->file->getResourceId());
    }

    private function getCurrentFileRevision(): ?Revision
    {
        $file_rid = $this->getIdentification();
        if (null !== $file_rid) {
            return $this->storage->manage()->getCurrentRevisionIncludingDraft($file_rid);
        }

        return null;
    }

    private function getParentIdType(): int
    {
        return ($this->isWorkspaceContext()) ?
            ilObject2GUI::WORKSPACE_NODE_ID :
            ilObject2GUI::REPOSITORY_NODE_ID;
    }

    private function isWorkspaceContext(): bool
    {
        return $this->http->wrapper()->query()->has('wsp_id');
    }

    protected function getUIFactory(): ILIAS\UI\Factory
    {
        return $this->ui->factory();
    }

    protected function getLanguage(): \ilLanguage
    {
        return $this->lng;
    }
}
