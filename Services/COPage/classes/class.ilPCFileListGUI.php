<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCFileList.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCListGUI
*
* User Interface for LM List Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileListGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilSetting
     */
    protected $settings;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
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
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);
        
        $this->getCharacteristicsOfCurrentStyle("flist_li");	// scorm-2004

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * insert new file list form
    */
    public function insert($a_form = null)
    {
        $ilUser = $this->user;
        $ilTabs = $this->tabs;

        if ($_GET["subCmd"] == "insertNew") {
            $_SESSION["cont_file_insert"] = "insertNew";
        }
        if ($_GET["subCmd"] == "insertFromRepository") {
            $_SESSION["cont_file_insert"] = "insertFromRepository";
        }
        if ($_GET["subCmd"] == "insertFromWorkspace") {
            $_SESSION["cont_file_insert"] = "insertFromWorkspace";
        }
        if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "") {
            $_GET["subCmd"] = $_SESSION["cont_file_insert"];
        }
    
        switch ($_GET["subCmd"]) {
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

    /**
    * Select file
    */
    public function selectFile()
    {
        $ilTabs = $this->tabs;
        $ilUser = $this->user;
        
        $this->setTabs();
        $ilTabs->setSubTabActive("cont_file_from_repository");

        $this->displayValidationError();
        $form = $this->initEditForm("select_file");
        
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
    * Insert file from repository
    */
    public function insertFromRepository($a_cmd = "insert")
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

        include_once("./Services/COPage/classes/class.ilPCFileItemFileSelectorGUI.php");
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
    public function insertFromWorkspace($a_cmd = "insert")
    {
        $ilTabs = $this->tabs;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        if ($a_cmd == "insert") {
            $this->setTabs();
        } else {
            $this->setItemTabs($a_cmd);
        }

        $ilTabs->setSubTabActive("cont_file_from_workspace");
        
        include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorerGUI.php");
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
    public function create()
    {
        global $DIC;

        include_once("./Modules/File/classes/class.ilObjFile.php");

        $mode = ($_POST["file_ref_id"] != "")
            ? "select_file"
            : "create";
        $form = $this->initEditForm($mode);
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->insert($form);
            return;
        }

        // from personal workspace
        if (substr($_POST["file_ref_id"], 0, 4) == "wsp_") {
            $fileObj = new ilObjFile(substr($_POST["file_ref_id"], 4), false);
        }
        // upload
        elseif ($_POST["file_ref_id"] == 0) {
            $fileObj = new ilObjFile();
            $fileObj->setType("file");
            $fileObj->setTitle($_FILES["file"]["name"]);
            $fileObj->setDescription("");
            $fileObj->setFileName($_FILES["file"]["name"]);
            $fileObj->setFileType($_FILES["file"]["type"]);
            $fileObj->setFileSize($_FILES["file"]["size"]);
            $fileObj->setMode("filelist");
            $fileObj->create();
            // upload file to filesystem
            $fileObj->createDirectory();
            $fileObj->raiseUploadError(false);

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
            $fileObj = new ilObjFile($_POST["file_ref_id"]);
        }
        $_SESSION["il_text_lang_" . $_GET["ref_id"]] = $_POST["flst_language"];

        //echo "::".is_object($this->dom).":";
        $this->content_obj = new ilPCFileList($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setListTitle(ilUtil::stripSlashes($_POST["flst_title"]), $_POST["flst_language"]);
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
    public function edit()
    {
        $this->setTabs(false);
        
        $form = $this->initEditForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init edit form
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initEditForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        if ($a_mode != "add_file") {
            // title
            $ti = new ilTextInputGUI($lng->txt("title"), "flst_title");
            $ti->setMaxLength(80);
            $ti->setSize(40);
            $form->addItem($ti);
            
            // language
            require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
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
            
            if (isset($_GET["file_ref_id"])) {
                include_once("./Modules/File/classes/class.ilObjFile.php");
                $file_obj = new ilObjFile((int) $_GET["file_ref_id"]);
                if (is_object($file_obj)) {
                    // ref id as hidden input
                    $hi = new ilHiddenInputGUI("file_ref_id");
                    $hi->setValue((int) $_GET["file_ref_id"]);
                    $form->addItem($hi);
                    
                    $ne->setValue($file_obj->getTitle());
                }
            } elseif (isset($_GET["fl_wsp_id"])) {
                // we need the object id for the instance
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
                $tree = new ilWorkspaceTree($ilUser->getId());
                $node = $tree->getNodeData((int) $_GET["fl_wsp_id"]);
                
                include_once("./Modules/File/classes/class.ilObjFile.php");
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
                if ($_SESSION["il_text_lang_" . $_GET["ref_id"]] != "") {
                    $s_lang = $_SESSION["il_text_lang_" . $_GET["ref_id"]];
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
    public function saveProperties()
    {
        $this->content_obj->setListTitle(
            ilUtil::stripSlashes($_POST["flst_title"]),
            ilUtil::stripSlashes($_POST["flst_language"])
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
    public function editFiles()
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
        
        include_once("./Services/COPage/classes/class.ilPCFileListTableGUI.php");
        $table_gui = new ilPCFileListTableGUI($this, "editFiles", $this->content_obj);
        $tpl->setContent($table_gui->getHTML());
    }

    /**
    * Set Tabs
    */
    public function setTabs($a_create = true)
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
    public function addFileItem()
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
    public function deleteFileItem()
    {
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["fid"])) {
            $ids = array();
            foreach ($_POST["fid"] as $k => $v) {
                $ids[] = $k;
            }
            $this->content_obj->deleteFileItems($ids);
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }
    
    /**
    * Save positions of file items
    */
    public function savePositions()
    {
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["position"])) {
            $this->content_obj->savePositions($_POST["position"]);
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }

    /**
    * Save positions of file items and style classes
    */
    public function savePositionsAndClasses()
    {
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["position"])) {
            $this->content_obj->savePositions($_POST["position"]);
        }
        if (is_array($_POST["class"])) {
            $this->content_obj->saveStyleClasses($_POST["class"]);
        }
        $this->updated = $this->pg_obj->update();
        $ilCtrl->redirect($this, "editFiles");
    }

    /**
    * Checks whether style selection shoudl be available or not
    */
    public function checkStyleSelection()
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
     *
     * @param
     * @return
     */
    public function newFileItem()
    {
        $ilTabs = $this->tabs;

        if ($_GET["subCmd"] == "insertNew") {
            $_SESSION["cont_file_insert"] = "insertNew";
        }
        if ($_GET["subCmd"] == "insertFromRepository") {
            $_SESSION["cont_file_insert"] = "insertFromRepository";
        }
        if ($_GET["subCmd"] == "insertFromWorkspace") {
            $_SESSION["cont_file_insert"] = "insertFromWorkspace";
        }
        if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "") {
            $_GET["subCmd"] = $_SESSION["cont_file_insert"];
        }

        switch ($_GET["subCmd"]) {
            case "insertFromWorkspace":
                $this->insertFromWorkspace("newFileItem");
                break;
            
            case "insertFromRepository":
                $this->insertFromRepository("newFileItem");
                break;

            case "selectFile":
                $this->insertNewFileItem($_GET["file_ref_id"]);
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
    public function insertNewFileItem($a_file_ref_id = 0)
    {
        $ilUser = $this->user;
        
        // from personal workspace
        if (isset($_GET["fl_wsp_id"])) {
            // we need the object id for the instance
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
            $tree = new ilWorkspaceTree($ilUser->getId());
            $node = $tree->getNodeData($_GET["fl_wsp_id"]);
            
            include_once("./Modules/File/classes/class.ilObjFile.php");
            $file_obj = new ilObjFile($node["obj_id"], false);
        }
        // upload
        elseif ($a_file_ref_id == 0) {
            $file_obj = $this->createFileItem();
        }
        // from repository
        else {
            include_once("./Modules/File/classes/class.ilObjFile.php");
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

        $_GET["subCmd"] = "-";
        $this->newFileItem();
    }

    /**
     * insert new file item
     */
    public function createFileItem()
    {
        global $DIC;

        $lng = $this->lng;

        if ($_FILES["file"]["name"] == "") {
            $_GET["subCmd"] = "-";
            ilUtil::sendFailure($lng->txt("upload_error_file_not_found"));
            return false;
        }

        $form = $this->initEditForm();
        // see #22541
        //		$form->checkInput();

        include_once("./Modules/File/classes/class.ilObjFile.php");
        $fileObj = new ilObjFile();
        $fileObj->setType("file");
        $fileObj->setTitle($_FILES["file"]["name"]);
        $fileObj->setDescription("");
        $fileObj->setVersion(0);
        $fileObj->setMaxVersion(0);
        $fileObj->setFileName($_FILES["file"]["name"]);
        $fileObj->setFileType($_FILES["file"]["type"]);
        $fileObj->setFileSize($_FILES["file"]["size"]);
        $fileObj->setMode("filelist");
        $fileObj->create();
        $fileObj->createDirectory();
        $fileObj->raiseUploadError(false);
        $fileObj->getUploadFile($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
        // upload file to filesystem

        return $fileObj;
    }


    /**
     * output tabs
     */
    public function setItemTabs($a_cmd = "")
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
