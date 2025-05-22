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

use ILIAS\Test\Statistics\Statistics;

/**
 * @deprecated 11; Result/EvaluationData will be refined.
 */
class ilTestEvaluationData
{
    public const FILTER_BY_NONE = '';
    public const FILTER_BY_NAME = 'name';
    public const FILTER_BY_GROUP = 'group';
    public const FILTER_BY_COURSE = 'course';
    public const FILTER_BY_ACTIVE_ID = 'active_id';

    public array $question_titles = [];

    protected ?Statistics $statistics = null;
    protected ?array $arr_filter = null;
    protected int $datasets;

    public function __sleep(): array
    {
        return ['question_titles', 'participants', 'statistics', 'arr_filter', 'datasets', 'test'];
    }

    /**
    * @param array<ilTestEvaluationUserData> $participants
    */
    public function __construct(
        protected array $participants
    ) {
    }

    public function setDatasets(int $datasets): void
    {
        $this->datasets = $datasets;
    }

    public function getDatasets(): int
    {
        return $this->datasets;
    }

    public function addQuestionTitle(int $question_id, string $question_title): void
    {
        $this->question_titles[$question_id] = $question_title;
    }

    /**
     * @return array<string>
     */
    public function getQuestionTitles(): array
    {
        return $this->question_titles;
    }

    public function getQuestionTitle(?int $question_id): string
    {
        if (array_key_exists($question_id, $this->question_titles)) {
            return $this->question_titles[$question_id];
        }

        return '';
    }

    public function getTotalFinishedParticipants(): int
    {
        $finishedParticipants = 0;

        foreach ($this->participants as $active_id => $participant) {
            if (!$participant->isSubmitted()) {
                continue;
            }

            $finishedParticipants++;
        }

        return $finishedParticipants;
    }

    /**
     * @return array<ilTestEvaluationUserData>
     */
    public function getParticipants(): array
    {
        if (is_array($this->arr_filter) && count($this->arr_filter) > 0) {
            $filtered_participants = [];
            $courseids = [];
            $groupids = [];

            if (array_key_exists(self::FILTER_BY_GROUP, $this->arr_filter)) {
                $ids = ilObject::_getIdsForTitle($this->arr_filter[self::FILTER_BY_GROUP], 'grp', true);
                $groupids = array_merge($groupids, $ids);
            }
            if (array_key_exists(self::FILTER_BY_COURSE, $this->arr_filter)) {
                $ids = ilObject::_getIdsForTitle($this->arr_filter[self::FILTER_BY_COURSE], 'crs', true);
                $courseids = array_merge($courseids, $ids);
            }
            foreach ($this->participants as $active_id => $participant) {
                $remove = false;
                if (array_key_exists(self::FILTER_BY_NAME, $this->arr_filter)) {
                    if (!(strpos(strtolower($participant->getName()), strtolower((string) $this->arr_filter[self::FILTER_BY_NAME])) !== false)) {
                        $remove = true;
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_GROUP, $this->arr_filter)) {
                        $groups = ilParticipants::_getMembershipByType($participant->getUserID(), ['grp']);
                        $foundfilter = false;
                        if (count(array_intersect($groupids, $groups))) {
                            $foundfilter = true;
                        }
                        if (!$foundfilter) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_COURSE, $this->arr_filter)) {
                        $courses = ilParticipants::_getMembershipByType($participant->getUserID(), ['crs']);
                        $foundfilter = false;
                        if (count(array_intersect($courseids, $courses))) {
                            $foundfilter = true;
                        }
                        if (!$foundfilter) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_ACTIVE_ID, $this->arr_filter)) {
                        if ($active_id != $this->arr_filter[self::FILTER_BY_ACTIVE_ID]) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    $filtered_participants[$active_id] = $participant;
                }
            }
            return $filtered_participants;
        } else {
            return $this->participants;
        }
    }

    public function resetFilter(): void
    {
        $this->arr_filter = [];
    }

    public function setFilter(string $by, string $text): void
    {
        if (in_array(
            $by,
            [self::FILTER_BY_ACTIVE_ID, self::FILTER_BY_NAME, self::FILTER_BY_COURSE, self::FILTER_BY_GROUP],
            true
        )) {
            $this->arr_filter = [$by => $text];
        }
    }

    public function setFilterArray(array $arr_filter): void
    {
        $this->arr_filter = $arr_filter;
    }

    public function addParticipant(int $active_id, ilTestEvaluationUserData $participant): void
    {
        $this->participants[$active_id] = $participant;
    }

    public function getParticipant(int $active_id): ?ilTestEvaluationUserData
    {
        return $this->participants[$active_id] ?? null;
    }

    public function participantExists($active_id): bool
    {
        return array_key_exists($active_id, $this->participants);
    }

    public function removeParticipant($active_id)
    {
        unset($this->participants[$active_id]);
    }

    public function getStatistics(): Statistics
    {
        if ($this->statistics === null) {
            $this->statistics = new Statistics($this);
        }
        return $this->statistics;
    }

    public function getParticipantIds(): array
    {
        return array_keys($this->participants);
    }
}
