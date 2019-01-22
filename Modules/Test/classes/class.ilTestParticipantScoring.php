<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilTestParticipantScoring
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/Test
 */
class ilTestParticipantScoring
{
	/**
	 * @var integer
	 */
	protected $activeId;
	
	/**
	 * @var integer
	 */
	protected $scoredPass;
	
	/**
	 * @var integer
	 */
	protected $answeredQuestions;
	
	/**
	 * @var integer
	 */
	protected $totalQuestions;
	
	/**
	 * @var integer
	 */
	protected $reachedPoints;
	
	/**
	 * @var integer
	 */
	protected $maxPoints;
	
	/**
	 * @var bool
	 */
	protected $passed;
	
	/**
	 * @var string
	 */
	protected $finalMark;
	
	/**
	 * ilTestParticipantScoring constructor.
	 */
	public function __construct()
	{
		$this->activeId = 0;
		$this->scoredPass = 0;
		$this->answeredQuestions = 0;
		$this->totalQuestions = 0;
		$this->reachedPoints = 0;
		$this->maxPoints = 0;
		$this->passed = false;
		$this->finalMark = '';
	}
	
	
	/**
	 * @return int
	 */
	public function getActiveId(): int
	{
		return $this->activeId;
	}
	
	/**
	 * @param int $activeId
	 */
	public function setActiveId(int $activeId)
	{
		$this->activeId = $activeId;
	}
	
	/**
	 * @return int
	 */
	public function getScoredPass(): int
	{
		return $this->scoredPass;
	}
	
	/**
	 * @param int $scoredPass
	 */
	public function setScoredPass(int $scoredPass)
	{
		$this->scoredPass = $scoredPass;
	}
	
	/**
	 * @return int
	 */
	public function getAnsweredQuestions(): int
	{
		return $this->answeredQuestions;
	}
	
	/**
	 * @param int $answeredQuestions
	 */
	public function setAnsweredQuestions(int $answeredQuestions)
	{
		$this->answeredQuestions = $answeredQuestions;
	}
	
	/**
	 * @return int
	 */
	public function getTotalQuestions(): int
	{
		return $this->totalQuestions;
	}
	
	/**
	 * @param int $totalQuestions
	 */
	public function setTotalQuestions(int $totalQuestions)
	{
		$this->totalQuestions = $totalQuestions;
	}
	
	/**
	 * @return int
	 */
	public function getReachedPoints(): int
	{
		return $this->reachedPoints;
	}
	
	/**
	 * @param int $reachedPoints
	 */
	public function setReachedPoints(int $reachedPoints)
	{
		$this->reachedPoints = $reachedPoints;
	}
	
	/**
	 * @return int
	 */
	public function getMaxPoints(): int
	{
		return $this->maxPoints;
	}
	
	/**
	 * @param int $maxPoints
	 */
	public function setMaxPoints(int $maxPoints)
	{
		$this->maxPoints = $maxPoints;
	}
	
	/**
	 * @return bool
	 */
	public function isPassed(): bool
	{
		return $this->passed;
	}
	
	/**
	 * @param bool $passed
	 */
	public function setPassed(bool $passed)
	{
		$this->passed = $passed;
	}
	
	/**
	 * @return string
	 */
	public function getFinalMark(): string
	{
		return $this->finalMark;
	}
	
	/**
	 * @param string $finalMark
	 */
	public function setFinalMark(string $finalMark)
	{
		$this->finalMark = $finalMark;
	}
	
	/**
	 * @return int
	 */
	public function getPercentResult()
	{
		if( $this->getMaxPoints() > 0 )
		{
			return $this->getReachedPoints() / $this->getMaxPoints();
		}
		
		return 0;
	}
}
