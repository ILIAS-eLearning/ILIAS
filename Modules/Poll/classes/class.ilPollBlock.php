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

/**
 * Custom block for polls
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollBlock extends ilCustomBlock
{
    protected ilLanguage $lng;
    protected ilObjPoll $poll;
    protected array $answers = [];
    protected bool $visible = false;
    protected bool $active = false;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        parent::__construct($a_id);
        $this->lng = $DIC->language();
    }

    /**
     * Set ref id (needed for poll access)
     */
    public function setRefId(int $a_id): void
    {
        $this->poll = new ilObjPoll($a_id, true);
        $this->answers = $this->poll->getAnswers();
    }

    public function getPoll(): ilObjPoll
    {
        return $this->poll;
    }

    /**
     * Check if user will see any content (vote/result)
     */
    public function hasAnyContent(int $a_user_id, int $a_ref_id): bool
    {
        if (!count($this->answers)) {
            return false;
        }

        $this->active = ilObjPollAccess::_isActivated($a_ref_id);
        if (!$this->active) {
            return false;
        }

        if (!$this->mayVote($a_user_id) &&
            !$this->maySeeResults($a_user_id)) {
            return false;
        }

        return true;
    }

    public function mayVote(int $a_user_id): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($a_user_id === ANONYMOUS_USER_ID) {
            return false;
        }

        if ($this->poll->hasUserVoted($a_user_id)) {
            return false;
        }

        if ($this->poll->getVotingPeriod() &&
            ($this->poll->getVotingPeriodBegin() > time() ||
            $this->poll->getVotingPeriodEnd() < time())) {
            return false;
        }

        return true;
    }

    public function mayNotResultsYet(): bool
    {
        if ($this->poll->getViewResults() === ilObjPoll::VIEW_RESULTS_AFTER_PERIOD &&
            $this->poll->getVotingPeriod() &&
            $this->poll->getVotingPeriodEnd() > time()) {
            return true;
        }
        return false;
    }

    public function maySeeResults(int $a_user_id): bool
    {
        if (!$this->active) {
            return false;
        }

        switch ($this->poll->getViewResults()) {
            case ilObjPoll::VIEW_RESULTS_NEVER:
                return false;

            case ilObjPoll::VIEW_RESULTS_ALWAYS:
                // fallthrough

            // #12023 - see mayNotResultsYet()
            case ilObjPoll::VIEW_RESULTS_AFTER_PERIOD:
                return true;

            case ilObjPoll::VIEW_RESULTS_AFTER_VOTE:
                if ($this->poll->hasUserVoted($a_user_id)) {
                    return true;
                }
                return false;
        }
        return false;
    }

    public function getMessage(int $a_user_id): ?string
    {
        if (!count($this->answers)) {
            return $this->lng->txt("poll_block_message_no_answers");
        }

        if (!$this->active) {
            if ($this->poll->getOfflineStatus()) {
                return $this->lng->txt("poll_block_message_offline");
            }
            if ($this->poll->getAccessBegin() > time()) {
                $date = ilDatePresentation::formatDate(new ilDateTime($this->poll->getAccessBegin(), IL_CAL_UNIX));
                return sprintf($this->lng->txt("poll_block_message_inactive"), $date);
            }
        }

        return null;
    }

    /**
     * Show Results as (Barchart or Piechart)
     */
    public function showResultsAs(): int
    {
        return $this->poll->getShowResultsAs();
    }

    /**
     * Are comments enabled or disabled
     */
    public function showComments(): bool
    {
        return $this->poll->getShowComments();
    }
}
