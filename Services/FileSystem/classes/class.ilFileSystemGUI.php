<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* File System Explorer GUI class
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilFileSystemGUI
{
    public $ctrl;

    protected $use_upload_directory = false;
    const CDIR = "cdir";
    /**
     * @var array
     */
    protected $allowed_suffixes = array();

    /**
     * @var array
     */
    protected $forbidden_suffixes = array();

    public function __construct($a_main_directory)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilias = $DIC['ilias'];

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->ilias = $ilias;
        $this->tpl = $tpl;
        $this->main_dir = $a_main_directory;
        $this->post_dir_path = false;

        $this->defineCommands();

        $this->file_labels = array();
        $this->label_enable = false;
        $this->ctrl->saveParameter($this, self::CDIR);
        $lng->loadLanguageModule("content");
        $this->setAllowDirectories(true);
        $this->setAllowDirectoryCreation(true);
        $this->setAllowFileCreation(true);
        //echo "<br>main_dir:".$this->main_dir.":";
    }

    /**
     * Set allowed Suffixes.
     *
     * @param	array	$a_suffixes	allowed Suffixes
     */
    public function setAllowedSuffixes($a_suffixes)
    {
        $this->allowed_suffixes = $a_suffixes;
    }

    /**
     * Get allowed Suffixes.
     *
     * @return	array	allowed Suffixes
     */
    public function getAllowedSuffixes()
    {
        return $this->allowed_suffixes;
    }

    /**
     * Set forbidden Suffixes.
     *
     * @param	array	$a_suffixes	forbidden Suffixes
     */
    public function setForbiddenSuffixes($a_suffixes)
    {
        $this->forbidden_suffixes = $a_suffixes;
    }

    /**
     * Get Accepted Suffixes.
     *
     * @return	array	forbidden Suffixes
     */
    public function getForbiddenSuffixes()
    {
        return $this->forbidden_suffixes;
    }

    /**
     * Is suffix valid?
     *
     * @param string $a_suffix
     * @return bool
     */
    public function isValidSuffix($a_suffix)
    {
        if (is_array($this->getForbiddenSuffixes()) && in_array($a_suffix, $this->getForbiddenSuffixes())) {
            return false;
        }
        if (is_array($this->getAllowedSuffixes()) && in_array($a_suffix, $this->getAllowedSuffixes())) {
            return true;
        }
        if (!is_array($this->getAllowedSuffixes()) || count($this->getAllowedSuffixes()) == 0) {
            return true;
        }
        return false;
    }


    /**
     * Set allow directories
     *
     * @param	boolean		allow directories
     */
    public function setAllowDirectories($a_val)
    {
        $this->allow_directories = $a_val;
    }
    
    /**
     * Get allow directories
     *
     * @return	boolean		allow directories
     */
    public function getAllowDirectories()
    {
        return $this->allow_directories;
    }

    /**
     * Set post dir path
     *
     * @param	boolean		post dir path
     */
    public function setPostDirPath($a_val)
    {
        $this->post_dir_path = $a_val;
    }

    /**
     * Get post dir path
     *
     * @return	boolean		post dir path
     */
    public function getPostDirPath()
    {
        return $this->post_dir_path;
    }

    /**
    * Set table id
    *
    * @param	string	table id
    */
    public function setTableId($a_val)
    {
        $this->table_id = $a_val;
    }
    
    /**
    * Get table id
    *
    * @return	string	table id
    */
    public function getTableId()
    {
        return $this->table_id;
    }

    /**
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set use upload directory
     *
     * @param bool $a_val use upload directory
     */
    public function setUseUploadDirectory($a_val)
    {
        $this->use_upload_directory = $a_val;
    }

    /**
     * Get use upload directory
     *
     * @return bool use upload directory
     */
    public function getUseUploadDirectory()
    {
        return $this->use_upload_directory;
    }
    
    /**
     * Set performed command
     *
     * @param	string	command
     * @param	array	parameter array
     */
    protected function setPerformedCommand($command, $pars = "")
    {
        if (!is_array($pars)) {
            $pars = array();
        }
        $_SESSION["fsys"]["lastcomm"] = array_merge(
            array("cmd" => $command),
            $pars
        );
    }
    
    /**
     * Get performed command
     *
     * @return	array	command array
     */
    public function getLastPerformedCommand()
    {
        $ret = $_SESSION["fsys"]["lastcomm"];
        $_SESSION["fsys"]["lastcomm"] = "none";
        return $ret;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listFiles");

        switch ($next_class) {

            default:
                if (substr($cmd, 0, 11) == "extCommand_") {
                    $ret = $this->extCommand(substr($cmd, 11, strlen($cmd) - 11));
                } else {
                    $ret = $this->$cmd();
                }
                break;
        }

        return $ret;
    }


    /**
     * Add command
     */
    public function addCommand(
        &$a_obj,
        $a_func,
        $a_name,
        $a_single = true,
        $a_allow_dir = false
    ) {
        $i = count($this->commands);

        $this->commands[$i]["object"] = $a_obj;
        $this->commands[$i]["method"] = $a_func;
        $this->commands[$i]["name"] = $a_name;
        $this->commands[$i]["single"] = $a_single;
        $this->commands[$i]["allow_dir"] = $a_allow_dir;

        //$this->commands[] = $arr;
    }

    /**
     * Clear commands
     */
    public function clearCommands()
    {
        $this->commands = array();
    }

    /**
    * label a file
    */
    public function labelFile($a_file, $a_label)
    {
        $this->file_labels[$a_file][] = $a_label;
    }

    /**
    * activate file labels
    */
    public function activateLabels($a_act, $a_label_header)
    {
        $this->label_enable = $a_act;
        $this->label_header = $a_label_header;
    }
    
    
    
    protected function parseCurrentDirectory()
    {
        // determine directory
        // FIXME: I have to call stripSlashes here twice, because I could not
        //        determine where the second layer of slashes is added to the
        //        URL Parameter
        $cur_subdir = ilUtil::stripSlashes(ilUtil::stripSlashes($_GET[self::CDIR]));
        $new_subdir = ilUtil::stripSlashes(ilUtil::stripSlashes($_GET["newdir"]));

        if ($new_subdir == "..") {
            $cur_subdir = substr($cur_subdir, 0, strrpos($cur_subdir, "/"));
        } else {
            if (!empty($new_subdir)) {
                if (!empty($cur_subdir)) {
                    $cur_subdir = $cur_subdir . "/" . $new_subdir;
                } else {
                    $cur_subdir = $new_subdir;
                }
            }
        }

        $cur_subdir = str_replace("..", "", $cur_subdir);
        $cur_dir = (!empty($cur_subdir))
            ? $this->main_dir . "/" . $cur_subdir
            : $this->main_dir;
        
        return array("dir"=>$cur_dir, "subdir"=>$cur_subdir);
    }
    
    protected function getFileList($a_dir, $a_subdir = null)
    {
        $items = array();
        
        $entries = (is_dir($a_dir))
            ? ilUtil::getDir($a_dir)
            : array(array("type" => "dir", "entry" => ".."));
    
        $items = array();
        foreach ($entries as $e) {
            if (($e["entry"] == ".") ||
                ($e["entry"] == ".." && empty($a_subdir))) {
                continue;
            }
            
            $cfile = (!empty($a_subdir))
                ? $a_subdir . "/" . $e["entry"]
                : $e["entry"];
            
            $items[] = array(
                "file" => $cfile,
                "entry" => $e["entry"],
                "type" => $e["type"],
                "size" => $e["size"],
                "hash" => md5($e["entry"])
            );
        }
        
        return $items;
    }

    protected function getIncomingFiles()
    {
        $sel_files = $hashes = array();
        if (isset($_POST["file"])) {
            $hashes = $_POST["file"];
        } elseif (isset($_GET["fhsh"])) {
            $hashes = array($_GET["fhsh"]);
        }
        
        if (sizeof($hashes)) {
            $dir = $this->parseCurrentDirectory();
            $all_files = $this->getFileList($dir["dir"], $dir["subdir"]);
            foreach ($hashes as $hash) {
                foreach ($all_files as $file) {
                    if ($file["hash"] == $hash) {
                        $sel_files[] = $this->getPostDirPath()
                            ? $file["file"]
                            : $file["entry"];
                        break;
                    }
                }
            }
        }
        
        return $sel_files;
    }

    /**
    * call external command
    */
    public function extCommand($a_nr)
    {
        $selected = $this->getIncomingFiles();

        if (!count($selected)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listFiles");
        }

        // check if only one item is select, if command does not allow multiple selection
        if (count($selected) > 1 && $this->commands[$a_nr]["single"]) {
            ilUtil::sendFailure($this->lng->txt("cont_select_max_one_item"), true);
            $this->ctrl->redirect($this, "listFiles");
        }

        $cur_subdir = $this->sanitizeCurrentDirectory();

        // collect files and
        $files = array();
        foreach ($selected as $file) {
            $file = ilUtil::stripSlashes($file);
            $file = (!empty($cur_subdir))
                ? $cur_subdir . "/" . $file
                : $file;
            
            // check wether selected item is a directory
            if (@is_dir($this->main_dir . "/" . $file) &&
                !$this->commands[$a_nr]["allow_dir"]) {
                ilUtil::sendFailure($this->lng->txt("select_a_file"), true);
                $this->ctrl->redirect($this, "listFiles");
            }
                        
            $files[] = $file;
        }
        
        if ($this->commands[$a_nr]["single"]) {
            $files = array_shift($files);
        }

        $obj = $this->commands[$a_nr]["object"];
        $method = $this->commands[$a_nr]["method"];

        return $obj->$method($files);
    }

    /**
     * Set allowed directory creation
     */
    public function setAllowDirectoryCreation($a_val)
    {
        $this->directory_creation = $a_val;
    }

    /**
     * Get allowed directory creation
     */
    public function getAllowDirectoryCreation()
    {
        return $this->directory_creation;
    }

    /**
     * Set allowed file creation
     */
    public function setAllowFileCreation($a_val)
    {
        $this->file_creation = $a_val;
    }

    /**
     * Get allowed file creation
     */
    public function getAllowFileCreation()
    {
        return $this->file_creation;
    }

    /**
     * List files
     *
     * @param array $a_class_table_gui if we are here from a child class
     *
     */
    public function listFiles($a_table_gui = null)
    {
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $dir = $this->parseCurrentDirectory();
        
        $this->ctrl->setParameter($this, self::CDIR, $dir["subdir"]);
        
        // toolbar for adding files/directories
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        
        if ($this->getAllowDirectories() && $this->getAllowDirectoryCreation()) {
            $ti = new ilTextInputGUI($this->lng->txt("cont_new_dir"), "new_dir");
            $ti->setMaxLength(80);
            $ti->setSize(10);
            $ilToolbar->addInputItem($ti, true);
            $ilToolbar->addFormButton($lng->txt("create"), "createDirectory");
            
            $ilToolbar->addSeparator();
        }
        
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        if ($this->getAllowFileCreation()) {
            $fi = new ilFileInputGUI($this->lng->txt("cont_new_file"), "new_file");
            $fi->setSize(10);
            $ilToolbar->addInputItem($fi, true);
            $ilToolbar->addFormButton($lng->txt("upload"), "uploadFile");
        }
        
        include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';
        if (ilUploadFiles::_getUploadDirectory() && $this->getAllowFileCreation() && $this->getUseUploadDirectory()) {
            $ilToolbar->addSeparator();
            $files = ilUploadFiles::_getUploadFiles();
            $options[""] = $lng->txt("cont_select_from_upload_dir");
            foreach ($files as $file) {
                $file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
                $options[$file] = $file;
            }
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt("cont_uploaded_file"), "uploaded_file");
            $si->setOptions($options);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton($lng->txt("copy"), "uploadFile");
        }

        $fs_table = $this->getTable($dir["dir"], $dir["subdir"]);

        if ($this->getTitle() != "") {
            $fs_table->setTitle($this->getTitle());
        }
        if ($_GET["resetoffset"] == 1) {
            $fs_table->resetOffset();
        }
        $this->tpl->setContent($fs_table->getHTML());
    }

    /**
     * Get table
     *
     * @param
     * @return
     */
    public function getTable($a_dir, $a_subdir)
    {
        include_once("./Services/FileSystem/classes/class.ilFileSystemTableGUI.php");
        return new ilFileSystemTableGUI(
            $this,
            "listFiles",
            $a_dir,
            $a_subdir,
            $this->label_enable,
            $this->file_labels,
            $this->label_header,
            $this->commands,
            $this->getPostDirPath(),
            $this->getTableId()
        );
    }

    /**
    * list files
    */
    public function renameFileForm($a_file)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $cur_subdir = $this->sanitizeCurrentDirectory();
        $file = $this->main_dir . "/" . $a_file;

        $this->ctrl->setParameter($this, "old_name", basename($a_file));
        $this->ctrl->setParameter($this, self::CDIR, ilUtil::stripSlashes($_GET[self::CDIR]));
            
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // file/dir name
        $ti = new ilTextInputGUI($this->lng->txt("name"), "new_name");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue(basename($a_file));
        $form->addItem($ti);
        
        // save and cancel commands
        $form->addCommandButton("renameFile", $lng->txt("rename"));
        $form->addCommandButton("cancelRename", $lng->txt("cancel"));
        $form->setFormAction($ilCtrl->getFormAction($this, "renameFile"));

        if (@is_dir($file)) {
            $form->setTitle($this->lng->txt("cont_rename_dir"));
        } else {
            $form->setTitle($this->lng->txt("rename_file"));
        }
        
        $this->tpl->setContent($form->getHTML());
    }

    /**
    * rename a file
    */
    public function renameFile()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $new_name = str_replace("..", "", ilUtil::stripSlashes($_POST["new_name"]));
        $new_name = str_replace("/", "", $new_name);
        if ($new_name == "") {
            $this->ilias->raiseError($this->lng->txt("enter_new_name"), $this->ilias->error_obj->MESSAGE);
        }

        $pi = pathinfo($new_name);
        $suffix = $pi["extension"];
        if ($suffix != "" && !$this->isValidSuffix($suffix)) {
            ilUtil::sendFailure($this->lng->txt("file_no_valid_file_type") . " ($suffix)", true);
            $this->ctrl->redirect($this, "listFiles");
        }

        $cur_subdir = $this->sanitizeCurrentDirectory();
        $dir = (!empty($cur_subdir))
            ? $this->main_dir . "/" . $cur_subdir . "/"
            : $this->main_dir . "/";


        if (is_dir($dir . ilUtil::stripSlashes($_GET["old_name"]))) {
            rename($dir . ilUtil::stripSlashes($_GET["old_name"]), $dir . $new_name);
        } else {
            include_once("./Services/Utilities/classes/class.ilFileUtils.php");

            try {
                ilFileUtils::rename($dir . ilUtil::stripSlashes($_GET["old_name"]), $dir . $new_name);
            } catch (ilException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                $this->ctrl->redirect($this, "listFiles");
            }
        }

        ilUtil::renameExecutables($this->main_dir);
        if (@is_dir($dir . $new_name)) {
            ilUtil::sendSuccess($lng->txt("cont_dir_renamed"), true);
            $this->setPerformedCommand("rename_dir", array("old_name" => $_GET["old_name"],
                "new_name" => $new_name));
        } else {
            ilUtil::sendSuccess($lng->txt("cont_file_renamed"), true);
            $this->setPerformedCommand("rename_file", array("old_name" => $_GET["old_name"],
                "new_name" => $new_name));
        }
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
    * cancel renaming a file
    */
    public function cancelRename()
    {
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
    * create directory
    */
    public function createDirectory()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        // determine directory
        $cur_subdir = $this->sanitizeCurrentDirectory();
        $cur_dir = (!empty($cur_subdir))
            ? $this->main_dir . "/" . $cur_subdir
            : $this->main_dir;

        $new_dir = str_replace(".", "", ilUtil::stripSlashes($_POST["new_dir"]));
        $new_dir = str_replace("/", "", $new_dir);

        if (!empty($new_dir)) {
            ilUtil::makeDir($cur_dir . "/" . $new_dir);
            if (is_dir($cur_dir . "/" . $new_dir)) {
                ilUtil::sendSuccess($lng->txt("cont_dir_created"), true);
                $this->setPerformedCommand("create_dir", array("name" => $new_dir));
            }
        } else {
            ilUtil::sendFailure($lng->txt("cont_enter_a_dir_name"), true);
        }
        $this->ctrl->saveParameter($this, self::CDIR);
        $this->ctrl->redirect($this, 'listFiles');
    }

    /**
     * Upload file
     *
     */
    public function uploadFile()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        // determine directory
        $cur_subdir = $this->sanitizeCurrentDirectory();
        $cur_dir = (!empty($cur_subdir))
            ? $this->main_dir . "/" . $cur_subdir
            : $this->main_dir;

        $tgt_file = null;

        $pi = pathinfo($_FILES["new_file"]["name"]);
        $suffix = $pi["extension"];
        if (!$this->isValidSuffix($suffix)) {
            ilUtil::sendFailure($this->lng->txt("file_no_valid_file_type") . " ($suffix)", true);
            $this->ctrl->redirect($this, "listFiles");
        }

        if (is_file($_FILES["new_file"]["tmp_name"])) {
            $name = ilUtil::stripSlashes($_FILES["new_file"]["name"]);
            $tgt_file = $cur_dir . "/" . $name;

            ilUtil::moveUploadedFile($_FILES["new_file"]["tmp_name"], $name, $tgt_file);
        } elseif ($_POST["uploaded_file"]) {
            include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';

            // check if the file is in the ftp directory and readable
            if (ilUploadFiles::_checkUploadFile($_POST["uploaded_file"])) {
                $tgt_file = $cur_dir . "/" . ilUtil::stripSlashes($_POST["uploaded_file"]);
                
                // copy uploaded file to data directory
                ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $tgt_file);
            }
        } elseif (trim($_FILES["new_file"]["name"]) == "") {
            ilUtil::sendFailure($lng->txt("cont_enter_a_file"), true);
        }
        
        if ($tgt_file && is_file($tgt_file)) {
            $unzip = null;
            
            // extract zip?
            include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
            if (ilMimeTypeUtil::getMimeType($tgt_file) == "application/zip") {
                $this->ctrl->setParameter($this, "upfile", basename($tgt_file));
                $url = $this->ctrl->getLinkTarget($this, "unzipFile");
                $this->ctrl->setParameter($this, "upfile", "");
                
                include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
                $unzip = ilLinkButton::getInstance();
                $unzip->setCaption("unzip");
                $unzip->setUrl($url);
                $unzip = " " . $unzip->render();
            }
            
            ilUtil::sendSuccess($lng->txt("cont_file_created") . $unzip, true);
            
            $this->setPerformedCommand(
                "create_file",
                array("name" => substr($tgt_file, strlen($this->main_dir)+1))
            );
        }

        $this->ctrl->saveParameter($this, self::CDIR);

        ilUtil::renameExecutables($this->main_dir);

        $this->ctrl->redirect($this, 'listFiles');
    }

    /**
    * Confirm file deletion
    */
    public function confirmDeleteFile(array $a_files)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setHeaderText($lng->txt("info_delete_sure"));
        $cgui->setCancel($lng->txt("cancel"), "listFiles");
        $cgui->setConfirm($lng->txt("delete"), "deleteFile");

        foreach ($a_files as $i) {
            $cgui->addItem("file[]", $i, $i);
        }
            
        $tpl->setContent($cgui->getHTML());
    }
    
    /**
     * delete object file
     */
    public function deleteFile()
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (!isset($_POST["file"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }
        
        foreach ($_POST["file"] as $post_file) {
            if (ilUtil::stripSlashes($post_file) == "..") {
                $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
                break;
            }

            $cur_subdir = $this->sanitizeCurrentDirectory();
            $cur_dir = (!empty($cur_subdir))
                ? $this->main_dir . "/" . $cur_subdir
                : $this->main_dir;
            $pi = pathinfo($post_file);
            $file = $cur_dir . "/" . ilUtil::stripSlashes($pi["basename"]);

            if (@is_file($file)) {
                unlink($file);
            }

            if (@is_dir($file)) {
                $is_dir = true;
                ilUtil::delDir($file);
            }
        }

        $this->ctrl->saveParameter($this, self::CDIR);
        if ($is_dir) {
            ilUtil::sendSuccess($lng->txt("cont_dir_deleted"), true);
            $this->setPerformedCommand(
                "delete_dir",
                array("name" => ilUtil::stripSlashes($post_file))
            );
        } else {
            ilUtil::sendSuccess($lng->txt("cont_file_deleted"), true);
            $this->setPerformedCommand(
                "delete_file",
                array("name" => ilUtil::stripSlashes($post_file))
            );
        }
        $this->ctrl->redirect($this, 'listFiles');
    }

    /**
    * delete object file
    */
    public function unzipFile($a_file = null)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        // #17470 - direct unzip call (after upload)
        if (!$a_file &&
            isset($_GET["upfile"])) {
            $a_file = basename($_GET["upfile"]);
        }

        $cur_subdir = $this->sanitizeCurrentDirectory();
        $cur_dir = (!empty($cur_subdir))
            ? $this->main_dir . "/" . $cur_subdir
            : $this->main_dir;
        $a_file = $this->main_dir . "/" . $a_file;
        
        if (@is_file($a_file)) {
            include_once("./Services/Utilities/classes/class.ilFileUtils.php");
            $cur_files = array_keys(ilUtil::getDir($cur_dir));
            $cur_files_r = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cur_dir)));
            
            if ($this->getAllowDirectories()) {
                ilUtil::unzip($a_file, true);
            } else {
                ilUtil::unzip($a_file, true, true);
            }
            
            $new_files = array_keys(ilUtil::getDir($cur_dir));
            $new_files_r = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cur_dir)));

            $diff = array_diff($new_files, $cur_files);
            $diff_r = array_diff($new_files_r, $cur_files_r);

            // unlink forbidden file types
            foreach ($diff_r as $f => $d) {
                $pi = pathinfo($f);
                if (!is_dir($f) && !$this->isValidSuffix(strtolower($pi["extension"]))) {
                    ilUtil::sendFailure($lng->txt("file_some_invalid_file_types_removed") . " (" . $pi["extension"] . ")", true);
                    unlink($f);
                }
            }

            if (sizeof($diff)) {
                if ($this->getAllowDirectories()) {
                    include_once("./Services/Utilities/classes/class.ilFileUtils.php");
                    $new_files = array();
                    
                    foreach ($diff as $new_item) {
                        if (is_dir($cur_dir . "/" . $new_item)) {
                            ilFileUtils::recursive_dirscan($cur_dir . "/" . $new_item, $new_files);
                        }
                    }
                    
                    if (is_array($new_files["path"])) {
                        foreach ($new_files["path"] as $idx => $path) {
                            $path = substr($path, strlen($this->main_dir)+1);
                            $diff[] = $path . $new_files["file"][$idx];
                        }
                    }
                }
                
                $this->setPerformedCommand(
                    "unzip_file",
                    array("name" => substr($file, strlen($this->main_dir)+1),
                        "added" => $diff)
                );
            }
        }

        ilUtil::renameExecutables($this->main_dir);

        $this->ctrl->saveParameter($this, self::CDIR);
        ilUtil::sendSuccess($lng->txt("cont_file_unzipped"), true);
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
    * delete object file
    */
    public function downloadFile($a_file)
    {
        $file = $this->main_dir . "/" . $a_file;
    
        if (@is_file($file) && !(@is_dir($file))) {
            ilUtil::deliverFile($file, basename($a_file));
            exit;
        } else {
            $this->ctrl->saveParameter($this, self::CDIR);
            $this->ctrl->redirect($this, "listFiles");
        }
    }

    /**
    * get tabs
    */
    public function getTabs(&$tabs_gui)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->setParameter($this, "resetoffset", 1);
        $tabs_gui->addTarget(
            "cont_list_files",
            $this->ctrl->getLinkTarget($this, "listFiles"),
            "listFiles",
            get_class($this)
        );
        $ilCtrl->setParameter($this, "resetoffset", "");
    }

    /**
     * @return array of commands
     */
    public function getActionCommands()
    {
        return $this->commands;
    }

    /**
     * Define commands available
     */
    public function defineCommands()
    {
        $this->commands = array(
            0 => array(
                "object" => $this,
                "method" => "downloadFile",
                "name" => $this->lng->txt("download"),
                "int" => true,
                "single" => true
            ),
            1 => array(
                "object" => $this,
                "method" => "confirmDeleteFile",
                "name" => $this->lng->txt("delete"),
                "allow_dir" => true,
                "int" => true
            ),
            2 => array(
                "object" => $this,
                "method" => "unzipFile",
                "name" => $this->lng->txt("unzip"),
                "int" => true,
                "single" => true
            ),
            3 => array(
                "object" => $this,
                "method" => "renameFileForm",
                "name" => $this->lng->txt("rename"),
                "allow_dir" => true,
                "int" => true,
                "single" => true
            ),
        );
    }


    /**
     * @return string
     */
    private function sanitizeCurrentDirectory()
    {
        global $DIC;

        return  str_replace("..", "", ilUtil::stripSlashes($DIC->http()->request()->getQueryParams()[self::CDIR]));
    }
}
