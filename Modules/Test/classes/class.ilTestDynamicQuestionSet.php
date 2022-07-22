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
 * Class manages access to the dynamic question set
 * provided for the current test
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
class ilTestDynamicQuestionSet
{
    private ilDBInterface $db;
    private ilLanguage $lng;
    private ilComponentRepository $component_repository;
    private ilObjTest $testOBJ;
    private ?ilAssQuestionList $completeQuestionList = null;
    private ?ilAssQuestionList $selectionQuestionList = null;
    private ?ilAssQuestionList $filteredQuestionList = null;
    private array $actualQuestionSequence = [];

    public function __construct(ilDBInterface $db, ilLanguage $lng, ilComponentRepository $component_repository, ilObjTest $testOBJ)
    {
        $this->db = $db;
        $this->lng = $lng;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function load(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection) : void
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

    private function initCompleteQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, $answerStatusActiveId) : ilAssQuestionList
    {
        $questionList = $this->buildQuestionList(
            $dynamicQuestionSetConfig->getSourceQuestionPoolId(),
            $answerStatusActiveId
        );
        
        $questionList->load();
        
        return $questionList;
    }
    
    private function initFilteredQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection) : ilAssQuestionList
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
    public function initSelectionQuestionList(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilTestDynamicQuestionSetFilterSelection $filterSelection) : ilAssQuestionList
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
    
    private function initActualQuestionSequence(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, ilAssQuestionList $questionList) : array
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
    
    private function getQuestionSequenceStructuredByTaxonomy(ilAssQuestionList $questionList, $orderingTaxId) : array
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
    
    private function getQuestionSequenceStructuredByUpdateDate(ilAssQuestionList $questionList) : array
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
    
    public function getActualQuestionSequence() : array
    {
        return $this->actualQuestionSequence;
    }
    
    public function questionExists($questionId) : bool
    {
        $questionData = $this->completeQuestionList->getQuestionDataArray();
        return isset($questionData[$questionId]);
    }
    
    public function getQuestionData($questionId)
    {
        $questionData = $this->completeQuestionList->getQuestionDataArray();
        return $questionData[$questionId];
    }
    
    public function getAllQuestionsData() : array
    {
        return $this->completeQuestionList->getQuestionDataArray();
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return ilAssQuestionList
     */
    public function getCompleteQuestionList() : ?ilAssQuestionList
    {
        return $this->completeQuestionList;
    }
    
    /**
     * @return ilAssQuestionList
     */
    public function getSelectionQuestionList() : ?ilAssQuestionList
    {
        return $this->selectionQuestionList;
    }
    
    /**
     * @return ilAssQuestionList
     */
    public function getFilteredQuestionList() : ?ilAssQuestionList
    {
        return $this->filteredQuestionList;
    }
    
    /**
     * @param integer $sourceQuestionPoolId
     * @param string $answerStatusActiveId
     * @return ilAssQuestionList
     */
    private function buildQuestionList($sourceQuestionPoolId, $answerStatusActiveId) : ilAssQuestionList
    {
        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
        $questionList->setParentObjId($sourceQuestionPoolId);
        $questionList->setAnswerStatusActiveId($answerStatusActiveId);
        return $questionList;
    }
}
