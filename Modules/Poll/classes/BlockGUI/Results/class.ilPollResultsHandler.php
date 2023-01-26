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

class ilPollResultsHandler
{
    protected ilPollAnswersHandler $answers;
    protected bool $sort_by_votes;
    protected int $total_votes;

    /**
     * @var float[]
     */
    protected array $answer_percentages;

    /**
     * @var int[]
     */
    protected array $answer_totals;

    public function __construct(
        ilObjPoll $poll,
        ilPollAnswersHandler $answers
    ) {
        $this->sort_by_votes = $poll->getSortResultByVotes();
        $this->answers = $answers;
        $res = $poll->getVotePercentages();
        $this->total_votes = (int) ($res['total'] ?? 0);
        $res = (array) ($res['perc'] ?? []);
        $this->answer_percentages = array_map(
            fn (array $a) => (float) ($a['perc'] ?? 0),
            $res
        );
        $this->answer_totals = array_map(
            fn (array $a) => (int) ($a['abs'] ?? 0),
            $res
        );
    }

    /**
     * @return Generator|int[]
     */
    public function getOrderedAnswerIds(): Generator
    {
        if ($this->sort_by_votes) {
            $order = $this->answer_totals;
            arsort($order);
            $order = array_keys($order);

            foreach ($this->answers->getAnswers() as $id => $answer) {
                if (!in_array($id, $order)) {
                    $order[] = $id;
                }
            }

            foreach ($order as $id) {
                yield $id;
            }
        } else {
            foreach ($this->answers->getAnswers() as $id => $answer) {
                yield $id;
            }
        }
    }

    public function getTotalVotes(): int
    {
        return $this->total_votes;
    }

    public function getAnswerText(int $id): string
    {
        return $this->answers->getAnswer($id);
    }

    public function getAnswerPercentage(int $id): float
    {
        return $this->answer_percentages[$id] ?? 0;
    }

    public function getAnswerTotal(int $id): int
    {
        return $this->answer_totals[$id] ?? 0;
    }
}
