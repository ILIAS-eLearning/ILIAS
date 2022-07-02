<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Mode\Standard;

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
    ) : array {
        $items = [];
        $lng = $ui_service->lng();
        $anon_list = null;

        $evaluation_access = new \ilRadioGroupInputGUI($lng->txt('evaluation_access'), "evaluation_access");

        $option = new \ilCheckboxOption($lng->txt("evaluation_access_off"), \ilObjSurvey::EVALUATION_ACCESS_OFF, '');
        $option->setInfo($lng->txt("svy_evaluation_access_off_info"));
        $evaluation_access->addOption($option);

        $option = new \ilCheckboxOption($lng->txt("evaluation_access_all"), \ilObjSurvey::EVALUATION_ACCESS_ALL, '');
        $option->setInfo($lng->txt("svy_evaluation_access_all_info"));
        $evaluation_access->addOption($option);

        $option = new \ilCheckboxOption($lng->txt("evaluation_access_participants"), \ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS, '');
        $option->setInfo($lng->txt("svy_evaluation_access_participants_info"));
        $evaluation_access->addOption($option);

        $evaluation_access->setValue($survey->getEvaluationAccess());
        $items[] = $evaluation_access;

        $anonymization_options = new \ilRadioGroupInputGUI($lng->txt("survey_results_anonymization"), "anonymization_options");

        $option = new \ilCheckboxOption($lng->txt("survey_results_personalized"), "statpers");
        $option->setInfo($lng->txt("survey_results_personalized_info"));
        $anonymization_options->addOption($option);

        $option = new \ilCheckboxOption($lng->txt("survey_results_anonymized"), "statanon");
        $option->setInfo($lng->txt("survey_results_anonymized_info"));
        $anonymization_options->addOption($option);
        $anonymization_options->setValue($survey->hasAnonymizedResults()
            ? "statanon"
            : "statpers");
        $items[] = $anonymization_options;

        $surveySetting = new \ilSetting("survey");
        if ($surveySetting->get("anonymous_participants", null)) {
            $min = "";
            if ((int) $surveySetting->get("anonymous_participants_min", "0") > 0) {
                $min = " (" . $lng->txt("svy_anonymous_participants_min") . ": " .
                    $surveySetting->get("anonymous_participants_min") . ")";
            }

            $anon_list = new \ilCheckboxInputGUI($lng->txt("svy_anonymous_participants_svy"), "anon_list");
            $anon_list->setInfo($lng->txt("svy_anonymous_participants_svy_info") . $min);
            $anon_list->setChecked($survey->hasAnonymousUserList());
            $option->addSubItem($anon_list);
        }

        if ($survey->_hasDatasets($survey->getSurveyId())) {
            $anonymization_options->setDisabled(true);
            if ($anon_list) {
                $anon_list->setDisabled(true);
            }
        }

        return $items;
    }

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void {
        $survey->setEvaluationAccess($form->getInput("evaluation_access"));
        $survey->setCalculateSumScore((bool) $form->getInput("calculate_sum_score"));
        $hasDatasets = \ilObjSurvey::_hasDatasets($survey->getSurveyId());
        if (!$hasDatasets) {
            $current = $survey->getAnonymize();

            // get current setting if property is hidden
            $codes = (bool) $form->getInput("acc_codes");
            $anon = ((string) $form->getInput("anonymization_options") === "statanon");

            // parse incoming values
            if (!$anon) {
                if (!$codes) {
                    $survey->setAnonymize(\ilObjSurvey::ANONYMIZE_OFF);
                } else {
                    $survey->setAnonymize(\ilObjSurvey::ANONYMIZE_CODE_ALL);
                }
            } else {
                if ($codes) {
                    $survey->setAnonymize(\ilObjSurvey::ANONYMIZE_ON);
                } else {
                    $survey->setAnonymize(\ilObjSurvey::ANONYMIZE_FREEACCESS);
                }

                $survey->setAnonymousUserList((bool) $form->getInput("anon_list"));
            }
        }
    }
}
