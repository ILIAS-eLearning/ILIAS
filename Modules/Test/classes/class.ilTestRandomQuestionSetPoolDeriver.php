<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolFactory.php';
require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
        
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetPoolDeriver
{
    /**
     * @var ilDBInterface
     */
    protected $db;
    
    /**
     * @var ilPluginAdmin
     */
    protected $pluginAdmin;
    
    /**
     * @var ilObjTest
     */
    protected $testOBJ;
    
    /**
     * @var integer
     */
    protected $targetContainerRef;
    
    /**
     * @var integer
     */
    protected $ownerId;
    
    /**
     * @var ilQuestionPoolFactory
     */
    protected $poolFactory;
    
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $sourcePoolDefinitionList;
    
    public function __construct(ilDBInterface $ilDB, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->db = $ilDB;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;
        $this->poolFactory = new ilQuestionPoolFactory();
    }
        
    /**
     * @return int
     */
    public function getTargetContainerRef()
    {
        return $this->targetContainerRef;
    }
    
    /**
     * @param int $targetContainerRef
     */
    public function setTargetContainerRef($targetContainerRef)
    {
        $this->targetContainerRef = $targetContainerRef;
    }
    
    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
    
    /**
     * @param int $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }
    
    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    public function getSourcePoolDefinitionList()
    {
        return $this->sourcePoolDefinitionList;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     */
    public function setSourcePoolDefinitionList($sourcePoolDefinitionList)
    {
        $this->sourcePoolDefinitionList = $sourcePoolDefinitionList;
    }
    
    protected function getQuestionsForPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
    {
        $questionList = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->db,
            $this->pluginAdmin
        );
        
        $questionList->setTestObjId($this->testOBJ->getId());
        $questionList->setTestId($this->testOBJ->getTestId());
        $questionList->setPoolId($nonAvailablePool->getId());
        
        $questionList->loadQuestions();
        
        $questions = array();
        
        foreach ($questionList as $questionId) {
            $questions[] = assQuestion::_instantiateQuestion($questionId);
        }
        
        return $questions;
    }
    
    protected function createNewPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
    {
        $pool = $this->poolFactory->createNewInstance($this->getTargetContainerRef());
        
        if (strlen($nonAvailablePool->getTitle())) {
            $pool->setTitle($nonAvailablePool->getTitle());
            $pool->update();
        }
        
        return $pool;
    }
    
    protected function copyQuestionsToPool(ilObjQuestionPool $pool, $questions)
    {
        $poolQidByTestQidMap = array();
        
        foreach ($questions as $questionOBJ) {
            /* @var assQuestion $questionOBJ */

            $testQuestionId = $questionOBJ->getId();
            $poolQuestionId = $questionOBJ->duplicate(false, '', '', $this->getOwnerId(), $pool->getId());
            
            $poolQidByTestQidMap[$testQuestionId] = $poolQuestionId;
        }
        
        return $poolQidByTestQidMap;
    }
    
    protected function updateTestQuestionStage($poolQidByTestQidMap)
    {
        foreach ($poolQidByTestQidMap as $testQid => $poolQid) {
            assQuestion::resetOriginalId($poolQid);
            assQuestion::saveOriginalId($testQid, $poolQid);
        }
    }
    
    protected function filterForQuestionRelatedTaxonomies($taxonomyIds, $relatedQuestionIds)
    {
        require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
        
        $filteredTaxIds = array();
        
        foreach ($taxonomyIds as $taxonomyId) {
            $taxNodeAssignment = new ilTaxNodeAssignment(
                $this->testOBJ->getType(),
                $this->testOBJ->getId(),
                'quest',
                $taxonomyId
            );
            
            foreach ($relatedQuestionIds as $questionId) {
                $assignedTaxNodes = $taxNodeAssignment->getAssignmentsOfItem($questionId);
                
                if (count($assignedTaxNodes)) {
                    $filteredTaxIds[] = $taxonomyId;
                    break;
                }
            }
        }
        
        return $filteredTaxIds;
    }
    
    protected function duplicateTaxonomies($poolQidByTestQidMap, ilObjQuestionPool $pool)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolTaxonomiesDuplicator.php';
        $taxDuplicator = new ilQuestionPoolTaxonomiesDuplicator();
        $taxDuplicator->setSourceObjId($this->testOBJ->getId());
        $taxDuplicator->setSourceObjType($this->testOBJ->getType());
        $taxDuplicator->setTargetObjId($pool->getId());
        $taxDuplicator->setTargetObjType($pool->getType());
        $taxDuplicator->setQuestionIdMapping($poolQidByTestQidMap);
        
        $taxDuplicator->duplicate($this->filterForQuestionRelatedTaxonomies(
            $taxDuplicator->getAllTaxonomiesForSourceObject(),
            array_keys($poolQidByTestQidMap)
        ));
        
        return $taxDuplicator->getDuplicatedTaxonomiesKeysMap();
    }
    
    protected function buildOriginalTaxonomyFilterForDerivedPool(ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxKeysMap, $mappedTaxonomyFilter)
    {
        $originalTaxonomyFilter = array();
        
        foreach ($mappedTaxonomyFilter as $testTaxonomyId => $testTaxNodes) {
            $poolTaxonomyId = $taxKeysMap->getMappedTaxonomyId($testTaxonomyId);
            $originalTaxonomyFilter[$poolTaxonomyId] = array();
            
            foreach ($testTaxNodes as $testTaxNode) {
                $poolTaxNode = $taxKeysMap->getMappedTaxNodeId($testTaxNode);
                $originalTaxonomyFilter[$poolTaxonomyId][] = $poolTaxNode;
            }
        }
        
        return $originalTaxonomyFilter;
    }
    
    protected function updateRelatedSourcePoolDefinitions(ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxKeysMap, $derivedPoolId, $nonAvailablePoolId)
    {
        foreach ($this->getSourcePoolDefinitionList() as $definition) {
            if ($definition->getPoolId() != $nonAvailablePoolId) {
                continue;
            }
            
            $definition->setPoolId($derivedPoolId);
            
            $definition->setOriginalTaxonomyFilter($this->buildOriginalTaxonomyFilterForDerivedPool(
                $taxKeysMap,
                $definition->getMappedTaxonomyFilter()
            ));
            
            $definition->saveToDb();
        }
    }
    
    /**
     * @param ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool
     * @return ilObjQuestionPool
     */
    public function derive(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
    {
        $pool = $this->createNewPool($nonAvailablePool);
        $questions = $this->getQuestionsForPool($nonAvailablePool);
        
        $poolQidByTestQidMap = $this->copyQuestionsToPool($pool, $questions);
        
        $this->updateTestQuestionStage($poolQidByTestQidMap);
        
        $duplicatedTaxKeysMap = $this->duplicateTaxonomies($poolQidByTestQidMap, $pool);
        
        $this->updateRelatedSourcePoolDefinitions($duplicatedTaxKeysMap, $pool->getId(), $nonAvailablePool->getId());
        
        return $pool;
    }
}
