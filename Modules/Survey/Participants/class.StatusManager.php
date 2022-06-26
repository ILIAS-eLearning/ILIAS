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

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * Participant status manager
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class StatusManager
{
    protected \ILIAS\Survey\Execution\RunManager $run_manager;
    protected InternalDomainService $domain_service;
    protected \ilObjSurvey $survey;
    protected int $user_id;
    protected \ILIAS\Survey\Access\AccessManager $access;
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected InternalRepoService $repo_service;

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
            $user_id !== ANONYMOUS_USER_ID) {
            return true;
        }
        return false;
    }

    /**
     * This will return true, if a survey without appraisees is finished
     * Note: Code will be gathered from session
     * @return bool
     */
    public function cantStartAgain() : bool
    {
        $feature_config = $this->feature_config;

        if ($feature_config->usesAppraisees()) {
            return false;
        }

        if ($this->run_manager->hasStarted() && $this->run_manager->hasFinished()) {
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
     * @return bool
     */
    public function canViewUserResults() : bool
    {
        if ($this->cantStartAgain() &&
            $this->user_id !== ANONYMOUS_USER_ID &&
            $this->survey->hasViewOwnResults()) {
            return true;
        }
        return false;
    }

    /**
     * Can the current user mail the confirmation
     * @return bool
     */
    public function canMailUserResults() : bool
    {
        if ($this->cantStartAgain() &&
            $this->user_id !== ANONYMOUS_USER_ID &&
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
                $code === "" &&
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
        $anon_session = $this->repo_service->execution()->runSession();
        if ($feature_config->usesAppraisees() &&
            $anon_session->issetCode($survey->getId())) {
            if (!$anon_session->isExternalRaterValidated($survey->getRefId())) {
                $code = $anon_session->getCode($survey->getId());
                $code_manager = $this->domain_service->code($survey, 0);
                $feature_config = $this->domain_service->modeFeatureConfig($survey->getMode());
                $access_manager = $this->domain_service->access($survey->getRefId(), 0);

                if ($code_manager->exists($code)) {
                    $anonymous_id = $survey->getAnonymousIdByCode($code);
                    if ($anonymous_id) {
                        if (count($survey->getAppraiseesToRate(null, $anonymous_id))) {
                            $anon_session->setExternalRaterValidation($survey->getRefId(), true);
                            return true;
                        }
                    }
                }
                $anon_session->setExternalRaterValidation($survey->getRefId(), false);
                return false;
            }
            return true;
        }
        return false;
    }

    public function isAppraisee() : bool
    {
        $survey = $this->survey;
        $feature_config = $this->feature_config;
        if ($feature_config->usesAppraisees() &&
            $survey->isAppraisee($this->user_id)) {
            return true;
        }
        return false;
    }
}
