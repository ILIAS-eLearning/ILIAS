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
     * @var ilDBInterface
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
    private $selectionQuestionList = null;
    
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
    public function __construct(ilDBInterface $db, ilLanguage $lng, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->db = $db;
        $this->lng = $lng;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function load(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection)
    {
        $this->completeQuestionList = $this->initCompleteQuestionList(
            $dynamicQuestionSetConfig,
            $filterSelection->getAnswerStatusActiveId()
        );
        
        $this->selectionQuestionList = $this->initSelectionQuestionList(
            $dynamicQuestionSetConfig,
            $filterSelection
        );
        
        $this->filteredQuestionList = $this->initFilteredQuestionList(
            $dynamicQuestionSetConfig,
            $filterSelection
        );
        
        $this->actualQuestionSequence = $this->initActualQuestionSequence(
            $dynamicQuestionSetConfig,
            $this->filteredQuestionList
        );
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function initCompleteQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, $answerStatusActiveId)
    {
        $questionList = $this->buildQuestionList(
            $dynamicQuestionSetConfig->getSourceQuestionPoolId(),
            $answerStatusActiveId
        );
        
        $questionList->load();
        
        return $questionList;
    }
    
    private function initFilteredQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection)
    {
        $questionList = $this->buildQuestionList(
            $dynamicQuestionSetConfig->getSourceQuestionPoolId(),
            $filterSelection->getAnswerStatusActiveId()
        );

        if ($dynamicQuestionSetConfig->isAnswerStatusFilterEnabled()) {
            $questionList->setAnswerStatusFilter($filterSelection->getAnswerStatusSelection());
        }

        if ($dynamicQuestionSetConfig->isTaxonomyFilterEnabled()) {
            require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
            
            $questionList->setAvailableTaxonomyIds(ilObjTaxonomy::getUsageOfObject(
                $dynamicQuestionSetConfig->getSourceQuestionPoolId()
            ));
            
            foreach ($filterSelection->getTaxonomySelection() as $taxId => $taxNodes) {
                $questionList->addTaxonomyFilter(
                    $taxId,
                    $taxNodes,
                    $this->testOBJ->getId(),
                    $this->testOBJ->getType()
                );
            }
        } elseif ($dynamicQuestionSetConfig->getOrderingTaxonomyId()) {
            $questionList->setAvailableTaxonomyIds(array(
                $dynamicQuestionSetConfig->getOrderingTaxonomyId()
            ));
        }
        
        $questionList->setForcedQuestionIds($filterSelection->getForcedQuestionIds());
        
        $questionList->load();
        
        return $questionList;
    }
    
    /**
     * @param ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig
     * @param ilTestDynamicQuestionSetFilterSelection $filterSelection
     * @return ilAssQuestionList
     */
    public function initSelectionQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection)
    {
        $questionList = $this->buildQuestionList(
            $dynamicQuestionSetConfig->getSourceQuestionPoolId(),
            $filterSelection->getAnswerStatusActiveId()
        );
        
        if ($dynamicQuestionSetConfig->isTaxonomyFilterEnabled()) {
            require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
            
            $questionList->setAvailableTaxonomyIds(ilObjTaxonomy::getUsageOfObject(
                $dynamicQuestionSetConfig->getSourceQuestionPoolId()
            ));
            
            foreach ($filterSelection->getTaxonomySelection() as $taxId => $taxNodes) {
                $questionList->addTaxonomyFilter(
                    $taxId,
                    $taxNodes,
                    $this->testOBJ->getId(),
                    $this->testOBJ->getType()
                );
            }
        }
        
        $questionList->load();
        
        return $questionList;
    }
    
    private function initActualQuestionSequence(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilAssQuestionList $questionList)
    {
        if ($dynamicQuestionSetConfig->getOrderingTaxonomyId()) {
            return $this->getQuestionSequenceStructuredByTaxonomy(
                $questionList,
                $dynamicQuestionSetConfig->getOrderingTaxonomyId()
            );
        }
        
        return $this->getQuestionSequenceStructuredByUpdateDate($questionList);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    private function getQuestionSequenceStructuredByTaxonomy(ilAssQuestionList $questionList, $orderingTaxId)
    {
        require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
        $tax = new ilObjTaxonomy($orderingTaxId);
        
        require_once 'Modules/Test/classes/class.ilTestTaxonomyTree.php';
        $tree = new ilTestTaxonomyTree($orderingTaxId);
        $tree->initOrderedTreeIndex($tax);
        
        $questionsByNode = array();
        $nodelessQuestions = array();
        
        foreach ($questionList->getQuestionDataArray() as $qId => $qData) {
            if (isset($qData['taxonomies'][$orderingTaxId]) && count($qData['taxonomies'][$orderingTaxId])) {
                foreach ($qData['taxonomies'][$orderingTaxId] as $nodeId => $itemData) {
                    $nodeOrderingPath = $tree->getNodeOrderingPathString($itemData['node_id']);
                    
                    if (!isset($questionsByNode[ $nodeOrderingPath ])) {
                        $questionsByNode[ $nodeOrderingPath ] = array();
                    }
                    
                    if ($tax->getItemSorting() == ilObjTaxonomy::SORT_MANUAL) {
                        $questionsByNode[ $nodeOrderingPath ][$itemData['order_nr']] = $qId;
                    } else {
                        $questionsByNode[ $nodeOrderingPath ][$qData['title'] . '::' . $qId] = $qId;
                    }
                }
            } else {
                $nodelessQuestions[$qData['tstamp'] . '::' . $qId] = $qId;
            }
        }
        
        foreach ($questionsByNode as $path => $questions) {
            if ($tax->getItemSorting() == ilObjTaxonomy::SORT_MANUAL) {
                ksort($questions, SORT_NUMERIC);
            } else {
                ksort($questions, SORT_STRING);
            }
            
            $questionsByNode[$path] = array_values($questions);
        }

        ksort($questionsByNode, SORT_STRING);
        $sequence = array_values($questionsByNode);
        
        ksort($nodelessQuestions);
        $sequence[] = array_values($nodelessQuestions);
        
        return $sequence;
    }
    
    private function getQuestionSequenceStructuredByUpdateDate(ilAssQuestionList $questionList)
    {
        $sequence = array();
        
        foreach ($questionList->getQuestionDataArray() as $qId => $qData) {
            $sequence[ $qData['tstamp'] . '::' . $qId ] = $qId;
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
    
    public function getAllQuestionsData()
    {
        return $this->completeQuestionList->getQuestionDataArray();
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return ilAssQuestionList
     */
    public function getCompleteQuestionList()
    {
        return $this->completeQuestionList;
    }
    
    /**
     * @return ilAssQuestionList
     */
    public function getSelectionQuestionList()
    {
        return $this->selectionQuestionList;
    }
    
    /**
     * @return ilAssQuestionList
     */
    public function getFilteredQuestionList()
    {
        return $this->filteredQuestionList;
    }
    
    /**
     * @param integer $sourceQuestionPoolId
     * @param string $answerStatusActiveId
     * @return ilAssQuestionList
     */
    private function buildQuestionList($sourceQuestionPoolId, $answerStatusActiveId)
    {
        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->pluginAdmin);
        $questionList->setParentObjId($sourceQuestionPoolId);
        $questionList->setAnswerStatusActiveId($answerStatusActiveId);
        return $questionList;
    }
}
