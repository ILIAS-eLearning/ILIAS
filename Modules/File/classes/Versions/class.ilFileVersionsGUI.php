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

/**
 * Class ilFileVersionsGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionsGUI
{
    public const KEY_FILE_RID = 'file_rid';
    public const KEY_FILE_EXTRACT = 'file_extract';
    public const KEY_FILE_STRUCTURE = 'file_structure';

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

    private ilToolbarGUI $toolbar;
    private \ILIAS\ResourceStorage\Services $storage;
    private \ILIAS\DI\UIServices $ui;
    private ilAccessHandler $access;
    private \ilWorkspaceAccessHandler $wsp_access;
    private int $ref_id;
    private ilLanguage $lng;
    private Services $http;
    private ilTabsGUI $tabs;
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private \ilObjFile $file;
    protected ?int $version_id = null;
    protected ilTree $tree;
    protected int $parent_id;

    /**
     * ilFileVersionsGUI constructor.
     */
    public function __construct(ilObjFile $file)
    {
        global $DIC;
        $this->file = $file;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->storage = $DIC->resourceStorage();
        $this->ui = $DIC->ui();
        if ($this->isWorkspaceContext()) {
            $this->tree = new ilWorkspaceTree($DIC->user()->getId());
        } else {
            $this->tree = $DIC->repositoryTree();
        }

        $this->parent_id = $this->tree->getParentId($this->file->getRefId()) ?? $this->getParentIdType();
        $this->wsp_access = new ilWorkspaceAccessHandler($this->tree);
        $this->version_id = $this->http->wrapper()->query()->has(self::HIST_ID)
            ? $this->http->wrapper()->query()->retrieve(self::HIST_ID, $DIC->refinery()->kindlyTo()->int())
            : null;
    }

    /**
     * @return void
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
        }
    }

    /**
     * @return void
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

    private function processUnzip(): void
    {
        $form = $this->getFileZipOptionsForm()->withRequest($this->http->request());
        $data = $form->getData();

        if (!empty($data)) {
            $file_rid = $this->storage->manage()->find($data[self::KEY_FILE_RID]);
            if (null !== $file_rid) {
                $processor = $this->getFileProcessor($data[self::KEY_FILE_STRUCTURE]);
                $processor->process($file_rid);

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
        $add_version = ilLinkButton::getInstance();
        $add_version->setCaption('file_new_version');
        $add_version->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_NEW_VERSION));
        $this->toolbar->addButtonInstance($add_version);

        $replace_version = ilLinkButton::getInstance();
        $replace_version->setCaption('replace_file');
        $replace_version->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_REPLACING_VERSION));
        $this->toolbar->addButtonInstance($replace_version);

        $current_file_revision = $this->getCurrentFileRevision();

        // only add unzip button if the current revision is a zip.
        if (null !== $current_file_revision &&
            ilObjFileAccess::isZIP($current_file_revision->getInformation()->getMimeType())
        ) {
            $unzip_button = ilLinkButton::getInstance();
            $unzip_button->setCaption($this->lng->txt('unzip'), false);
            $unzip_button->setUrl(
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    self::CMD_UNZIP_CURRENT_REVISION
                )
            );

            $this->toolbar->addButtonInstance($unzip_button);
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
        } catch (FileNotFoundException $e) {
        }
    }

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

        if (count($version_ids) < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        } else {
            $conf_gui = new ilConfirmationGUI();
            $conf_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_DEFAULT));
            $conf_gui->setCancel($this->lng->txt("cancel"), self::CMD_DEFAULT);

            $icon = ilObject::_getIcon($this->file->getId(), "small", $this->file->getType());
            $alt = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->file->getType());

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
                // Ask
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
    }

    private function rollbackVersion(): void
    {
        $version_ids = $this->getVersionIdsFromRequest();

        // more than one entry selected?
        if (count($version_ids) != 1) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("file_rollback_select_exact_one"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        // rollback the version
        $this->file->rollback($version_ids[0]);

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
        // get ids either from GET (if single item was clicked) or
        // from POST (if multiple items were selected)
        $request = $this->http->request();

        $version_ids = [];
        if (isset($request->getQueryParams()[self::HIST_ID])) {
            $version_ids = [$request->getQueryParams()[self::HIST_ID]];
        } elseif (isset($request->getParsedBody()[self::HIST_ID])) {
            $version_ids = (array) $request->getParsedBody()[self::HIST_ID];
        }

        array_walk($version_ids, static function (&$i): void {
            $i = (int) $i;
        });

        return $version_ids;
    }

    /**
     * @param array $version_ids
     * @return array
     */
    private function getVersionsToKeep(array $version_ids): array
    {
        $versions_to_keep = $this->file->getVersions();
        array_udiff($versions_to_keep, $version_ids, static function ($v1, $v2): bool {
            if (is_array($v1) || $v1 instanceof ilObjFileVersion) {
                $v1 = (int) $v1["hist_entry_id"];
            } else {
                if (!is_numeric($v1)) {
                    $v1 = (int) $v1;
                }
            }

            if (is_array($v2) || $v2 instanceof ilObjFileVersion) {
                $v2 = (int) $v2["hist_entry_id"];
            } else {
                if (!is_numeric($v2)) {
                    $v2 = (int) $v2;
                }
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
        } else {
            // permission-check concerning a repository object
            if ($this->access->checkAccess($a_permission, '', $this->ref_id)) {
                return true;
            }
        }

        return false;
    }

    private function getFileZipOptionsForm(): Form
    {
        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_PROCESS_UNZIP),
            [
                self::KEY_FILE_RID => $this->ui->factory()->input()->field()->hidden()->withValue($this->file->getResourceId()),
                self::KEY_FILE_STRUCTURE => $this->ui->factory()->input()->field()->checkbox(
                    $this->lng->txt('take_over_structure'),
                    $this->lng->txt('take_over_structure_info'),
                ),
            ]
        );
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
            $this->tree
        );
    }

    private function getCurrentFileRevision(): ?Revision
    {
        $file_rid = $this->storage->manage()->find($this->file->getResourceId());
        if (null !== $file_rid) {
            return $this->storage->manage()->getCurrentRevision($file_rid);
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
}
