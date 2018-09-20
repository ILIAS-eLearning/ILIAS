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
	 * @return integer
	 */
	public function getPoints() : integer;
	
	/**
	 * @return bool
	 */
	public function isCorrect() : bool;
}