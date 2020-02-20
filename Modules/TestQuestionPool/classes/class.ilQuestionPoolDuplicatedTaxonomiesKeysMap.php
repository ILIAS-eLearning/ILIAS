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
    /**
     * @var array
     */
    private $taxonomyKeyMap = array();

    /**
     * @var array
     */
    private $taxNodeKeyMap = array();

    /**
     * @var array
     */
    private $taxRootNodeKeyMap = array();

    /**
     * @param ilObjTaxonomy $originalTaxonomyId
     * @param ilObjTaxonomy $mappedTaxonomyId
     */
    public function addDuplicatedTaxonomy(ilObjTaxonomy $originalTaxonomy, ilObjTaxonomy $mappedTaxonomy)
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
    public function getMappedTaxonomyId($originalTaxonomyId)
    {
        return $this->taxonomyKeyMap[$originalTaxonomyId];
    }

    /**
     * @param integer $originalTaxNodeId
     * @return integer
     */
    public function getMappedTaxNodeId($originalTaxNodeId)
    {
        return $this->taxNodeKeyMap[$originalTaxNodeId];
    }

    /**
     * @return array
     */
    public function getTaxonomyRootNodeMap()
    {
        return $this->taxRootNodeKeyMap;
    }
}
