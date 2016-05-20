<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImporter
{
	/**
	 * @var ilAssQuestionSkillAssignmentImportList
	 */
	protected $assignmentList;
	
	/**
	 * ilAssQuestionSkillAssignmentImporter constructor.
	 */
	public function __construct()
	{
		$this->assignmentList = null;
	}
	
	/**
	 * @return ilAssQuestionSkillAssignmentImportList
	 */
	public function getAssignmentList()
	{
		return $this->assignmentList;
	}
	
	/**
	 * @param ilAssQuestionSkillAssignmentImportList $assignmentList
	 */
	public function setAssignmentList($assignmentList)
	{
		$this->assignmentList = $assignmentList;
	}
	
	/**
	 * @return bool
	 */
	public function	import()
	{
		return true;
	}
}