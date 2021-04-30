<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Scorm2004\Editor;

/**
 * @author killing@leifos.de
 */
class ilSCORMMediaOverviewTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->id = "sahs_sco_media";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt("cont_files"));

        $this->addColumn($this->lng->txt("cont_format"));
        $this->addColumn($this->lng->txt("cont_file"));
        $this->addColumn($this->lng->txt("size"));
        $this->addColumn($this->lng->txt("date"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.media_overview_row.html", "Modules/Scorm2004/Editor");
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $tpl->setVariable("FILE", $a_set["file"]);
        $tpl->setVariable("SIZE", $a_set["size"]);
        $tpl->setVariable("FORMAT", $a_set["type"]);

        $tpl->setVariable("DATE", $a_set["date"]);

        if ($a_set["size"] > 0) {
            $tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
            $ctrl->setParameter($this->parent_obj, "resource", rawurlencode($a_set["path"]));
            $ctrl->setParameter($this->parent_obj, "file_id", rawurlencode($a_set["file_id"]));
            $link = $ui->factory()->link()->standard(
                $lng->txt("download"),
                $ctrl->getLinkTarget($this->parent_obj, "downloadResource")
            );
        } else {
            $link = $ui->factory()->link()->standard(
                $lng->txt("show"),
                $a_set["path"]
            )->withOpenInNewViewport(true);
        }
        $tpl->setVariable(
            "DOWNLOAD",
            $ui->renderer()->render($link)
        );
    }
}