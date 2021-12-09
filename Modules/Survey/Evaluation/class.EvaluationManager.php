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
        $this->feature_config = $this->domain_service->modeFeatureConfig($survey->getMode());
        $this->survey = $survey;
        $this->user_id = $user_id;
    }

    /**
     * Get all appraisees the current user may evaluate
     * @return int[]
     */
    public function getSelectableAppraisees() : array
    {
        $survey = $this->survey;
        $user_id = $this->user_id;
        $access = $this->access;
        $feature_config = $this->feature_config;

        $appraisee_ids = [];
        if ($access->canEditSettings() ||
            $survey->get360Results() == \ilObjSurvey::RESULTS_360_ALL ||
            $survey->getSelfEvaluationResults() == \ilObjSurvey::RESULTS_SELF_EVAL_ALL) {
            if ($feature_config->usesAppraisees()) {
                foreach ($survey->getAppraiseesData() as $item) {
                    if (!$survey->get360Mode() || $item["closed"]) {
                        $appraisee_ids[] = $item["user_id"];
                    }
                }
            } elseif ($survey->getMode() == \ilObjSurvey::MODE_SELF_EVAL) {
                foreach ($survey->getSurveyParticipants() as $item) {
                    $appraisee_ids[] = \ilObjUser::_lookupId($item['login']);
                }
            }
        }
        return $appraisee_ids;
    }
}
