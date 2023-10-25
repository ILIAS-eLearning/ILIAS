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

use ILIAS\Refinery;
use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Exercise\InternalDataService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\Assignment;
use ILIAS\Exercise\PeerReview;
use ILIAS\Exercise\PermanentLink\PermanentLinkManager;

/**
 * Exercise UI frontend presentation service class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected \ILIAS\Exercise\InternalDataService $data_service;
    protected \ILIAS\Exercise\InternalDomainService $domain_service;
    protected \ilLanguage $lng;
    protected Refinery\Factory $refinery;

    protected InternalService $service;

    protected ?GUIRequest $request = null;
    protected \ilExSubmissionGUI $submission_gui;
    protected \ilObjExercise $exc;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    public function assignment(): Assignment\GUIService
    {
        return new Assignment\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function peerReview(): PeerReview\GUIService
    {
        return new PeerReview\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function permanentLink(): PermanentLinkManager
    {
        return new PermanentLinkManager(
            $this->domain_service,
            $this
        );
    }


    /**
     * Get request wrapper. If dummy data is provided the usual http wrapper will
     * not be used.
     * @return GUIRequest
     */
    public function request(
        ?array $query_params = null,
        ?array $post_data = null
    ): GUIRequest {
        if (is_null($query_params) && is_null($post_data) && !is_null($this->request)) {
            return $this->request;
        }
        $request = new GUIRequest(
            $this->http(),
            $this->domain_service->refinery(),
            $query_params,
            $post_data
        );
        if (is_null($query_params) && is_null($post_data)) {
            $this->request = $request;
        }
        return $request;
    }

    /**
     * @throws \ilExerciseException
     */
    public function getExerciseGUI(?int $ref_id = null): \ilObjExerciseGUI
    {
        if ($ref_id === null) {
            $ref_id = $this->request()->getRefId();
        }
        return new \ilObjExerciseGUI([], $ref_id, true);
    }

    public function getRandomAssignmentGUI(\ilObjExercise $exc = null): \ilExcRandomAssignmentGUI
    {
        if ($exc === null) {
            $exc = $this->request()->getExercise();
        }
        return new \ilExcRandomAssignmentGUI(
            $this->ui(),
            $this->toolbar(),
            $this->domain_service->lng(),
            $this->ctrl(),
            $this->domain_service->assignment()->randomAssignments($exc)
        );
    }

    public function getSubmissionGUI(
        \ilObjExercise $exc = null,
        \ilExAssignment $ass = null,
        $member_id = null
    ): \ilExSubmissionGUI {
        if ($exc === null) {
            $exc = $this->request()->getExercise();
        }
        if ($ass === null) {
            $ass = $this->request()->getAssignment();
        }
        if ($member_id === null) {
            $member_id = $this->request()->getMemberId();
        }
        return new \ilExSubmissionGUI(
            $exc,
            $ass,
            $member_id
        );
    }

    public function getTeamSubmissionGUI(
        \ilObjExercise $exc,
        \ilExSubmission $submission
    ): \ilExSubmissionTeamGUI {
        return new \ilExSubmissionTeamGUI($exc, $submission);
    }
}
