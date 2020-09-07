<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    public function hasQuestionRelatedObjectives($questionId)
    {
        if (!isset($this->objectivesByQuestion[$questionId])) {
            return false;
        }
        
        return (bool) count($this->objectivesByQuestion[$questionId]);
    }

    /**
     * @param integer $questionId
     * @return string
     */
    public function getQuestionRelatedObjectives($questionId)
    {
        return $this->objectivesByQuestion[$questionId];
    }
    
    public function loadObjectivesTitles()
    {
        require_once 'Modules/Course/classes/class.ilCourseObjective.php';
        
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
    public function getQuestionRelatedObjectiveTitles($questionId)
    {
        $titles = array();
        
        foreach ((array) $this->objectivesByQuestion[$questionId] as $objectiveId) {
            $titles[] = $this->objectivesTitles[$objectiveId];
        }
        
        return implode(', ', $titles);
    }
    
    public function getUniqueObjectivesString()
    {
        return implode(', ', $this->objectivesTitles);
    }

    public function getUniqueObjectivesStringForQuestions($questionIds)
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

    public function getObjectives()
    {
        return $this->objectivesTitles;
    }

    public function isQuestionRelatedToObjective($questionId, $objectiveId)
    {
        foreach ($this->objectivesByQuestion[$questionId] as $relatedObjectiveId) {
            if ($relatedObjectiveId == $objectiveId) {
                return true;
            }
        }

        return false;
    }

    public function filterResultsByObjective($testResults, $objectiveId)
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
