<?php

use ILIAS\DI\HTTPServices;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * Class ilFileVersionsGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionsGUI
{
    const CMD_DEFAULT = 'index';
    const CMD_DELETE_VERSIONS = "deleteVersions";
    const CMD_ROLLBACK_VERSION = "rollbackVersion";
    const CMD_DOWNLOAD_VERSION = "sendFile";
    const HIST_ID = 'hist_id';
    const CMD_CANCEL_DELETE = "cancelDeleteFile";
    const CMD_CONFIRMED_DELETE_FILE = "confirmDeleteFile";
    const CMD_CONFIRMED_DELETE_VERSIONS = 'confirmDeleteVersions';
    const CMD_ADD_NEW_VERSION = 'addNewVersion';
    const CMD_CREATE_NEW_VERSION = 'saveVersion';
    const CMD_ADD_REPLACING_VERSION = 'addReplacingVersion';
    const CMD_CREATE_REPLACING_VERSION = 'createReplacingVersion';
    const CMD_MIGRATE = 'migrate';
    /**
     * @var ilToolbarGUI
     */
    private $toolbar;
    /**
     * @var ilAccessHandler
     */
    private $access;
    /**
     * @var ilWorkspaceAccessHandler
     */
    private $wsp_access;
    /**
     * @var int
     */
    private $ref_id;
    /**
     * @var ilLanguage
     */
    private $lng;
    /**
     * @var HTTPServices
     */
    private $http;
    /**
     * @var ilTabsGUI
     */
    private $tabs;
    /**
     * @var ilCtrl
     */
    private $ctrl;
    /**
     * @var ilTemplate
     */
    private $tpl;
    /**
     * @var ilObjFile
     */
    private $file;
    /**
     * @var bool
     */
    protected $has_been_migrated = false;

    /**
     * ilFileVersionsGUI constructor.
     * @param ilObjFile $file
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
        $this->ref_id = (int) $this->http->request()->getQueryParams()['ref_id'];
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->wsp_access = new ilWorkspaceAccessHandler();
        $this->has_been_migrated = !empty($file->getResourceId());
    }

    public function executeCommand()
    {
        // bugfix mantis 26007: use new function hasPermission to ensure that the check also works for workspace files
        if (!$this->hasPermission('write')) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }
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
            case self::CMD_MIGRATE:
                $this->migrate();
                break;
        }
    }

    private function index()
    {
        // Buttons
        if ($this->has_been_migrated) {
            $add_version = ilLinkButton::getInstance();
            $add_version->setCaption('file_new_version');
            $add_version->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_NEW_VERSION));
            $this->toolbar->addButtonInstance($add_version);

            $replace_version = ilLinkButton::getInstance();
            $replace_version->setCaption('replace_file');
            $replace_version->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_REPLACING_VERSION));
            $this->toolbar->addButtonInstance($replace_version);
        } else {
            $migrate = ilLinkButton::getInstance();
            $migrate->setCaption('migrate');
            $migrate->setUrl($this->ctrl->getLinkTarget($this, self::CMD_MIGRATE));
//            $this->toolbar->addButtonInstance($migrate);
            ilUtil::sendInfo($this->lng->txt('not_yet_migrated'));
        }

        $table = new ilFileVersionsTableGUI($this, self::CMD_DEFAULT);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * @param int $mode
     */
    private function addVersion($mode = ilFileVersionFormGUI::MODE_ADD)
    {
        if (!$this->has_been_migrated) {
            return;
        }
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT));

        $form = new ilFileVersionFormGUI($this, $mode);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param int $mode
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    private function saveVersion($mode = ilFileVersionFormGUI::MODE_ADD)
    {
        if (!$this->has_been_migrated) {
            return;
        }
        $form = new ilFileVersionFormGUI($this, $mode);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    private function downloadVersion()
    {
        $version = (int) $_GET[self::HIST_ID];
        $this->file->sendFile($version);
        try {
        } catch (FileNotFoundException $e) {
        }
    }

    private function deleteVersions()
    {
        if (!$this->has_been_migrated) {
            return;
        }

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
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        } else {
            $conf_gui = new ilConfirmationGUI();
            $conf_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_DEFAULT));
            $conf_gui->setCancel($this->lng->txt("cancel"), self::CMD_DEFAULT);

            $icon = ilObject::_getIcon($this->file->getId(), "small", $this->file->getType());
            $alt = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->file->getType());

            if (count($remaining_versions) < 1) {
                // Ask
                ilUtil::sendQuestion($this->lng->txt("file_confirm_delete_all_versions"));

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
                ilUtil::sendQuestion($this->lng->txt("file_confirm_delete_versions"));

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

    private function rollbackVersion()
    {
        if (!$this->has_been_migrated) {
            return;
        }
        $version_ids = $this->getVersionIdsFromRequest();

        // more than one entry selected?
        if (count($version_ids) != 1) {
            ilUtil::sendInfo($this->lng->txt("file_rollback_select_exact_one"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        // rollback the version
        $new_version = $this->file->rollback($version_ids[0]);

        ilUtil::sendSuccess(sprintf($this->lng->txt("file_rollback_done"), $new_version["rollback_version"]), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function confirmDeleteVersions()
    {
        if (!$this->has_been_migrated) {
            return;
        }
        // delete versions after confirmation
        $versions_to_delete = $this->getVersionIdsFromRequest();
        if (is_array($versions_to_delete) && count($versions_to_delete) > 0) {
            $this->file->deleteVersions($versions_to_delete);
            ilUtil::sendSuccess($this->lng->txt("file_versions_deleted"), true);
        }

        $this->ctrl->setParameter($this, self::HIST_ID, "");
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    private function confirmDeleteFile()
    {
        if (!$this->has_been_migrated) {
            return;
        }
        global $DIC;

        $parent_id = $DIC->repositoryTree()->getParentId($this->ref_id);

        $ru = new ilRepUtilGUI($this);
        $ru->deleteObjects($parent_id, array($this->ref_id));

        // redirect to parent object
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $parent_id);
        $this->ctrl->redirectByClass(ilRepositoryGUI::class);
    }

    private function migrate() : void
    {
        global $DIC;
        $migration = new ilFileObjectToStorageMigrationRunner(
            $DIC->fileSystem()->storage(),
            $DIC->database(),
            rtrim(CLIENT_DATA_DIR, "/") . '/ilFile/migration_log.csv'
        );
        $migration->migrate(new ilFileObjectToStorageDirectory($this->file->getId(), $this->file->getDirectory()));
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    /**
     * @return ilObjFile
     */
    public function getFile() : ilObjFile
    {
        return $this->file;
    }

    /**
     * @return array
     */
    private function getVersionIdsFromRequest() : array
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

        array_walk($version_ids, static function (&$i) {
            $i = (int) $i;
        });

        return $version_ids;
    }

    /**
     * @param array $version_ids
     * @return array
     */
    private function getVersionsToKeep(array $version_ids) : array
    {
        $versions_to_keep = $this->file->getVersions();
        array_udiff($versions_to_keep, $version_ids, static function ($v1, $v2) {
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
     * @param string $a_permission
     * @return bool
     */
    private function hasPermission($a_permission)
    {
        // determine if the permission check concerns a workspace- or repository-object
        if (isset($_GET['wsp_id'])) {
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
}
