<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

use ILIAS\DI\UIServices;
use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Exercise UI frontend presentation service class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;
    protected \ilToolbarGUI $toolbar;
    protected UIServices $ui;
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    protected InternalService $service;

    protected GUIRequest $request;
    protected \ilExSubmissionGUI $submission_gui;
    protected \ilObjExercise $exc;

    public function __construct(
        InternalService $service,
        HTTP\Services $http,
        Refinery\Factory $refinery,
        array $query_params = null,
        array $post_data = null
    ) {
        global $DIC;

        $this->ui = $DIC->ui();

        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->http = $http;
        $this->refinery = $refinery;

        $this->service = $service;
        $this->request = new GUIRequest(
            $this->http,
            $this->refinery,
            $query_params,
            $post_data
        );
    }

    /**
     * Get request wrapper. If dummy data is provided the usual http wrapper will
     * not be used.
     * @return GUIRequest
     */
    public function request() : GUIRequest
    {
        return $this->request;
    }

    /**
     * @throws \ilExerciseException
     */
    public function getExerciseGUI(?int $ref_id = null) : \ilObjExerciseGUI
    {
        if ($ref_id === null) {
            $ref_id = $this->request->getRequestedRefId();
        }
        return new \ilObjExerciseGUI([], $ref_id, true);
    }

    public function getRandomAssignmentGUI(\ilObjExercise $exc = null) : \ilExcRandomAssignmentGUI
    {
        if ($exc === null) {
            $exc = $this->request->getRequestedExercise();
        }
        return new \ilExcRandomAssignmentGUI(
            $this->ui,
            $this->toolbar,
            $this->lng,
            $this->ctrl,
            $this->service->domain()->assignment()->randomAssignments($exc)
        );
    }

    public function getSubmissionGUI(
        \ilObjExercise $exc = null,
        \ilExAssignment $ass = null,
        $member_id = null
    ) : \ilExSubmissionGUI {
        if ($exc === null) {
            $exc = $this->request->getRequestedExercise();
        }
        if ($ass === null) {
            $ass = $this->request->getRequestedAssignment();
        }
        if ($member_id === null) {
            $member_id = $this->request->getRequestedMemberId();
        }
        return new \ilExSubmissionGUI(
            $exc,
            $ass,
            $member_id
        );
    }
}
