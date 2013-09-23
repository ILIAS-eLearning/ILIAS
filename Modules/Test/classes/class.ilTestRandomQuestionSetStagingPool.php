<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetStagingPool
{
	/**
	 * @var ilDB
	 */
	public $db = null;

	/**
	 * @var ilObjTest
	 */
	public $testOBJ = null;

	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}

	public function rebuild(ilTestRandomQuestionSetConfig $questionSetConfig, ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->reset();

		$this->build($questionSetConfig, $sourcePoolDefinitionList);
	}

	private function reset()
	{
		$this->removeMirroredTaxonomies();

		$this->removeStagedQuestions();
	}

	private function removeMirroredTaxonomies()
	{
		$taxonomyIds = ilObjTaxonomy::getUsageOfObject($this->testOBJ->getId());

		foreach($taxonomyIds as $taxId)
		{
			$taxonomy = new ilObjTaxonomy($taxId);
			$taxonomy->delete();
		}
	}

	private function removeStagedQuestions()
	{
		$query = 'SELECT * FROM tst_rnd_cpy WHERE tst_fi = %s';
		$res = $this->db->queryF( $query, array('integer'), array($this->testOBJ->getTestId())
		);

		while( $row = $this->db->fetchAssoc($res) )
		{
			$question = assQuestion::_instanciateQuestion($row['qst_fi']);
			$question->delete($row['qst_fi']);
		}

		$query = "DELETE FROM tst_rnd_cpy WHERE tst_fi = %s";
		$this->db->manipulateF( $query, array('integer'), array($this->testOBJ->getTestId()) );
	}

	private function build(ilTestRandomQuestionSetConfig $questionSetConfig, ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->mirrorSourcePoolsTaxonomies(
			$sourcePoolDefinitionList->getInvolvedSourcePoolIds()
		);

		$this->stageQuestionsFromSourcePools($sourcePoolDefinitionList);
	}

	private function mirrorSourcePoolsTaxonomies($questionPoolIds)
	{
		foreach($questionPoolIds as $poolId)
		{
			$taxonomyIds = ilObjTaxonomy::getUsageOfObject($this->testOBJ->getId());

			foreach($taxonomyIds as $taxId)
			{
				$this->copyTaxonomyFromPoolToTest($taxId);
			}
		}
	}

	private function copyTaxonomyFromPoolToTest($poolTaxonomyId)
	{
		$testTaxonomy = new ilObjTaxonomy();
		$testTaxonomy->doCreate();

		$poolTaxonomy = new ilObjTaxonomy($poolTaxonomyId);
		$poolTaxonomy->doCloneObject($testTaxonomy, null, null);

		$testTaxonomy->doUpdate();

		ilObjTaxonomy::saveUsage( $testTaxonomy->getId(), $this->testOBJ->getId() );
	}

	private function stageQuestionsFromSourcePools(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		
	}
}