<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolTaxonomiesDuplicator.php';
require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetStagingPoolBuilder
{
	/**
	 * @var ilDBInterface
	 */
	public $db = null;

	/**
	 * @var ilObjTest
	 */
	public $testOBJ = null;

	public function __construct(ilDBInterface $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}

	// =================================================================================================================

	public function rebuild(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->reset();

		$this->build($sourcePoolDefinitionList);
	}

	public function reset()
	{
		$this->removeMirroredTaxonomies();

		$this->removeStagedQuestions();
		
		$this->cleanupTestSettings();
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

			if( $question instanceof assQuestion )
			{
				$question->delete($row['qst_fi']);
			}
			else
			{
				$GLOBALS['ilLog']->warning(
					"could not delete staged random question (ref={$this->testOBJ->getRefId()} / qst={$row['qst_fi']})"
				);
			}
		}

		$query = "DELETE FROM tst_rnd_cpy WHERE tst_fi = %s";
		$this->db->manipulateF( $query, array('integer'), array($this->testOBJ->getTestId()) );
	}

	private function build(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$involvedSourcePoolIds = $sourcePoolDefinitionList->getInvolvedSourcePoolIds();

		foreach($involvedSourcePoolIds as $sourcePoolId)
		{
			$questionIdMapping = $this->stageQuestionsFromSourcePool($sourcePoolId);

			$taxonomiesKeysMap = $this->mirrorSourcePoolTaxonomies($sourcePoolId, $questionIdMapping);

			$this->applyMappedTaxonomiesKeys($sourcePoolDefinitionList, $taxonomiesKeysMap, $sourcePoolId);
		}
	}

	private function stageQuestionsFromSourcePool($sourcePoolId)
	{
		$questionIdMapping = array();

		$query = 'SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND complete = %s AND original_id IS NULL';
		$res = $this->db->queryF( $query, array('integer', 'text'), array($sourcePoolId, 1) );

		while( $row = $this->db->fetchAssoc($res) )
		{
			$question = assQuestion::_instanciateQuestion($row['question_id']);
			$duplicateId = $question->duplicate(true, null, null, null, $this->testOBJ->getId());

			$nextId = $this->db->nextId('tst_rnd_cpy');
			$this->db->insert('tst_rnd_cpy', array(
				'copy_id' => array('integer', $nextId),
				'tst_fi' => array('integer', $this->testOBJ->getTestId()),
				'qst_fi' => array('integer', $duplicateId),
				'qpl_fi' => array('integer', $sourcePoolId)
			));

			$questionIdMapping[ $row['question_id'] ] = $duplicateId;
		}

		return $questionIdMapping;
	}

	private function mirrorSourcePoolTaxonomies($sourcePoolId, $questionIdMapping)
	{
		$duplicator = new ilQuestionPoolTaxonomiesDuplicator();

		$duplicator->setSourceObjId($sourcePoolId);
		$duplicator->setSourceObjType('qpl');
		$duplicator->setTargetObjId($this->testOBJ->getId());
		$duplicator->setTargetObjType($this->testOBJ->getType());
		$duplicator->setQuestionIdMapping($questionIdMapping);

		$duplicator->duplicate();

		return $duplicator->getDuplicatedTaxonomiesKeysMap();
	}

	/**
	 * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
	 * @param ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap
	 * @param integer $sourcePoolId
	 */
	private function applyMappedTaxonomiesKeys(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList, ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap, $sourcePoolId)
	{
		foreach($sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			if($definition->getPoolId() == $sourcePoolId)
			{
				$definition->setMappedFilterTaxId(
					$taxonomiesKeysMap->getMappedTaxonomyId($definition->getOriginalFilterTaxId())
				);

				$definition->setMappedFilterTaxNodeId(
					$taxonomiesKeysMap->getMappedTaxNodeId($definition->getOriginalFilterTaxNodeId())
				);
			}
		}
	}
	
	private function cleanupTestSettings()
	{
		$this->testOBJ->setResultFilterTaxIds(array());
		$this->testOBJ->saveToDb(true);
	}

	// =================================================================================================================
}