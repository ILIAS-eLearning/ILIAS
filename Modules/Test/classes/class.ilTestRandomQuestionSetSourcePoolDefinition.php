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
class ilTestRandomQuestionSetSourcePoolDefinition
{
    private ?int $id = null;
    private ?int $pool_id = null;
    private ?int $pool_ref_id = null;
    private ?string $pool_title = null;
    private ?string $pool_path = null;
    private ?int $pool_question_count = null;

    /**
     * @var array taxId => [nodeId, ...]
     */
    private array $original_taxonomy_filter = [];

    /**
     * @var array taxId => [nodeId, ...]
     */
    private array $mapped_taxonomy_filter = [];

    private array $type_filter = [];
    private array $lifecycle_filter = [];

    private ?int $question_amount = null;

    private ?int $sequence_position = null;

    public function __construct(
        protected ilDBInterface $db,
        protected ilObjTest $test_obj
    ) {
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPoolId(int $pool_id): void
    {
        $this->pool_id = $pool_id;
    }

    public function getPoolId(): ?int
    {
        return $this->pool_id;
    }

    public function getPoolRefId(): ?int
    {
        return $this->pool_ref_id;
    }

    public function setPoolRefId(?int $pool_ref_id): void
    {
        $this->pool_ref_id = $pool_ref_id;
    }

    public function setPoolTitle(string $pool_title): void
    {
        $this->pool_title = $pool_title;
    }

    public function getPoolTitle(): string
    {
        return $this->pool_title;
    }

    public function setPoolPath(?string $pool_path): void
    {
        $this->pool_path = $pool_path;
    }

    public function getPoolPath(): ?string
    {
        return $this->pool_path;
    }

    public function setPoolQuestionCount(?int $pool_question_count): void
    {
        $this->pool_question_count = $pool_question_count;
    }

    public function getPoolQuestionCount(): ?int
    {
        return $this->pool_question_count;
    }

    public function getOriginalTaxonomyFilter(): array
    {
        return $this->original_taxonomy_filter;
    }

    public function setOriginalTaxonomyFilter(array $filter = []): void
    {
        $this->original_taxonomy_filter = $filter;
    }

    private function getOriginalTaxonomyFilterForDbValue(): ?string
    {
        // TODO-RND2017: migrate to separate table for common selections by e.g. statistics
        return empty($this->original_taxonomy_filter) ? null : serialize($this->original_taxonomy_filter);
    }

    private function setOriginalTaxonomyFilterFromDbValue(?string $value): void
    {
        // TODO-RND2017: migrate to separate table for common selections by e.g. statistics
        $this->original_taxonomy_filter = empty($value) ? [] : unserialize($value);
    }

    /**
     * get the mapped taxonomy filter conditions
     * @return 	array	taxId => [nodeId, ...]
     */
    public function getMappedTaxonomyFilter(): array
    {
        return $this->mapped_taxonomy_filter;
    }

    /**
     * set the original taxonomy filter condition
     * @param array 	taxId => [nodeId, ...]
     */
    public function setMappedTaxonomyFilter(array $filter = []): void
    {
        $this->mapped_taxonomy_filter = $filter;
    }

    private function getMappedTaxonomyFilterForDbValue(): ?string
    {
        return empty($this->mapped_taxonomy_filter) ? null : serialize($this->mapped_taxonomy_filter);
    }

    private function setMappedTaxonomyFilterFromDbValue(?string $value): void
    {
        $this->mapped_taxonomy_filter = empty($value) ? [] : unserialize($value);
    }

    public function mapTaxonomyFilter(ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomies_keys_map): void
    {
        $this->mapped_taxonomy_filter = [];
        foreach ($this->original_taxonomy_filter as $taxId => $nodeIds) {
            $mappedNodeIds = [];
            foreach ($nodeIds as $nodeId) {
                $mappedNodeIds[] = $taxonomies_keys_map->getMappedTaxNodeId($nodeId);
            }
            $this->mapped_taxonomy_filter[$taxonomies_keys_map->getMappedTaxonomyId($taxId)] = $mappedNodeIds;
        }
    }

    public function setTypeFilter(array $type_filter = []): void
    {
        $this->type_filter = $type_filter;
    }

    public function getTypeFilter(): array
    {
        return $this->type_filter;
    }

    /**
     * get the question type filter for insert into the database
     */
    private function getTypeFilterForDbValue(): ?string
    {
        return empty($this->type_filter) ? null : serialize($this->type_filter);
    }

    /**
     * get the question type filter from database value
     */
    private function setTypeFilterFromDbValue(?string $value): void
    {
        $this->type_filter = empty($value) ? [] : unserialize($value);
    }

    public function getLifecycleFilter(): array
    {
        return $this->lifecycle_filter;
    }

    public function setLifecycleFilter(array $lifecycle_filter): void
    {
        $this->lifecycle_filter = $lifecycle_filter;
    }

    public function getLifecycleFilterForDbValue(): ?string
    {
        return empty($this->lifecycle_filter) ? null : serialize($this->lifecycle_filter);
    }

    public function setLifecycleFilterFromDbValue(?string $db_value)
    {
        $this->lifecycle_filter = empty($db_value) ? [] : unserialize($db_value);
    }

    /**
     * Get the type filter as a list of type tags
     * @return string[]
     */
    public function getTypeFilterAsTypeTags(): array
    {
        $map = [];
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $row) {
            $map[$row['question_type_id']] = $row['type_tag'];
        }

        $tags = [];
        foreach ($this->type_filter as $type_id) {
            if (isset($map[$type_id])) {
                $tags[] = $map[$type_id];
            }
        }

        return $tags;
    }

