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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestRandomQuestionsQuantitiesDistribution
{
    /**
     * @var array[ $question_id => ilTestRandomQuestionSetSourcePoolDefinitionList ]
     */
    protected array $quest_related_src_pool_def_register = [];

    /**
     * @var array[ $definition_id => ilTestRandomSetQuestionCollection ]
     */
    protected array $src_pool_def_related_quest_register = [];

    // -----------------------------------------------------------------------------------------------------------------

    public function __construct(
        private ilDBInterface $db,
        private ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $question_collection_provider,
        private ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list
    ) {
    }

    protected function buildSourcePoolDefinitionListInstance(): ilTestRandomQuestionSetSourcePoolDefinitionList
    {
        $any_test_object = new ilObjTest();
        $non_required_db = $this->db;
        $non_used_factory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($non_required_db, $any_test_object);
        return new ilTestRandomQuestionSetSourcePoolDefinitionList($non_required_db, $any_test_object, $non_used_factory);
    }

    /**
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    protected function buildRandomQuestionCollectionInstance(): ilTestRandomQuestionSetQuestionCollection
    {
        return new ilTestRandomQuestionSetQuestionCollection();
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication
     */
    protected function buildQuestionCollectionSubsetApplicationInstance(): ilTestRandomQuestionCollectionSubsetApplication
    {
        return new ilTestRandomQuestionCollectionSubsetApplication();
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplicationList
     */
    protected function buildQuestionCollectionSubsetApplicationListInstance(): ilTestRandomQuestionCollectionSubsetApplicationList
    {
        return new ilTestRandomQuestionCollectionSubsetApplicationList();
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function resetQuestRelatedSrcPoolDefRegister()
    {
        $this->quest_related_src_pool_def_register = [];
    }

    protected function registerQuestRelatedSrcPoolDef(int $question_id, ilTestRandomQuestionSetSourcePoolDefinition $definition)
    {
        if (!array_key_exists($question_id, $this->quest_related_src_pool_def_register)) {
            $this->quest_related_src_pool_def_register[$question_id] = $this->buildSourcePoolDefinitionListInstance();
        }

        $this->quest_related_src_pool_def_register[$question_id]->addDefinition($definition);
    }

    /**
     * @param $question_id
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected function getQuestRelatedSrcPoolDefinitionList($question_id): ?ilTestRandomQuestionSetSourcePoolDefinitionList
    {
        if (isset($this->quest_related_src_pool_def_register[$question_id])) {
            return $this->quest_related_src_pool_def_register[$question_id];
        }

        return null;
    }

    protected function resetSrcPoolDefRelatedQuestRegister()
    {
        $this->src_pool_def_related_quest_register = [];
    }

    protected function registerSrcPoolDefRelatedQuest(int $definition_id, ilTestRandomQuestionSetQuestion $random_set_question): void
    {
        if (!isset($this->src_pool_def_related_quest_register[$definition_id])) {
            $this->src_pool_def_related_quest_register[$definition_id] = $this->buildRandomQuestionCollectionInstance();
        }

        $this->src_pool_def_related_quest_register[$definition_id]->addQuestion($random_set_question);
    }

    protected function getSrcPoolDefRelatedQuestionCollection(int $definition_id): ilTestRandomQuestionSetQuestionCollection
    {
        if (isset($this->src_pool_def_related_quest_register[$definition_id])) {
            return $this->src_pool_def_related_quest_register[$definition_id];
        }

        return new ilTestRandomQuestionSetQuestionCollection();
    }

    protected function initialiseRegisters(): void
    {
        foreach ($this->getSrcPoolDefQuestionCombinationCollection() as $random_question) {
            $source_pool_definition = $this->source_pool_definition_list->getDefinition(
                $random_question->getSourcePoolDefinitionId()
            );

            $this->registerSrcPoolDefRelatedQuest(
                $random_question->getSourcePoolDefinitionId(),
                $random_question
            );

            if ($source_pool_definition && $random_question->getQuestionId()) {
                $this->registerQuestRelatedSrcPoolDef(
                    $random_question->getQuestionId(),
                    $source_pool_definition
                );
            }
        }
    }

    protected function resetRegisters(): void
    {
        $this->resetQuestRelatedSrcPoolDefRegister();
        $this->resetSrcPoolDefRelatedQuestRegister();
    }

    protected function getSrcPoolDefQuestionCombinationCollection(): ilTestRandomQuestionSetQuestionCollection
    {
        return $this->question_collection_provider->getSrcPoolDefListRelatedQuestCombinationCollection(
            $this->source_pool_definition_list
        );
    }

    protected function getExclusiveQuestionCollection(int $definition_id): ilTestRandomQuestionSetQuestionCollection
    {
        $exclusive_qst_collection = $this->buildRandomQuestionCollectionInstance();

        foreach ($this->getSrcPoolDefRelatedQuestionCollection($definition_id) as $question) {
            if ($this->isQuestionUsedByMultipleSrcPoolDefinitions($question)) {
                continue;
            }

            $exclusive_qst_collection->addQuestion($question);
        }

        return $exclusive_qst_collection;
    }

    protected function getSharedQuestionCollection(int $definition_id): ilTestRandomQuestionSetQuestionCollection
    {
        $src_pool_def_related_qst_collection = $this->getSrcPoolDefRelatedQuestionCollection($definition_id);
        $exclusive_qst_collection = $this->getExclusiveQuestionCollection($definition_id);
        return $src_pool_def_related_qst_collection->getRelativeComplementCollection($exclusive_qst_collection);
    }

    protected function getIntersectionQuestionCollection(
        int $this_definition_id,
        int $that_definition_id
    ): ilTestRandomQuestionSetQuestionCollection {
        $this_def_related_shared_qst_collection = $this->getSharedQuestionCollection($this_definition_id);
        $that_def_related_shared_qst_collection = $this->getSharedQuestionCollection($that_definition_id);

        return $this_def_related_shared_qst_collection->getIntersectionCollection(
            $that_def_related_shared_qst_collection
        );
    }

    /**
     * @return array[ $definition_id => ilTestRandomQuestionSetQuestionCollection ]
     */
    protected function getIntersectionQstCollectionByDefinitionMap(
        ilTestRandomQuestionSetSourcePoolDefinition $definition
    ): array {
        $intersection_qst_collections_by_def_id = [];

        $shared_question_collection = $this->getSharedQuestionCollection($definition->getId());
        foreach ($shared_question_collection as $shared_question) {
            $related_src_pool_def_list = $this->getQuestRelatedSrcPoolDefinitionList($shared_question->getQuestionId());
            foreach ($related_src_pool_def_list as $other_definition) {
                if ($other_definition->getId() == $definition->getId()) {
                    continue;
                }

                if (isset($intersection_qst_collections_by_def_id[$other_definition->getId()])) {
                    continue;
                }

                $intersection_question_collection = $this->getIntersectionQuestionCollection(
                    $definition->getId(),
                    $other_definition->getId()
                );

                $intersection_qst_collections_by_def_id[$other_definition->getId()] = $intersection_question_collection;
            }
        }

        return $intersection_qst_collections_by_def_id;
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionCollectionSubsetApplicationList
     */
    protected function getIntersectionQuestionCollectionSubsetApplicationList(ilTestRandomQuestionSetSourcePoolDefinition $definition): ilTestRandomQuestionCollectionSubsetApplicationList
    {
        $qst_collection_subset_application_list = $this->buildQuestionCollectionSubsetApplicationListInstance();

        $intersection_qst_collection_by_def_id_map = $this->getIntersectionQstCollectionByDefinitionMap($definition);
        foreach ($intersection_qst_collection_by_def_id_map as $other_definition_id => $intersection_collection) {
            /* @var ilTestRandomQuestionSetQuestionCollection $intersection_collection */

            $qst_collection_subset_application = $this->buildQuestionCollectionSubsetApplicationInstance();
            $qst_collection_subset_application->setQuestions($intersection_collection->getQuestions());
            $qst_collection_subset_application->setApplicantId($other_definition_id);

            $qst_collection_subset_application->setRequiredAmount(
                $this->source_pool_definition_list->getDefinition($other_definition_id)->getQuestionAmount()
            );

            $qst_collection_subset_application_list->addCollectionSubsetApplication($qst_collection_subset_application);
        }

        return $qst_collection_subset_application_list;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected function getIntersectionSharingDefinitionList(ilTestRandomQuestionSetSourcePoolDefinition $definition): ilTestRandomQuestionSetSourcePoolDefinitionList
    {
        $intersection_sharing_definition_list = $this->buildSourcePoolDefinitionListInstance();

        $shared_question_collection = $this->getSharedQuestionCollection($definition->getId());
        foreach ($shared_question_collection as $shared_question) {
            $related_src_pool_def_list = $this->getQuestRelatedSrcPoolDefinitionList($shared_question->getQuestionId());
            foreach ($related_src_pool_def_list as $other_definition) {
                if ($other_definition->getId() == $definition->getId()) {
                    continue;
                }

                if ($intersection_sharing_definition_list->hasDefinition($other_definition->getId())) {
                    continue;
                }

                $intersection_sharing_definition_list->addDefinition($other_definition);
            }
        }

        return $intersection_sharing_definition_list;
    }

    /**
     * @param ilTestRandomQuestionSetQuestion $question
     * @return bool
     */
    protected function isQuestionUsedByMultipleSrcPoolDefinitions(ilTestRandomQuestionSetQuestion $question): bool
    {
        /* @var ilTestRandomQuestionSetSourcePoolDefinitionList $qst_related_src_pool_def_list */
        $qst_related_src_pool_def_list = $this->quest_related_src_pool_def_register[$question->getQuestionId()];
        return $qst_related_src_pool_def_list->getDefinitionCount() > 1;
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     */
    protected function getSrcPoolDefRelatedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        return $this->getSrcPoolDefRelatedQuestionCollection($definition->getId())->getQuestionAmount();
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer
     */
    protected function getExclusiveQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        return $this->getExclusiveQuestionCollection($definition->getId())->getQuestionAmount();
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer $available_shared_question_amount
     */
    protected function getAvailableSharedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        $intersection_subset_application_list = $this->getIntersectionQuestionCollectionSubsetApplicationList($definition);

        foreach ($this->getSharedQuestionCollection($definition->getId()) as $shared_question) {
            $intersection_subset_application_list->handleQuestionRequest($shared_question);
        }

        return $intersection_subset_application_list->getNonReservedQuestionAmount();
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return integer
     */
    protected function getRequiredSharedQuestionAmount(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        $exclusive_qst_collection = $this->getExclusiveQuestionCollection($definition->getId());
        $missing_exclsuive_qst_count = $exclusive_qst_collection->getMissingCount($definition->getQuestionAmount());
        return $missing_exclsuive_qst_count;
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return bool
     */
    protected function requiresSharedQuestions(ilTestRandomQuestionSetSourcePoolDefinition $definition): bool
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
    public function calculateQuantities(ilTestRandomQuestionSetSourcePoolDefinition $definition): ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation
    {
        $quantity_calculation = new ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation($definition);

        $quantity_calculation->setOverallQuestionAmount($this->getSrcPoolDefRelatedQuestionAmount($definition));
        $quantity_calculation->setExclusiveQuestionAmount($this->getExclusiveQuestionAmount($definition));
        $quantity_calculation->setAvailableSharedQuestionAmount($this->getAvailableSharedQuestionAmount($definition));

        $quantity_calculation->setIntersectionQuantitySharingDefinitionList(
            $this->getIntersectionSharingDefinitionList($definition)
        );

        return $quantity_calculation;
    }

    // -----------------------------------------------------------------------------------------------------------------
}
