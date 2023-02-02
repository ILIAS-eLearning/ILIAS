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
 * @package     Modules/TestQuestionPool
 */
class ilQuestionPoolDuplicatedTaxonomiesKeysMap
{
    private array $taxonomyKeyMap = array();
    private array $taxNodeKeyMap = array();
    private array $taxRootNodeKeyMap = array();

    public function addDuplicatedTaxonomy(ilObjTaxonomy $originalTaxonomy, ilObjTaxonomy $mappedTaxonomy): void
    {
        $this->taxonomyKeyMap[ $originalTaxonomy->getId() ] = $mappedTaxonomy->getId();

        foreach ($originalTaxonomy->getNodeMapping() as $originalNodeId => $mappedNodeId) {
            $this->taxNodeKeyMap[$originalNodeId] = $mappedNodeId;
        }
    }

    /**
     * @param integer $originalTaxonomyId
     * @return integer
     */
    public function getMappedTaxonomyId($originalTaxonomyId): int
    {
        if (isset($this->taxonomyKeyMap[$originalTaxonomyId])) {
            return $this->taxonomyKeyMap[$originalTaxonomyId];
        }
        return 0;
    }

    /**
     * @param integer $originalTaxNodeId
     * @return integer
     */
    public function getMappedTaxNodeId($originalTaxNodeId): int
    {
        return $this->taxNodeKeyMap[$originalTaxNodeId];
    }

    /**
     * @return array
     */
    public function getTaxonomyRootNodeMap(): array
    {
        return $this->taxRootNodeKeyMap;
    }
}
