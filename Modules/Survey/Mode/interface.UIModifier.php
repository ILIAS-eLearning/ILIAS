<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalUIService;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface UIModifier
{
    public function setInternalService(InternalService $internal_service) : void;
    public function getInternalService() : InternalService;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ) : array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array;

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void;

    public function setResultsOverviewToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id,
        int $appraisee_id = 0
    ) : void;

    public function setResultsDetailToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id,
        int $appraisee_id = 0
    ) : void;

    public function getDetailPanels(
        array $participants,
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ) : array;
}
