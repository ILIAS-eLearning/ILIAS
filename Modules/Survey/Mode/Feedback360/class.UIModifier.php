<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Mode\Feedback360;

use ILIAS\Survey\Mode;
use ILIAS\Survey\InternalGUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class UIModifier extends Mode\AbstractUIModifier
{
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ): array {
        $items = [];
        $lng = $this->service->gui()->lng();

        $self_eval = new \ilCheckboxInputGUI($lng->txt("survey_360_self_evaluation"), "self_eval");
        $self_eval->setInfo($lng->txt("survey_360_self_evaluation_info"));
        $self_eval->setChecked($survey->get360SelfEvaluation());
        $items[] = $self_eval;

        return $items;
    }

    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array {
        $items = [];
        $lng = $ui_service->lng();

        // remind appraisees
        $cb = new \ilCheckboxInputGUI($lng->txt("survey_notification_target_group"), "remind_appraisees");
        $cb->setOptionTitle($lng->txt("survey_360_appraisees"));
        $cb->setInfo($lng->txt("survey_360_appraisees_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_APPRAISEES, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS),
            true
        ));
        $items[] = $cb;

        // remind raters
        $cb = new \ilCheckboxInputGUI("", "remind_raters");
        $cb->setOptionTitle($lng->txt("survey_360_raters"));
        $cb->setInfo($lng->txt("survey_360_raters_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_RATERS, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS),
            true
        ));
        $items[] = $cb;

        return $items;
    }

    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array {
        $items = [];
        $lng = $ui_service->lng();

        $ts_results = new \ilRadioGroupInputGUI($lng->txt("survey_360_results"), "ts_res");
        $ts_results->setValue((string) $survey->get360Results());

        $option = new \ilRadioOption($lng->txt("survey_360_results_none"), (string) \ilObjSurvey::RESULTS_360_NONE);
        $option->setInfo($lng->txt("survey_360_results_none_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption($lng->txt("survey_360_results_own"), (string) \ilObjSurvey::RESULTS_360_OWN);
        $option->setInfo($lng->txt("survey_360_results_own_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption($lng->txt("survey_360_results_all"), (string) \ilObjSurvey::RESULTS_360_ALL);
        $option->setInfo($lng->txt("survey_360_results_all_info"));
        $ts_results->addOption($option);

        $items[] = $ts_results;

        return $items;
    }

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ): void {
        if ($form->getInput("remind_appraisees") && $form->getInput("remind_raters")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS);
        } elseif ($form->getInput("remind_appraisees")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_APPRAISEES);
        } elseif ($form->getInput("remind_raters")) {
            $survey->setReminderTarget(\ilObjSurvey::NOTIFICATION_RATERS);
        } else {
            $survey->setReminderTarget(0);
        }

        $survey->set360SelfEvaluation((bool) $form->getInput("self_eval"));
        $survey->set360Results((int) $form->getInput("ts_res"));
    }
}
