<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImporter
{
    protected $db;
    
    /**
     * @var integer
     */
    private $targetParentObjId;
    
    /**
     * @var integer
     */
    protected $importInstallationId;
    
    /**
     * @var ilImportMapping
     */
    protected $importMappingRegistry;
    
    /**
     * @var string
     */
    protected $importMappingComponent;
    /**
     * @var ilAssQuestionSkillAssignmentImportList
     */
    protected $importAssignmentList;
    
    /**
     * @var ilAssQuestionSkillAssignmentImportList
     */
    protected $failedImportAssignmentList;
    
    /**
     * @var ilAssQuestionSkillAssignmentList
     */
    protected $successImportAssignmentList;
    
    /**
     * ilAssQuestionSkillAssignmentImporter constructor.
     */
    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $this->db = $ilDB;
        
        $this->targetParentObjId = null;
        $this->importInstallationId = null;
        $this->importMappingRegistry = null;
        $this->importAssignmentList = null;
        $this->failedImportAssignmentList = new ilAssQuestionSkillAssignmentImportList();
        $this->successImportAssignmentList = new ilAssQuestionSkillAssignmentList($this->db);
    }
    
    /**
     * @param int $targetParentObjId
     */
    public function setTargetParentObjId($targetParentObjId) : void
    {
        $this->targetParentObjId = $targetParentObjId;
    }
    
    /**
     * @return int
     */
    public function getTargetParentObjId() : ?int
    {
        return $this->targetParentObjId;
    }
    
    /**
     * @return int
     */
    public function getImportInstallationId() : ?int
    {
        return $this->importInstallationId;
    }
    
    /**
     * @param int $installationId
     */
    public function setImportInstallationId($importInstallationId) : void
    {
        $this->importInstallationId = $importInstallationId;
    }
    
    /**
     * @return ilImportMapping
     */
    public function getImportMappingRegistry() : ?ilImportMapping
    {
        return $this->importMappingRegistry;
    }
    
    /**
     * @param ilImportMapping $importMappingRegistry
     */
    public function setImportMappingRegistry($importMappingRegistry) : void
    {
        $this->importMappingRegistry = $importMappingRegistry;
    }
    
    /**
     * @return string
     */
    public function getImportMappingComponent() : string
    {
        return $this->importMappingComponent;
    }
    
    /**
     * @param string $importMappingComponent
     */
    public function setImportMappingComponent($importMappingComponent) : void
    {
        $this->importMappingComponent = $importMappingComponent;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentImportList
     */
    public function getImportAssignmentList() : ?ilAssQuestionSkillAssignmentImportList
    {
        return $this->importAssignmentList;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentImportList $importAssignmentList
     */
    public function setImportAssignmentList($importAssignmentList) : void
    {
        $this->importAssignmentList = $importAssignmentList;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentImportList
     */
    public function getFailedImportAssignmentList() : ilAssQuestionSkillAssignmentImportList
    {
        return $this->failedImportAssignmentList;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentList
     */
    public function getSuccessImportAssignmentList() : ilAssQuestionSkillAssignmentList
    {
        return $this->successImportAssignmentList;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentList $successImportAssignmentList
     */
    public function setSuccessImportAssignmentList($successImportAssignmentList) : void
    {
        $this->successImportAssignmentList = $successImportAssignmentList;
    }
    
    /*
        $r = ilBasicSkill::getCommonSkillIdForImportId($a_source_inst_id,
        $a_skill_import_id, $a_tref_import_id);

        $results[] = array("skill_id" => $rec["obj_id"], "tref_id" => $t,
        "creation_date" => $rec["creation_date"]);
    */

    public function import() : void
    {
        foreach ($this->getImportAssignmentList() as $assignment) {
            $foundSkillId = $this->getSkillIdMapping(
                $assignment->getImportSkillBaseId(),
                $assignment->getImportSkillTrefId()
            );
            
            if (!$this->isValidSkill($foundSkillId)) {
                $this->getFailedImportAssignmentList()->addAssignment($assignment);
                continue;
            }
            
            $this->getImportMappingRegistry()->addMapping(
                'Modules/Test',
                'skl_base_id_reverse',
                $foundSkillId['skill_id'],
                $assignment->getImportSkillBaseId()
            );
            
            $this->getImportMappingRegistry()->addMapping(
                'Modules/Test',
                'skl_tref_id_reverse',
                $foundSkillId['tref_id'],
                $assignment->getImportSkillTrefId()
            );
            
            $importableAssignment = $this->buildImportableAssignment($assignment, $foundSkillId);
            
            foreach ($assignment->getImportSolutionComparisonExpressionList() as $solCompExp) {
                $importableSolCompExp = $this->buildImportableSolutionComparisonExpression($solCompExp);
                $importableAssignment->getSolutionComparisonExpressionList()->add($importableSolCompExp);
            }
            
            $importableAssignment->saveToDb();
            $importableAssignment->saveComparisonExpressions();

            // add skill usage
            ilSkillUsage::setUsage($this->getTargetParentObjId(), $foundSkillId['skill_id'], $foundSkillId['tref_id']);
            
            $this->getSuccessImportAssignmentList()->addAssignment($importableAssignment);
        }
    }
    
    protected function buildImportableAssignment(ilAssQuestionSkillAssignmentImport $assignment, $foundSkillId) : ilAssQuestionSkillAssignment
    {
        $importableAssignment = new ilAssQuestionSkillAssignment($this->db);
        
        $importableAssignment->setEvalMode($assignment->getEvalMode());
        $importableAssignment->setSkillPoints($assignment->getSkillPoints());
        
        $importableAssignment->setSkillBaseId($foundSkillId['skill_id']);
        $importableAssignment->setSkillTrefId($foundSkillId['tref_id']);
        
        $importableAssignment->setParentObjId($this->getTargetParentObjId());
        
        $importableAssignment->setQuestionId($this->getImportMappingRegistry()->getMapping(
            $this->getImportMappingComponent(),
            'quest',
            $assignment->getImportQuestionId()
        ));
        
        $importableAssignment->initSolutionComparisonExpressionList();
        
        return $importableAssignment;
    }
    
    protected function buildImportableSolutionComparisonExpression(ilAssQuestionSolutionComparisonExpressionImport $solCompExp) : ilAssQuestionSolutionComparisonExpression
    {
        $importableSolCompExp = new ilAssQuestionSolutionComparisonExpression($this->db);
        
        $importableSolCompExp->setOrderIndex($solCompExp->getOrderIndex());
        $importableSolCompExp->setExpression($solCompExp->getExpression());
        $importableSolCompExp->setPoints($solCompExp->getPoints());
        
        return $importableSolCompExp;
    }
    
    protected function isValidSkill($foundSkillData) : bool
    {
        if (!is_array($foundSkillData) || !isset($foundSkillData['skill_id']) || !$foundSkillData['skill_id']) {
            return false;
        }
        
        return true;
    }
    
    protected function getSkillIdMapping($importSkillBaseId, $importSkillTrefId)
    {
        if ($this->getImportInstallationId() == $this->getLocalInstallationId()) {
            return array( 'skill_id' => $importSkillBaseId, 'tref_id' => $importSkillTrefId );
        }
        
        $foundSkillData = ilBasicSkill::getCommonSkillIdForImportId(
            $this->getImportInstallationId(),
            $importSkillBaseId,
            $importSkillTrefId
        );
        
        return current($foundSkillData);
    }
    
    protected function getLocalInstallationId() : ?string
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return $DIC->settings()->get('inst_id', 0);
    }
}
