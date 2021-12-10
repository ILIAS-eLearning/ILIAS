<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\SelfEvaluation;

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
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        //check the names of these vars
        $evaluation_access = new \ilRadioGroupInputGUI($lng->txt('evaluation_access'), "self_eval_res");
        $evaluation_access->setValue($survey->getSelfEvaluationResults());

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_none"), \ilObjSurvey::RESULTS_SELF_EVAL_NONE);
        $evaluation_access->addOption($option);

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_own"), \ilObjSurvey::RESULTS_SELF_EVAL_OWN);
        $evaluation_access->addOption($option);

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_all"), \ilObjSurvey::RESULTS_SELF_EVAL_ALL);
        $evaluation_access->addOption($option);

        $items[] = $evaluation_access;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void {
        $survey->setSelfEvaluationResults($form->getInput("self_eval_res"));
    }
}
