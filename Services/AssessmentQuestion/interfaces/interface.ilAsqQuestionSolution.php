<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqQuestionSolution
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqQuestionSolution
{
	/**
	 * @param integer $solutionId
	 * @return void
	 */
	public function setSolutionId($solutionId);
	
	/**
	 * @return integer
	 */
	public function getSolutionId();
	
	/**
	 * @param integer $questionId
	 * @return void
	 */
	public function setQuestionId($questionId);
	
	/**
	 * @return integer
	 */
	public function getQuestionId();
	
	/**
	 * @return void
	 */
	public function load();
	
	/**
	 * @return void
	 */
	public function save();
	
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @return void
	 */
	public function initFromServerRequest(\Psr\Http\Message\ServerRequestInterface $request);
	
	/**
	 * @return bool
	 */
	public function isEmpty();
}