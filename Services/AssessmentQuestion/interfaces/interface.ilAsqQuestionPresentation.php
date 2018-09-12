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
	 * @return void
	 */
	public function setQuestion(ilAsqQuestion $question);
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getQuestionPresentation(ilAsqQuestionSolution $solution);
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getSolutionPresentation(ilAsqQuestionSolution $solution);
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getGenericFeedbackOutput(ilAsqQuestionSolution $solution);
	
	/**
	 * @param ilAsqQuestionSolution $solution
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getSpecificFeedbackOutput(ilAsqQuestionSolution $solution);
	
	/**
	 * @return bool
	 */
	public function hasInlineFeedback();
	
	/**
	 * @return bool
	 */
	public function isAutosaveable();
	
	/**
	 * @return mixed
	 */
	public function setQuestionNavigation(ilAsqQuestionNavigationAware $questionNavigationAware);
	
	/**
	 * render purpose constants that are required for using desired presentation renderer
	 */
	const RENDER_PURPOSE_PLAYBACK = 'renderPurposePlayback'; // e.g. Test Player
	const RENDER_PURPOSE_DEMOPLAY = 'renderPurposeDemoplay'; // e.g. Page Editing View in Test
	const RENDER_PURPOSE_PREVIEW = 'renderPurposePreview'; // e.g. Preview Player
	const RENDER_PURPOSE_PRINT_PDF = 'renderPurposePrintPdf'; // When used for PDF rendering
	const RENDER_PURPOSE_INPUT_VALUE = 'renderPurposeInputValue'; // When used as RTE Input Content
	
	/**
	 * @param $renderPurpose
	 */
	public function setRenderPurpose($renderPurpose);
}