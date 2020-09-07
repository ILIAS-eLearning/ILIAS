<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for lm download files
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMDownloadTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilObjLearningModule $a_lm)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
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

    /**
     * Get download files
     */
    public function getDownloadFiles()
    {
        $this->setData($this->lm->getPublicExportFiles());
    }


    /**
     * Fill table row
     */
    protected function fillRow($a_set)
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
