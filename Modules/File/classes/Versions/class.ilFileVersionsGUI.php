<?php

use ILIAS\HTTP\Services;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

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
    
    private ilToolbarGUI $toolbar;
    
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
    
    /**
     * ilFileVersionsGUI constructor.
     * @param ilObjFile $file
     */
    public function __construct(ilObjFile $file)
    {
        global $DIC;
        $this->file       = $file;
        $this->ctrl       = $DIC->ctrl();
        $this->tpl        = $DIC->ui()->mainTemplate();
        $this->tabs       = $DIC->tabs();
        $this->http       = $DIC->http();
        $this->lng        = $DIC->language();
        $this->ref_id     = $this->http->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->toolbar    = $DIC->toolbar();
        $this->access     = $DIC->access();
        $this->wsp_access = new ilWorkspaceAccessHandler();
        $this->version_id = $this->http->wrapper()->query()->has(self::HIST_ID)
            ? $this->http->wrapper()->query()->retrieve(self::HIST_ID, $DIC->refinery()->kindlyTo()->int())
            : null;
    }
    
    public function executeCommand() : void
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
        }
    }
    
    private function index() : void
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
    
    private function addVersion(int $mode = ilFileVersionFormGUI::MODE_ADD) : void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT));
        
        $form = new ilFileVersionFormGUI($this, $mode);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    private function saveVersion(int $mode = ilFileVersionFormGUI::MODE_ADD) : void
    {
        $form = new ilFileVersionFormGUI($this, $mode);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
    
    private function downloadVersion() : void
    {
        try {
            $this->file->sendFile($this->version_id);
        } catch (FileNotFoundException $e) {
        }
    }
    
    private function deleteVersions()
    {
        $version_ids        = $this->getVersionIdsFromRequest();
        $existing_versions  = $this->file->getVersions();
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
            $alt  = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->file->getType());
            
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
                    $a_text         = $version['filename'] ?? $version->getFilename() ?? $this->file->getTitle();
                    $version_string = $version['hist_id'] ?? $version->getVersion();
                    $a_text         .= " (v" . $version_string . ")";
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
    
    private function rollbackVersion() : void
    {
        $version_ids = $this->getVersionIdsFromRequest();
        
        // more than one entry selected?
        if (count($version_ids) != 1) {
            ilUtil::sendInfo($this->lng->txt("file_rollback_select_exact_one"), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        
        // rollback the version
        $this->file->rollback($version_ids[0]);
        
        ilUtil::sendSuccess(sprintf($this->lng->txt("file_rollback_done"), ''), true);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }
    
    private function confirmDeleteVersions() : void
    {
        // delete versions after confirmation
        $versions_to_delete = $this->getVersionIdsFromRequest();
        if (is_array($versions_to_delete) && count($versions_to_delete) > 0) {
            $this->file->deleteVersions($versions_to_delete);
            ilUtil::sendSuccess($this->lng->txt("file_versions_deleted"), true);
        }
        
        $this->ctrl->setParameter($this, self::HIST_ID, "");
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }
    
    private function confirmDeleteFile() : void
    {
        global $DIC;
        
        $parent_id = $DIC->repositoryTree()->getParentId($this->ref_id);
        
        $ru = new ilRepositoryTrashGUI($this);
        $ru->deleteObjects($parent_id, array($this->ref_id));
        
        // redirect to parent object
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $parent_id);
        $this->ctrl->redirectByClass(ilRepositoryGUI::class);
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
        
        array_walk($version_ids, static function (&$i) : void {
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
        array_udiff($versions_to_keep, $version_ids, static function ($v1, $v2) : bool {
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
    private function hasPermission(string $a_permission) : bool
    {
        // determine if the permission check concerns a workspace- or repository-object
        if ($this->http->wrapper()->query()->has('wsp_id')) {
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
