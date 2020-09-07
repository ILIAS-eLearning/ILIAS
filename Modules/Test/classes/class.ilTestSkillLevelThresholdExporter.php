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
     * @var ilAssQuestionSkillAssignmentList
     */
    protected $assignmentList;
    
    /**
     * @var ilTestSkillLevelThresholdList
     */
    protected $thresholdList;
    
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
     * @return ilAssQuestionSkillAssignmentList
     */
    public function getAssignmentList()
    {
        return $this->assignmentList;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentList $assignmentList
     */
    public function setAssignmentList($assignmentList)
    {
        $this->assignmentList = $assignmentList;
    }
    
    /**
     * @return ilTestSkillLevelThresholdList
     */
    public function getThresholdList()
    {
        return $this->thresholdList;
    }
    
    /**
     * @param ilTestSkillLevelThresholdList $thresholdList
     */
    public function setThresholdList($thresholdList)
    {
        $this->thresholdList = $thresholdList;
    }
    
    public function export()
    {
        $this->getXmlWriter()->xmlStartTag('SkillsLevelThresholds');
        
        foreach ($this->getAssignmentList()->getUniqueAssignedSkills() as $assignedSkillData) {
            $this->getXmlWriter()->xmlStartTag('QuestionsAssignedSkill', array(
                'BaseId' => $assignedSkillData['skill_base_id'],
                'TrefId' => $assignedSkillData['skill_tref_id']
            ));
            
            $this->getXmlWriter()->xmlElement('OriginalSkillTitle', null, $assignedSkillData['skill_title']);
            $this->getXmlWriter()->xmlElement('OriginalSkillPath', null, $assignedSkillData['skill_path']);
            
            /* @var ilBasicSkill $assignedSkill */
            $assignedSkill = $assignedSkillData['skill'];
            $skillLevels = $assignedSkill->getLevelData();
            
            for ($i = 0, $max = count($skillLevels); $i < $max; $i++) {
                $levelData = $skillLevels[$i];
                
                $skillLevelThreshold = $this->getThresholdList()->getThreshold(
                    $assignedSkillData['skill_base_id'],
                    $assignedSkillData['skill_tref_id'],
                    $levelData['id'],
                    true
                );
                
                $this->getXmlWriter()->xmlStartTag('SkillLevel', array(
                    'Id' => $levelData['id'], 'Nr' => $levelData['nr']
                ));
                
                $this->getXmlWriter()->xmlElement('ThresholdPercentage', null, $skillLevelThreshold->getThreshold());
                
                $this->getXmlWriter()->xmlElement('OriginalLevelTitle', null, $levelData['title']);
                $this->getXmlWriter()->xmlElement('OriginalLevelDescription', null, $levelData['description']);
                
                $this->getXmlWriter()->xmlEndTag('SkillLevel');
            }
            
            $this->getXmlWriter()->xmlEndTag('QuestionsAssignedSkill');
        }
        
        $this->getXmlWriter()->xmlEndTag('SkillsLevelThresholds');
    }
}
