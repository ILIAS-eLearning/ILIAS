<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bheyser
 * Date: 23.09.13
 * Time: 12:26
 * To change this template use File | Settings | File Templates.
 */

class ilTestRandomQuestionSetSourcePoolTaxonomiesDuplicator
{
	private $sourcePoolId = null;

	private $questionIdMapping = null;

	public function __construct($sourcePoolId, $questionIdMapping)
	{
		$this->sourcePoolId = $sourcePoolId;
		$this->questionIdMapping = $questionIdMapping;
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
		$taxonomyIds = ilObjTaxonomy::getUsageOfObject($this->testOBJ->getId());

		foreach($taxonomyIds as $taxId)
		{
			$nodeMapping = $this->duplicateTaxonomyFromPoolToTest($taxId);
		}
	}

	private function duplicateTaxonomyFromPoolToTest($poolTaxonomyId)
	{
		$testTaxonomy = new ilObjTaxonomy();
		$testTaxonomy->doCreate();

		$poolTaxonomy = new ilObjTaxonomy($poolTaxonomyId);
		$poolTaxonomy->doCloneObject($testTaxonomy, null, null);

		$testTaxonomy->doUpdate();

		ilObjTaxonomy::saveUsage( $testTaxonomy->getId(), $this->testOBJ->getId() );

		return $poolTaxonomy->getNodeMapping();
	}
}