<?php

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

declare(strict_types=1);

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;

class Positions
{
    /**
     * "position" is a set of two arrays
     * [[$user_id(s)], [$question_id(s)]], where - according to transposition -
     * either the first or the second array holds but one value.
    */
    private array $positions = [];

    /**
     * @param array <int $usr_id, int[] $question_ids> $user_questions
     * @param array <int $usr_id, int $attempt> $user_attempts
     * @param array <int $question_id, GeneralQuestionProperties $properties> $question_properties
     */
    public function __construct(
        private array $user_questions,
        private array $user_attempts,
        private array $question_properties
    ) {
        foreach ($user_questions as $uid => $qids) {
            $this->positions[] = [[$uid], $qids];
        }
    }

    public function get(ConsecutiveScoringMode $mode): array
    {
        return $this->applyMode($mode, $this->positions);
    }

    public function getAllQuestionProperties(): array
    {
        return $this->question_properties;
    }

    public function getAllAttempts(): array
    {
        return $this->user_attempts;
    }

    public function applyFilters(\Closure ...$filters): self
    {
        $clone = clone $this;
        $positions = $clone->positions;
        foreach ($filters as $filter) {
            $positions = array_map(
                fn($set) => $filter(...$set),
                $positions
            );
            $positions = array_values(
                array_filter(
                    $positions,
                    fn($set) => $set[0] !== [] && $set[1] !== []
                )
            );
        }
        $clone->positions = $positions === []
            ? [null]
            : $positions;

        return $clone;
    }

    private function applyMode(ConsecutiveScoringMode $mode, array $positions): array
    {
        $ret = [];
        if ($mode->isSingle()) {
            foreach ($positions as $set) {
                [$uids, $qids] = $set;
                foreach ($qids as $qid) {
                    $ret[] = [$uids, [$qid]];
                }
            }

            if (!$mode->isUserCentric()) {
                usort($ret, static fn($a, $b) => $a[1][0] <=> $b[1][0]);
            }
            return $ret;
        }

        if ($mode->isUserCentric()) {
            return $positions;
        }

        $q2u = [];
        foreach ($positions as $set) {
            [$uids, $qids] = $set;
            foreach ($qids as $qid) {
                if (! array_key_exists($qid, $q2u)) {
                    $q2u[$qid] = [];
                }
                $q2u[$qid] = array_unique(array_merge($q2u[$qid], $uids));
            }
        }
        foreach ($q2u as $qid => $uids) {
            $ret[] = [$uids, [$qid]];
        }

        return $ret;
    }
}
