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
 * Upload dir files table
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUploadDirFilesTableGUI extends ilTable2GUI
{
    protected string $upload_dir;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_files
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $import_directory_factory = new ilImportDirectoryFactory();
        $mob_import_directory = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_MOB);
        $this->upload_dir = $mob_import_directory->getAbsolutePath();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getFiles($a_files));
        $this->setTitle($lng->txt("mep_upload_dir_files"));
        $this->setLimit(9999);

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("mep_file"));
        $this->setOpenFormTag(false);

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.upload_dir_files_row.html", "Modules/MediaPool");
        $this->disable("footer");
        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox("file[]");

        $this->addMultiCommand("createMediaFromUploadDir", $lng->txt("mep_create_media_files"));
    }

    /**
     * Get files
     */
    public function getFiles(array $a_files): array
    {
        $files = array();
        foreach ($a_files as $f) {
            if (is_file($this->upload_dir . "/" . $f)) {
                $files[] = $f;
            } elseif (is_dir($this->upload_dir . "/" . $f)) {
                $dir = ilFileUtils::getDir($this->upload_dir . "/" . $f, true);
                foreach ($dir as $d) {
                    if ($d["type"] === "file") {
                        $files[] = $f . $d["subdir"] . "/" . $d["entry"];
                    }
                }
            }
        }

        return $files;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TXT_FILE", $a_set);
        $this->tpl->setVariable("VAL_FILE", $a_set);
    }
}
