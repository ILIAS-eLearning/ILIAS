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
 * Class ilPCListGUI
 *
 * User Interface for LM List Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileListGUI extends ilPageContentGUI
{
    protected string $requested_file_ref_id;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilTree $tree;
    protected ilToolbarGUI $toolbar;
    protected ilSetting $settings;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->setCharacteristics(array("FileListItem" => $this->lng->txt("cont_FileListItem")));
        $this->requested_file_ref_id = $this->request->getString("file_ref_id");
    }

    /**
     * execute command
     */
    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);
        
        $this->getCharacteristicsOfCurrentStyle(["flist_li"]);	// scorm-2004

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * insert new file list form
     */
    public function insert(ilPropertyFormGUI $a_form = null) : void
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
                $this->insertFromWorkspace();
                break;
            
            case "insertFromRepository":
                $this->insertFromRepository();
                break;
                
            case "selectFile":
                $this->selectFile();
                break;

            default:
                $this->setTabs();
                $ilTabs->setSubTabActive("cont_new_file");
                
                $this->displayValidationError();

                if ($a_form != null) {
                    $form = $a_form;
                } else {
                    $form = $this->initEditForm("create");
                }
                $this->tpl->setContent($form->getHTML());
                break;
        }
    }

    public function selectFile() : void
    {
        $ilTabs = $this->tabs;
        $this->setTabs();
        $ilTabs->setSubTabActive("cont_file_from_repository");

        $this->displayValidationError();
        $form = $this->initEditForm("select_file");
        
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Insert file from repository
     */
    public function insertFromRepository(string $a_cmd = "insert") : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if ($a_cmd == "insert") {
            $this->setTabs();
        } else {
            $this->setItemTabs($a_cmd);
        }

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
    public function insertFromWorkspace(
        string $a_cmd = "insert"
    ) : void {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        if ($a_cmd == "insert") {
            $this->setTabs();
        } else {
            $this->setItemTabs($a_cmd);
        }

        $ilTabs->setSubTabActive("cont_file_from_workspace");
        
        $exp = new ilWorkspaceExplorerGUI($ilUser->getId(), $this, $a_cmd, $this, $a_cmd, "fl_wsp_id");
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
     * create new file list in dom and update page in db
     */
    public function create() : void
    {
        global $DIC;

        $mode = ($this->requested_file_ref_id != "")
            ? "select_file"
            : "create";
        $form = $this->initEditForm($mode);
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->insert($form);
            return;
        }

        // from personal workspace
        if (substr($this->requested_file_ref_id, 0, 4) == "wsp_") {
            $fileObj = new ilObjFile(substr($this->requested_file_ref_id, 4), false);
        }
        // upload
        elseif ($this->requested_file_ref_id == "") {
            $fileObj = new ilObjFile();
            $fileObj->setType("file");
            $fileObj->setTitle($_FILES["file"]["name"]);
            $fileObj->setDescription("");
            $fileObj->setFileName($_FILES["file"]["name"]);
            $fileObj->setMode("filelist");
            $fileObj->create();
            // upload file to filesystem

            $upload = $DIC->upload();
            if ($upload->hasBeenProcessed() !== true) {
                $upload->process();
            }

            $fileObj->getUploadFile(
                $_FILES["file"]["tmp_name"],
                $_FILES["file"]["name"]
            );
        }
        // from repository
        else {
            $fileObj = new ilObjFile($this->requested_file_ref_id);
        }
        $this->setCurrentTextLang($form->getInput("flst_language"));

        //echo "::".is_object($this->dom).":";
        $this->content_obj = new ilPCFileList($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setListTitle(
            $form->getInput("flst_title"),
            $form->getInput("flst_language")
        );
        $this->content_obj->appendItem(
            $fileObj->getId(),
            $fileObj->getFileName(),
            $fileObj->getFileType()
        );
            
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
     * edit properties form
     */
    public function edit() : void
    {
        $this->setTabs(false);
        
        $form = $this->initEditForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init edit form
     */
    public function initEditForm(string $a_mode = "edit") : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ti = null;
        $si = null;
        $form = new ilPropertyFormGUI();
        
        if ($a_mode != "add_file") {
            // title
            $ti = new ilTextInputGUI($lng->txt("title"), "flst_title");
            $ti->setMaxLength(80);
            $ti->setSize(40);
            $form->addItem($ti);
            
            // language
            $lang = ilMDLanguageItem::_getLanguages();
            $si = new ilSelectInputGUI($lng->txt("language"), "flst_language");
            $si->setOptions($lang);
            $form->addItem($si);
        }
        
        if (in_array($a_mode, array("create", "add_file"))) {
            // file
            $fi = new ilFileInputGUI($lng->txt("file"), "file");
            $fi->setRequired(true);
            $form->addItem($fi);
        } elseif (in_array($a_mode, array("select_file"))) {
            // file
            $ne = new ilNonEditableValueGUI($lng->txt("file"), "");

            $file_ref_id = $this->requested_file_ref_id;
            $fl_wsp_id = $this->request->getInt("fl_wsp_id");

            if ($file_ref_id > 0) {
                $file_obj = new ilObjFile($file_ref_id);
                if (is_object($file_obj)) {
                    // ref id as hidden input
                    $hi = new ilHiddenInputGUI("file_ref_id");
                    $hi->setValue($file_ref_id);
                    $form->addItem($hi);
                    
                    $ne->setValue($file_obj->getTitle());
                }
            } elseif ($fl_wsp_id > 0) {
                // we need the object id for the instance
                $tree = new ilWorkspaceTree($ilUser->getId());
                $node = $tree->getNodeData($fl_wsp_id);
                
                $file_obj = new ilObjFile($node["obj_id"], false);
                if (is_object($file_obj)) {
                    // ref id as hidden input
                    $hi = new ilHiddenInputGUI("file_ref_id");
                    $hi->setValue("wsp_" . (int) $node["obj_id"]);
                    $form->addItem($hi);
                    
                    $ne->setValue($file_obj->getTitle());
                }
                $this->tpl->parseCurrentBlock();
            }

            $form->addItem($ne);
        }
        
        
        switch ($a_mode) {
            case "edit":
                $ti->setValue($this->content_obj->getListTitle());
                $si->setValue($this->content_obj->getLanguage());
                $form->addCommandButton("saveProperties", $lng->txt("save"));
                $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
                $form->setTitle($lng->txt("cont_edit_file_list_properties"));
                break;
                
            case "create":
            case "select_file":
                if ($this->getCurrentTextLang() != "") {
                    $s_lang = $this->getCurrentTextLang();
                } else {
                    $s_lang = $ilUser->getLanguage();
                }
                $si->setValue($s_lang);
                $form->addCommandButton("create_flst", $lng->txt("save"));
                $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
                $form->setTitle($lng->txt("cont_insert_file_list"));
                break;
                
            case "add_file":
                $form->addCommandButton("insertNewFileItem", $lng->txt("save"));
                $form->addCommandButton("editFiles", $lng->txt("cancel"));
                $form->setTitle($lng->txt("cont_insert_file_item"));
                break;
        }

        $form->setFormAction($ilCtrl->getFormAction($this));
     
        return $form;
    }
    

    /**
     * save table properties in db and return to page edit screen
     */
    public function saveProperties() : void
    {
        $form = $this->initEditForm("edit");
        $form->checkInput();
        $this->content_obj->setListTitle(
            $form->getInput("flst_title"),
            $form->getInput("flst_language")
        );
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }

    /**
     * Edit Files
     */
    public function editFiles() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->setTabs(false);
        
        $ilToolbar->addButton(
            $lng->txt("cont_add_file"),
            $ilCtrl->getLinkTarget($this, "addFileItem")
        );

        /** @var ilPCFileList $fl */
        $fl = $this->content_obj;
        $table_gui = new ilPCFileListTableGUI($this, "editFiles", $fl);
        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * Set Tabs
     */
    public function setTabs(bool $a_create = true) : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;

        if ($a_create) {
            $cmd = "insert";
            
            $ilCtrl->setParameter($this, "subCmd", "insertNew");
            $ilTabs->addSubTabTarget(
                "cont_new_file",
                $ilCtrl->getLinkTarget($this, $cmd),
                $cmd
            );
    
            $ilCtrl->setParameter($this, "subCmd", "insertFromRepository");
            $ilTabs->addSubTabTarget(
                "cont_file_from_repository",
                $ilCtrl->getLinkTarget($this, $cmd),
                $cmd
            );
            $ilCtrl->setParameter($this, "subCmd", "");
            
            if (!$ilSetting->get("disable_personal_workspace") &&
                !$ilSetting->get("disable_wsp_files")) {
                $ilCtrl->setParameter($this, "subCmd", "insertFromWorkspace");
                $ilTabs->addSubTabTarget(
                    "cont_file_from_workspace",
                    $ilCtrl->getLinkTarget($this, $cmd),
                    $cmd
                );
                $ilCtrl->setParameter($this, "subCmd", "");
            }
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("pg"),
                $this->ctrl->getParentReturn($this)
            );
    
            $ilTabs->addTarget(
                "cont_ed_edit_files",
                $ilCtrl->getLinkTarget($this, "editFiles"),
                "editFiles",
                get_class($this)
            );

            $ilTabs->addTarget(
                "cont_ed_edit_prop",
                $ilCtrl->getLinkTarget($this, "edit"),
                "edit",
                get_class($this)
            );
        }
    }

    /**
     * Add file item. This function is called from file list table and calls
     * newItemAfter function.
     */
    public function addFileItem() : void
    {
        $ilCtrl = $this->ctrl;
        
        $files = $this->content_obj->getFileList();

        if (count($files) >= 1) {
            $ilCtrl->setParameterByClass(
                "ilpcfileitemgui",
                "hier_id",
                $files[count($files) - 1]["hier_id"]
            );
            $ilCtrl->setParameterByClass(
                "ilpcfileitemgui",
                "pc_id",
                $files[count($files) - 1]["pc_id"]
            );
            $ilCtrl->redirectByClass("ilpcfileitemgui", "newItemAfter");
        } else {
            $ilCtrl->redirect($this, "newFileItem");
        }
    }
    
    /**
     * Delete file items from list
     */
    public function deleteFileItem() : void
    {
        $ilCtrl = $this->ctrl;

        $fid = $this->request->getIntArray("fid");
        if (count($fid) > 0) {
            $this->content_obj->deleteFileItems(array_keys($fid));
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }
    
    /**
     * Save positions of file items
     */
    public function savePositions() : void
    {
        $ilCtrl = $this->ctrl;

        $pos = $this->request->getIntArray("position");
        if (count($pos) > 0) {
            $this->content_obj->savePositions($pos);
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }

    /**
     * Save positions of file items and style classes
     */
    public function savePositionsAndClasses() : void
    {
        $ilCtrl = $this->ctrl;

        $pos = $this->request->getIntArray("position");
        $class = $this->request->getStringArray("class");
        if (count($pos) > 0) {
            $this->content_obj->savePositions($pos);
        }
        if (count($class) > 0) {
            $this->content_obj->saveStyleClasses($class);
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }

    /**
     * Checks whether style selection shoudl be available or not
     */
    public function checkStyleSelection() : bool
    {
        // check whether there is more than one style class
        $chars = $this->getCharacteristics();

        $classes = $this->content_obj->getAllClasses();
        if (count($chars) > 1) {
            return true;
        }
        foreach ($classes as $class) {
            if ($class != "" && $class != "FileListItem") {
                return true;
            }
        }
        return false;
    }

    //
    //
    // New file item
    //
    //

    /**
     * New file item (called, if there is no file item in an existing)
     */
    public function newFileItem() : void
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
                $this->insertFromWorkspace("newFileItem");
                break;
            
            case "insertFromRepository":
                $this->insertFromRepository("newFileItem");
                break;

            case "selectFile":
                $this->insertNewFileItem($this->requested_file_ref_id);
                break;

            default:
                $this->setItemTabs("newFileItem");
                $ilTabs->setSubTabActive("cont_new_file");

                $this->displayValidationError();
                
                $form = $this->initEditForm("add_file");
                $this->tpl->setContent($form->getHTML());
                break;
        }
    }

    /**
     * insert new file item after another item
     */
    public function insertNewFileItem(int $a_file_ref_id = 0) : void
    {
        $ilUser = $this->user;

        $fl_wsp_id = $this->request->getInt("fl_wsp_id");

        // from personal workspace
        if ($fl_wsp_id > 0) {
            // we need the object id for the instance
            $tree = new ilWorkspaceTree($ilUser->getId());
            $node = $tree->getNodeData($fl_wsp_id);
            
            $file_obj = new ilObjFile($node["obj_id"], false);
        }
        // upload
        elseif ($a_file_ref_id == 0) {
            $file_obj = $this->createFileItem();
        }
        // from repository
        else {
            $file_obj = new ilObjFile($a_file_ref_id);
        }
        if (is_object($file_obj)) {
            $this->content_obj->appendItem(
                $file_obj->getId(),
                $file_obj->getTitle(),
                $file_obj->getFileType()
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
                $this->ctrl->redirect($this, "editFiles");
            }
        }

        $this->newFileItem();
    }

    /**
     * insert new file item
     */
    public function createFileItem() : ?ilObjFile
    {
        global $DIC;

        $lng = $this->lng;

        if ($_FILES["file"]["name"] == "") {
            throw new ilCOPageFileHandlingException($lng->txt("upload_error_file_not_found"));
        }

        $form = $this->initEditForm();
        // see #22541
        //		$form->checkInput();

        $fileObj = new ilObjFile();
        $fileObj->setType("file");
        $fileObj->setTitle($_FILES["file"]["name"]);
        $fileObj->setDescription("");
        $fileObj->setFileName($_FILES["file"]["name"]);
        $fileObj->setMode("filelist");
        $fileObj->create();
        // upload file to filesystem

        $upload = $DIC->upload();
        if ($upload->hasBeenProcessed() !== true) {
            $upload->process();
        }

        $fileObj->getUploadFile(
            $_FILES["file"]["tmp_name"],
            $_FILES["file"]["name"]
        );

        return $fileObj;
    }


    /**
     * output tabs
     */
    public function setItemTabs(string $a_cmd = "") : void
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
}
