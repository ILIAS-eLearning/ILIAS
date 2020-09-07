<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolDuplicatedTaxonomiesKeysMap.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilQuestionPoolTaxonomiesDuplicator
{
    private $sourceObjId = null;
    
    private $sourceObjType = null;

    private $targetObjId = null;
    
    private $targetObjType = null;

    /**
     * @var null
     */
    private $questionIdMapping = null;

    /**
     * @var ilQuestionPoolDuplicatedTaxonomiesKeysMap
     */
    private $duplicatedTaxonomiesKeysMap = null;

    public function __construct()
    {
        $this->duplicatedTaxonomiesKeysMap = new ilQuestionPoolDuplicatedTaxonomiesKeysMap();
    }

    public function setSourceObjId($sourceObjId)
    {
        $this->sourceObjId = $sourceObjId;
    }

    public function getSourceObjId()
    {
        return $this->sourceObjId;
    }

    public function getSourceObjType()
    {
        return $this->sourceObjType;
    }

    public function setSourceObjType($sourceObjType)
    {
        $this->sourceObjType = $sourceObjType;
    }

    public function getTargetObjId()
    {
        return $this->targetObjId;
    }

    public function setTargetObjId($targetObjId)
    {
        $this->targetObjId = $targetObjId;
    }

    public function getTargetObjType()
    {
        return $this->targetObjType;
    }

    public function setTargetObjType($targetObjType)
    {
        $this->targetObjType = $targetObjType;
    }

    public function setQuestionIdMapping($questionIdMapping)
    {
        $this->questionIdMapping = $questionIdMapping;
    }

    public function getQuestionIdMapping()
    {
        return $this->questionIdMapping;
    }

    public function duplicate($poolTaxonomyIds)
    {
        foreach ($poolTaxonomyIds as $poolTaxId) {
            $this->duplicateTaxonomyFromPoolToTest($poolTaxId);

            $this->transferAssignmentsFromOriginalToDuplicatedTaxonomy(
                $poolTaxId,
                $this->duplicatedTaxonomiesKeysMap->getMappedTaxonomyId($poolTaxId)
            );
        }
    }

    private function duplicateTaxonomyFromPoolToTest($poolTaxonomyId)
    {
        $testTaxonomy = new ilObjTaxonomy();
        $testTaxonomy->create();

        $poolTaxonomy = new ilObjTaxonomy($poolTaxonomyId);
        $poolTaxonomy->doCloneObject($testTaxonomy, null, null);

        $poolTaxonomy->getTree()->readRootId();
        $testTaxonomy->getTree()->readRootId();

        $testTaxonomy->update();

        ilObjTaxonomy::saveUsage($testTaxonomy->getId(), $this->getTargetObjId());

        $this->duplicatedTaxonomiesKeysMap->addDuplicatedTaxonomy($poolTaxonomy, $testTaxonomy);
    }

    private function transferAssignmentsFromOriginalToDuplicatedTaxonomy($originalTaxonomyId, $mappedTaxonomyId)
    {
        $originalTaxAssignment = new ilTaxNodeAssignment($this->getSourceObjType(), $this->getSourceObjId(), 'quest', $originalTaxonomyId);

        $duplicatedTaxAssignment = new ilTaxNodeAssignment($this->getTargetObjType(), $this->getTargetObjId(), 'quest', $mappedTaxonomyId);

        foreach ($this->getQuestionIdMapping() as $originalQuestionId => $duplicatedQuestionId) {
            $assignments = $originalTaxAssignment->getAssignmentsOfItem($originalQuestionId);

            foreach ($assignments as $assData) {
                $mappedNodeId = $this->duplicatedTaxonomiesKeysMap->getMappedTaxNodeId($assData['node_id']);

                $duplicatedTaxAssignment->addAssignment($mappedNodeId, $duplicatedQuestionId);
            }
        }
    }

    /**
     * @return ilQuestionPoolDuplicatedTaxonomiesKeysMap
     */
    public function getDuplicatedTaxonomiesKeysMap()
    {
        return $this->duplicatedTaxonomiesKeysMap;
    }
    
    public function getAllTaxonomiesForSourceObject()
    {
        return ilObjTaxonomy::getUsageOfObject($this->getSourceObjId());
    }
}
