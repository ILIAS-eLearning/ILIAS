<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilAssQuestionSkillAssignmentListImport
{
	
	/**
	 * @var integer
	 */
	protected $parentObjId;
	
	/**
	 * @var array[ilAssQuestionSkillAssignmentImport]
	 */
	protected $assignments;
	
	/**
	 * ilAssQuestionSkillAssignmentListImport constructor.
	 */
	public function __construct()
	{
		$this->parentObjId = null;
		$this->assignments = array();
	}
	
	/**
	 * @param ilAssQuestionSkillAssignmentImport $assignment
	 */
	public function add(ilAssQuestionSkillAssignmentImport $assignment)
	{
		$this->assignments[] = $assignment;
	}
	
}