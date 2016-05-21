<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportList
{
	protected $originalSkillTitles = array();
	protected $originalSkillPaths = array();
	
	public function addOriginalSkillTitle($skillBaseId, $skillTrefId, $originalSkillTitle)
	{
		$this->originalSkillTitles["{$skillBaseId}:{$skillTrefId}"] = $originalSkillTitle;
	}
	
	public function addOriginalSkillPath($skillBaseId, $skillTrefId, $originalSkillPath)
	{
		$this->originalSkillPaths["{$skillBaseId}:{$skillTrefId}"] = $originalSkillPath;
	}
}