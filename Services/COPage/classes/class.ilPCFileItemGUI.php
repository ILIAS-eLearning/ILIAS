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

/**
 * Class ilPCFileItemGUI
 * Handles user commands on items of file lists
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileItemGUI extends ilPageContentGUI
{
    protected ilObjFile $file_object;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilSetting $settings;


    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->tpl = $DIC["tpl"];
        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * insert new file item
     */
    public function newFileItem() : bool
    {
        $lng = $this->lng;
        
        if ($_FILES["file"]["name"] == "") {
            throw new ilCOPageFileHandlingException($lng->txt("upload_error_file_not_found"));
        }

        $form = $this->initAddFileForm();
        $form->checkInput();

        $fileObj = new ilObjFile();
        $fileObj->setType("file");
        $fileObj->setTitle($_FILES["file"]["name"]);
        $fileObj->setDescription("");
        $fileObj->setFileName($_FILES["file"]["name"]);
        $fileObj->setMode("filelist");
        $fileObj->create();
        // upload file to filesystem
        global $DIC;
        $upload = $DIC->upload();
        if ($upload->hasBeenProcessed() !== true) {
            $upload->process();
        }
        $fileObj->getUploadFile(
            $_FILES["file"]["tmp_name"],
            $_FILES["file"]["name"]
        );

        $this->file_object = $fileObj;
        return true;
    }


    /**
     * insert new list item after current one
     */
    public function newItemAfter() : void
    {
        $ilTabs = $this->tabs;

        $sub_command = $this->sub_command;

        if (in_array($sub_command, ["insertNew", "insertFromRepository", "insertFromWorkspace"])) {
            $this->edit_repo->setSubCmd($sub_command);
        }

        if (($sub_command == "") && $this->edit_repo->getSubCmd() != "") {
            $sub_command = $this->edit_repo->getSubCmd();
        }

        switch ($sub_command) {
            case "insertFromWorkspace":
                $this->insertFromWorkspace("newItemAfter");
                break;
            
            case "insertFromRepository":
                $this->insertFromRepository("newItemAfter");
                break;
                
            case "selectFile":
                $this->insertNewItemAfter(
                    $this->request->getInt("file_ref_id")
                );
                break;
                
            default:
                $this->setTabs("newItemAfter");
                $ilTabs->setSubTabActive("cont_new_file");
        
                $this->displayValidationError();
                $form = $this->initAddFileForm(false);
                $this->tpl->setContent($form->getHTML());
                break;
        }
    }

    /**
     * Init add file form
     */
    public function initAddFileForm(bool $a_before = true) : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        
        // file
        $fi = new ilFileInputGUI($lng->txt("file"), "file");
        $fi->setRequired(true);
        $form->addItem($fi);
        
        if ($a_before) {
            $form->addCommandButton("insertNewItemBefore", $lng->txt("save"));
        } else {
            $form->addCommandButton("insertNewItemAfter", $lng->txt("save"));
        }
        $form->addCommandButton("cancelAddFile", $lng->txt("cancel"));
        
        $form->setTitle($lng->txt("cont_insert_file_item"));

        $form->setFormAction($ilCtrl->getFormAction($this));
     
        return $form;
    }

    
    /**
     * Insert file from repository
     */
    public function insertFromRepository(string $a_cmd) : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->setTabs($a_cmd);
        $ilTabs->setSubTabActive("cont_file_from_repository");
        $ilCtrl->setParameter($this, "subCmd", "insertFromRepository");

        $exp = new ilPCFileItemFileSelectorGUI(
            $this,
            $a_cmd,
            $this,
            $a_cmd,
            "file_ref_id"
        );
        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }
    
    /**
     * Insert file from personal workspace
     */
    public function insertFromWorkspace(string $a_cmd = "insert") : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->setTabs($a_cmd);
        $ilTabs->setSubTabActive("cont_file_from_workspace");
        
        $exp = new ilWorkspaceExplorerGUI($this->user->getId(), $this, $a_cmd, $this, $a_cmd, "fl_wsp_id");
        $ilCtrl->setParameter($this, "subCmd", "selectFile");
        $exp->setCustomLinkTarget($ilCtrl->getLinkTarget($this, $a_cmd));
        $ilCtrl->setParameter($this, "subCmd", "insertFromWorkspace");
        $exp->setTypeWhiteList(array("wsrt", "wfld", "file"));
        $exp->setSelectableTypes(array("file"));
        if ($exp->handleCommand()) {
            return;
        }
        $tpl->setContent($exp->getHTML());
    }

    /**
     * insert new file item after another item
     */
    public function insertNewItemAfter(int $a_file_ref_id = 0) : void
    {
        $ilUser = $this->user;

        $fl_wsp_id = $this->request->getInt("fl_wsp_id");

        $res = true;
        if ($fl_wsp_id > 0) {
            // we need the object id for the instance
            $tree = new ilWorkspaceTree($ilUser->getId());
            $node = $tree->getNodeData($fl_wsp_id);
            
            $this->file_object = new ilObjFile($node["obj_id"], false);
        } elseif ($a_file_ref_id == 0) {
            $res = $this->newFileItem();
        } else {
            $this->file_object = new ilObjFile($a_file_ref_id);
        }
        if ($res) {
            $this->content_obj->newItemAfter(
                $this->file_object->getId(),
                $this->file_object->getTitle(),
                $this->file_object->getFileType()
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }
        
        $this->newItemAfter();
    }

    /**
     * insert new list item before current one
     */
    public function newItemBefore() : void
    {
        $ilTabs = $this->tabs;

        $sub_command = $this->sub_command;

        if (in_array($sub_command, ["insertNew", "insertFromRepository", "insertFromWorkspace"])) {
            $this->edit_repo->setSubCmd($sub_command);
        }

        if (($sub_command == "") && $this->edit_repo->getSubCmd() != "") {
            $sub_command = $this->edit_repo->getSubCmd();
        }

        switch ($sub_command) {
            case "insertFromWorkspace":
                $this->insertFromWorkspace("newItemBefore");
                break;
            
            case "insertFromRepository":
                $this->insertFromRepository("newItemBefore");
                break;
                
            case "selectFile":
                $this->insertNewItemBefore(
                    $this->request->getInt("file_ref_id")
                );
                break;
                
            default:
                $this->setTabs("newItemBefore");
                $ilTabs->setSubTabActive("cont_new_file");
        
                $this->displayValidationError();
                $form = $this->initAddFileForm(true);
                $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * insert new list item before current one
     */
    public function insertNewItemBefore(int $a_file_ref_id = 0) : void
    {
        $ilUser = $this->user;
        
        $res = true;

        $fl_wsp_id = $this->request->getInt("fl_wsp_id");
        if ($fl_wsp_id > 0) {
            // we need the object id for the instance
            $tree = new ilWorkspaceTree($ilUser->getId());
            $node = $tree->getNodeData($fl_wsp_id);
            
            $this->file_object = new ilObjFile($node["obj_id"], false);
        } elseif ($a_file_ref_id == 0) {
            $res = $this->newFileItem();
        } else {
            $this->file_object = new ilObjFile($a_file_ref_id);
        }
        if ($res) {
            $this->content_obj->newItemBefore(
                $this->file_object->getId(),
                $this->file_object->getTitle(),
                $this->file_object->getFileType()
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->newItemBefore();
    }

    /**
     * delete a list item
     */
    public function deleteItem() : void
    {
        $this->content_obj->deleteItem();
        $this->updateAndReturn();
    }

    /**
     * output tabs
     */
    public function setTabs(string $a_cmd = "") : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $ilTabs->addTarget(
            "cont_back",
            $this->ctrl->getParentReturn($this),
            "",
            ""
        );
            
        if ($a_cmd != "") {
            $ilCtrl->setParameter($this, "subCmd", "insertNew");
            $ilTabs->addSubTabTarget(
                "cont_new_file",
                $ilCtrl->getLinkTarget($this, $a_cmd),
                $a_cmd
            );
    
            $ilCtrl->setParameter($this, "subCmd", "insertFromRepository");
            $ilTabs->addSubTabTarget(
                "cont_file_from_repository",
                $ilCtrl->getLinkTarget($this, $a_cmd),
                $a_cmd
            );
            $ilCtrl->setParameter($this, "subCmd", "");
            
            if (!$ilSetting->get("disable_personal_workspace") &&
                !$ilSetting->get("disable_wsp_files")) {
                $ilCtrl->setParameter($this, "subCmd", "insertFromWorkspace");
                $ilTabs->addSubTabTarget(
                    "cont_file_from_workspace",
                    $ilCtrl->getLinkTarget($this, $a_cmd),
                    $a_cmd
                );
                $ilCtrl->setParameter($this, "subCmd", "");
            }
        }
    }

    /**
     * move list item down
     */
    public function moveItemDown() : void
    {
        $this->content_obj->moveItemDown();
        $this->updateAndReturn();
    }

    /**
     * move list item up
     */
    public function moveItemUp() : void
    {
        $this->content_obj->moveItemUp();
        $this->updateAndReturn();
    }

    /**
     * Cancel adding a file
     */
    public function cancelAddFile() : void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
