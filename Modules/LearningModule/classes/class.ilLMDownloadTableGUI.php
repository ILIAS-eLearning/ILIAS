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
 * TableGUI class for lm download files
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMDownloadTableGUI extends ilTable2GUI
{
    protected ilObjLearningModule $lm;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm
    ) {
        $this->lm = $a_lm;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->lm->getPublicExportFiles());
        $this->setTitle($this->lng->txt("download"));
        
        $this->addColumn($this->lng->txt("cont_format"));
        $this->addColumn($this->lng->txt("cont_file"));
        $this->addColumn($this->lng->txt("size"));
        $this->addColumn($this->lng->txt("date"));
        $this->addColumn($this->lng->txt("action"));
        $this->disable("footer");
        $this->setMaxCount(9999);
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.download_file_row.html", "Modules/LearningModule");
    }

    public function getDownloadFiles() : void
    {
        $this->setData($this->lm->getPublicExportFiles());
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("TXT_FILENAME", $a_set["file"]);

        $this->tpl->setVariable("TXT_SIZE", ilUtil::formatSize($a_set["size"]));
        $this->tpl->setVariable("TXT_FORMAT", strtoupper($a_set["type"]));
        $this->tpl->setVariable("CHECKBOX_ID", $a_set["type"] . ":" . $a_set["file"]);

        $file_arr = explode("__", $a_set["file"]);
        ilDatePresentation::setUseRelativeDates(false);
        $this->tpl->setVariable("TXT_DATE", ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)));

        $this->tpl->setVariable("TXT_DOWNLOAD", $this->lng->txt("download"));
        $this->ctrl->setParameter($this->parent_obj, "type", $a_set["dir_type"]);
        $this->tpl->setVariable(
            "LINK_DOWNLOAD",
            $this->ctrl->getLinkTarget($this->parent_obj, "downloadExportFile")
        );

        $this->tpl->parseCurrentBlock();
    }
}
