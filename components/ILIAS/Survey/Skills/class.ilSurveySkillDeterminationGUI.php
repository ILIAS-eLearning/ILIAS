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
 * Survey skill determination GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilSurveySkillDeterminationGUI:
 */
class ilSurveySkillDeterminationGUI
{
    protected ilObjSurvey $survey;
    protected ilCtrl $ctrl;
    protected ilTemplate $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;

    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->survey = $a_survey;
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("listSkillChanges");

        if ($cmd === "listSkillChanges") {
            $this->$cmd();
        }
    }

    public function listSkillChanges(): void
    {
        $tpl = $this->tpl;

        if ($this->survey->get360Mode()) {
            $apps = $this->survey->getAppraiseesData();
        } else { // Mode self evaluation, No Appraisee and Rater involved.
            $apps = $this->survey->getSurveyParticipants();
        }
        $ctpl = new ilTemplate("tpl.svy_skill_list_changes.html", true, true, "Modules/Survey");
        foreach ($apps as $app) {
            $changes_table = new ilSurveySkillChangesTableGUI(
                $this,
                "listSkillChanges",
                $this->survey,
                $app
            );

            $ctpl->setCurrentBlock("appraisee");
            $ctpl->setVariable("LASTNAME", $app["lastname"]);
            $ctpl->setVariable("FIRSTNAME", $app["firstname"]);

            $ctpl->setVariable("CHANGES_TABLE", $changes_table->getHTML());

            $ctpl->parseCurrentBlock();
        }

        $tpl->setContent($ctpl->get());
    }
}
