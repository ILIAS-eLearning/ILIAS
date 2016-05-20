<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImportList
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
	 * ilAssQuestionSkillAssignmentImportList constructor.
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