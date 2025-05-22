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
 * @package components\ILIAS/Test
 */
class ilTestQuestionFilterLabelTranslator
{
    private array $taxonomy_tree_ids = [];
    private array $taxonomy_node_ids = [];

    private array $taxonomy_tree_labels = [];
    private array $taxonomy_node_labels = [];

    private array $type_labels = [];

    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng
    ) {
        $this->loadTypeLabels();
    }

    public function loadLabels(
        ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list
    ): void {
        $this->collectIds($source_pool_definition_list);

        $this->loadTaxonomyTreeLabels();
        $this->loadTaxonomyNodeLabels();
    }

    private function collectIds(
        ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list
    ): void {
        foreach ($source_pool_definition_list as $definition) {
            foreach ($definition->getOriginalTaxonomyFilter() as $tax_id => $node_ids) {
                $this->taxonomy_tree_ids[] = $tax_id;
                $this->taxonomy_node_ids = array_merge($this->taxonomy_node_ids, $node_ids);
            }

            // mapped filter will be shown after synchronisation
            foreach ($definition->getMappedTaxonomyFilter() as $tax_id => $node_ids) {
                $this->taxonomy_tree_ids[] = $tax_id;
                $this->taxonomy_node_ids = array_merge($this->taxonomy_node_ids, $node_ids);
            }
        }
    }

    private function loadTaxonomyTreeLabels(): void
    {
        $res = $this->db->queryF(
            "
                SELECT		obj_id tax_tree_id,
                            title tax_tree_title

                FROM		object_data

                WHERE		{$this->db->in('obj_id', $this->taxonomy_tree_ids, false, 'integer')}
                AND			type = %s
            ",
            ['text'],
            ['tax']
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->taxonomy_tree_labels[ $row['tax_tree_id'] ] = $row['tax_tree_title'];
        }
    }

    private function loadTaxonomyNodeLabels()
    {
        $res = $this->db->query("
            SELECT		tax_node.obj_id tax_node_id,
                        tax_node.title tax_node_title

            FROM		tax_node

            WHERE		{$this->db->in('tax_node.obj_id', $this->taxonomy_node_ids, false, 'integer')}
        ");

        while ($row = $this->db->fetchAssoc($res)) {
            $this->taxonomy_node_labels[ $row['tax_node_id'] ] = $row['tax_node_title'];
        }
    }

    private function loadTypeLabels(): void
    {
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $translation => $data) {
            $this->type_labels[$data['question_type_id']] = $translation;
        }
    }

    public function getTaxonomyTreeLabel(int $taxonomy_tree_id): string
    {
        return $this->taxonomy_tree_labels[$taxonomy_tree_id];
    }

    public function getTaxonomyNodeLabel(int $taxonomy_node_id): string
    {
        return $this->taxonomy_node_labels[$taxonomy_node_id];
    }

    public function loadLabelsFromTaxonomyIds(array $taxonomy_ids): void
    {
        $this->taxonomy_tree_ids = $taxonomy_ids;

        $this->loadTaxonomyTreeLabels();
    }

    public function getTaxonomyFilterLabel(
        array $filter = [],
        string $filter_delimiter = ' + ',
        string $tax_node_delimiter = ': ',
        string $nodes_delimiter = ', '
    ): string {
        $labels = [];
        foreach ($filter as $tax_id => $node_ids) {
            $nodes = [];
            foreach ($node_ids as $node_id) {
                $nodes[] = $this->getTaxonomyNodeLabel((int) $node_id);
            }
            $labels[] = $this->getTaxonomyTreeLabel($tax_id)
                . $tax_node_delimiter
                . implode($nodes_delimiter, $nodes);
        }
        return implode($filter_delimiter, $labels);
    }

    public function getLifecycleFilterLabel(array $filter = []): string
    {
        $lifecycle_translations = ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng);

        $lifecycles = [];
        foreach ($filter as $lifecycle) {
            $lifecycles[] = $lifecycle_translations[$lifecycle];
        }
        asort($lifecycles);
        return implode(', ', $lifecycles);
    }

    public function getTypeFilterLabel(array $filter = []): string
    {
        $types = [];
        foreach ($filter as $type_id) {
            $types[] = $this->type_labels[$type_id];
        }
        asort($types);
        return implode(', ', $types);
    }
}
