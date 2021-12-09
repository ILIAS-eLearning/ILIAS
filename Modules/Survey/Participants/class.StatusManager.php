<?php
declare(strict_types = 1);

namespace ILIAS\Survey\Participants;

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;
use ILIAS\Survey\Execution\RunDBRepository;

/**
 * Participant status manager
 *
 * @author killing@leifos.de
 */
class StatusManager
{
    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * @var \ilObjSurvey
     */
    protected $survey;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var \ILIAS\Survey\Access\AccessManager
     */
    protected $access;

    /**
     * @var \ILIAS\Survey\Mode\FeatureConfig
     */
    protected $feature_config;

    /**
     * @var InternalRepoService
     */
    protected $repo_service;

    /**
     * Constructor
     */
    public function __construct(
        InternalDomainService $domain_service,
        InternalRepoService $repo_service,
        \ilObjSurvey $survey,
        int $user_id
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
        $this->access = $this->domain_service->access($survey->getRefId(), $user_id);
        //$this->anonymous_session_repo = $this->
        $this->feature_config = $this->domain_service->modeFeatureConfig($survey->getMode());
        $this->survey = $survey;
        $this->user_id = $user_id;
        $this->run_manager = $domain_service->execution()->run($survey, $user_id);
    }

    /**
     * Checks if a user can add himself as an appraisee
     */
    public function canAddItselfAsAppraisee() : bool
    {
        $survey = $this->survey;
        $user_id = $this->user_id;
        $access = $this->access;
        $feature_config = $this->feature_config;

        if ($access->canRead() &&
            $feature_config->usesAppraisees() &&
            $survey->get360SelfAppraisee() &&
            !$survey->isAppraisee($user_id) &&
            $user_id != ANONYMOUS_USER_ID) {
            return true;
        }
        return false;
    }

    /**
     * This will return true, if a survey without appraisees is finished
     * @param string $code
     * @return bool
     */
    public function cantStartAgain(string $code = "") : bool
    {
        $feature_config = $this->feature_config;

        if ($feature_config->usesAppraisees()) {
            return false;
        }

        if ($this->run_manager->hasFinished($this->user_id, $code)) {
            // check for
            // !(!$this->object->isAccessibleWithoutCode() && !$anonymous_code && $ilUser->getId() == ANONYMOUS_USER_ID)
            // removed
            // not code accessible an no anonymous code and anonymous user (see #0020333)
            return true;
        }
        return false;
    }

    /**
     * Can the current user see the own results
     * @param string $code
     * @return bool
     */
    public function canViewUserResults(string $code = "") : bool
    {
        if ($this->cantStartAgain($code) &&
            $this->user_id != ANONYMOUS_USER_ID &&
            $this->survey->hasViewOwnResults()) {
            return true;
        }
        return false;
    }

    /**
     * Can the current user mail the confirmation
     * @param string $code
     * @return bool
     */
    public function canMailUserResults(string $code = "") : bool
    {
        if ($this->cantStartAgain($code) &&
            $this->user_id != ANONYMOUS_USER_ID &&
            $this->survey->hasMailConfirmation()) {
            return true;
        }
        return false;
    }

    /**
     * Check if user must enter code to start (and currently is able to start)
     */
    public function mustEnterCode(string $code = "") : bool
    {
        if ($this->access->canStartSurvey()) {
            // code is mandatory and not given yet
            if (!$this->isAppraisee() &&
                $code == "" &&
                !$this->survey->isAccessibleWithoutCode()) {
                return true;
            }
        }
        return false;
    }

    public function isExternalRater() : bool
    {
        $survey = $this->survey;
        $feature_config = $this->feature_config;
        $anon_session = $this->repo_service->execution()->anonymousSession();

        if (!is_null($survey) &&
            $feature_config->usesAppraisees() &&
            $anon_session->issetCode($survey->getId()) &&
            \ilObjSurvey::validateExternalRaterCode(
                $survey->getRefId(),
                $anon_session->getCode($survey->getId())
            )) {
            return true;
        }
        return false;
    }

    public function isAppraisee() : bool
    {
        $survey = $this->survey;
        $feature_config = $this->feature_config;
        if (!is_null($survey) &&
            $feature_config->usesAppraisees() &&
            $survey->isAppraisee($this->user_id)) {
            return true;
        }
        return false;
    }
}
