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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilPollContentRenderer
{
    protected ilPollStateInfo $state;
    protected ilPollCommentsHandler $comments;
    protected ilPollAnswersHandler $answers;
    protected ilPollAnswersRenderer $answers_renderer;
    protected ilPollResultsHandler $results;
    protected ilPollResultsRenderer $results_renderer;
    protected ilLanguage $lng;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    public function __construct(
        ilLanguage $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        ilPollStateInfo $availability,
        ilPollCommentsHandler $comments,
        ilPollAnswersHandler $answers,
        ilPollAnswersRenderer $answers_renderer,
        ilPollResultsHandler $results,
        ilPollResultsRenderer $results_renderer
    ) {
        $this->lng = $lng;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->state = $availability;
        $this->comments = $comments;
        $this->answers = $answers;
        $this->answers_renderer = $answers_renderer;
        $this->results = $results;
        $this->results_renderer = $results_renderer;
    }

    public function render(
        ilTemplate $tpl,
        int $ref_id,
        int $user_id,
        ilObjPoll $poll,
        bool $admin_view = false
    ): void {
        $this->renderAnchor($tpl, $poll->getId());
        $this->renderAvailability($tpl, $poll);
        $this->renderDescription($tpl, $poll->getDescription());

        if (!$this->state->hasQuestion($poll)) {
            $this->renderNoQuestionMessage($tpl);
        } elseif ($this->state->hasVotingPeriodNotStarted($poll, false)) {
            $this->renderNotWithinVotingPeriodMessage(
                $tpl,
                $poll->getVotingPeriodBegin(),
                $poll->getVotingPeriodEnd()
            );
        } else {
            $this->renderQuestion(
                $tpl,
                $poll->getQuestion(),
                $poll->getImageFullPath()
            );
            if (!$admin_view) {
                $this->renderAnswersAndResults($tpl, $poll, $user_id);
            }
        }

        if ($poll->getShowComments()) {
            $this->renderComments($tpl, $ref_id);
        }

        if ($this->state->isUserAnonymous($user_id)) {
            $this->renderAlertForAnonymousUser($tpl);
        }
    }

    protected function renderAnswersAndResults(
        ilTemplate $tpl,
        ilObjPoll $poll,
        int $user_id
    ): void {
        $either_is_shown = false;
        if (
            !$this->state->hasUserAlreadyVoted($user_id, $poll) &&
            !$this->state->hasVotingPeriodEnded($poll, false)
        ) {
            $this->renderAnswers($tpl, $poll, $user_id);
            $either_is_shown = true;
        }

        if (
            $this->state->areResultsVisible($user_id, $poll) &&
            $this->results->getTotalVotes()
        ) {
            $this->renderResults($tpl, $poll);
            $this->renderTotalParticipantsInfo(
                $tpl,
                $this->results->getTotalVotes()
            );
            $either_is_shown = true;
        } elseif ($this->state->areResultsVisible($user_id, $poll)) {
            $this->renderTotalParticipantsInfo(
                $tpl,
                $this->results->getTotalVotes()
            );
        }

        if (!$either_is_shown) {
            $this->renderNotAbleToVoteMessage(
                $tpl,
                $this->state->hasUserAlreadyVoted($user_id, $poll),
                $this->state->willResultsBeShown($poll),
                $poll->getVotingPeriodEnd()
            );
        }
    }

    protected function renderAnswers(
        ilTemplate $tpl,
        ilObjPoll $poll,
        int $user_id
    ): void {
        $this->renderMiscVoteInfo(
            $tpl,
            $this->answers->getAnswerLimitForInfo(),
            $poll->getVotingPeriodEnd()
        );
        $this->answers_renderer->render(
            $tpl,
            $this->answers,
            !$this->state->mayUserVote($user_id)
        );
        $this->renderAnonimityInfo($tpl, !$poll->getNonAnonymous());
    }

    protected function renderResults(
        ilTemplate $tpl,
        ilObjPoll $poll
    ): void {
        $this->results_renderer->render(
            $tpl,
            $this->results,
            $poll->getShowResultsAs()
        );
    }

    protected function renderTotalParticipantsInfo(
        ilTemplate $tpl,
        int $total
    ): void {
        if ($total === 1) {
            $tpl->setVariable(
                "TOTAL_ANSWERS",
                $this->lng->txt("poll_population_singular")
            );
            return;
        }
        $tpl->setVariable(
            "TOTAL_ANSWERS",
            sprintf($this->lng->txt("poll_population"), $total)
        );
    }

    protected function renderNotAbleToVoteMessage(
        ilTemplate $tpl,
        bool $has_voted,
        bool $results_in_future,
        int $voting_end_date
    ): void {
        $messages = [];

        if ($has_voted) {
            $messages[] = $this->lng->txt("poll_block_message_already_voted");
        } else {
            $messages[] = sprintf(
                $this->lng->txt("poll_voting_period_ended_info"),
                $this->getFormattedDate($voting_end_date)
            );
        }

        if ($results_in_future && $has_voted) {
            $messages[] = sprintf(
                $this->lng->txt("poll_block_results_available_on"),
                $this->getFormattedDate($voting_end_date)
            );
        }

        $tpl->setVariable(
            "MESSAGE_BOX",
            $this->getMessageBox(
                implode(' ', $messages)
            )
        );
    }

    protected function renderQuestion(
        ilTemplate $tpl,
        string $text,
        ?string $img_path
    ): void {
        $tpl->setVariable("TXT_QUESTION", nl2br(trim($text)));
        if ($img_path) {
            $tpl->setVariable(
                "URL_IMAGE",
                ilWACSignedPath::signFile($img_path)
            );
        }
    }

    protected function renderMiscVoteInfo(
        ilTemplate $tpl,
        ?int $answer_limit,
        int $deadline
    ): void {
        $infos = [];

        if ($answer_limit) {
            $infos[] = sprintf(
                $this->lng->txt('poll_max_number_of_answers_info'),
                $answer_limit
            );
        }

        if ($deadline) {
            $infos[] = sprintf(
                $this->lng->txt("poll_voting_period_info"),
                $this->getFormattedDate($deadline)
            );
        }

        if ($infos) {
            $tpl->setVariable('VOTE_INFO', implode(' ', $infos));
        }
    }

    protected function renderAnonimityInfo(
        ilTemplate $tpl,
        bool $anonymous
    ): void {
        if ($anonymous) {
            $info = $this->lng->txt("poll_anonymous_warning");
        } else {
            $info = $this->lng->txt("poll_non_anonymous_warning");
        }
        $tpl->setVariable("ANONIMITY_INFO", $info);
    }

    protected function renderNotWithinVotingPeriodMessage(
        ilTemplate $tpl,
        int $vote_start,
        int $vote_end
    ): void {
        $message = sprintf(
            $this->lng->txt("poll_voting_period_full_info"),
            $this->getFormattedDate($vote_start),
            $this->getFormattedDate($vote_end)
        );
        $tpl->setVariable("MESSAGE_BOX", $this->getMessageBox($message));
    }

    protected function renderNoQuestionMessage(ilTemplate $tpl): void
    {
        $tpl->setVariable(
            "MESSAGE_BOX",
            $this->getMessageBox(
                $this->lng->txt("poll_block_message_no_answers")
            )
        );
    }

    protected function renderAnchor(ilTemplate $tpl, int $obj_id): void
    {
        $tpl->setVariable("ANCHOR_ID", $obj_id);
    }

    protected function renderDescription(
        ilTemplate $tpl,
        string $description
    ): void {
        $description = trim($description);
        if ($description) {
            $tpl->setVariable("TXT_DESC", nl2br($description));
        }
    }

    protected function renderAvailability(ilTemplate $tpl, ilObjPoll $poll): void
    {
        if ($this->state->isOfflineOrUnavailable($poll)) {
            $tpl->setVariable("TXT_OFFLINE", $this->lng->txt('offline'));
        }
    }

    protected function renderAlertForAnonymousUser(ilTemplate $tpl): void
    {
        $tpl->setVariable("TXT_ANON", $this->lng->txt('no_access_item_public'));
    }

    protected function renderComments(ilTemplate $tpl, int $ref_id): void
    {
        $tpl->setVariable("LANG_COMMENTS", $this->lng->txt('poll_comments'));
        $tpl->setVariable("COMMENT_JSCALL", $this->comments->commentJSCall($ref_id));
        $tpl->setVariable("COMMENTS_COUNT_ID", $ref_id);

        $comments_count = $this->comments->getNumberOfComments($ref_id);

        if ($comments_count > 0) {
            $tpl->setVariable("COMMENTS_COUNT", "(" . $comments_count . ")");
        }

        $tpl->setVariable("COMMENTS_REDRAW_URL", $this->comments->getRedrawURL());
    }

    protected function getMessageBox(string $message): string
    {
        return $this->ui_renderer->render(
            $this->ui_factory->messageBox()->info($message)
        );
    }

    protected function getFormattedDate(int $date): string
    {
        return ilDatePresentation::formatDate(
            new ilDateTime($date, IL_CAL_UNIX)
        );
    }
}
