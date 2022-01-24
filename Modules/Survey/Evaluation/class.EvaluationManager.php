<?php declare(strict_types = 1);

namespace ILIAS\Survey\Evaluation;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * Evaluation manager
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EvaluationManager
{
    protected EvaluationSessionRepo $eval_repo;
    protected int $requested_appr_id;
    protected InternalDomainService $domain_service;
    protected \ilObjSurvey $survey;
    protected int $user_id;
    protected \ILIAS\Survey\Access\AccessManager $access;
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected InternalRepoService $repo_service;
    protected string $requested_rater_id;

    public function __construct(
        InternalDomainService $domain_service,
        InternalRepoService $repo_service,
        \ilObjSurvey $survey,
        int $user_id,
        int $requested_appr_id,
        string $requested_rater_id
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
        $this->access = $this->domain_service->access($survey->getRefId(), $user_id);
        $this->feature_config = $this->domain_service->modeFeatureConfig($survey->getMode());
        $this->survey = $survey;
        $this->user_id = $user_id;
        $this->requested_appr_id = $requested_appr_id;
        $this->requested_rater_id = $requested_rater_id;
        $this->eval_repo = $this->repo_service->evaluation();
    }

    /**
     * Can the current user switch between participants and see their results?
     *
     * This is true for tutors (can edit settings) or normal users, if the mode
     * supports to see the results of others.
     */
    public function isMultiParticipantsView() : bool
    {
        $survey = $this->survey;
        $access = $this->access;
        return ($access->canEditSettings() ||
            $survey->get360Results() == \ilObjSurvey::RESULTS_360_ALL ||
            $survey->getSelfEvaluationResults() == \ilObjSurvey::RESULTS_SELF_EVAL_ALL);
    }

    /**
     * Get all participants the current user may see results from,
     * including itself
     * @return int[]
     */
    public function getSelectableAppraisees() : array
    {
        $survey = $this->survey;
        $user_id = $this->user_id;
        $access = $this->access;
        $feature_config = $this->feature_config;

        $appraisee_ids = [];
        if ($this->isMultiParticipantsView()) {     // the user may see results of "others"
            if ($feature_config->usesAppraisees()) {
                foreach ($survey->getAppraiseesData() as $item) {
                    if (!$survey->get360Mode() || $item["closed"]) {
                        $appraisee_ids[] = (int) $item["user_id"];
                    }
                }
            } elseif ($survey->getMode() == \ilObjSurvey::MODE_SELF_EVAL) {
                foreach ($survey->getSurveyParticipants() as $item) {
                    $appraisee_ids[] = (int) \ilObjUser::_lookupId($item['login']);
                }
            }
        } else {
            if ($feature_config->usesAppraisees() ||
                $survey->getMode() == \ilObjSurvey::MODE_SELF_EVAL) {
                $appraisee_ids[] = $user_id;
            }
        }
        return $appraisee_ids;
    }

    /**
     * 1) We have a set of selectable appraisees.
     *    - If the requested appraisee is within this set, the requested
     *      apraisee will be returned.
     *    - If no appraisee is requested and the current user is part
     *      of the set, the current user will be returned.
     *    - Otherwise the first selectable appraisee will be returned.
     * In all other cases 0 will be returned.
     */
    public function getCurrentAppraisee() : int
    {
        $req_appr_id = $this->requested_appr_id;

        // if no user is requested, request current user
        $user_id = $this->user_id;
        if ($req_appr_id == 0) {
            $req_appr_id = $user_id;
        }

        // requested appraisee is valid -> return appraisee
        $valid = $this->getSelectableAppraisees();
        if (in_array($req_appr_id, $valid)) {
            return $req_appr_id;
        }

        // we have at least one selectable appraisee -> return appraisee
        if (count($valid) > 0) {
            return current($valid);
        }

        return 0;
    }

    /**
     * Only the individual feedback mode allows to select raters
     * and only, if the user cannot select appraisees on top level
     */
    public function getSelectableRaters() : array
    {
        $raters = [];
        $survey = $this->survey;

        $appr_id = $this->getCurrentAppraisee();

        if ($survey->getMode() == \ilObjSurvey::MODE_IND_FEEDB
            && !$this->isMultiParticipantsView()) {
            foreach ($survey->getRatersData($appr_id) as $rater) {
                if ($rater["finished"]) {
                    $raters[] = $rater;
                }
            }
        }

        return $raters;
    }

    public function getCurrentRater() : string
    {
        $req_rater_id = $this->requested_rater_id;

        $valid = array_map(function ($i) {
            return $i["user_id"];
        }, $this->getSelectableRaters());
        if (in_array($req_rater_id, $valid)) {
            return $req_rater_id;
        }
        return "";
    }

    public function setAnonEvaluationAccess(int $ref_id) : void
    {
        $this->eval_repo->setAnonEvaluationAccess($ref_id);
    }

    public function getAnonEvaluationAccess() : int
    {
        return $this->eval_repo->getAnonEvaluationAccess();
    }

    public function clearAnonEvaluationAccess() : void
    {
        $this->eval_repo->clearAnonEvaluationAccess();
    }
}
