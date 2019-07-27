<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqQuestionPresentation
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqQuestionPresentation
{
	/**
	 * @param ilAsqQuestion $question
	 */
	public function setQuestion(ilAsqQuestion $question);
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getQuestionPresentation(ilAsqQuestionSolution $solution) : \ILIAS\UI\Component\Component;
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getSolutionPresentation(ilAsqQuestionSolution $solution) : \ILIAS\UI\Component\Component;
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getGenericFeedbackOutput(ilAsqQuestionSolution $solution) : \ILIAS\UI\Component\Component;
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getSpecificFeedbackOutput(ilAsqQuestionSolution $solution) : \ILIAS\UI\Component\Component;
	
	/**
	 * @return bool
	 */
	public function hasInlineFeedback() : bool;
	
	/**
	 * @return bool
	 */
	public function isAutosaveable() : bool;
	
	/**
	 * @param ilAsqQuestionNavigationAware
	 */
	public function setQuestionNavigation(ilAsqQuestionNavigationAware $questionNavigationAware);
}