<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for Listeners that want to be notified about question changes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
interface AsqEventListener
{
	//TODO
	/**
	 * @param assQuestion $question
	 */
	public function QuestionCreated(assQuestion $question);

	/**
	 * @param assQuestion $question
	 */
	public function notifyQuestionEdited(assQuestion $question);

	/**
	 * @param assQuestion $question
	 */
	public function notifyQuestionDeleted(assQuestion $question);
}
