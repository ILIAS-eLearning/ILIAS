<?php

declare(strict_types=1);

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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilQuestionPoolTaxonomiesDuplicator
{
    private ?int $source_obj_id = null;

    private ?string $source_obj_type = null;

    private ?int $target_obj_id = null;

    private ?string $target_obj_type = null;

    /**
     * @var array<int>
     */
    private array $question_id_mapping = [];

    private ilQuestionPoolDuplicatedTaxonomiesKeysMap $duplicated_taxonomies_keys_map;

    public function __construct()
    {
        $this->duplicated_taxonomies_keys_map = new ilQuestionPoolDuplicatedTaxonomiesKeysMap();
    }

    public function setSourceObjId(int $source_obj_id): void
    {
        $this->source_obj_id = $source_obj_id;
    }

    public function getSourceObjId(): ?int
    {
        return $this->source_obj_id;
    }

    public function getSourceObjType(): ?string
    {
        return $this->source_obj_type;
    }

    public function setSourceObjType(string $source_obj_type): void
    {
        $this->source_obj_type = $source_obj_type;
    }

    public function getTargetObjId(): ?int
    {
        return $this->target_obj_id;
    }

    public function setTargetObjId(int $target_obj_id): void
    {
        $this->target_obj_id = $target_obj_id;
    }

    public function getTargetObjType(): ?string
    {
        return $this->target_obj_type;
    }

    public function setTargetObjType(string $target_obj_type): void
    {
        $this->target_obj_type = $target_obj_type;
    }

    /**
     * @param array<int> $question_id_mapping
     */
    public function setQuestionIdMapping(array $question_id_mapping): void
    {
        $this->question_id_mapping = $question_id_mapping;
    }

    /**
     * @return array<int>
     */
    public function getQuestionIdMapping(): array
    {
        return $this->question_id_mapping;
    }

    /**
     *
     * @param array<int> $pool_taxonomy_ids
     */
    public function duplicate(array $pool_taxonomy_ids): void
    {
        foreach ($pool_taxonomy_ids as $pool_tax_id) {
            $this->duplicateTaxonomyFromPoolToTest($pool_tax_id);

            $this->transferAssignmentsFromOriginalToDuplicatedTaxonomy(
                $pool_tax_id,
                $this->duplicated_taxonomies_keys_map->getMappedTaxonomyId($pool_tax_id)
            );
        }
    }

    private function duplicateTaxonomyFromPoolToTest(int $pool_taxonomy_id): void
    {
        $pool_taxonomy = new ilObjTaxonomy($pool_taxonomy_id);
        $test_taxonomy = new ilObjTaxonomy();
        $test_taxonomy->create();
        $test_taxonomy->setTitle($pool_taxonomy->getTitle());
        $test_taxonomy->setDescription($pool_taxonomy->getDescription());
        $test_taxonomy->setSortingMode($pool_taxonomy->getSortingMode());

        $pool_taxonomy->cloneNodes(
            $test_taxonomy,
            $test_taxonomy->getTree()->readRootId(),
            $pool_taxonomy->getTree()->readRootId()
        );

        $test_taxonomy->update();

        ilObjTaxonomy::saveUsage($test_taxonomy->getId(), $this->getTargetObjId());

        $this->duplicated_taxonomies_keys_map->addDuplicatedTaxonomy($pool_taxonomy, $test_taxonomy);
    }

    private function transferAssignmentsFromOriginalToDuplicatedTaxonomy(int $original_taxonomy_id, int $mapped_taxonomy_id): void
    {
        $original_tax_assignment = new ilTaxNodeAssignment($this->getSourceObjType(), $this->getSourceObjId(), 'quest', $original_taxonomy_id);

        $duplicate_tax_assignment = new ilTaxNodeAssignment($this->getTargetObjType(), $this->getTargetObjId(), 'quest', $mapped_taxonomy_id);

        foreach ($this->getQuestionIdMapping() as $original_question_id => $duplicated_question_id) {
            $assignments = $original_tax_assignment->getAssignmentsOfItem($original_question_id);

            foreach ($assignments as $ass_data) {
                $mapped_node_id = $this->duplicated_taxonomies_keys_map->getMappedTaxNodeId($ass_data['node_id']);

                $duplicate_tax_assignment->addAssignment($mapped_node_id, $duplicated_question_id);
            }
        }
    }

    /**
     * @return ilQuestionPoolDuplicatedTaxonomiesKeysMap
     */
    public function getDuplicatedTaxonomiesKeysMap(): ilQuestionPoolDuplicatedTaxonomiesKeysMap
    {
        return $this->duplicated_taxonomies_keys_map;
    }

    public function getAllTaxonomiesForSourceObject(): array
    {
        return ilObjTaxonomy::getUsageOfObject($this->getSourceObjId());
    }
}
