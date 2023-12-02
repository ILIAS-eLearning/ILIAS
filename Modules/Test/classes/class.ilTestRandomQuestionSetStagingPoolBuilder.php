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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetStagingPoolBuilder
{
    public function __construct(
        private ilDBInterface $db,
        private ilLogger $log,
        private ilObjTest $test_obj
    ) {
    }

    // =================================================================================================================

    public function rebuild(ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list): void
    {
        $this->reset();
        $this->buildCheap($source_pool_definition_list);
    }

    public function reset()
    {
        $this->removeMirroredTaxonomies();
        $this->removeStagedQuestions();
    }

    private function removeMirroredTaxonomies()
    {
        $taxonomyIds = ilObjTaxonomy::getUsageOfObject($this->test_obj->getId());

        foreach ($taxonomyIds as $taxId) {
            $taxonomy = new ilObjTaxonomy($taxId);
            $taxonomy->delete();
        }
    }

    private function removeStagedQuestions()
    {
        $query = 'SELECT * FROM tst_rnd_cpy WHERE tst_fi = %s';
        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->test_obj->getTestId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            try {
                $question = assQuestion::instantiateQuestion($row['qst_fi']);
            } catch (InvalidArgumentException $ex) {
                $this->log->warning(
                    "could not delete staged random question (ref={$this->test_obj->getRefId()} / qst={$row['qst_fi']})"
                );
                return;
            }
            $question->delete($row['qst_fi']);
        }

        $query = "DELETE FROM tst_rnd_cpy WHERE tst_fi = %s";
        $this->db->manipulateF($query, array('integer'), array($this->test_obj->getTestId()));
    }

    private function build(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
    {
        $involvedSourcePoolIds = $sourcePoolDefinitionList->getInvolvedSourcePoolIds();

        foreach ($involvedSourcePoolIds as $sourcePoolId) {
            $questionIdMapping = $this->stageQuestionsFromSourcePool($sourcePoolId);

            $taxonomiesKeysMap = $this->mirrorSourcePoolTaxonomies($sourcePoolId, $questionIdMapping);

            $this->applyMappedTaxonomiesKeys($sourcePoolDefinitionList, $taxonomiesKeysMap, $sourcePoolId);
        }
    }

    private function stageQuestionsFromSourcePool($sourcePoolId): array
    {
        $questionIdMapping = array();

        $query = 'SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND complete = %s AND original_id IS NULL';
        $res = $this->db->queryF($query, array('integer', 'text'), array($sourcePoolId, 1));

        while ($row = $this->db->fetchAssoc($res)) {
            $question = assQuestion::instantiateQuestion($row['question_id']);
            $duplicateId = $question->duplicate(true, '', '', -1, $this->test_obj->getId());

            $nextId = $this->db->nextId('tst_rnd_cpy');
            $this->db->insert('tst_rnd_cpy', array(
                'copy_id' => array('integer', $nextId),
                'tst_fi' => array('integer', $this->test_obj->getTestId()),
                'qst_fi' => array('integer', $duplicateId),
                'qpl_fi' => array('integer', $sourcePoolId)
            ));

            $questionIdMapping[ $row['question_id'] ] = $duplicateId;
        }

        return $questionIdMapping;
    }

    // fau: taxFilter/typeFilter - select only the needed questions, and copy every question only once
    private function buildCheap(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
    {
        // TODO-RND2017: refactor using assQuestionList and wrap with assQuestionListCollection for unioning

        $questionIdMappingPerPool = [];

        // select questions to be copied by the definitions
        // note: a question pool may appear many times in this list

        /* @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
        foreach ($sourcePoolDefinitionList as $definition) {
            $taxFilter = $definition->getOriginalTaxonomyFilter();
            $typeFilter = $definition->getTypeFilter();
            $lifecycleFilter = $definition->getLifecycleFilter();

            if (!empty($taxFilter)) {
                $filterItems = null;
                foreach ($taxFilter as $taxId => $nodeIds) {
                    $taxItems = array();
                    foreach ($nodeIds as $nodeId) {
                        $nodeItems = ilObjTaxonomy::getSubTreeItems(
                            'qpl',
                            $definition->getPoolId(),
                            'quest',
                            $taxId,
                            $nodeId
                        );

                        foreach ($nodeItems as $nodeItem) {
                            $taxItems[] = $nodeItem['item_id'];
                        }
                    }

                    $filterItems = isset($filterItems) ? array_intersect($filterItems, array_unique($taxItems)) : array_unique($taxItems);
                }

                // stage only the questions applying to the tax/type filter
                // and save the duplication map for later use

                $questionIdMappingPerPool = $this->stageQuestionsFromSourcePoolCheap(
                    $definition->getPoolId(),
                    $questionIdMappingPerPool,
                    array_values($filterItems),
                    $typeFilter,
                    $lifecycleFilter
                );
            } else {
                // stage only the questions applying to the tax/type filter
                // and save the duplication map for later use

                $questionIdMappingPerPool = $this->stageQuestionsFromSourcePoolCheap(
                    $definition->getPoolId(),
                    $questionIdMappingPerPool,
                    null,
                    $typeFilter,
                    $lifecycleFilter
                );
            }
        }

        // copy the taxonomies to the test and map them
        foreach ($questionIdMappingPerPool as $sourcePoolId => $questionIdMapping) {
            $taxonomiesKeysMap = $this->mirrorSourcePoolTaxonomies($sourcePoolId, $questionIdMapping);
            $this->applyMappedTaxonomiesKeys($sourcePoolDefinitionList, $taxonomiesKeysMap, $sourcePoolId);
        }
    }

    private function stageQuestionsFromSourcePoolCheap($sourcePoolId, $questionIdMappingPerPool, $filterIds = null, $typeFilter = null, $lifecycleFilter = null)
    {
        $query = 'SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND complete = %s AND original_id IS NULL';
        if (!empty($filterIds)) {
            $query .= ' AND ' . $this->db->in('question_id', $filterIds, false, 'integer');
        }
        if (!empty($typeFilter)) {
            $query .= ' AND ' . $this->db->in('question_type_fi', $typeFilter, false, 'integer');
        }
        if (!empty($lifecycleFilter)) {
            $query .= ' AND ' . $this->db->in('lifecycle', $lifecycleFilter, false, 'text');
        }
        $res = $this->db->queryF($query, array('integer', 'text'), array($sourcePoolId, 1));

        while ($row = $this->db->fetchAssoc($res)) {
            if (!isset($questionIdMappingPerPool[$sourcePoolId])) {
                $questionIdMappingPerPool[$sourcePoolId] = array();
            }
            if (!isset($questionIdMappingPerPool[$sourcePoolId][ $row['question_id'] ])) {
                $question = assQuestion::instantiateQuestion($row['question_id']);
                $duplicateId = $question->duplicate(true, '', '', -1, $this->test_obj->getId());

                $nextId = $this->db->nextId('tst_rnd_cpy');
                $this->db->insert('tst_rnd_cpy', array(
                    'copy_id' => array('integer', $nextId),
                    'tst_fi' => array('integer', $this->test_obj->getTestId()),
                    'qst_fi' => array('integer', $duplicateId),
                    'qpl_fi' => array('integer', $sourcePoolId)
                ));

                $questionIdMappingPerPool[$sourcePoolId][ $row['question_id'] ] = $duplicateId;
            }
        }

        return $questionIdMappingPerPool;
    }
    // fau.

    private function mirrorSourcePoolTaxonomies($sourcePoolId, $questionIdMapping): ilQuestionPoolDuplicatedTaxonomiesKeysMap
    {
        $duplicator = new ilQuestionPoolTaxonomiesDuplicator();

        $duplicator->setSourceObjId($sourcePoolId);
        $duplicator->setSourceObjType('qpl');
        $duplicator->setTargetObjId($this->test_obj->getId());
        $duplicator->setTargetObjType($this->test_obj->getType());
        $duplicator->setQuestionIdMapping($questionIdMapping);

        $duplicator->duplicate($duplicator->getAllTaxonomiesForSourceObject());

        return $duplicator->getDuplicatedTaxonomiesKeysMap();
    }

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @param ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap
     * @param integer $sourcePoolId
     */
    private function applyMappedTaxonomiesKeys(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList, ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap, $sourcePoolId)
    {
        foreach ($sourcePoolDefinitionList as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

            if ($definition->getPoolId() == $sourcePoolId) {
                // fau: taxFilter/typeFilter - map the enhanced taxonomy filter
                #$definition->setMappedFilterTaxId(
                #	$taxonomiesKeysMap->getMappedTaxonomyId($definition->getOriginalFilterTaxId())
                #);

                #$definition->setMappedFilterTaxNodeId(
                #	$taxonomiesKeysMap->getMappedTaxNodeId($definition->getOriginalFilterTaxNodeId())
                #);

                $definition->mapTaxonomyFilter($taxonomiesKeysMap);
                // fau.
            }
        }
    }
}
