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
	protected $questionIds;
	
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
		$this->questionIds = array();
		$this->objectivesByQuestion = array();
		$this->objectivesTitles = array();
	}

	/**
	 * @return array
	 */
	public function getQuestionIds()
	{
		return $this->questionIds;
	}

	/**
	 * @param array $questionIds
	 */
	public function setQuestionIds($questionIds)
	{
		$this->questionIds = $questionIds;
	}

	/**
	 * @param integer $questionId
	 * @param string $objectiveTitle
	 */
	public function addQuestionRelatedObjective($questionId, $objectiveTitle)
	{
		$this->objectivesByQuestion[$questionId] = $objectiveTitle;
	}

	/**
	 * @param integer $questionId
	 * @return bool
	 */
	public function hasQuestionRelatedObjective($questionId)
	{
		return isset($this->objectivesByQuestion[$questionId]);
	}

	/**
	 * @param integer $questionId
	 * @return string
	 */
	public function getQuestionRelatedObjective($questionId)
	{
		return $this->objectivesByQuestion[$questionId];
	}
	
	public function loadObjectivesTitles()
	{
		require_once 'Modules/Course/classes/class.ilCourseObjective.php';
		
		foreach( $this->objectivesByQuestion as $objectiveId )
		{
			if( !isset($this->objectivesTitles[$objectiveId]) )
			{
				$objectiveTitle = ilCourseObjective::lookupObjectiveTitle($objectiveId);
				$this->objectivesTitles[$objectiveId] = $objectiveTitle;
			}
		}
	}

	/**
	 * @param integer $questionId
	 * @return string
	 */
	public function getQuestionRelatedObjectiveTitle($questionId)
	{
		$objectiveId = $this->objectivesByQuestion[$questionId];
		return $this->objectivesTitles[$objectiveId];
	}
	
	public function getUniqueObjectivesString()
	{
		return implode(', ', $this->objectivesTitles);
	}
} 