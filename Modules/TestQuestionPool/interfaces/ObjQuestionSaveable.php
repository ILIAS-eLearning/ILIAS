<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ObjQuestionSaveable
 * 
 * This is the base interface for "saveable question". This should of course apply to all question types.
 * 
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version		$Id$
 * 
 * @ingroup 	ModulesTestQuestionPool
 */
interface ObjQuestionSaveable 
{
	/**
	 * Saves a question "as a whole" to the database.
	 * 
	 * @param $original_id	int	Original Id of the question.
	 *
	 * @return mixed
	 */
	public function saveToDb($original_id);
}