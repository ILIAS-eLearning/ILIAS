<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/COPage/classes/class.ilPageObject.php');

/**
 * Question page object
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * 
 * @version $Id$
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssQuestionPage extends ilPageObject
{
	/**
	 * Constructor
	 *
	 * @param int $a_id
	 * @param int $a_old_nr
	 *
	 * @return \ilAssQuestionPage
	 */
	public function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct('qpl', $a_id, $a_old_nr);
	}
}
