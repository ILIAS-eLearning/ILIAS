<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdExporter
{
	/**
	 * @var ilXmlWriter
	 */
	protected $xmlWriter;
	
	/**
	 * @var integer
	 */
	protected $parentObjId;
	
	/**
	 * @var integer
	 */
	protected $testId;
	
	/**
	 * ilAssQuestionSkillAssignmentExporter constructor.
	 */
	public function __construct()
	{
		$this->xmlWriter = null;
	}
	
	/**
	 * @return ilXmlWriter
	 */
	public function getXmlWriter()
	{
		return $this->xmlWriter;
	}
	
	/**
	 * @param ilXmlWriter $xmlWriter
	 */
	public function setXmlWriter(ilXmlWriter $xmlWriter)
	{
		$this->xmlWriter = $xmlWriter;
	}
	
	/**
	 * @return int
	 */
	public function getParentObjId()
	{
		return $this->parentObjId;
	}
	
	/**
	 * @param int $parentObjId
	 */
	public function setParentObjId($parentObjId)
	{
		$this->parentObjId = $parentObjId;
	}
	
	/**
	 * @return int
	 */
	public function getTestId()
	{
		return $this->testId;
	}
	
	/**
	 * @param int $testId
	 */
	public function setTestId($testId)
	{
		$this->testId = $testId;
	}
	
	public function export()
	{
		global $ilDB;
		
		$this->getXmlWriter()->xmlStartTag('SkillsLevelThresholds');
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
		
		$assignmentList->setParentObjId($this->getParentObjId());
		$assignmentList->loadFromDb();
		
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
		$thresholdList = new ilTestSkillLevelThresholdList($ilDB);
		$thresholdList->setTestId($this->getTestId());
		$thresholdList->loadFromDb();
		
		foreach($assignmentList->getUniqueAssignedSkills() as $assignedSkillData)
		{
			$this->getXmlWriter()->xmlStartTag('QuestionsAssignedSkill', array(
				'SkillBaseId' => $assignedSkillData['skill_base_id'],
				'SkillTrefId' => $assignedSkillData['skill_tref_id']
			));
			
			$this->getXmlWriter()->xmlElement('OriginalSkillTitle', null, $assignedSkillData['skill_title']);
			$this->getXmlWriter()->xmlElement('OriginalSkillPath', null, $assignedSkillData['skill_path']);
			
			/* @var ilBasicSkill $assignedSkill */
			$assignedSkill = $assignedSkillData['skill'];
			$skillLevels = $assignedSkill->getLevelData();
			
			for($i = 0, $max = count($skillLevels); $i < $max; $i++)
			{
				$levelData = $skillLevels[$i];
				
				$skillLevelThreshold = $thresholdList->getThreshold(
					$assignedSkillData['skill_base_id'], $assignedSkillData['skill_tref_id'], $levelData['id'], true
				);
				
				$this->getXmlWriter()->xmlStartTag('SkillLevel', array(
					'Id' => $levelData['id'], 'Nr' => $levelData['nr']
				));
				
				$this->getXmlWriter()->xmlElement('SkillPointsThreshold', null, $skillLevelThreshold->getThreshold());
				
				$this->getXmlWriter()->xmlElement('OriginalSkillTitle', null, $levelData['title']);
				$this->getXmlWriter()->xmlElement('OriginalSkillDescription', null, $levelData['description']);
				
				$this->getXmlWriter()->xmlEndTag('SkillLevel');
			}
			
			$this->getXmlWriter()->xmlEndTag('QuestionsAssignedSkill');
		}
		
		$this->getXmlWriter()->xmlEndTag('SkillsLevelThresholds');
	}
}