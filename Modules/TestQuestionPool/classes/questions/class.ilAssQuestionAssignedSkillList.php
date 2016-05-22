<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilAssQuestionAssignedSkillList
{
	/**
	 * @var array
	 */
	protected $skills = array();
	
	/**
	 * @param integer $skillBaseId
	 * @param integer $skillTrefId
	 */
	public function addSkill($skillBaseId, $skillTrefId)
	{
		$this->skills[] = "{$skillBaseId}:{$skillTrefId}";
	}
	
	/**
	 * @return bool
	 */
	public function skillsExist()
	{
		return (bool)count($this->skills);
	}
}