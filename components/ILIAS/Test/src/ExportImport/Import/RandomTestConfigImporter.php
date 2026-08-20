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

namespace ILIAS\Test\ExportImport\Import;

use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Test\ExportImport\Envelopes\QuestionSetConfig;
use ilImportMapping;
use ilObjTest;
use ilTestRandomQuestionSetSourcePoolDefinition;
use ilTestRandomQuestionSetSourcePoolDefinitionFactory;
use ilTestRandomQuestionSetSourcePoolDefinitionList;
use ilTestRandomQuestionSetStagingPoolQuestion;
use Psr\Log\LoggerInterface;

/**
 * Imports the random question set config and its related data. It also remaps the taxonomy IDs in the mapped_taxonomy_filter
 * of all imported source pool definitions.
 */
class RandomTestConfigImporter
{
    public function __construct(
        private readonly ilDBInterface $database,
        private readonly LoggerInterface $log,
        private readonly DataFactory $data_factory
    ) {
    }

    /**
     * Import the random question set config and its related data. It will import the random question set config, the
     * random question staging pools and random question selection definitions.
     */
    public function import(
        QuestionSetConfig $config,
        ilImportMapping $mapping,
        ilObjTest $test_object
    ): void {
        if (!$config->isRandom()) {
            throw new \InvalidArgumentException('Expected random question set config');
        }

        $config->getConfig()->saveToDb();
        $this->log->debug("Imported random question set config for test {$test_object->getTestId()}");

        foreach ($config->getStagingPools() as $pool_id => $questions) {
            $this->importRandomQuestionStagingPool($pool_id, $questions, $mapping, $test_object);
        }

        foreach ($config->getDefinitions() as $definition) {
            $this->importSourcePoolDefinition($definition, $mapping);
        }
    }

    private function importRandomQuestionStagingPool(
        int $old_pool_id,
        array $questions,
        ilImportMapping $mapping,
        ilObjTest $test_object
    ): void {
        $new_pool_id = $this->database->nextId('object_data');
        $mapping->addMapping(
            'components/ILIAS/Test',
            'pool',
            (string) $old_pool_id,
            (string) $new_pool_id
        );
        $this->log->debug("Imported random question staging pool: {$old_pool_id} -> {$new_pool_id}");

        // QuestionID was mapped during question set config denormalization
        foreach ($questions as $question_id) {
            $question = new ilTestRandomQuestionSetStagingPoolQuestion($this->database);
            $question->setTestId($test_object->getTestId());
            $question->setPoolId($new_pool_id);
            $question->setQuestionId($question_id);
            $question->saveQuestionStaging();
            $this->log->debug("Imported random question staging question: {$question_id}");
        }
    }

    private function importSourcePoolDefinition(
        ilTestRandomQuestionSetSourcePoolDefinition $definition,
        ilImportMapping $mapping,
    ): void {
        // New PoolID was not available during denormalization, so we have to map it here
        $old_pool_id = $definition->getPoolId();
        $new_pool_id = (int) $mapping->getMapping('components/ILIAS/Test', 'pool', (string) $old_pool_id);
        $definition->setPoolId($new_pool_id);

        if ($old_pool_id !== $new_pool_id) {
            $ref_ids = $this->data_factory->objId($new_pool_id)->toReferenceIds();
            if ($ref_ids !== []) {
                $definition->setPoolRefId(current($ref_ids)->toInt());
                $this->log->debug("Derived source pool definition from Object ID: {$old_pool_id} -> {$definition->getPoolRefId()}");
            }
        }

        $old_definition_id = $definition->getId();
        $definition->setId(0);
        $definition->saveToDb();
        $this->log->debug("Imported source pool definition: {$old_definition_id} -> {$definition->getId()}");

        $mapping->addMapping(
            'components/ILIAS/Test',
            'rnd_src_pool_def',
            (string) $old_definition_id,
            (string) $definition->getId()
        );
    }

    /**
     * Remap taxonomy IDs in the mapped_taxonomy_filter of all imported source pool definitions.
     * Taxonomy mappings are only available after the Taxonomy component has finished its import, so this must run
     * during finalProcessing().
     */
    public function finalizeTaxonomyFilters(ilImportMapping $mapping): void
    {
        $tst_mappings = $mapping->getMappingsOfEntity('components/ILIAS/Test', 'tst');

        foreach ($tst_mappings as $old_test_id => $new_test_id) {
            if ($old_test_id === 'new_id' || (int) $old_test_id <= 0) {
                continue;
            }

            $test_obj = new ilObjTest(0, false);
            $test_obj->setTestId((int) $new_test_id);

            $definition_list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
                $this->database,
                $test_obj,
                new ilTestRandomQuestionSetSourcePoolDefinitionFactory($this->database, $test_obj)
            );
            $definition_list->loadDefinitions();

            foreach ($definition_list as $definition) {
                $mapped_filter = $definition->getMappedTaxonomyFilter();
                if ($mapped_filter === []) {
                    continue;
                }

                $definition->setMappedTaxonomyFilter($this->remapTaxonomyFilter($mapping, $mapped_filter));
                $definition->saveToDb();
                $this->log->debug("Remapped taxonomy filter for definition {$definition->getId()}");
            }
        }
    }

    private function remapTaxonomyFilter(ilImportMapping $mapping, array $filter): array
    {
        $remapped = [];
        foreach ($filter as $tax_id => $node_ids) {
            $new_tax_id = (int) $mapping->getMapping('components/ILIAS/Taxonomy', 'tax', (string) $tax_id);
            if ($new_tax_id <= 0) {
                continue;
            }

            $remapped[$new_tax_id] = [];
            foreach ($node_ids as $node_id) {
                $new_node_id = (int) $mapping->getMapping('components/ILIAS/Taxonomy', 'tax_tree', (string) $node_id);
                if ($new_node_id > 0) {
                    $remapped[$new_tax_id][] = $new_node_id;
                }
            }
        }

        return $remapped;
    }
}
