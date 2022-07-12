<?php

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
            $ref_id = $this->request->getRefId();
        }
        return new \ilObjExerciseGUI([], $ref_id, true);
    }

    public function getRandomAssignmentGUI(\ilObjExercise $exc = null) : \ilExcRandomAssignmentGUI
    {
        if ($exc === null) {
            $exc = $this->request->getExercise();
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
            $exc = $this->request->getExercise();
        }
        if ($ass === null) {
            $ass = $this->request->getAssignment();
        }
        if ($member_id === null) {
            $member_id = $this->request->getMemberId();
        }
        return new \ilExSubmissionGUI(
            $exc,
            $ass,
            $member_id
        );
    }
}
