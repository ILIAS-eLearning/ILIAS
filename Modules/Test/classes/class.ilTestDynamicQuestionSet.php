<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';

/**
 * Class manages access to the dynamic question set
 * provided for the current test
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/Test
 */
class ilTestDynamicQuestionSet
{
	/**
	 * @var ilDB
	 */
	private $db = null;
	
	/**
	 * @var ilLanguage
	 */
	private $lng = null;
	
	/**
	 * @var ilPluginAdmin
	 */
	private $pluginAdmin = null;
	
	/**
	 * @var ilObjTest
	 */
	private $testOBJ = null;
	
	/**
	 * @var ilAssQuestionList
	 */
	private $completeQuestionList = null;
	
	/**
	 * @var ilAssQuestionList
	 */
	private $filteredQuestionList = null;
	
	/**
	 * @var array 
	 */
	private $actualQuestionSequence = array();
	
	/**
	 * Constructor
	 * 
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilDB $db, ilLanguage $lng, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->lng = $lng;
		$this->pluginAdmin = $pluginAdmin;
		$this->testOBJ = $testOBJ;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function load(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, $taxonomyFilterSelection)
	{
		$this->completeQuestionList = $this->initCompleteQuestionList(
					$dynamicQuestionSetConfig
		);
		
		$this->filteredQuestionList = $this->initFilteredQuestionList(
					$dynamicQuestionSetConfig, $taxonomyFilterSelection
		);
		
		$this->actualQuestionSequence = $this->initActualQuestionSequence(
					$dynamicQuestionSetConfig, $this->filteredQuestionList
		);
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function initCompleteQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig)
	{
		$questionList = new ilAssQuestionList(
				$this->db, $this->lng, $this->pluginAdmin, $dynamicQuestionSetConfig->getSourceQuestionPoolId()
		);
		
		$questionList->load();
		
		return $questionList;
	}
	
	private function initFilteredQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, $taxonomyFilterSelection)
	{
		$questionList = new ilAssQuestionList(
				$this->db, $this->lng, $this->pluginAdmin, $dynamicQuestionSetConfig->getSourceQuestionPoolId()
		);

		if( $dynamicQuestionSetConfig->isTaxonomyFilterEnabled() )
		{
			require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
			
			$questionList->setAvailableTaxonomyIds( ilObjTaxonomy::getUsageOfObject(
					$dynamicQuestionSetConfig->getSourceQuestionPoolId()
			));
			
			foreach($taxonomyFilterSelection as $taxId => $taxNodes)
			{
				$questionList->addTaxonomyFilter($taxId, $taxNodes);
			}
		}
		elseif( $dynamicQuestionSetConfig->getOrderingTaxonomyId() )
		{
			$questionList->setAvailableTaxonomyIds( array(
				$dynamicQuestionSetConfig->getOrderingTaxonomyId()
			));
		}
		
		$questionList->load();
		
		return $questionList;
	}
	
	private function initActualQuestionSequence(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilAssQuestionList $questionList)
	{
		if( $dynamicQuestionSetConfig->getOrderingTaxonomyId() )
		{
			return $this->getQuestionSequenceStructuredByTaxonomy(
					$questionList, $dynamicQuestionSetConfig->getOrderingTaxonomyId()
			);
		}
		
		return $this->getQuestionSequenceStructuredByUpdateDate($questionList);
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	private function getQuestionSequenceStructuredByTaxonomy(ilAssQuestionList $questionList, $orderingTaxId)
	{
		$questionsByNode = array();
		$nodelessQuestions = array();
		
		foreach($questionList->getQuestionDataArray() as $qId => $qData)
		{
			if( isset($qData['taxonomies'][$orderingTaxId]) && count($qData['taxonomies'][$orderingTaxId]) )
			{
				foreach($qData['taxonomies'][$orderingTaxId] as $nodeId => $itemData)
				{
					$questionsByNode[ $itemData['node_lft'] ][ $itemData['order_nr'] ] = $qId;
					break;
				}
			}
			else
			{
				$nodelessQuestions[$qData['tstamp'].'::'.$qId] = $qId;
			}
		}
		
		foreach($questionsByNode as $nodeLft => $questions)
		{
			ksort($questions, SORT_NUMERIC);
			$questionsByNode[$nodeLft] = array_values($questions);
		}

		ksort($questionsByNode, SORT_NUMERIC);
		$sequence = array_values($questionsByNode);
		
		ksort($nodelessQuestions);
		$sequence[] = array_values($nodelessQuestions);
		
		return $sequence;
	}
	
	private function getQuestionSequenceStructuredByUpdateDate(ilAssQuestionList $questionList)
	{
		$sequence = array();
		
		foreach($questionList->getQuestionDataArray() as $qId => $qData)
		{
			$sequence[ $qData['tstamp'].'::'.$qId ] = $qId;
		}
		
		ksort($sequence);
		$sequence = array_values($sequence);
		
		return array($sequence);
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function getActualQuestionSequence()
	{
		return $this->actualQuestionSequence;
	}
	
	public function questionExists($questionId)
	{
		$questionData = $this->completeQuestionList->getQuestionDataArray();
		return isset($questionData[$questionId]);
	}
	
	public function getQuestionData($questionId)
	{
		$questionData = $this->completeQuestionList->getQuestionDataArray();
		return $questionData[$questionId];
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function getFilteredQuestionsData()
	{
		return $this->filteredQuestionList->getQuestionDataArray();
	}
}

