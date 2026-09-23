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

declare(strict_types=1);

use ILIAS\Test\Logging\TestLogger;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestRandomQuestionSetBuilder implements ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
{
    protected $check_messages = [];

    protected function __construct(
        protected ilDBInterface $db,
        protected ilLanguage $lng,
        protected TestLogger $logger,
        protected ilObjTest $test_obj,
        protected ilTestRandomQuestionSetConfig $question_set_config,
        protected ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list,
        protected ilTestRandomQuestionSetStagingPoolQuestionList $staging_pool_question_list
    ) {
        $this->staging_pool_question_list->setTestObjId($this->test_obj->getId());
        $this->staging_pool_question_list->setTestId($this->test_obj->getTestId());
    }

    abstract public function checkBuildable();

    abstract public function performBuild(ilTestSession $test_session);

    public function getSrcPoolDefListRelatedQuestCombinationCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list): ilTestRandomQuestionSetQuestionCollection
    {
        $question_stage = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($source_pool_definition_list as $definition) {
            $questions = $this->getSrcPoolDefRelatedQuestCollection($definition);
            $question_stage->mergeQuestionCollection($questions);
        }

        return $question_stage;
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefRelatedQuestCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition): ilTestRandomQuestionSetQuestionCollection
    {
        $question_ids = $this->getQuestionIdsForSourcePoolDefinitionIds($definition);
        $question_stage = $this->buildSetQuestionCollection($definition, $question_ids);

        return $question_stage;
    }

    // hey: fixRandomTestBuildable - rename/public-access to be aware for building interface
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefListRelatedQuestUniqueCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list): ilTestRandomQuestionSetQuestionCollection
    {
        $combination_collection = $this->getSrcPoolDefListRelatedQuestCombinationCollection($source_pool_definition_list);
        return $combination_collection->getUniqueQuestionCollection();
    }
    // hey.

    private function getQuestionIdsForSourcePoolDefinitionIds(ilTestRandomQuestionSetSourcePoolDefinition $definition): array
    {
        $this->staging_pool_question_list->resetQuestionList();

        $this->staging_pool_question_list->setPoolId($definition->getPoolId());

        if ($this->hasTaxonomyFilter($definition)) {
            foreach ($definition->getMappedTaxonomyFilter() as $tax_id => $node_ids) {
                $tax_id = (int) $tax_id;
                if ($tax_id < 1) {
                    continue;
                }
                $this->staging_pool_question_list->addTaxonomyFilter($tax_id, $node_ids);
            }
        }

        if (count($definition->getLifecycleFilter())) {
            $this->staging_pool_question_list->setLifecycleFilter($definition->getLifecycleFilter());
        }

        // fau: taxFilter/typeFilter - use type filter
        if ($this->hasTypeFilter($definition)) {
            $this->staging_pool_question_list->setTypeFilter($definition->getTypeFilter());
        }
        // fau.

        $this->staging_pool_question_list->loadQuestions();

        return $this->staging_pool_question_list->getQuestions();
    }

    private function buildSetQuestionCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition, $question_ids): ilTestRandomQuestionSetQuestionCollection
    {
        $set_question_collection = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($question_ids as $question_id) {
            $set_question = new ilTestRandomQuestionSetQuestion();

            $set_question->setQuestionId($question_id);
            $set_question->setSourcePoolDefinitionId($definition->getId());

            $set_question_collection->addQuestion($set_question);
        }

        return $set_question_collection;
    }

    private function hasTaxonomyFilter(ilTestRandomQuestionSetSourcePoolDefinition $definition): bool
    {
        if (!count($definition->getMappedTaxonomyFilter())) {
            return false;
        }
        return true;
    }

    //	fau: typeFilter - check for existing type filter
    private function hasTypeFilter(ilTestRandomQuestionSetSourcePoolDefinition $definition): bool
    {
        if (count($definition->getTypeFilter())) {
            return true;
        }

        return false;
    }
    //	fau.

    protected function storeQuestionSet(ilTestSession $test_session, $question_set)
    {
        $position = 0;

        foreach ($question_set->getQuestions() as $set_question) {
            /* @var ilTestRandomQuestionSetQuestion $set_question */

            $set_question->setSequencePosition($position++);

            $this->storeQuestion($test_session, $set_question);
        }
    }

    private function storeQuestion(ilTestSession $test_session, ilTestRandomQuestionSetQuestion $set_question)
    {
        $next_id = $this->db->nextId('tst_test_rnd_qst');

        $this->db->insert('tst_test_rnd_qst', [
            'test_random_question_id' => ['integer', $next_id],
            'active_fi' => ['integer', $test_session->getActiveId()],
            'question_fi' => ['integer', $set_question->getQuestionId()],
            'sequence' => ['integer', $set_question->getSequencePosition()],
            'pass' => ['integer', $test_session->getPass()],
            'tstamp' => ['integer', time()],
            'src_pool_def_fi' => ['integer', $set_question->getSourcePoolDefinitionId()]
        ]);
    }

    protected function fetchQuestionsFromStageRandomly(ilTestRandomQuestionSetQuestionCollection $question_stage, $required_question_amount): ilTestRandomQuestionSetQuestionCollection
    {
        $question_set = $question_stage->getRandomQuestionCollection($required_question_amount);

        return $question_set;
    }

    protected function handleQuestionOrdering(ilTestRandomQuestionSetQuestionCollection $question_set)
    {
        if ($this->test_obj->getShuffleQuestions()) {
            $question_set->shuffleQuestions();
        }
    }

    // =================================================================================================================

    final public static function getInstance(
        ilDBInterface $db,
        ilLanguage $lng,
        TestLogger $logger,
        ilObjTest $test_obj,
        ilTestRandomQuestionSetConfig $question_set_config,
        ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list,
        ilTestRandomQuestionSetStagingPoolQuestionList $staging_pool_question_list
    ) {
        if ($question_set_config->isQuestionAmountConfigurationModePerPool()) {
            return new ilTestRandomQuestionSetBuilderWithAmountPerPool(
                $db,
                $lng,
                $logger,
                $test_obj,
                $question_set_config,
                $source_pool_definition_list,
                $staging_pool_question_list
            );
        }

        return new ilTestRandomQuestionSetBuilderWithAmountPerTest(
            $db,
            $lng,
            $logger,
            $test_obj,
            $question_set_config,
            $source_pool_definition_list,
            $staging_pool_question_list
        );
    }

    //fau: fixRandomTestBuildable - function to get messages
    /**
     * @return array
     */
    public function getCheckMessages(): array
    {
        return $this->check_messages;
    }
    // fau.
}
