<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetPoolDeriver
{
    protected ilDBInterface $db;
    protected ilComponentRepository $component_repository;
    protected ilObjTest $testOBJ;
    protected ilQuestionPoolFactory $poolFactory;

    /**
     * @var integer
     */
    protected $targetContainerRef;

    /**
     * @var integer
     */
    protected $ownerId;

    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $sourcePoolDefinitionList;
    
    public function __construct(ilDBInterface $ilDB, ilComponentRepository $component_repository, ilObjTest $testOBJ)
    {
        $this->db = $ilDB;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;
        $this->poolFactory = new ilQuestionPoolFactory();
    }
        
    /**
     * @return int
     */
    public function getTargetContainerRef() : int
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
    public function getOwnerId() : int
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
    public function getSourcePoolDefinitionList() : ilTestRandomQuestionSetSourcePoolDefinitionList
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
    
    protected function getQuestionsForPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool) : array
    {
        $questionList = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->db,
            $this->component_repository
        );
        
        $questionList->setTestObjId($this->testOBJ->getId());
        $questionList->setTestId($this->testOBJ->getTestId());
        $questionList->setPoolId($nonAvailablePool->getId());
        
        $questionList->loadQuestions();
        
        $questions = array();
        $list = $questionList->getQuestions();
        foreach ($list as $questionId) {
            $questions[] = assQuestion::_instantiateQuestion($questionId);
        }
        
        return $questions;
    }
    
    protected function createNewPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool) : ilObjQuestionPool
    {
        $pool = $this->poolFactory->createNewInstance($this->getTargetContainerRef());
        
        if (strlen($nonAvailablePool->getTitle())) {
            $pool->setTitle($nonAvailablePool->getTitle());
            $pool->update();
        }
        
        return $pool;
    }
    
    protected function copyQuestionsToPool(ilObjQuestionPool $pool, $questions) : array
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
    
    protected function filterForQuestionRelatedTaxonomies($taxonomyIds, $relatedQuestionIds) : array
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
    
    protected function duplicateTaxonomies($poolQidByTestQidMap, ilObjQuestionPool $pool) : ilQuestionPoolDuplicatedTaxonomiesKeysMap
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
    
    protected function buildOriginalTaxonomyFilterForDerivedPool(ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxKeysMap, $mappedTaxonomyFilter) : array
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
    public function derive(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool) : ilObjQuestionPool
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
