<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjTest.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestionCollection.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionCollectionSubsetApplicationList.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 */
class ilTestRandomQuestionsQuantitiesDistribution
{
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @var ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
     */
    protected $questionCollectionProvider;
    
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $sourcePoolDefinitionList;
    
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @var array[ $questionId => ilTestRandomQuestionSetSourcePoolDefinitionList ]
     */
    protected $questRelatedSrcPoolDefRegister = array();
    
    /**
     * @var array[ $definitionId => ilTestRandomSetQuestionCollection ]
     */
    protected $srcPoolDefRelatedQuestRegister= array();
    
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider
     */
    public function __construct(ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider)
    {
        if ($questionCollectionProvider !== null) {
            $this->setQuestionCollectionProvider($questionCollectionProvider);
        }
    }
    
    /**
     * @param ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider
     */
    public function setQuestionCollectionProvider(ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider)
    {
        $this->questionCollectionProvider = $questionCollectionProvider;
    }
    
    /**
     * @return ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
     */
    public function getQuestionCollectionProvider()
    {
        return $this->questionCollectionProvider;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     */
    public function setSourcePoolDefinitionList($sourcePoolDefinitionList)
    {
        $this->sourcePoolDefinitionList = $sourcePoolDefinitionList;
    }
    
    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    public function getSourcePoolDefinitionList()
    {
        return $this->sourcePoolDefinitionList;
    }

    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected function buildSourcePoolDefinitionListInstance()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $anyTestObject = new ilObjTest();
        $nonRequiredDb = $DIC['ilDB'];
        $nonUsedFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($nonRequiredDb, $anyTestObject);
        return new ilTestRandomQuestionSetSourcePoolDefinitionList($nonRequiredDb, $anyTestObject, $nonUsedFactory);
    }
    
    /**
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function buildRandomQuestionCollectionInstance()
    {
        return new ilTestRandomQuestionSetQuestionCollection();
    }
    
    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication
     */
    protected function buildQuestionCollectionSubsetApplicationInstance()
    {
        return new ilTestRandomQuestionCollectionSubsetApplication();
    }
    
