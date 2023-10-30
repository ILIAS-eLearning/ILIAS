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

declare(strict_types=1);

/**
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @ingroup ModulesTest
 */
class ilAssessmentFolderLogTableGUI extends ilTable2GUI
{
    protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;

    public function __construct(
        ilObjAssessmentFolderGUI $parent_obj,
        string $parent_cmd
    ) {
        global $DIC;
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();

        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('showlog');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("assessment_log_datetime"), 'date', '10%');
        $this->addColumn($this->lng->txt("user"), 'user', '20%');
        $this->addColumn($this->lng->txt("assessment_log_text"), 'message', '50%');
        $this->addColumn($this->lng->txt("ass_location"), '', '20%');

        $this->setRowTemplate("tpl.il_as_tst_assessment_log_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("asc");

        $this->enable('header');
        $this->enable('sort');
        $this->disable('select_all');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime((int) $a_set['tstamp'], IL_CAL_UNIX)));
        $user = ilObjUser::_lookupName((int) $a_set["user_fi"]);
        $this->tpl->setVariable(
            "USER",
            ilLegacyFormElementsUtil::prepareFormOutput(
                trim($user["title"] . " " . $user["firstname"] . " " . $user["lastname"])
            )
        );

        $title = "";
        if ($a_set["question_fi"] || $a_set["original_fi"]) {
            $title = $this->questioninfo->getQuestionTitle((int) $a_set["question_fi"]);
            if ($title === '') {
                $title = $this->questioninfo->getQuestionTitle((int) $a_set["original_fi"]);
            }
            $title = $this->lng->txt("assessment_log_question") . ": " . $title;
        }
        $this->tpl->setVariable(
            "MESSAGE",
            ilLegacyFormElementsUtil::prepareFormOutput($a_set['logtext']) . (($title !== '') ? " (" . $title . ")" : '')
        );

        if ($a_set['location_href'] !== '' && $a_set['location_txt'] !== '') {
            $this->tpl->setVariable("LOCATION_HREF", $a_set['location_href']);
            $this->tpl->setVariable("LOCATION_TXT", $a_set['location_txt']);
        }
    }
}
