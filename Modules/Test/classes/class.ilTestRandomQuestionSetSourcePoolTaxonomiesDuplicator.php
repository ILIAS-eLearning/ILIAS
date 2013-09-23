<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';

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

	private $questionIdMapping = null;

	private $taxonomyIdMapping = null;

	public function __construct(ilObjTest $testOBJ, $sourcePoolId, $questionIdMapping)
	{
		$this->testOBJ = $testOBJ;
		$this->sourcePoolId = $sourcePoolId;
		$this->questionIdMapping = $questionIdMapping;
		$this->taxonomyIdMapping = array();
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

	public function addTaxonomyIdMapping($originalTaxonomyId, $mappedTaxonomyId)
	{
		$this->taxonomyIdMapping[ $originalTaxonomyId ] = $mappedTaxonomyId;
	}

	public function getMappedTaxonomyId($originalTaxonomyId)
	{
		return $this->taxonomyIdMapping[ $originalTaxonomyId ];
	}

	public function duplicate()
	{
		$poolTaxonomyIds = ilObjTaxonomy::getUsageOfObject($this->getSourcePoolId());

		foreach($poolTaxonomyIds as $poolTaxId)
		{
			$nodeMapping = $this->duplicateTaxonomyFromPoolToTest($poolTaxId);

			$this->transferAssignmentsFromOriginalToDuplicatedTaxonomy($poolTaxId, $nodeMapping);
		}
	}

	private function duplicateTaxonomyFromPoolToTest($poolTaxonomyId)
	{
		$testTaxonomy = new ilObjTaxonomy();
		$testTaxonomy->create();

		$poolTaxonomy = new ilObjTaxonomy($poolTaxonomyId);
		$poolTaxonomy->doCloneObject($testTaxonomy, null, null);

		$testTaxonomy->update();

		ilObjTaxonomy::saveUsage( $testTaxonomy->getId(), $this->testOBJ->getId() );

		$this->addTaxonomyIdMapping($poolTaxonomy->getId(), $testTaxonomy->getId());

		return $poolTaxonomy->getNodeMapping();
	}

	private function transferAssignmentsFromOriginalToDuplicatedTaxonomy($originalTaxonomyId, $nodeMapping)
	{
		$duplicatedTaxonomyId = $this->getMappedTaxonomyId($originalTaxonomyId);

		$originalTaxAssignment = new ilTaxNodeAssignment('qpl', 'quest', $originalTaxonomyId);
		$duplicatedTaxAssignment = new ilTaxNodeAssignment('qpl', 'quest', $duplicatedTaxonomyId);

		foreach($this->getQuestionIdMapping() as $originalQuestionId => $duplicatedQuestionId)
		{
			$assignments = $originalTaxAssignment->getAssignmentsOfItem($originalQuestionId);

			foreach($assignments as $assData)
			{
				$duplicatedTaxAssignment->addAssignment($nodeMapping[$assData['node_id']], $duplicatedQuestionId);
			}
		}
	}
}