<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Scorm2004\Editor;

/**
 * @author killing@leifos.de
 */
class ilSCORMQuestionOverviewTableGUI extends \ilTable2GUI
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

        $this->id = "sahs_sco_quest";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt("sahs_questions"));

        $this->addColumn($this->lng->txt("page"));
        $this->addColumn($this->lng->txt("question"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.question_overview_row.html", "Modules/Scorm2004/Editor");
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $ui = $this->ui;

        $ctrl->setParameterByClass("ilscorm2004pagenodegui", "obj_id", $a_set["page"]["obj_id"]);
        $link = $ui->factory()->link()->standard(
            $a_set["page"]["title"],
            $ctrl->getLinkTargetByClass("ilscorm2004pagenodegui", "edit")
        );
        $tpl->setVariable("PAGE", $ui->renderer()->render($link));
        $tpl->setVariable("QUESTION", \assQuestion::_getTitle($a_set["qid"]));
    }
}