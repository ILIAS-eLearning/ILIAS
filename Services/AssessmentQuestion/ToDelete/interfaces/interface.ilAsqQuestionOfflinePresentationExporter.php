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
interface ilAsqQuestionOfflinePresentationExporter
{
	/**
	 * @param ilAsqQuestion $questionInstance
	 */
	public function setQuestion(ilAsqQuestion $questionInstance);
	
	/**
	 * @param ilAsqQuestionResourcesCollector $resourcesCollector
	 * @param bool $a_no_interaction
	 * @return \ILIAS\UI\Component\Component
	 */
	public function exportQuestion(ilAsqQuestionResourcesCollector $resourcesCollector, $a_no_interaction) : \ILIAS\UI\Component\Component;
}