<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Skill/classes/class.ilBasicSkill.php';
require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionAssignedSkillList.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdImporter
{
    /**
     * @var integer
     */
    protected $targetTestId = null;
    
    /**
     * @var integer
     */
    protected $importInstallationId = null;
    
    /**
     * @var ilImportMapping
     */
    protected $importMappingRegistry = null;
    
    /**
     * @var ilAssQuestionSkillAssignmentList
     */
    protected $importedQuestionSkillAssignmentList = null;
    
    /**
     * @var ilTestSkillLevelThresholdImportList
     */
    protected $importThresholdList = null;
    
    /**
     * @var ilAssQuestionAssignedSkillList
     */
    protected $failedThresholdImportSkillList = null;
    
    /**
     * ilTestSkillLevelThresholdImporter constructor.
     */
    public function __construct()
    {
        $this->failedThresholdImportSkillList = new ilAssQuestionAssignedSkillList();
    }
    
    /**
     * @return int
     */
    public function getTargetTestId()
    {
        return $this->targetTestId;
    }
    
    /**
     * @param int $targetTestId
     */
    public function setTargetTestId($targetTestId)
    {
        $this->targetTestId = $targetTestId;
    }
    
    /**
     * @return int
     */
    public function getImportInstallationId()
    {
        return $this->importInstallationId;
    }
    
    /**
     * @param int $importInstallationId
     */
    public function setImportInstallationId($importInstallationId)
    {
        $this->importInstallationId = $importInstallationId;
    }
    
    /**
     * @return ilImportMapping
     */
    public function getImportMappingRegistry()
    {
        return $this->importMappingRegistry;
    }
    
    /**
     * @param ilImportMapping $importMappingRegistry
     */
    public function setImportMappingRegistry($importMappingRegistry)
    {
        $this->importMappingRegistry = $importMappingRegistry;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentList
     */
    public function getImportedQuestionSkillAssignmentList()
    {
        return $this->importedQuestionSkillAssignmentList;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentList $importedQuestionSkillAssignmentList
     */
    public function setImportedQuestionSkillAssignmentList($importedQuestionSkillAssignmentList)
    {
        $this->importedQuestionSkillAssignmentList = $importedQuestionSkillAssignmentList;
    }
    
    /**
     * @return ilTestSkillLevelThresholdImportList
     */
    public function getImportThresholdList()
    {
        return $this->importThresholdList;
    }
    
    /**
     * @param ilTestSkillLevelThresholdImportList $importThresholdList
     */
    public function setImportThresholdList($importThresholdList)
    {
        $this->importThresholdList = $importThresholdList;
    }
    
    /**
     * @return ilAssQuestionAssignedSkillList
     */
    public function getFailedThresholdImportSkillList()
    {
        return $this->failedThresholdImportSkillList;
    }
    
    /**
     * @param ilAssQuestionAssignedSkillList $failedThresholdImportSkillList
     */
    public function setFailedThresholdImportSkillList($failedThresholdImportSkillList)
    {
        $this->failedThresholdImportSkillList = $failedThresholdImportSkillList;
    }
    
    /**
     */
    public function import()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $importedLevelThresholdList = new ilTestSkillLevelThresholdList($ilDB);
        
        foreach ($this->getImportedQuestionSkillAssignmentList()->getUniqueAssignedSkills() as $skillData) {
            /* @var ilBasicSkill $skill */
            $skill = $skillData['skill'];
            
            $importSkillBaseId = $this->getImportMappingRegistry()->getMapping(
                'Modules/Test',
                'skl_base_id_reverse',
                $skillData['skill_base_id']
            );
            
            $importSkillTrefId = $this->getImportMappingRegistry()->getMapping(
                'Modules/Test',
                'skl_tref_id_reverse',
                $skillData['skill_tref_id']
            );
            
            $levelThresholds = $this->getImportThresholdList()->getThresholdsByImportSkill(
                $importSkillBaseId,
                $importSkillTrefId
            );
            
            $existingLevels = $skill->getLevelData();
            
            if (count($levelThresholds) != count($existingLevels)) {
                $this->getFailedThresholdImportSkillList()->addSkill(
                    $skillData['skill_base_id'],
                    $skillData['skill_tref_id']
                );
                
                continue;
            }
            
            for ($i = 0, $max = count($existingLevels); $i < $max; $i++) {
                $existingLevelData = $existingLevels[$i];

                /* @var ilTestSkillLevelThresholdImport $importLevelThreshold */
                $importLevelThreshold = $levelThresholds[$i];
                
                if ($importLevelThreshold->getOrderIndex() != $existingLevelData['nr']) {
                    $this->getFailedThresholdImportSkillList()->addSkill(
                        $skillData['skill_base_id'],
                        $skillData['skill_tref_id']
                    );
                    
                    continue(2);
                }
                
                if (!is_numeric($importLevelThreshold->getThreshold())) {
                    continue(2);
                }
                
                $mappedLevelId = $this->getLevelIdMapping($importLevelThreshold->getImportLevelId());
                
                $threshold = new ilTestSkillLevelThreshold($ilDB);
                $threshold->setTestId($this->getTargetTestId());
                $threshold->setSkillBaseId($skillData['skill_base_id']);
                $threshold->setSkillTrefId($skillData['skill_tref_id']);
                $threshold->setSkillLevelId($mappedLevelId);
                $threshold->setThreshold($importLevelThreshold->getThreshold());
                
                $importedLevelThresholdList->addThreshold($threshold);
            }
        }
        
        $importedLevelThresholdList->saveToDb();
    }
    
    /**
     * @param $importLevelId
     * @return integer
     */
    protected function getLevelIdMapping($importLevelId)
    {
        /*
                include_once("./Services/Skill/classes/class.ilBasicSkill.php");
                $r = ilBasicSkill::getLevelIdForImportId($a_source_inst_id,
                $a_level_import_id);

                $results[] = array("level_id" => $rec["id"], "creation_date" =>
                $rec["creation_date"]);
        */
        
        $result = ilBasicSkill::getLevelIdForImportId($this->getImportInstallationId(), $importLevelId);
        $mostNewLevelData = current($result);
        return $mostNewLevelData['level_id'];
    }
}
