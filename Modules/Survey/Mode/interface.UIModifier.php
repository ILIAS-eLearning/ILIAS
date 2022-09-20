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

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface UIModifier
{
    public function setInternalService(InternalService $internal_service): void;
    public function getInternalService(): InternalService;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ): array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array;

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ): void;

    public function setResultsOverviewToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void;

    public function setResultsDetailToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void;

    public function setResultsCompetenceToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void;

    public function getDetailPanels(
        array $participants,
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ): array;
}
