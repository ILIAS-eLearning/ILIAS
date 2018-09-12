<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAsqService
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqService
{
	/**
	 * @param ilCtrl $ctrl
	 * @return string
	 */
	public static function fetchNextAuthoringCommandClass(ilCtrl $ctrl);
	
	/**
	 * @param ilQTIItem $qtiItem
	 * @return string
	 */
	public static function determineQuestionTypeByQtiItem(ilQTIItem $qtiItem);
}