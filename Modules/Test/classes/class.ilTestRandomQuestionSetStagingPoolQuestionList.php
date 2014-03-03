<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles a list of questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 * 
 */
class ilTestRandomQuestionSetStagingPoolQuestionList implements Iterator
{
	/**
	 * @var ilDB
	 */
	private $db = null;
	
	/**
	 * @var ilPluginAdmin
	 */
	private $pluginAdmin = null;

	/**
	 * @var integer
	 */
	private $testObjId = -1;

	/**
	 * @var integer
	 */
	private $testId = -1;

	/**
	 * @var integer
	 */
	private $poolId = -1;

	/**
	 * @var array
	 */
	private $taxFilters = array();
	
	/**
	 * @var array
	 */
	private $questions = array();

	/**
	 * @param ilDB $db
	 * @param ilPluginAdmin $pluginAdmin
	 */
	public function __construct(ilDB $db, ilPluginAdmin $pluginAdmin)
	{
		$this->db = $db;
		$this->pluginAdmin = $pluginAdmin;
	}

	public function setTestObjId($testObjId)
	{
		$this->testObjId = $testObjId;
	}

	public function getTestObjId()
	{
		return $this->testObjId;
	}

	public function setTestId($testId)
	{
		$this->testId = $testId;
	}

	public function getTestId()
	{
		return $this->testId;
	}

	public function setPoolId($poolId)
	{
		$this->poolId = $poolId;
	}

	public function getPoolId()
	{
		return $this->poolId;
	}

	public function addTaxonomyFilter($taxId, $taxNodes)
	{
		$this->taxFilters[$taxId] = $taxNodes;
	}

	public function getTaxonomyFilters()
	{
		return $this->taxFilters;
	}

	public function loadQuestions()
	{		
		$query = "
			SELECT		qpl_questions.question_id,
						qpl_qst_type.type_tag,
						qpl_qst_type.plugin

			FROM		tst_rnd_cpy

			INNER JOIN	qpl_questions
			ON			qpl_questions.question_id = tst_rnd_cpy.qst_fi

			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi

			WHERE		tst_rnd_cpy.tst_fi = %s
			AND			tst_rnd_cpy.qpl_fi = %s

			{$this->getConditionalExpression()}
		";
		
		$res = $this->db->queryF(
			$query, array('integer', 'integer'), array($this->getTestId(), $this->getPoolId())
		);

		//vd($this->db->db->last_query);
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			if( !$this->isActiveQuestionType($row) )
			{
				continue;
			}

			$this->questions[] = $row['question_id'];
		}
	}

	private function getConditionalExpression()
	{
		$CONDITIONS = $this->getTaxonomyFilterExpressions();

		$CONDITIONS = implode(' AND ', $CONDITIONS);

		return strlen($CONDITIONS) ? 'AND '.$CONDITIONS : '';
	}

	private function getTaxonomyFilterExpressions()
	{
		$expressions = array();

		require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
		require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';

		foreach($this->getTaxonomyFilters() as $taxId => $taxNodes)
		{
			$questionIds = array();

			$forceBypass = true;

			foreach($taxNodes as $taxNode)
			{
				$forceBypass = false;

				$taxTree = new ilTaxonomyTree($taxId);

				$taxNodeAssignment = new ilTaxNodeAssignment('tst', $this->getTestObjId(), 'quest', $taxId);

				$subNodes = $taxTree->getSubTreeIds($taxNode);
				$subNodes[] = $taxNode;

				$taxItems = $taxNodeAssignment->getAssignmentsOfNode($subNodes);

				foreach($taxItems as $taxItem)
				{
					$questionIds[$taxItem['item_id']] = $taxItem['item_id'];
				}
			}

			if( !$forceBypass )
			{
				$expressions[] = $this->db->in('question_id', $questionIds, false, 'integer');
			}
		}

		return $expressions;
	}

	private function isActiveQuestionType($questionData)
	{
		if( !isset($questionData['plugin']) )
		{
			return false;
		}
		
		if( !$questionData['plugin'] )
		{
			return true;
		}
		
		return $this->pluginAdmin->isActive(IL_COMP_MODULE, 'TestQuestionPool', 'qst', $questionData['type_tag']);
	}

	public function resetQuestionList()
	{
		$this->questions = array();
		$this->taxFilters = array();

		$this->testObjId = -1;
		$this->testId = -1;
		$this->poolId = -1;

	}
	
	public function getQuestions()
	{
		return array_values($this->questions);
	}

	// =================================================================================================================

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function rewind()
	{
		return reset($this->questions);
	}

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function current()
	{
		return current($this->questions);
	}

	/**
	 * @return integer
	 */
	public function key()
	{
		return key($this->questions);
	}

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function next()
	{
		return next($this->questions);
	}

	/**
	 * @return boolean
	 */
	public function valid()
	{
		return key($this->questions) !== null;
	}
}