    /**
     * @return ilTestRandomQuestionCollectionSubsetApplicationList
     */
    protected function buildQuestionCollectionSubsetApplicationListInstance()
    {
        return new ilTestRandomQuestionCollectionSubsetApplicationList();
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * re-setter for questRelatedSrcPoolDefRegister
     */
    protected function resetQuestRelatedSrcPoolDefRegister()
    {
        $this->questRelatedSrcPoolDefRegister = array();
    }
    
    /**
     * @param integer $questionId
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     */
    protected function registerQuestRelatedSrcPoolDef($questionId, ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        if (!$this->questRelatedSrcPoolDefRegister[$questionId]) {
            $this->questRelatedSrcPoolDefRegister[$questionId] = $this->buildSourcePoolDefinitionListInstance();
        }
        
        $this->questRelatedSrcPoolDefRegister[$questionId]->addDefinition($definition);
    }
    
    /**
     * @param $questionId
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected function getQuestRelatedSrcPoolDefinitionList($questionId)
    {
        if (isset($this->questRelatedSrcPoolDefRegister[$questionId])) {
            return $this->questRelatedSrcPoolDefRegister[$questionId];
        }
        
        return null;
    }
    
    /**
     * re-setter the srcPoolDefRelatedQuestRegister
     */
    protected function resetSrcPoolDefRelatedQuestRegister()
    {
        $this->srcPoolDefRelatedQuestRegister = array();
    }
    
    /**
     * @param integer $definitionId
     * @param ilTestRandomQuestionSetQuestion $randomSetQuestion
     */
    protected function registerSrcPoolDefRelatedQuest($definitionId, ilTestRandomQuestionSetQuestion $randomSetQuestion)
    {
        if (!isset($this->srcPoolDefRelatedQuestRegister[$definitionId])) {
            $this->srcPoolDefRelatedQuestRegister[$definitionId] = $this->buildRandomQuestionCollectionInstance();
        }
        
        $this->srcPoolDefRelatedQuestRegister[$definitionId]->addQuestion($randomSetQuestion);
    }
    
    /**
     * @param integer $definitionId
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function getSrcPoolDefRelatedQuestionCollection($definitionId)
    {
        if (isset($this->srcPoolDefRelatedQuestRegister[$definitionId])) {
            return $this->srcPoolDefRelatedQuestRegister[$definitionId];
        }
        
        return new ilTestRandomQuestionSetQuestionCollection();
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * initialise the src-pool-def/question registers
     */
    protected function initialiseRegisters()
    {
        foreach ($this->getSrcPoolDefQuestionCombinationCollection() as $randomQuestion) {
            $sourcePoolDefinition = $this->getSourcePoolDefinitionList()->getDefinition(
                $randomQuestion->getSourcePoolDefinitionId()
            );
            
            $this->registerSrcPoolDefRelatedQuest(
                $randomQuestion->getSourcePoolDefinitionId(),
                $randomQuestion
            );
            
            $this->registerQuestRelatedSrcPoolDef(
                $randomQuestion->getQuestionId(),
                $sourcePoolDefinition
            );
        }
    }
    
    /**
     * reset internal registers
     */
    protected function resetRegisters()
    {
        $this->resetQuestRelatedSrcPoolDefRegister();
        $this->resetSrcPoolDefRelatedQuestRegister();
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function getSrcPoolDefQuestionCombinationCollection()
    {
        $qstCollectionProvider = $this->getQuestionCollectionProvider();
        $srcPoolDefinitionList = $this->getSourcePoolDefinitionList();
        
        $defQstCombinationCollection = $qstCollectionProvider->getSrcPoolDefListRelatedQuestCombinationCollection(
            $srcPoolDefinitionList
        );
        
        return $defQstCombinationCollection;
    }
    
    /**
     * @param integer $definitionId
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function getExclusiveQuestionCollection($definitionId)
    {
        $exclusiveQstCollection = $this->buildRandomQuestionCollectionInstance();
        
        foreach ($this->getSrcPoolDefRelatedQuestionCollection($definitionId) as $question) {
            if ($this->isQuestionUsedByMultipleSrcPoolDefinitions($question)) {
                continue;
            }
            
            $exclusiveQstCollection->addQuestion($question);
        }
        
        return $exclusiveQstCollection;
    }
    
    /**
     * @param integer $definitionId
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function getSharedQuestionCollection($definitionId)
    {
        $srcPoolDefRelatedQstCollection = $this->getSrcPoolDefRelatedQuestionCollection($definitionId);
        $exclusiveQstCollection = $this->getExclusiveQuestionCollection($definitionId);
        return $srcPoolDefRelatedQstCollection->getRelativeComplementCollection($exclusiveQstCollection);
    }
    
    /**
     * @param integer $thisDefinitionId
     * @param integer $thatDefinitionId
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function getIntersectionQuestionCollection($thisDefinitionId, $thatDefinitionId)
    {
        $thisDefRelatedSharedQstCollection = $this->getSharedQuestionCollection($thisDefinitionId);
        $thatDefRelatedSharedQstCollection = $this->getSharedQuestionCollection($thatDefinitionId);
        
        $intersectionQstCollection = $thisDefRelatedSharedQstCollection->getIntersectionCollection(
            $thatDefRelatedSharedQstCollection
        );
        
        return $intersectionQstCollection;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return array[ $definitionId => ilTestRandomQuestionSetQuestionCollection ]
     */
    protected function getIntersectionQstCollectionByDefinitionMap(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $intersectionQstCollectionsByDefId = array();
        
        $sharedQuestionCollection = $this->getSharedQuestionCollection($definition->getId());
        foreach ($sharedQuestionCollection as $sharedQuestion) {
            $relatedSrcPoolDefList = $this->getQuestRelatedSrcPoolDefinitionList($sharedQuestion->getQuestionId());
            foreach ($relatedSrcPoolDefList as $otherDefinition) {
                if ($otherDefinition->getId() == $definition->getId()) {
                    continue;
                }
                
                if (isset($intersectionQstCollectionsByDefId[$otherDefinition->getId()])) {
                    continue;
                }
                
                $intersectionQuestionCollection = $this->getIntersectionQuestionCollection(
                    $definition->getId(),
                    $otherDefinition->getId()
                );
                
                $intersectionQstCollectionsByDefId[$otherDefinition->getId()] = $intersectionQuestionCollection;
            }
        }
        
        return $intersectionQstCollectionsByDefId;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionCollectionSubsetApplicationList
     */
    protected function getIntersectionQuestionCollectionSubsetApplicationList(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $qstCollectionSubsetApplicationList = $this->buildQuestionCollectionSubsetApplicationListInstance();
        
        $intersectionQstCollectionByDefIdMap = $this->getIntersectionQstCollectionByDefinitionMap($definition);
        foreach ($intersectionQstCollectionByDefIdMap as $otherDefinitionId => $intersectionCollection) {
            /* @var ilTestRandomQuestionSetQuestionCollection $intersectionCollection */
            
            $qstCollectionSubsetApplication = $this->buildQuestionCollectionSubsetApplicationInstance();
            $qstCollectionSubsetApplication->setQuestions($intersectionCollection->getQuestions());
            $qstCollectionSubsetApplication->setApplicantId($otherDefinitionId);
            
            #$qstCollectionSubsetApplication->setRequiredAmount($this->getRequiredSharedQuestionAmount(
            #	$this->getSourcePoolDefinitionList()->getDefinition($otherDefinitionId)
            #));
            
            $qstCollectionSubsetApplication->setRequiredAmount(
                $this->getSourcePoolDefinitionList()->getDefinition($otherDefinitionId)->getQuestionAmount()
            );
            
            $qstCollectionSubsetApplicationList->addCollectionSubsetApplication($qstCollectionSubsetApplication);
        }
        
        return $qstCollectionSubsetApplicationList;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected function getIntersectionSharingDefinitionList(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $intersectionSharingDefinitionList = $this->buildSourcePoolDefinitionListInstance();
        
        $sharedQuestionCollection = $this->getSharedQuestionCollection($definition->getId());
        foreach ($sharedQuestionCollection as $sharedQuestion) {
            $relatedSrcPoolDefList = $this->getQuestRelatedSrcPoolDefinitionList($sharedQuestion->getQuestionId());
            foreach ($relatedSrcPoolDefList as $otherDefinition) {
                if ($otherDefinition->getId() == $definition->getId()) {
                    continue;
                }
                
                if ($intersectionSharingDefinitionList->hasDefinition($otherDefinition->getId())) {
                    continue;
                }
                
                $intersectionSharingDefinitionList->addDefinition($otherDefinition);
            }
        }
        
        return $intersectionSharingDefinitionList;
    }
            
    /**
     * @param ilTestRandomQuestionSetQuestion $question
     * @return bool
     */
    protected function isQuestionUsedByMultipleSrcPoolDefinitions(ilTestRandomQuestionSetQuestion $question)
    {
        /* @var ilTestRandomQuestionSetSourcePoolDefinitionList $qstRelatedSrcPoolDefList */
        $qstRelatedSrcPoolDefList = $this->questRelatedSrcPoolDefRegister[$question->getQuestionId()];
        return $qstRelatedSrcPoolDefList->getDefinitionCount() > 1;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     */
    protected function getSrcPoolDefRelatedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        return $this->getSrcPoolDefRelatedQuestionCollection($definition->getId())->getQuestionAmount();
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer
     */
    protected function getExclusiveQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        return $this->getExclusiveQuestionCollection($definition->getId())->getQuestionAmount();
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer $availableSharedQuestionAmount
     */
    protected function getAvailableSharedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $intersectionSubsetApplicationList = $this->getIntersectionQuestionCollectionSubsetApplicationList($definition);
        
        foreach ($this->getSharedQuestionCollection($definition->getId()) as $sharedQuestion) {
            $intersectionSubsetApplicationList->handleQuestionRequest($sharedQuestion);
        }
        
        return $intersectionSubsetApplicationList->getNonReservedQuestionAmount();
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer
     */
    protected function getRequiredSharedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $exclusiveQstCollection = $this->getExclusiveQuestionCollection($definition->getId());
        $missingExclsuiveQstCount = $exclusiveQstCollection->getMissingCount($definition->getQuestionAmount());
        return $missingExclsuiveQstCount;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return bool
     */
    protected function requiresSharedQuestions(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        return $this->getRequiredSharedQuestionAmount($definition) > 0;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    public function initialise()
    {
        $this->initialiseRegisters();
    }
    
    public function reset()
    {
        $this->resetRegisters();
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation
     */
    public function calculateQuantities(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $quantityCalculation = new ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation($definition);
        
        $quantityCalculation->setOverallQuestionAmount($this->getSrcPoolDefRelatedQuestionAmount($definition));
        $quantityCalculation->setExclusiveQuestionAmount($this->getExclusiveQuestionAmount($definition));
        $quantityCalculation->setAvailableSharedQuestionAmount($this->getAvailableSharedQuestionAmount($definition));
        
        $quantityCalculation->setIntersectionQuantitySharingDefinitionList(
            $this->getIntersectionSharingDefinitionList($definition)
        );
        
        return $quantityCalculation;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
}
