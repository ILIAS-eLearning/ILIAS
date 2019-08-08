<?php

use ILIAS\DI\HTTPServices;

/**
 * Class ilFileVersionsGUI
 *
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
    const CMD_CONFIRM_DELETE_FILE = "confirmDeleteFile";
    const CMD_CONFIRM_DELETE_VERSIONS = 'confirmDeleteVersions';
    const CMD_ADD_NEW_VERSION = 'addNewVersion';
    const CMD_CREATE_NEW_VERSION = 'saveVersion';
    const CMD_ADD_REPLACING_VERSION = 'addReplacingVersion';
    const CMD_CREATE_REPLACING_VERSION = 'createReplacingVersion';
    /**
     * @var ilToolbarGUI
     */
    private $toolbar;
    /**
     * @var ilAccessHandler
     */
    private $access;
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
     * ilFileVersionsGUI constructor.
     *
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
    }


    public function executeCommand()
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
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
            case self::CMD_CREATE_REPLACING_VERSION:
                $this->saveVersion(ilFileVersionFormGUI::MODE_REPLACE);
                break;
        }
    }


    private function index()
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

        $table = new ilFileVersionsTableGUI($this, self::CMD_DEFAULT);
        $this->tpl->setContent($table->getHTML());
    }


    /**
     * @param int $mode
     */
    private function addVersion($mode = ilFileVersionFormGUI::MODE_ADD)
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT));

        $form = new ilFileVersionFormGUI($this, $mode);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * @param int $mode
     *
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    private function saveVersion($mode = ilFileVersionFormGUI::MODE_ADD)
    {
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
    }


    private function deleteVersions()
    {
        $version_ids = $this->getVersionIdsFromRequest();

        if (count($version_ids) < 1) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "versions");
        } else {
            // check if all versions are selected
            $versions_to_keep = $this->getVersionsToKeep($version_ids);

            $conf_gui = new ilConfirmationGUI();
            $conf_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_DEFAULT));
            $conf_gui->setCancel($this->lng->txt("cancel"), self::CMD_CANCEL_DELETE);

            $icon = ilObject::_getIcon($this->file->getId(), "small", $this->file->getType());
            $alt = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->file->getType());

            if (count($versions_to_keep) < 1) {
                // Ask
                ilUtil::sendQuestion($this->lng->txt("file_confirm_delete_all_versions"));

                $conf_gui->setConfirm($this->lng->txt("confirm"), self::CMD_CONFIRM_DELETE_FILE);
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

                $conf_gui->setConfirm($this->lng->txt("confirm"), self::CMD_CONFIRM_DELETE_VERSIONS);

                /**
                 * array (
                 * 'date' => '2019-07-25 11:19:51',
                 * 'user_id' => '6',
                 * 'obj_id' => '287',
                 * 'obj_type' => 'file',
                 * 'action' => 'create',
                 * 'info_params' => 'chicken_outlined.pdf,1,1',
                 * 'user_comment' => '',
                 * 'hist_entry_id' => '3',
                 * 'title' => NULL,
                 * )
                 */
                foreach ($this->file->getVersions($version_ids) as $version) {
                    $conf_gui->addItem(
                        "hist_id[]",
                        $version['hist_entry_id'],
                        $version['title'] ?? $this->file->getTitle(),
                        $icon,
                        $alt);
                }
            }

            $this->tpl->setContent($conf_gui->getHTML());
        }
    }


    private function rollbackVersion()
    {
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

        return $version_ids;
    }


    /**
     * @param array $version_ids
     *
     * @return array
     */
    private function getVersionsToKeep(array $version_ids) : array
    {
        $versions_to_keep = array_udiff($this->file->getVersions(), $version_ids, function ($v1, $v2) {
            if (is_array($v1)) {
                $v1 = (int) $v1["hist_entry_id"];
            } else {
                if (!is_int($v1)) {
                    $v1 = (int) $v1;
                }
            }

            if (is_array($v2)) {
                $v2 = (int) $v2["hist_entry_id"];
            } else {
                if (!is_int($v2)) {
                    $v2 = (int) $v2;
                }
            }

            return $v1 - $v2;
        });

        return $versions_to_keep;
    }
}
