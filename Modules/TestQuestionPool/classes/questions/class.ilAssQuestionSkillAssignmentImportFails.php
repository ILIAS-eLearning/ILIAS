<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImportFails
{
	/**
	 * @var ilSeting
	 */
	protected $settings;
	
	/**
	 * @return ilSeting|ilSetting
	 */
	protected function getSettings()
	{
		if( $this->settings === null )
		{
			$this->settings = new ilSetting('assessment');
		}
		
		return $this->settings;
	}
	
	/**
	 * @param $targetParentObjId
	 * @param ilAssQuestionSkillAssignmentImportList $assignmentList
	 */
	public function registerFailedImports($targetParentObjId, ilAssQuestionSkillAssignmentImportList $assignmentList)
	{
		$this->getSettings()->set('failed_imp_qsa_parentobj_'.$targetParentObjId, serialize($assignmentList));
	}
}