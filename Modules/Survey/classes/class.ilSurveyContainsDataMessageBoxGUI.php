<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Message box for survey, when data is alrady available.
 *
 * Should be moved to some survey ui subservice in the future.
 *
 * @author @leifos.de
 * @ingroup
 */
class ilSurveyContainsDataMessageBoxGUI
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Get HTML
     *
     * @return string
     */
    public function getHTML()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        $mbox = $ui->factory()->messageBox()->info($lng->txt("survey_has_datasets_warning_page_view"))
            ->withLinks([$ui->factory()->link()->standard(
                $lng->txt("survey_has_datasets_warning_page_view_link"),
                $ctrl->getLinkTargetByClass(["ilObjSurveyGUI", "ilSurveyParticipantsGUI"], "maintenance")
            )]);

        return $ui->renderer()->render($mbox);
    }
}
