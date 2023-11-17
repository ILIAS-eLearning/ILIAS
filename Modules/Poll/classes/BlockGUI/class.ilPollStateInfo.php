<?php

declare(strict_types=1);

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
 ********************************************************************
 */

class ilPollStateInfo
{
    protected array $cached_user_status = [];

    public function isOfflineOrUnavailable(ilObjPoll $poll): bool
    {
        $offline = $poll->getOfflineStatus();
        $before_start = false;
        $after_end = false;
        if ($poll->getAccessType() === ilObjectActivation::TIMINGS_ACTIVATION) {
            $before_start = $poll->getAccessBegin() > time();
            $after_end = $poll->getAccessEnd() < time();
        }
        return $offline || $before_start || $after_end;
    }

    public function hasQuestion(ilObjPoll $poll): bool
    {
        if (count($poll->getAnswers())) {
            return true;
        }
        return false;
    }

    /**
     * returns default if no voting period is set
     */
    public function hasVotingPeriodNotStarted(
        ilObjPoll $poll,
        bool $default
    ): bool {
        if (!$poll->getVotingPeriod()) {
            return $default;
        }
        if ($poll->getVotingPeriodBegin() > time()) {
            return true;
        }
        return false;
    }

    /**
     * returns default if no voting period is set
     */
    public function hasVotingPeriodEnded(
        ilObjPoll $poll,
        bool $default
    ): bool {
        if (!$poll->getVotingPeriod()) {
            return $default;
        }
        if ($poll->getVotingPeriodEnd() < time()) {
            return true;
        }
        return false;
    }

    public function hasUserAlreadyVoted(int $user_id, ilObjPoll $poll): bool
    {
        if (!array_key_exists($user_id, $this->cached_user_status)) {
            $this->cached_user_status[$user_id] = $poll->hasUserVoted($user_id);
        }
        return $this->cached_user_status[$user_id];
    }

    public function mayUserVote(int $user_id): bool
    {
        return !$this->isUserAnonymous($user_id);
    }

    public function isUserAnonymous(int $user_id): bool
    {
        return $user_id === ANONYMOUS_USER_ID;
    }

    public function areResultsVisible(int $user_id, ilObjPoll $poll): bool
    {
        switch ($poll->getViewResults()) {
            case ilObjPoll::VIEW_RESULTS_NEVER:
                return false;

            case ilObjPoll::VIEW_RESULTS_ALWAYS:
                return true;

            case ilObjPoll::VIEW_RESULTS_AFTER_PERIOD:
                return $this->hasVotingPeriodEnded($poll, true);

            case ilObjPoll::VIEW_RESULTS_AFTER_VOTE:
                if ($this->hasUserAlreadyVoted($user_id, $poll)) {
                    return true;
                }
                return false;
        }
        return false;
    }

    public function willResultsBeShown(ilObjPoll $poll): bool
    {
        if (
            $poll->getViewResults() !== ilObjPoll::VIEW_RESULTS_AFTER_PERIOD ||
            $this->hasVotingPeriodEnded($poll, false)
        ) {
            return false;
        }
        return true;
    }
}
