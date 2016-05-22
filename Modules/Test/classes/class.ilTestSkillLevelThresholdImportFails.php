<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportFails
{
	/**
	 * @var ilSetting
	 */
	protected $settings;
	
	/**
	 * @return ilSetting
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
	 * @param ilAssQuestionAssignedSkillList $skillList
	 */
	public function registerFailedImports($targetParentObjId, ilAssQuestionAssignedSkillList $skillList)
	{
		$this->getSettings()->set('failed_imp_slt_parentobj_'.$targetParentObjId, serialize($skillList));
	}
}