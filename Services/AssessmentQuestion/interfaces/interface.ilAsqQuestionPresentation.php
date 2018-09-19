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
	public function setQuestion(ilAsqQuestion $question) : void;
	
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
	public function setQuestionNavigation(ilAsqQuestionNavigationAware $questionNavigationAware) : void;
	
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
	public function setRenderPurpose($renderPurpose) : void;
}