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

    public function __construct(string $main_absolute_directory)
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
        parent::__construct($main_absolute_directory);
    }

    public function getTable(
        string $a_dir,
        string $a_subdir
    ): ilExAssignmentFileSystemTableGUI {
        return new ilExAssignmentFileSystemTableGUI(
            $this,
            "listFiles",
            $a_dir,
            $a_subdir,
            $this->label_enable,
            $this->file_labels,
            "",
            $this->getActionCommands(),
            $this->getPostDirPath()
        );
    }


    public function uploadFile(): void
    {
        $filename = ilUtil::stripSlashes($_FILES["new_file"]["name"]);

        ilExAssignment::instructionFileInsertOrder($filename, $this->requested_ass_id);
        parent::uploadFile();
    }

    public function saveFilesOrder(): void
    {
        $ilCtrl = $this->ctrl;

        if ($this->requested_ass_id > 0) {
            ilExAssignment::saveInstructionFilesOrderOfAssignment($this->requested_ass_id, $this->requested_order);
            $ilCtrl->redirect($this, "listFiles");
        }
    }

    public function deleteFile(): void
    {
        if ($this->requested_ass_id > 0) {
            ilExAssignment::instructionFileDeleteOrder($this->requested_ass_id, $this->requested_file);

            parent::deleteFile();
        }
    }

    /**
     * Rename File name
     */
    public function renameFile(): void
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
