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
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd();

        switch ($cmd)
        {
            default:
                $this->$cmd();
                break;
        }
    }

    function asyncUploadFile()
    {
        global $ilTabs;

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
        global $ilCtrl, $lng;

        include_once("./Services/Form/classes/class.ilDragDropFileInputGUI.php");
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");

        $this->form = new ilPropertyFormGUI();
        $this->form->setId("upload");
        $this->form->setMultipart(true);
        $this->form->setHideLabels();

        $file = new ilDragDropFileInputGUI($lng->txt("cld_upload_flies"), "upload_files");
        $file->setRequired(true);
        $this->form->addItem($file);

        $this->form->addCommandButton("uploadFiles", $lng->txt("upload"));
        $this->form->addCommandButton("cancelAll", $lng->txt("cancel"));

        $this->form->setTableWidth("100%");
        $this->form->setTitle($lng->txt("upload_files_title"));
        $this->form->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $lng->txt('obj_file'));

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
        if ($this->form->checkInput())
        {
            try
            {
                $fileresult = $this->handleFileUpload($this->form->getInput("upload_files"));
                if ($fileresult)
                {
                    $response = (object)array_merge((array)$response, (array)$fileresult);
                }
            } catch (ilException $e)
            {
                $response->error = $e->getMessage();
            }
        } else
        {
            $error = new ilCloudException(ilCloudException::UPLOAD_FAILED);
            $response->error = $error->getMessage();
        }

        // send response object (don't use 'application/json' as IE wants to download it!)
        header('Vary: Accept');
        header('Content-type: text/plain');
        echo ilJsonUtil::encode($response);
        exit;
    }

    function handleFileUpload($file_upload)
    {
        // create answer object
        $response               = new stdClass();
        $response->fileName     = $_POST["title"];
        $response->fileSize     = intval($file_upload["size"]);
        $response->fileType     = $file_upload["type"];
        $response->fileUnzipped = false;
        $response->error        = null;

        $file_tree = ilCloudFileTree::getFileTreeFromSession();
        $file_tree->uploadFileToService($_SESSION["cld_folder_id"], $file_upload["tmp_name"], $_POST["title"]);
        return $response;
    }
}

?>