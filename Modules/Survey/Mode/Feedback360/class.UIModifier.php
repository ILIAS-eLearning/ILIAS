<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\Feedback360;

use \ILIAS\Survey\Mode;
use ILIAS\Survey\InternalUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class UIModifier extends Mode\AbstractUIModifier
{
    /**
     * @inheritDoc
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ) : array {
        $items = [];
        $lng = $this->gui_service->lng();

        $self_eval = new \ilCheckboxInputGUI($lng->txt("survey_360_self_evaluation"), "self_eval");
        $self_eval->setInfo($lng->txt("survey_360_self_evaluation_info"));
        $self_eval->setChecked($survey->get360SelfEvaluation());
        $items[] = $self_eval;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        // remind appraisees
        $cb = new \ilCheckboxInputGUI($lng->txt("survey_notification_target_group"), "remind_appraisees");
        $cb->setOptionTitle($lng->txt("survey_360_appraisees"));
        $cb->setInfo($lng->txt("survey_360_appraisees_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_APPRAISEES, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS)
        ));
        $items[] = $cb;

        // remind raters
        $cb = new \ilCheckboxInputGUI("", "remind_raters");
        $cb->setOptionTitle($lng->txt("survey_360_raters"));
        $cb->setInfo($lng->txt("survey_360_raters_remind_info"));
        $cb->setValue("1");
        $cb->setChecked(in_array(
            $survey->getReminderTarget(),
            array(\ilObjSurvey::NOTIFICATION_RATERS, \ilObjSurvey::NOTIFICATION_APPRAISEES_AND_RATERS)
        ));
        $items[] = $cb;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        $ts_results = new \ilRadioGroupInputGUI($lng->txt("survey_360_results"), "ts_res");
        $ts_results->setValue($survey->get360Results());

        $option = new \ilRadioOption($lng->txt("survey_360_results_none"), \ilObjSurvey::RESULTS_360_NONE);
        $option->setInfo($lng->txt("survey_360_results_none_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption($lng->txt("survey_360_results_own"), \ilObjSurvey::RESULTS_360_OWN);
        $option->setInfo($lng->txt("survey_360_results_own_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption($lng->txt("survey_360_results_all"), \ilObjSurvey::RESULTS_360_ALL);
        $option->setInfo($lng->txt("survey_360_results_all_info"));
        $ts_results->addOption($option);

        $items[] = $ts_results;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void {
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
