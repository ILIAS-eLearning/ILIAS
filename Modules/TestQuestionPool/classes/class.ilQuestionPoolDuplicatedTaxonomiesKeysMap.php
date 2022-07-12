<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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

    public function addDuplicatedTaxonomy(ilObjTaxonomy $originalTaxonomy, ilObjTaxonomy $mappedTaxonomy) : void
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
    public function getMappedTaxonomyId($originalTaxonomyId) : int
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
    public function getMappedTaxNodeId($originalTaxNodeId) : int
    {
        return $this->taxNodeKeyMap[$originalTaxNodeId];
    }

    /**
     * @return array
     */
    public function getTaxonomyRootNodeMap() : array
    {
        return $this->taxRootNodeKeyMap;
    }
}
