<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise UI frontend presentation service class
 *
 * @author killing@leifos.de
 */
class ilExerciseUI
{
    /**
     * @var ilExerciseUIRequest
     */
    protected $request;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilExSubmissionGUI
     */
    protected $submission_gui;

    /**
     * @var ilObjExercise
     */
    protected $exc;

    /**
     * Constructor
     */
    public function __construct(
        ilExerciseInternalService $service,
        ilExerciseUIRequest $request
    )
    {
        global $DIC;

        $this->ui = $DIC->ui();

        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->service = $service;
        $this->request = $request;
    }

    /**
     * @return ilObjExerciseGUI
     */
    public function getExerciseGUI(int $ref_id = null)
    {
        if ($ref_id === null) {
            $ref_id = $this->request->getRequestedRefId();
        }
        return new ilObjExerciseGUI([], $ref_id,true,false);
    }

    /**
     * @return ilExcRandomAssignmentGUI
     */
    public function getRandomAssignmentGUI(ilObjExercise $exc = null)
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

    /**
     * @return ilExSubmissionGUI
     */
    public function getSubmissionGUI(ilObjExercise $exc = null,
        ilExAssignment $ass = null,
        $member_id = null
    )
    {
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
            $member_id);
    }


}