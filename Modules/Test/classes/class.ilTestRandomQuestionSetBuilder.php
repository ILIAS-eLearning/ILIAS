<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestionCollection.php';
require_once 'Modules/Test/interfaces/interface.ilTestRandomSourcePoolDefinitionQuestionCollectionProvider.php';
/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestRandomQuestionSetBuilder implements ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
{
    /**
     * @var ilDBInterface
     */
    protected $db = null;

    /**
     * @var ilObjTest
     */
    protected $testOBJ = null;

    /**
     * @var ilTestRandomQuestionSetConfig
     */
    protected $questionSetConfig = null;

    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $sourcePoolDefinitionList = null;

    /**
     * @var ilTestRandomQuestionSetStagingPoolQuestionList
     */
    protected $stagingPoolQuestionList = null;

    //fau: fixRandomTestBuildable - variable for messages
    protected $checkMessages = array();
    // fau.

    /**
     * @param ilDBInterface $db
     * @param ilObjTest $testOBJ
     * @param ilTestRandomQuestionSetConfig $questionSetConfig
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @param ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
     */
    protected function __construct(
        ilDBInterface $db,
        ilObjTest $testOBJ,
        ilTestRandomQuestionSetConfig $questionSetConfig,
        ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
        ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
    ) {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
        $this->questionSetConfig = $questionSetConfig;
        $this->sourcePoolDefinitionList = $sourcePoolDefinitionList;
        $this->stagingPoolQuestionList = $stagingPoolQuestionList;
    }

    abstract public function checkBuildable();

    abstract public function performBuild(ilTestSession $testSession);


    // hey: fixRandomTestBuildable - rename/public-access to be aware for building interface
    public function getSrcPoolDefListRelatedQuestCombinationCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
    // hey.
    {
        $questionStage = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($sourcePoolDefinitionList as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            
            // hey: fixRandomTestBuildable - rename/public-access to be aware for building interface
            $questions = $this->getSrcPoolDefRelatedQuestCollection($definition);
            // hey.
            $questionStage->mergeQuestionCollection($questions);
        }

        return $questionStage;
    }
    
    // hey: fixRandomTestBuildable - rename/public-access to be aware for building interface
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefRelatedQuestCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    // hey.
    {
        $questionIds = $this->getQuestionIdsForSourcePoolDefinitionIds($definition);
        $questionStage = $this->buildSetQuestionCollection($definition, $questionIds);

        return $questionStage;
    }
    
    // hey: fixRandomTestBuildable - rename/public-access to be aware for building interface
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefListRelatedQuestUniqueCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
    {
        $combinationCollection = $this->getSrcPoolDefListRelatedQuestCombinationCollection($sourcePoolDefinitionList);
        return $combinationCollection->getUniqueQuestionCollection();
    }
    // hey.

    private function getQuestionIdsForSourcePoolDefinitionIds(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        $this->stagingPoolQuestionList->resetQuestionList();

        $this->stagingPoolQuestionList->setTestObjId($this->testOBJ->getId());
        $this->stagingPoolQuestionList->setTestId($this->testOBJ->getTestId());
        $this->stagingPoolQuestionList->setPoolId($definition->getPoolId());

        if ($this->hasTaxonomyFilter($definition)) {
            // fau: taxFilter/typeFilter - use new taxonomy filter
            foreach ($definition->getMappedTaxonomyFilter() as $taxId => $nodeIds) {
                $this->stagingPoolQuestionList->addTaxonomyFilter($taxId, $nodeIds);
            }
            #$this->stagingPoolQuestionList->addTaxonomyFilter(
            #	$definition->getMappedFilterTaxId(), array($definition->getMappedFilterTaxNodeId())
            #);
            // fau.
        }
        
        // fau: taxFilter/typeFilter - use type filter
        if ($this->hasTypeFilter($definition)) {
            $this->stagingPoolQuestionList->setTypeFilter($definition->getTypeFilter());
        }
        // fau.

        $this->stagingPoolQuestionList->loadQuestions();

        return $this->stagingPoolQuestionList->getQuestions();
    }

    private function buildSetQuestionCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition, $questionIds)
    {
        $setQuestionCollection = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($questionIds as $questionId) {
            $setQuestion = new ilTestRandomQuestionSetQuestion();

            $setQuestion->setQuestionId($questionId);
            $setQuestion->setSourcePoolDefinitionId($definition->getId());

            $setQuestionCollection->addQuestion($setQuestion);
        }

        return $setQuestionCollection;
    }

    private function hasTaxonomyFilter(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        // fau: taxFilter - check for existing taxonomy filter
        if (!count($definition->getMappedTaxonomyFilter())) {
            return false;
        }
        #if( !(int)$definition->getMappedFilterTaxId() )
        #{
        #	return false;
        #}
        #
        #if( !(int)$definition->getMappedFilterTaxNodeId() )
        #{
        #	return false;
        #}
        // fau.
        return true;
    }
    
    //	fau: typeFilter - check for existing type filter
    private function hasTypeFilter(ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        if (count($definition->getTypeFilter())) {
            return true;
        }
        
        return false;
    }
    //	fau.

    protected function storeQuestionSet(ilTestSession $testSession, $questionSet)
    {
        $position = 0;

        foreach ($questionSet->getQuestions() as $setQuestion) {
            /* @var ilTestRandomQuestionSetQuestion $setQuestion */

            $setQuestion->setSequencePosition($position++);

            $this->storeQuestion($testSession, $setQuestion);
        }
    }

    private function storeQuestion(ilTestSession $testSession, ilTestRandomQuestionSetQuestion $setQuestion)
    {
        $nextId = $this->db->nextId('tst_test_rnd_qst');

        $this->db->insert('tst_test_rnd_qst', array(
            'test_random_question_id' => array('integer', $nextId),
            'active_fi' => array('integer', $testSession->getActiveId()),
            'question_fi' => array('integer', $setQuestion->getQuestionId()),
            'sequence' => array('integer', $setQuestion->getSequencePosition()),
            'pass' => array('integer', $testSession->getPass()),
            'tstamp' => array('integer', time()),
            'src_pool_def_fi' => array('integer', $setQuestion->getSourcePoolDefinitionId())
        ));
    }

    protected function fetchQuestionsFromStageRandomly(ilTestRandomQuestionSetQuestionCollection $questionStage, $requiredQuestionAmount)
    {
        $questionSet = $questionStage->getRandomQuestionCollection($requiredQuestionAmount);

        return $questionSet;
    }

    protected function handleQuestionOrdering(ilTestRandomQuestionSetQuestionCollection $questionSet)
    {
        if ($this->testOBJ->getShuffleQuestions()) {
            $questionSet->shuffleQuestions();
        }
    }

    // =================================================================================================================

    final public static function getInstance(
        ilDBInterface $db,
        ilObjTest $testOBJ,
        ilTestRandomQuestionSetConfig $questionSetConfig,
        ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
        ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
    ) {
        if ($questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerPool.php';

            return new ilTestRandomQuestionSetBuilderWithAmountPerPool(
                $db,
                $testOBJ,
                $questionSetConfig,
                $sourcePoolDefinitionList,
                $stagingPoolQuestionList
            );
        }

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerTest.php';

        return new ilTestRandomQuestionSetBuilderWithAmountPerTest(
            $db,
            $testOBJ,
            $questionSetConfig,
            $sourcePoolDefinitionList,
            $stagingPoolQuestionList
        );
    }
    
    //fau: fixRandomTestBuildable - function to get messages
    /**
     * @return array
     */
    public function getCheckMessages()
    {
        return $this->checkMessages;
    }
    // fau.
}
