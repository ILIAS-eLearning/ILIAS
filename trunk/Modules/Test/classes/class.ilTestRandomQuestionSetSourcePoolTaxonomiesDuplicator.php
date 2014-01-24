<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetDuplicatedTaxonomiesKeysMap.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolTaxonomiesDuplicator
{
	/**
	 * @var ilObjTest
	 */
	public $testOBJ = null;

	private $sourcePoolId = null;

	/**
	 * @var null
	 */
	private $questionIdMapping = null;

	/**
	 * @var ilTestRandomQuestionSetDuplicatedTaxonomiesKeysMap
	 */
	private $duplicatedTaxonomiesKeysMap = null;

	public function __construct(ilObjTest $testOBJ, $sourcePoolId, $questionIdMapping)
	{
		$this->testOBJ = $testOBJ;
		$this->sourcePoolId = $sourcePoolId;
		$this->questionIdMapping = $questionIdMapping;

		$this->duplicatedTaxonomiesKeysMap = new ilTestRandomQuestionSetDuplicatedTaxonomiesKeysMap();
	}

	public function setSourcePoolId($sourcePoolId)
	{
		$this->sourcePoolId = $sourcePoolId;
	}

	public function getSourcePoolId()
	{
		return $this->sourcePoolId;
	}

	public function setQuestionIdMapping($questionIdMapping)
	{
		$this->questionIdMapping = $questionIdMapping;
	}

	public function getQuestionIdMapping()
	{
		return $this->questionIdMapping;
	}

	public function duplicate()
	{
		$poolTaxonomyIds = ilObjTaxonomy::getUsageOfObject($this->getSourcePoolId());

		foreach($poolTaxonomyIds as $poolTaxId)
		{
			$this->duplicateTaxonomyFromPoolToTest($poolTaxId);

			$this->transferAssignmentsFromOriginalToDuplicatedTaxonomy(
				$poolTaxId, $this->duplicatedTaxonomiesKeysMap->getMappedTaxonomyId($poolTaxId)
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

		ilObjTaxonomy::saveUsage( $testTaxonomy->getId(), $this->testOBJ->getId() );

		$this->duplicatedTaxonomiesKeysMap->addDuplicatedTaxonomy($poolTaxonomy, $testTaxonomy);
	}

	private function transferAssignmentsFromOriginalToDuplicatedTaxonomy($originalTaxonomyId, $mappedTaxonomyId)
	{
		$originalTaxAssignment = new ilTaxNodeAssignment('qpl', $this->getSourcePoolId(), 'quest', $originalTaxonomyId);

		$duplicatedTaxAssignment = new ilTaxNodeAssignment('tst', $this->testOBJ->getId(), 'quest', $mappedTaxonomyId);

		foreach($this->getQuestionIdMapping() as $originalQuestionId => $duplicatedQuestionId)
		{
			$assignments = $originalTaxAssignment->getAssignmentsOfItem($originalQuestionId);

			foreach($assignments as $assData)
			{
				$mappedNodeId = $this->duplicatedTaxonomiesKeysMap->getMappedTaxNodeId($assData['node_id']);

				$duplicatedTaxAssignment->addAssignment($mappedNodeId, $duplicatedQuestionId);
			}
		}
	}

	/**
	 * @return ilTestRandomQuestionSetDuplicatedTaxonomiesKeysMap
	 */
	public function getDuplicatedTaxonomiesKeysMap()
	{
		return $this->duplicatedTaxonomiesKeysMap;
	}
}