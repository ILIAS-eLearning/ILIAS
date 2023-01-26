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

class ilPollAnswersHandler
{
    protected string $vote_url;
    protected string $vote_cmd;
    protected int $poll_id;

    /**
     * @var string[]
     */
    protected array $answers;
    protected int $answer_limit;

    public function __construct(
        ilObjPoll $poll,
        string $vote_url,
        string $vote_cmd
    ) {
        $this->vote_url = $vote_url;
        $this->vote_cmd = $vote_cmd;
        $this->answer_limit = $poll->getMaxNumberOfAnswers();
        $this->poll_id = $poll->getId();

        $answers = [];
        foreach ($poll->getAnswers() as $answer) {
            $id = (int) ($answer['id'] ?? 0);
            $text = (string) ($answer['answer'] ?? '');
            $answers[$id] = $text;
        }
        $this->answers = $answers;
    }

    /**
     * TODO session handling should get its own class
     */
    public function popLastVoteFromSession(): ?array
    {
        $session_last_poll_vote = ilSession::get('last_poll_vote');
        if (isset($session_last_poll_vote[$this->poll_id])) {
            $last_vote = $session_last_poll_vote[$this->poll_id];
            unset($session_last_poll_vote[$this->poll_id]);
            ilSession::set('last_poll_vote', $session_last_poll_vote);
            return $last_vote;
        }
        return null;
    }

    /**
     * @return Generator|string[]
     */
    public function getAnswers(): Generator
    {
        foreach ($this->answers as $id => $answer) {
            yield $id => $answer;
        }
    }

    public function getAnswer(int $id): string
    {
        return (string) ($this->answers[$id] ?? '');
    }

    public function getNumberOfAnswers(): int
    {
        return count($this->answers);
    }

    public function getAnswerLimitForInfo(): ?int
    {
        $single_answer = $this->getAnswerLimit() === 1;
        $below_max = $this->getNumberOfAnswers() > $this->getAnswerLimit();
        if ($below_max && !$single_answer) {
            return $this->getAnswerLimit();
        }
        return null;
    }

    public function getAnswerLimit(): int
    {
        return $this->answer_limit;
    }

    public function getVoteURL(): string
    {
        return $this->vote_url;
    }

    public function getVoteCommand(): string
    {
        return $this->vote_cmd;
    }
}