    /**
     * Set the type filter from a list of type tags
     * @param string[] $tags
     */
    public function setTypeFilterFromTypeTags(array $tags): void
    {
        $map = [];
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $row) {
            $map[$row['type_tag']] = $row['question_type_id'];
        }

        $this->type_filter = [];
        foreach ($tags as $type_tag) {
            if (isset($map[$type_tag])) {
                $this->type_filter[] = $map[$type_tag];
            }
        }
    }

    public function setQuestionAmount(?int $question_amount): void
    {
        $this->question_amount = $question_amount;
    }

    public function getQuestionAmount(): ?int
    {
        return $this->question_amount;
    }

    public function setSequencePosition(int $sequence_position): void
    {
        $this->sequence_position = $sequence_position;
    }

    public function getSequencePosition(): ?int
    {
        return $this->sequence_position;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function initFromArray(array $data_array): void
    {
        foreach ($data_array as $field => $value) {
            switch ($field) {
                case 'def_id':
                    $this->setId($value);
                    break;
                case 'pool_fi':
                    $this->setPoolId($value);
                    break;
                case 'pool_ref_id':
                    $this->setPoolRefId($value ? (int) $value : null);
                    break;
                case 'pool_title':
                    $this->setPoolTitle($value);
                    break;
                case 'pool_path':
                    $this->setPoolPath($value);
                    break;
                case 'pool_quest_count':
                    $this->setPoolQuestionCount($value);
                    break;
                case 'origin_tax_filter':
                    $this->setOriginalTaxonomyFilterFromDbValue($value);
                    break;
                case 'mapped_tax_filter':
                    $this->setMappedTaxonomyFilterFromDbValue($value);
                    break;
                case 'type_filter':
                    $this->setTypeFilterFromDbValue($value);
                    break;
                case 'lifecycle_filter':
                    $this->setLifecycleFilterFromDbValue($value);
                    break;
                    // fau.
                case 'quest_amount':
                    $this->setQuestionAmount($value);
                    break;
                case 'sequence_pos':
                    $this->setSequencePosition($value);
                    break;
            }
        }
    }

    public function loadFromDb(int $id): bool
    {
        $res = $this->db->queryF(
            "SELECT * FROM tst_rnd_quest_set_qpls WHERE def_id = %s",
            ['integer'],
            [$id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->initFromArray($row);

            return true;
        }

        return false;
    }

    public function saveToDb(): void
    {
        if ($this->getId()) {
            $this->updateDbRecord($this->test_obj->getTestId());
            return;
        }

        $this->insertDbRecord($this->test_obj->getTestId());
    }

    public function cloneToDbForTestId(int $test_id): void
    {
        $this->insertDbRecord($test_id);
    }

    public function deleteFromDb(): void
    {
        $this->db->manipulateF(
            "DELETE FROM tst_rnd_quest_set_qpls WHERE def_id = %s",
            ['integer'],
            [$this->getId()]
        );
    }

    private function updateDbRecord(int $test_id): void
    {
        $this->db->update(
            'tst_rnd_quest_set_qpls',
            [
                'test_fi' => ['integer', $test_id],
                'pool_fi' => ['integer', $this->getPoolId()],
                'pool_ref_id' => ['integer', $this->getPoolRefId()],
                'pool_title' => ['text', $this->getPoolTitle()],
                'pool_path' => ['text', $this->getPoolPath()],
                'pool_quest_count' => ['integer', $this->getPoolQuestionCount()],
                'origin_tax_filter' => ['text', $this->getOriginalTaxonomyFilterForDbValue()],
                'mapped_tax_filter' => ['text', $this->getMappedTaxonomyFilterForDbValue()],
                'type_filter' => ['text', $this->getTypeFilterForDbValue()],
                'lifecycle_filter' => ['text', $this->getLifecycleFilterForDbValue()],
                'quest_amount' => ['integer', $this->getQuestionAmount()],
                'sequence_pos' => ['integer', $this->getSequencePosition()]
            ],
            [
                'def_id' => ['integer', $this->getId()]
            ]
        );
    }

    private function insertDbRecord(int $test_id): void
    {
        $next_id = $this->db->nextId('tst_rnd_quest_set_qpls');

        $this->db->insert('tst_rnd_quest_set_qpls', [
                'def_id' => ['integer', $next_id],
                'test_fi' => ['integer', $test_id],
                'pool_fi' => ['integer', $this->getPoolId()],
                'pool_ref_id' => ['integer', $this->getPoolRefId()],
                'pool_title' => ['text', $this->getPoolTitle()],
                'pool_path' => ['text', $this->getPoolPath()],
                'pool_quest_count' => ['integer', $this->getPoolQuestionCount()],
                'origin_tax_filter' => ['text', $this->getOriginalTaxonomyFilterForDbValue()],
                'mapped_tax_filter' => ['text', $this->getMappedTaxonomyFilterForDbValue()],
                'type_filter' => ['text', $this->getTypeFilterForDbValue()],
                'lifecycle_filter' => ['text', $this->getLifecycleFilterForDbValue()],
                'quest_amount' => ['integer', $this->getQuestionAmount()],
                'sequence_pos' => ['integer', $this->getSequencePosition()]
        ]);

        $this->setId($next_id);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function getPoolInfoLabel(ilLanguage $lng): string
    {
        $pool_path = $this->getPoolPath();
        if (is_int($this->getPoolRefId()) && ilObject::_lookupObjId($this->getPoolRefId())) {
            $path = new ilPathGUI();
            $path->enableTextOnly(true);
            $pool_path = $path->getPath(ROOT_FOLDER_ID, (int) $this->getPoolRefId());
        }

        $poolInfoLabel = sprintf(
            $lng->txt('tst_random_question_set_source_questionpool_summary_string'),
            $this->getPoolTitle(),
            $pool_path,
            $this->getPoolQuestionCount()
        );

        return $poolInfoLabel;
    }

    // -----------------------------------------------------------------------------------------------------------------
}
