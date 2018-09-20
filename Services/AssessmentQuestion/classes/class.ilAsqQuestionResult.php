<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAsqResultCalculator
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
class ilAsqQuestionResult
{
	/**
	 * @var float
	 */
	protected $points;
	
	/**
	 * @var bool
	 */
	protected $correct;
	
	/**
	 * @param float $points
	 */
	public function setPoints(float $points)
	{
		$this->points = $points;
	}
	
	/**
	 * @return float
	 */
	public function getPoints() : float
	{
		$this->points
	}
	
	/**
	 * @param bool $correct
	 */
	public function setCorrect(bool $correct)
	{
		$this->correct = $correct;
	}
	
	/**
	 * @return bool
	 */
	public function isCorrect() : bool
	{
		return $this->correct;
	}
}