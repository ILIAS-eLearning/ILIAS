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

namespace ILIAS\Survey\Mode\SelfEvaluation;

use ILIAS\Survey\Mode;
use ILIAS\Survey\InternalGUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class UIModifier extends Mode\AbstractUIModifier
{
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array {
        $items = [];
        $lng = $ui_service->lng();

        //check the names of these vars
        $evaluation_access = new \ilRadioGroupInputGUI($lng->txt('evaluation_access'), "self_eval_res");
        $evaluation_access->setValue((string) $survey->getSelfEvaluationResults());

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_none"), (string) \ilObjSurvey::RESULTS_SELF_EVAL_NONE);
        $evaluation_access->addOption($option);

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_own"), (string) \ilObjSurvey::RESULTS_SELF_EVAL_OWN);
        $evaluation_access->addOption($option);

        $option = new \ilRadioOption($lng->txt("svy_self_ev_access_results_all"), (string) \ilObjSurvey::RESULTS_SELF_EVAL_ALL);
        $evaluation_access->addOption($option);

        $items[] = $evaluation_access;

        return $items;
    }

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ): void {
        $survey->setSelfEvaluationResults($form->getInput("self_eval_res"));
    }
}
