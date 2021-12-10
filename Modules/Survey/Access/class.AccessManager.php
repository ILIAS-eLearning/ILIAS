<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Access;

use ILIAS\Survey\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessManager
{
    protected \ilAccessHandler $access;
    protected int $ref_id;
    protected int $user_id;
    protected InternalDomainService $domain_service;

    /**
     * Constructor
     */
    public function __construct(
        InternalDomainService $domain_service,
        \ilAccessHandler $access,
        int $ref_id,
        int $user_id
    ) {
        $this->domain_service = $domain_service;
        $this->access = $access;
        $this->user_id = $user_id;
        $this->ref_id = $ref_id;
    }

    protected function getSurvey() : \ilObjSurvey
    {
        return new \ilObjSurvey($this->ref_id);
    }

    /**
     * Can read
     * @return bool
     */
    public function canRead() : bool
    {
        return $this->access->checkAccessOfUser(
            $this->user_id,
            "read",
            "",
            $this->ref_id
        );
    }

    /**
     * Can manage codes
     * @return bool
     */
    public function canManageCodes() : bool
    {
        return $this->access->checkAccessOfUser(
            $this->user_id,
            "write",
            "",
            $this->ref_id
        );
    }

    /**
     * Can access info screen:
     * This is possible for external raters, or users with read or visible permission
     * @return bool
     */
    public function canAccessInfoScreen() : bool
    {
        $participant_status = $this->domain_service
            ->participants()
            ->status($this->getSurvey(), $this->user_id);
        if ($participant_status->isExternalRater() ||
            $this->access->checkAccessOfUser($this->user_id, "read", "", $this->ref_id) ||
            $this->access->checkAccessOfUser($this->user_id, "visible", "", $this->ref_id)) {
            return true;
        }
        return false;
    }

    /**
     * Can start the survey
     * This is possible for external raters, or users with read or visible permission
     * Note: This is true before entering the code, the code is not checked yet
     * @return bool
     */
    public function canStartSurvey() : bool
    {
        $survey = $this->getSurvey();
        $participant_status = $this->domain_service
            ->participants()
            ->status($survey, $this->user_id);
        if ($participant_status->isExternalRater() ||
            $this->access->checkAccessOfUser($this->user_id, "read", "", $this->ref_id)) {
            if (!$survey->getOfflineStatus() &&
                $survey->hasStarted() &&
                !$survey->hasEnded()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canEditSettings() : bool
    {
        return $this->access->checkAccessOfUser($this->user_id, "write", "", $this->ref_id);
    }

    /**
     * Can access evaluation
     * @return bool
     */
    public function canAccessEvaluation() : bool
    {
        $survey = $this->getSurvey();
        if ($this->access->checkAccessOfUser($this->user_id, "write", "", $this->ref_id) ||
            \ilObjSurveyAccess::_hasEvaluationAccess($survey->getId(), $this->user_id)) {
            return true;
        }
        return false;
    }

    public function isCodeInputAllowed() : bool
    {
        $survey = $this->getSurvey();
        $participant_status = $this->domain_service
            ->participants()
            ->status($this->getSurvey(), $this->user_id);
        if ($participant_status->isExternalRater() ||
            $survey->getAnonymize() || !$survey->isAccessibleWithoutCode()) {
            return true;
        }
        return false;
    }

    /**
     * Can access evaluation
     * @return bool
     */
    public function canAppraiseesAccessEvaluation() : bool
    {
        $survey = $this->getSurvey();
        $survey->read();
        if (in_array($survey->get360Results(), [ilObjSurvey::RESULTS_360_OWN, ilObjSurvey::RESULTS_360_ALL])) {
        }

        if (\ilObjSurveyAccess::_hasEvaluationAccess($survey->getId(), $this->user_id)) {
            return true;
        }
        return false;
    }
}
