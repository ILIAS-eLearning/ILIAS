<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/FileSystem/classes/class.ilFileSystemGUI.php');

/**
 * File System Explorer GUI class
 *
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 */
class ilExAssignmentFileSystemGUI extends ilFileSystemGUI
{
    public function __construct($a_main_directory)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_main_directory);
    }

    /**
     * Get table
     *
     * @param
     * @return
     */
    public function getTable($a_dir, $a_subdir)
    {
        include_once("./Modules/Exercise/classes/class.ilExAssignmentFileSystemTableGUI.php");
        return new ilExAssignmentFileSystemTableGUI(
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
     * Insert into database the file order and update the file.
     *
     * @param string view to redirect
     */
    public function uploadFile()
    {
        $filename = ilUtil::stripSlashes($_FILES["new_file"]["name"]);

        ilExAssignment::instructionFileInsertOrder($filename, $_GET['ass_id']);
        parent::uploadFile();
    }

    /**
     * Save all the orders.
     */
    public function saveFilesOrder()
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["ass_id"]) {
            ilExAssignment::saveInstructionFilesOrderOfAssignment($_GET['ass_id'], $_POST["order"]);
            $ilCtrl->redirect($this, "listFiles");
        }
    }

    /**
     * delete object file
     * we can pass one parameter to deleteFile in fileSystemGUI, that contains the name of the class to redirect.
     * @param string view to redirect
     */
    public function deleteFile()
    {
        if ($_GET["ass_id"]) {
            ilExAssignment::instructionFileDeleteOrder($_GET['ass_id'], $_POST["file"]);

            parent::deleteFile();
        }
    }

    /**
     * Rename File name
     */
    public function renameFile()
    {
        if ($_GET["ass_id"]) {
            $new_name = str_replace("..", "", ilUtil::stripSlashes($_POST["new_name"]));
            $old_name = str_replace("/", "", $_GET["old_name"]);

            if ($new_name != $old_name) {
                ilExAssignment::renameInstructionFile($old_name, $new_name, $_GET['ass_id']);
            }
        }
        parent::renameFile();
    }
}
