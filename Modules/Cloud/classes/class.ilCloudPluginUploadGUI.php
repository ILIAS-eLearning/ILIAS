<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
include_once("./Services/JSON/classes/class.ilJsonUtil.php");

/**
 * Class ilCloudPluginUploadGUI
 *
 * Standard class for uploading files. Can be overwritten if needed.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 * @extends ilCloudPluginGUI
 * @ingroup ModulesCloud
 */
class ilCloudPluginUploadGUI extends ilCloudPluginGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;
    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $cmd = $ilCtrl->getCmd();

        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function asyncUploadFile()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->activateTab("content");
        $this->initUploadForm();
        echo $this->form->getHTML();


        $options                   = new stdClass();
        $options->dropZone         = "#ilFileUploadDropZone_1";
        $options->fileInput        = "#ilFileUploadInput_1";
        $options->submitButton     = "uploadFiles";
        $options->cancelButton     = "cancelAll";
        $options->dropArea         = "#ilFileUploadDropArea_1";
        $options->fileList         = "#ilFileUploadList_1";
        $options->fileSelectButton = "#ilFileUploadFileSelect_1";
        echo "<script language='javascript' type='text/javascript'>var fileUpload1 = new ilFileUpload(1, " . ilJsonUtil::encode($options) . ");</script>";

        $_SESSION["cld_folder_id"] = $_POST["folder_id"];

        exit;
    }

    public function initUploadForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        include_once("./Services/Form/classes/class.ilDragDropFileInputGUI.php");
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");

        $this->form = new ilPropertyFormGUI();
        $this->form->setId("upload");
        $this->form->setMultipart(true);
        $this->form->setHideLabels();

        $file = new ilDragDropFileInputGUI($lng->txt("cld_upload_files"), "upload_files");
        $file->setRequired(true);
        $this->form->addItem($file);

        $this->form->addCommandButton("uploadFiles", $lng->txt("upload"));
        $this->form->addCommandButton("cancelAll", $lng->txt("cancel"));

        $this->form->setTableWidth("100%");
        $this->form->setTitle($lng->txt("upload_files_title"));
//        $this->form->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $lng->txt('obj_file'));
        $this->form->setTitleIcon(ilUtil::getImagePath('icon_dcl_file.svg'), $lng->txt('obj_file'));

        $this->form->setTitle($lng->txt("upload_files"));
        $this->form->setFormAction($ilCtrl->getFormAction($this, "uploadFiles"));
        $this->form->setTarget("cld_blank_target");
    }

    public function cancelAll()
    {
        echo "<script language='javascript' type='text/javascript'>window.parent.il.CloudFileList.afterUpload('cancel');</script>";
        exit;
    }

    /**
     * Update properties
     */

    public function uploadFiles()
    {
        $response        = new stdClass();
        $response->error = null;
        $response->debug = null;

        $this->initUploadForm();
        if ($this->form->checkInput()) {
            try {
                $fileresult = $this->handleFileUpload($this->form->getInput("upload_files"));
                if ($fileresult) {
                    $response = (object) array_merge((array) $response, (array) $fileresult);
                }
            } catch (ilException $e) {
                $response->error = $e->getMessage();
            }
        } else {
            $error = new ilCloudException(ilCloudException::UPLOAD_FAILED);
            $response->error = $error->getMessage();
        }

        // send response object (don't use 'application/json' as IE wants to download it!)
        header('Vary: Accept');
        header('Content-type: text/plain');
        echo ilJsonUtil::encode($response);
        exit;
    }

    public function handleFileUpload($file_upload)
    {
        // create answer object
        $response               = new stdClass();
        $response->fileName     = $_POST["title"];
        $response->fileSize     = intval($file_upload["size"]);
        $response->fileType     = $file_upload["type"];
        $response->fileUnzipped = $file_upload["extract"];
        $response->error        = null;

        $file_tree = ilCloudFileTree::getFileTreeFromSession();

        if ($file_upload["extract"]) {
            $newdir = ilUtil::ilTempnam();
            ilUtil::makeDir($newdir);

            include_once './Services/Utilities/classes/class.ilFileUtils.php';
            try {
                ilFileUtils::processZipFile($newdir, $file_upload["tmp_name"], $file_upload["keep_structure"]);
            } catch (Exception $e) {
                $response->error = $e->getMessage();
                ilUtil::delDir($newdir);
                exit;
            }

            try {
                $this->uploadDirectory($newdir, $_SESSION["cld_folder_id"], $file_tree, $file_upload["keep_structure"]);
            } catch (Exception $e) {
                $response->error = $e->getMessage();
                ilUtil::delDir($newdir);
                exit;
            }

            ilUtil::delDir($newdir);
            return $response;
        } else {
            $file_tree->uploadFileToService($_SESSION["cld_folder_id"], $file_upload["tmp_name"], $_POST["title"]);
            return $response;
        }
    }

    /**
     * Recursive Method to upload a directory
     *
     * @param string $dir path to directory
     * @param int $parent_id id of parent folder
     * @param ilCloudFileTree $file_tree
     * @param bool $keep_structure if false, only files will be extracted, without folder structure
     * @throws ilCloudException
     */
    protected function uploadDirectory($dir, $parent_id, $file_tree, $keep_structure = true)
    {
        $dirlist = opendir($dir);

        while (false !== ($file = readdir($dirlist))) {
            if (!is_file($dir . "/" . $file) && !is_dir($dir . "/" . $file)) {
                global $DIC;
                $lng = $DIC['lng'];
                throw new ilCloudException($lng->txt("filenames_not_supported"), ilFileUtilsException::$BROKEN_FILE);
            }
            if ($file != '.' && $file != '..') {
                $newpath = $dir . '/' . $file;
                if (is_dir($newpath)) {
                    if ($keep_structure) {
                        $newnode = $file_tree->addFolderToService($parent_id, basename($newpath));
                        $this->uploadDirectory($newpath, $newnode->getId(), $file_tree);
                    } else {
                        $this->uploadDirectory($newpath, $parent_id, $file_tree, false);
                    }
                } else {
                    $file_tree->uploadFileToService($parent_id, $newpath, basename($newpath));
                }
            }
        }
        closedir($dirlist);
    }
}
