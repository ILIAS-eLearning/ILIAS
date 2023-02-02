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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestQuestionRelatedObjectivesList
{
    /**
     * @var array
     */
    protected $objectivesByQuestion;

    /**
     * @var array
     */
    protected $objectivesTitles;

    public function __construct()
    {
        $this->objectivesByQuestion = array();
        $this->objectivesTitles = array();
    }

    /**
     * @param integer $questionId
     * @param string $objectiveTitle
     */
    public function addQuestionRelatedObjectives($questionId, $objectiveIds)
    {
        $this->objectivesByQuestion[$questionId] = $objectiveIds;
    }

    /**
     * @param integer $questionId
     * @return bool
     */
    public function hasQuestionRelatedObjectives($questionId): bool
    {
        if (!isset($this->objectivesByQuestion[$questionId])) {
            return false;
        }

        return (bool) count($this->objectivesByQuestion[$questionId]);
    }

    /**
     * @param integer $questionId
     */
    public function getQuestionRelatedObjectives($questionId)
    {
        return $this->objectivesByQuestion[$questionId];
    }

    public function loadObjectivesTitles()
    {
        foreach ($this->objectivesByQuestion as $objectiveIds) {
            foreach ($objectiveIds as $objectiveId) {
                if (!isset($this->objectivesTitles[$objectiveId])) {
                    $objectiveTitle = ilCourseObjective::lookupObjectiveTitle($objectiveId);
                    $this->objectivesTitles[$objectiveId] = $objectiveTitle;
                }
            }
        }
    }

    /**
     * @param integer $questionId
     * @return string
     */
    public function getQuestionRelatedObjectiveTitles($questionId): string
    {
        $titles = array();

        foreach ((array) $this->objectivesByQuestion[$questionId] as $objectiveId) {
            $titles[] = $this->objectivesTitles[$objectiveId];
        }

        return implode(', ', $titles);
    }

    public function getUniqueObjectivesString(): string
    {
        return implode(', ', $this->objectivesTitles);
    }

    public function getUniqueObjectivesStringForQuestions($questionIds): string
    {
        $objectiveTitles = array();

        foreach ($this->objectivesByQuestion as $questionId => $objectiveIds) {
            if (!in_array($questionId, $questionIds)) {
                continue;
            }

            foreach ($objectiveIds as $objectiveId) {
                $objectiveTitles[$objectiveId] = $this->objectivesTitles[$objectiveId];
            }
        }

        return implode(', ', $objectiveTitles);
    }

    public function getObjectiveTitleById($objectiveId)
    {
        return $this->objectivesTitles[$objectiveId];
    }

    public function getObjectives(): array
    {
        return $this->objectivesTitles;
    }

    public function isQuestionRelatedToObjective($questionId, $objectiveId): bool
    {
        foreach ($this->objectivesByQuestion[$questionId] as $relatedObjectiveId) {
            if ($relatedObjectiveId == $objectiveId) {
                return true;
            }
        }

        return false;
    }

    public function filterResultsByObjective($testResults, $objectiveId): array
    {
        $filteredResults = array();

        foreach ($testResults as $questionId => $resultData) {
            if (!$this->isQuestionRelatedToObjective($questionId, $objectiveId)) {
                continue;
            }

            $filteredResults[$questionId] = $resultData;
        }

        return $filteredResults;
    }
}
