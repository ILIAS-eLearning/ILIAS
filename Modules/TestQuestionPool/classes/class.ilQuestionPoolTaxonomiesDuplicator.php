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

    public function setSourceObjId($sourceObjId): void
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

    public function setSourceObjType($sourceObjType): void
    {
        $this->sourceObjType = $sourceObjType;
    }

    public function getTargetObjId()
    {
        return $this->targetObjId;
    }

    public function setTargetObjId($targetObjId): void
    {
        $this->targetObjId = $targetObjId;
    }

    public function getTargetObjType()
    {
        return $this->targetObjType;
    }

    public function setTargetObjType($targetObjType): void
    {
        $this->targetObjType = $targetObjType;
    }

    public function setQuestionIdMapping($questionIdMapping): void
    {
        $this->questionIdMapping = $questionIdMapping;
    }

    public function getQuestionIdMapping()
    {
        return $this->questionIdMapping;
    }

    public function duplicate($poolTaxonomyIds): void
    {
        foreach ($poolTaxonomyIds as $poolTaxId) {
            $this->duplicateTaxonomyFromPoolToTest($poolTaxId);

            $this->transferAssignmentsFromOriginalToDuplicatedTaxonomy(
                $poolTaxId,
                $this->duplicatedTaxonomiesKeysMap->getMappedTaxonomyId($poolTaxId)
            );
        }
    }

    private function duplicateTaxonomyFromPoolToTest($poolTaxonomyId): void
    {
        $poolTaxonomy = new ilObjTaxonomy($poolTaxonomyId);
        $testTaxonomy = new ilObjTaxonomy();
        $testTaxonomy->create();
        $testTaxonomy->setTitle($poolTaxonomy->getTitle());
        $testTaxonomy->setDescription($poolTaxonomy->getDescription());
        $testTaxonomy->setSortingMode($poolTaxonomy->getSortingMode());

        $this->node_mapping = array();

        $poolTaxonomy->cloneNodes(
            $testTaxonomy,
            $testTaxonomy->getTree()->readRootId(),
            $poolTaxonomy->getTree()->readRootId()
        );

        $testTaxonomy->update();

        ilObjTaxonomy::saveUsage($testTaxonomy->getId(), $this->getTargetObjId());

        $this->duplicatedTaxonomiesKeysMap->addDuplicatedTaxonomy($poolTaxonomy, $testTaxonomy);
    }

    private function transferAssignmentsFromOriginalToDuplicatedTaxonomy($originalTaxonomyId, $mappedTaxonomyId): void
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
    public function getDuplicatedTaxonomiesKeysMap(): ilQuestionPoolDuplicatedTaxonomiesKeysMap
    {
        return $this->duplicatedTaxonomiesKeysMap;
    }

    public function getAllTaxonomiesForSourceObject(): array
    {
        return ilObjTaxonomy::getUsageOfObject($this->getSourceObjId());
    }
}
