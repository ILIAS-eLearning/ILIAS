<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * File System Explorer GUI class
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExAssignmentFileSystemGUI extends ilFileSystemGUI
{
    protected int $requested_ass_id;
    protected string $requested_old_name;
    protected string $requested_new_name;
    protected array $requested_order;
    /**
     * @var string[]
     */
    protected array $requested_file;

    public function __construct($a_main_directory)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_ass_id = $request->getAssId();
        $this->requested_old_name = $request->getOldName();
        $this->requested_new_name = $request->getNewName();
        $this->requested_order = $request->getOrder();
        $this->requested_file = $request->getFiles();

        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_main_directory);
    }

    public function getTable(
        string $a_dir,
        string $a_subdir
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
            $this->getPostDirPath()
        );
    }


    public function uploadFile() : void
    {
        $filename = ilUtil::stripSlashes($_FILES["new_file"]["name"]);

        ilExAssignment::instructionFileInsertOrder($filename, $this->requested_ass_id);
        parent::uploadFile();
    }

    public function saveFilesOrder() : void
    {
        $ilCtrl = $this->ctrl;

        if ($this->requested_ass_id > 0) {
            ilExAssignment::saveInstructionFilesOrderOfAssignment($this->requested_ass_id, $this->requested_order);
            $ilCtrl->redirect($this, "listFiles");
        }
    }

    public function deleteFile() : void
    {
        if ($this->requested_ass_id > 0) {
            ilExAssignment::instructionFileDeleteOrder($this->requested_ass_id, $this->requested_file);

            parent::deleteFile();
        }
    }

    /**
     * Rename File name
     */
    public function renameFile() : void
    {
        if ($this->requested_ass_id > 0) {
            $new_name = str_replace("..", "", ilUtil::stripSlashes($this->requested_new_name));
            $old_name = str_replace("/", "", $this->requested_old_name);

            if ($new_name != $old_name) {
                ilExAssignment::renameInstructionFile($old_name, $new_name, $this->requested_ass_id);
            }
        }
        parent::renameFile();
    }
}
