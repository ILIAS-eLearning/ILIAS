<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * File System Explorer GUI class
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExAssignmentFileSystemGUI extends ilFileSystemGUI
{
    public function __construct($a_main_directory)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_main_directory);
    }

    public function getTable(
        $a_dir,
        $a_subdir
    ) : ilExAssignmentFileSystemTableGUI {
        return new ilExAssignmentFileSystemTableGUI(
            $this,
            "listFiles",
            $a_dir,
            $a_subdir,
            $this->label_enable,
            $this->file_labels,
            "",
            [],
            $this->getPostDirPath(),
            $this->getTableId()
        );
    }


    public function uploadFile() : void
    {
        $filename = ilUtil::stripSlashes($_FILES["new_file"]["name"]);

        ilExAssignment::instructionFileInsertOrder($filename, $_GET['ass_id']);
        parent::uploadFile();
    }

    public function saveFilesOrder() : void
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["ass_id"]) {
            ilExAssignment::saveInstructionFilesOrderOfAssignment($_GET['ass_id'], $_POST["order"]);
            $ilCtrl->redirect($this, "listFiles");
        }
    }

    public function deleteFile() : void
    {
        if ($_GET["ass_id"]) {
            ilExAssignment::instructionFileDeleteOrder($_GET['ass_id'], $_POST["file"]);

            parent::deleteFile();
        }
    }

    /**
     * Rename File name
     */
    public function renameFile() : void
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
