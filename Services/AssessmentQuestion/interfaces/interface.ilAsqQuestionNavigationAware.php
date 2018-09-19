<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqQuestionNavigationAware
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqQuestionNavigationAware
{
	/**
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getQuestionButtonsHTML() : \ILIAS\UI\Component\Component;
	
	/**
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getQuestionPlayerActionsHTML() : \ILIAS\UI\Component\Component;
	
	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getQuestionActionHandlingLink() : \ILIAS\UI\Component\Link\Link;
}