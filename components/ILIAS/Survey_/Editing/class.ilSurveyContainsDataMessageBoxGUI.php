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
 * Message box for survey, when data is alrady available.
 * Should be moved to some survey ui subservice in the future.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveyContainsDataMessageBoxGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public function getHTML(): string
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
