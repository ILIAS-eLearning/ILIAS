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
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilTestRandomQuestionSetPoolDeriver
{
    private ilQuestionPoolFactory $pool_factory;

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly ilComponentRepository $component_repository,
        private readonly ilObjTest $test_obj,
        private readonly ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list,
        private int $owner_id,
        private int $target_container_ref
    ) {
        $this->pool_factory = new ilQuestionPoolFactory();
    }

    public function derive(
        ilTestRandomQuestionSetNonAvailablePool $non_available_pool
    ): ilObjQuestionPool {
        $pool = $this->createNewPool($non_available_pool);
        $questions = $this->getQuestionsForPool($non_available_pool);
        $pool_qid_by_test_qid_map = $this->copyQuestionsToPool($pool, $questions);

        $this->updateTestQuestionStage($pool_qid_by_test_qid_map);
        $this->updateRelatedSourcePoolDefinitions(
            $this->duplicateTaxonomies($pool_qid_by_test_qid_map, $pool),
            $pool->getId(),
            $non_available_pool->getId()
        );

        return $pool;
    }

    private function getQuestionsForPool(
        ilTestRandomQuestionSetNonAvailablePool $non_available_pool
    ): array {
        $question_list = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->db,
            $this->component_repository
        );
        $question_list->setTestObjId($this->test_obj->getId());
        $question_list->setTestId($this->test_obj->getTestId());
        $question_list->setPoolId($non_available_pool->getId());
        $question_list->loadQuestions();

        $questions = [];
        foreach ($question_list->getQuestions() as $question_id) {
            $questions[] = assQuestion::instantiateQuestion($question_id);
        }

        return $questions;
    }

    private function createNewPool(
        ilTestRandomQuestionSetNonAvailablePool $non_available_pool
    ): ilObjQuestionPool {
        $pool = $this->pool_factory->createNewInstance($this->target_container_ref);

        if ($non_available_pool->getTitle() !== '') {
            $pool->setTitle($non_available_pool->getTitle());
            $pool->update();
        }

        return $pool;
    }

    private function copyQuestionsToPool(
        ilObjQuestionPool $pool,
        array $questions
    ): array {
        $pool_qid_by_test_qid_map = [];
        foreach ($questions as $question_obj) {
            $pool_qid_by_test_qid_map[$question_obj->getId()] = $question_obj
                ->duplicate(false, '', '', $this->owner_id, $pool->getId());
        }

        return $pool_qid_by_test_qid_map;
    }

    private function updateTestQuestionStage(
        array $pool_qid_by_test_qid_map
    ): void {
        foreach ($pool_qid_by_test_qid_map as $test_qid => $pool_qid) {
            assQuestion::resetOriginalId($pool_qid);
            assQuestion::saveOriginalId($test_qid, $pool_qid);
        }
    }

    private function filterForQuestionRelatedTaxonomies(
        array $taxonomy_ids,
        array $related_question_ids
    ): array {
        $filtered_tax_ids = [];
        foreach ($taxonomy_ids as $taxonomy_id) {
            $tax_node_assignment = new ilTaxNodeAssignment(
                $this->test_obj->getType(),
                $this->test_obj->getId(),
                'quest',
                $taxonomy_id
            );

            foreach ($related_question_ids as $question_id) {
                if ($tax_node_assignment->getAssignmentsOfItem($question_id) !== []) {
                    $filtered_tax_ids[] = $taxonomy_id;
                    break;
                }
            }
        }

        return $filtered_tax_ids;
    }

    private function duplicateTaxonomies(
        array $pool_qid_by_test_qid_map,
        ilObjQuestionPool $pool
    ): ilQuestionPoolDuplicatedTaxonomiesKeysMap {
        $tax_duplicator = new ilQuestionPoolTaxonomiesDuplicator();
        $tax_duplicator->setSourceObjId($this->test_obj->getId());
        $tax_duplicator->setSourceObjType($this->test_obj->getType());
        $tax_duplicator->setTargetObjId($pool->getId());
        $tax_duplicator->setTargetObjType($pool->getType());
        $tax_duplicator->setQuestionIdMapping($pool_qid_by_test_qid_map);

        $tax_duplicator->duplicate($this->filterForQuestionRelatedTaxonomies(
            $tax_duplicator->getAllTaxonomiesForSourceObject(),
            array_keys($pool_qid_by_test_qid_map)
        ));

        return $tax_duplicator->getDuplicatedTaxonomiesKeysMap();
    }

    private function buildOriginalTaxonomyFilterForDerivedPool(
        ilQuestionPoolDuplicatedTaxonomiesKeysMap $tax_keys_map,
        array $mapped_taxonomy_filter
    ): array {
        $original_taxonomy_filter = [];
        foreach ($mapped_taxonomy_filter as $test_taxonomy_id => $test_tax_nodes) {
            $pool_taxonomy_id = $tax_keys_map->getMappedTaxonomyId($test_taxonomy_id);
            if ($pool_taxonomy_id === null) {
                continue;
            }
            $original_taxonomy_filter[$pool_taxonomy_id] = [];

            foreach ($test_tax_nodes as $test_tax_node) {
                $mapped_tax_node_id = $tax_keys_map->getMappedTaxNodeId((int) $test_tax_node);
                if ($mapped_tax_node_id !== null) {
                    $original_taxonomy_filter[$pool_taxonomy_id][] = $mapped_tax_node_id;
                }
            }
        }

        return $original_taxonomy_filter;
    }

    private function updateRelatedSourcePoolDefinitions(
        ilQuestionPoolDuplicatedTaxonomiesKeysMap $tax_keys_map,
        int $derived_pool_id,
        int $non_available_pool_id
    ): void {
        foreach ($this->source_pool_definition_list as $definition) {
            if ($definition->getPoolId() !== $non_available_pool_id) {
                continue;
            }

            $definition->setPoolId($derived_pool_id);
            $definition->setOriginalTaxonomyFilter(
                $this->buildOriginalTaxonomyFilterForDerivedPool(
                    $tax_keys_map,
                    $definition->getMappedTaxonomyFilter()
                )
            );

            $definition->saveToDb();
        }
    }
}
