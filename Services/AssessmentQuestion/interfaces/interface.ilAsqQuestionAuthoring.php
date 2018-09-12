<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqQuestionAuthoring
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqQuestionAuthoring
{
	/**
	 * Execute Command
	 */
	public function executeCommand();
	
	/**
	 * @param ilAsqQuestion $question
	 * @return void
	 */
	public function setQuestion(ilAsqQuestion $question);

	/**
	 * @param ilQuestionChangeListener $listener
	 */
	public function addQuestionChangeListener(ilQuestionChangeListener $listener);
	
	/**
	 * @param object $a_object
	 * @param string $a_method
	 * @param mixed $a_parameters
	 */
	public function addNewIdListener($a_object, $a_method, $a_parameters = "");
	
	/**
	 * @param int $a_new_question_id
	 */
	public function callNewIdListeners($a_new_question_id);
	
	/**
	 * @param array $taxonomies - an array of taxonomy ids
	 */
	public function setTaxonomies($taxonomies);
	
	/**
	 * @param string $backLinkTarget - an http link
	 */
	public function setBackLink($backLinkTarget);
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getPreviewLink();
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditQuestionConfigLink();
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditQuestionPageLink();
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditFeedbacksLink();
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditHintsLink();
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getStatisticLink();
}