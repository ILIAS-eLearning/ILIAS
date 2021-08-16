<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\DI\UIServices;

/**
 * Exercise UI frontend presentation service class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilExerciseInternalService $service;

    protected ilExerciseUIRequest $request;
    protected UIServices $ui;
    protected ilExSubmissionGUI $submission_gui;
    protected ilObjExercise $exc;

    public function __construct(
        ilExerciseInternalService $service,
        ilExerciseUIRequest $request
    ) {
        global $DIC;

        $this->ui = $DIC->ui();

        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->service = $service;
        $this->request = $request;
    }

    /**
     * @throws ilExerciseException
     */
    public function getExerciseGUI(?int $ref_id = null) : ilObjExerciseGUI
    {
        if ($ref_id === null) {
            $ref_id = $this->request->getRequestedRefId();
        }
        return new ilObjExerciseGUI([], $ref_id, true);
    }

    public function getRandomAssignmentGUI(ilObjExercise $exc = null) : ilExcRandomAssignmentGUI
    {
        if ($exc === null) {
            $exc = $this->request->getRequestedExercise();
        }
        return new ilExcRandomAssignmentGUI(
            $this->ui,
            $this->toolbar,
            $this->lng,
            $this->ctrl,
            $this->service->getRandomAssignmentManager($exc)
        );
    }

    public function getSubmissionGUI(
        ilObjExercise $exc = null,
        ilExAssignment $ass = null,
        $member_id = null
    ) : ilExSubmissionGUI {
        if ($exc === null) {
            $exc = $this->request->getRequestedExercise();
        }
        if ($ass === null) {
            $ass = $this->request->getRequestedAssignment();
        }
        if ($member_id === null) {
            $member_id = $this->request->getRequestedMemberId();
        }
        return new ilExSubmissionGUI(
            $exc,
            $ass,
            $member_id
        );
    }
}
