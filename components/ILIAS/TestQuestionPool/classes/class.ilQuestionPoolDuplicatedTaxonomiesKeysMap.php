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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilQuestionPoolDuplicatedTaxonomiesKeysMap
{
    private array $taxonomy_key_map = [];
    private array $tax_node_key_map = [];
    private array $tax_root_node_key_map = [];

    public function addDuplicatedTaxonomy(
        ilObjTaxonomy $original_taxonomy,
        ilObjTaxonomy $mapped_taxonomy
    ): void {
        $this->taxonomy_key_map[ $original_taxonomy->getId() ] = $mapped_taxonomy->getId();

        foreach ($original_taxonomy->getNodeMapping() as $original_node_id => $mapped_node_id) {
            $this->tax_node_key_map[$original_node_id] = $mapped_node_id;
        }
    }

    public function getMappedTaxonomyId(int $original_taxonomy_id): ?int
    {
        return $this->taxonomy_key_map[$original_taxonomy_id] ?? null;
    }

    public function getMappedTaxNodeId(int $original_tax_node_id): ?int
    {
        return $this->tax_node_key_map[$original_tax_node_id] ?? null;
    }

    /**
     * @return array
     */
    public function getTaxonomyRootNodeMap(): array
    {
        return $this->tax_root_node_key_map;
    }
}
