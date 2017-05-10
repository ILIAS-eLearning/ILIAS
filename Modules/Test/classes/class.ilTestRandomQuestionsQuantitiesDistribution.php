<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 */
class ilTestRandomQuestionsQuantitiesDistribution
{
	/**
	 * @var ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
	 */
	protected $questionCollectionProvider;
	
	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionList
	 */
	protected $sourcePoolDefinitionList;
	
	/**
	 * @var array[ $questionId => array[$definitionId] ] 
	 */
	protected $questRelatedSrcPoolDefRegister = array();
	
	/**
	 * @var array[ $definitionId => array[ilTestRandomSetQuestion] ]
	 */
	protected $srcPoolDefRelatedQuestRegister= array();
	
	/**
	 * @param ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider
	 */
	public function __construct(ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider)
	{
		if( $questionCollectionProvider !== null )
		{
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
	
	public function resetQuestRelatedSrcPoolDefRegister()
	{
		$this->questRelatedSrcPoolDefRegister = array();
	}
	
	/**
	 * @param integer $questionId
	 * @param integer $definitionId
	 */
	public function registerQuestRelatedSrcPoolDef($questionId, $definitionId)
	{
		if( !$this->questRelatedSrcPoolDefRegister[$questionId] )
		{
			$this->questRelatedSrcPoolDefRegister[$questionId] = array();
		}
		
		$this->questRelatedSrcPoolDefRegister[$questionId][] = $definitionId;
	}
	
	public function resetSrcPoolDefRelatedQuestRegister()
	{
		$this->srcPoolDefRelatedQuestRegister = array();
	}
	
	/**
	 * @param integer $definitionId
	 * @param ilTestRandomQuestionSetQuestion $randomSetQuestion
	 */
	public function registerSrcPoolDefRelatedQuest($definitionId, ilTestRandomQuestionSetQuestion $randomSetQuestion)
	{
		if( !$this->srcPoolDefRelatedQuestRegister[$definitionId] )
		{
			$this->srcPoolDefRelatedQuestRegister[$definitionId] = array();
		}
		
		$this->srcPoolDefRelatedQuestRegister[$definitionId][$randomSetQuestion->getQuestionId()] = $randomSetQuestion;
	}
	
	/**
	 * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
	 * @return integer
	 */
	protected function getMissingQuestionCountUsingUpExclusiveQuestionCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		$exclusiveQstCollection = $this->getExclusiveQuestionCollection($definition->getId());
		return $exclusiveQstCollection->getMissingCount($definition->getQuestionAmount());
	}
	
	/**
	 * @param ilTestRandomQuestionSetQuestion $question
	 * @param $definitionId
	 */
	protected function isQuestionUsedUpByAnySrcPoolDefinition(ilTestRandomQuestionSetQuestion $question, $definitionId)
	{
		// true when question must be used by any other definition than given
		// otherwise false, becauseevery definition will left questions over that fits our need in sum
		
		return true;
	}
	
	/**
	 * @param ilTestRandomQuestionSetQuestion $question
	 * @return bool
	 */
	protected function isQuestionUsedByMultipleSrcPoolDefinitions(ilTestRandomQuestionSetQuestion $question)
	{
		return 1 < count($this->questRelatedSrcPoolDefRegister[$question->getQuestionId()]);
	}
	
	/**
	 * @param integer $definitionId
	 * @return ilTestRandomQuestionSetQuestionCollection
	 */
	protected function getSrcPoolDefRelatedQuestionCollection($definitionId)
	{
		$srcPoolDefRelatedQstCollection = new ilTestRandomQuestionSetQuestionCollection();
		
		foreach($this->srcPoolDefRelatedQuestRegister[$definitionId] as $question)
		{
			$srcPoolDefRelatedQstCollection->addQuestion($question);
		}
		
		return $srcPoolDefRelatedQstCollection;
	}

	protected function getNonUsedUpSharedQuestionCollection($definitionId)
	{
		$nonUsedUpSharedQstCollection = new ilTestRandomQuestionSetQuestionCollection();
		
		foreach($this->getSrcPoolDefRelatedQuestionCollection($definitionId) as $question)
		{
			if( !$this->isQuestionUsedByMultipleSrcPoolDefinitions($question) )
			{
				continue;
			}
			
			if( $this->isQuestionUsedUpByAnySrcPoolDefinition($question, $definitionId) )
			{
				continue;
			}
			
			$nonUsedUpSharedQstCollection->addQuestion($question);
		}
		
		return $nonUsedUpSharedQstCollection;
	}
	
	/**
	 * @param $definitionId
	 * @return ilTestRandomQuestionSetQuestionCollection
	 */
	protected function getExclusiveQuestionCollection($definitionId)
	{
		$exclusiveQstCollection = new ilTestRandomQuestionSetQuestionCollection();
		
		foreach($this->getSrcPoolDefRelatedQuestionCollection($definitionId) as $question)
		{
			if( $this->isQuestionUsedByMultipleSrcPoolDefinitions($question) )
			{
				continue;
			}
			
			$exclusiveQstCollection->addQuestion($question);
		}
		
		return $exclusiveQstCollection;
	}
	
	/**
	 * @return ilTestRandomQuestionSetQuestionCollection
	 */
	protected function getOverallQuestionCollection()
	{
		$questCollectionProvider = $this->getQuestionCollectionProvider();
		$srcPoolDefinitionList = $this->getSourcePoolDefinitionList();
		
		return $questCollectionProvider->getSrcPoolDefListRelatedQuestCollection(
			$srcPoolDefinitionList
		);
	}
	
	public function initialiseRegisters()
	{
		foreach($this->getOverallQuestionCollection() as $randomQuestion)
		{
			$this->registerSrcPoolDefRelatedQuest(
				$randomQuestion->getSourcePoolDefinitionId(), $randomQuestion
			);
			
			$this->registerQuestRelatedSrcPoolDef(
				$randomQuestion->getQuestionId(), $randomQuestion->getSourcePoolDefinitionId()
			);
		}
	}
	
	public function isRequiredAmountSatisfiedByExclusiveQstCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		if( 0 < $this->getMissingQuestionCountUsingUpExclusiveQuestionCollection($definition) )
		{
			return false;
		}
		
		return true;
	}
	
	public function isMissingAmountSatisfiedByNonUsedUpSharedQstCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		$requiredAmount = $this->getMissingQuestionCountUsingUpExclusiveQuestionCollection($definition);
		$nonUsedUpSharedQstCollection = $this->getNonUsedUpSharedQuestionCollection();
		
		if( 0 < $nonUsedUpSharedQstCollection->getMissingCount($requiredAmount) )
		{
			return false;
		}
		
		return true;
	}
}