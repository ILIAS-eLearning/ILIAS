<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestQuestionSetConfig.php';

/**
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilObjTestDynamicQuestionSetConfig extends ilTestQuestionSetConfig
{
	/**
	 * id of question pool to be used as source
	 *
	 * @var integer
	 */
	private $sourceQuestionPoolId = null;

	/**
	 * @var boolean
	 */
	private $answerStatusFilterEnabled = null;
	
	/**
	 * the fact wether a taxonomie filter
	 * can be used by test takers or not
	 *
	 * @var boolean 
	 */
	private $taxonomyFilterEnabled = null;

	/**
	 * the id of taxonomy used for ordering the questions
	 *
	 * @var integer
	 */
	private $orderingTaxonomyId = null;

	/**
	 * @var boolean
	 */
	private $previousQuestionsListEnabled = null;
	
	/**
	 * getter for source question pool id
	 * 
	 * @return integer
	 */
	public function getSourceQuestionPoolId()
	{
		return $this->sourceQuestionPoolId;
	}

	/**
	 * getter for source question pool id
	 * 
	 * @param integer $sourceQuestionPoolId
	 */
	public function setSourceQuestionPoolId($sourceQuestionPoolId)
	{
		$this->sourceQuestionPoolId = (int)$sourceQuestionPoolId;
	}
	
	/**
	 * getter for source question pool title
	 * 
	 * @return string
	 */
	public function getSourceQuestionPoolTitle()
	{
		return $this->sourceQuestionPoolTitle;
	}

	/**
	 * getter for source question pool title
	 * 
	 * @param string $sourceQuestionPoolTitle
	 */
	public function setSourceQuestionPoolTitle($sourceQuestionPoolTitle)
	{
		$this->sourceQuestionPoolTitle = $sourceQuestionPoolTitle;
	}

	/**
	 * @return boolean
	 */
	public function isAnswerStatusFilterEnabled()
	{
		return $this->answerStatusFilterEnabled;
	}

	/**
	 * @param boolean $answerStatusFilterEnabled
	 */
	public function setAnswerStatusFilterEnabled($answerStatusFilterEnabled)
	{
		$this->answerStatusFilterEnabled = $answerStatusFilterEnabled;
	}

	/**
	 * isser for taxonomie filter enabled
	 * 
	 * @return boolean
	 */
	public function isTaxonomyFilterEnabled()
	{
		return $this->taxonomyFilterEnabled;
	}

	/**
	 * setter for taxonomie filter enabled
	 * 
	 * @param boolean $taxonomyFilterEnabled
	 */
	public function setTaxonomyFilterEnabled($taxonomyFilterEnabled)
	{
		$this->taxonomyFilterEnabled = (bool)$taxonomyFilterEnabled;
	}
	
	/**
	 * setter for ordering taxonomy id
	 * 
	 * @return integer $orderingTaxonomyId
	 */
	public function getOrderingTaxonomyId()
	{
		return $this->orderingTaxonomyId;
	}
	
	/**
	 * getter for ordering taxonomy id
	 * 
	 * @param integer $orderingTaxonomyId
	 */
	public function setOrderingTaxonomyId($orderingTaxonomyId)
	{
		$this->orderingTaxonomyId = $orderingTaxonomyId;
	}

	/**
	 * @return boolean
	 */
	public function isPreviousQuestionsListEnabled()
	{
		return $this->previousQuestionsListEnabled;
	}

	/**
	 * @param boolean $previousQuestionsListEnabled
	 */
	public function setPreviousQuestionsListEnabled($previousQuestionsListEnabled)
	{
		$this->previousQuestionsListEnabled = $previousQuestionsListEnabled;
	}
	
	/**
	 * initialises the current object instance with values
	 * from matching properties within the passed array
	 * 
	 * @param array $dataArray
	 */
	public function initFromArray($dataArray)
	{
		foreach($dataArray as $field => $value)
		{
			switch($field)
			{
				case 'source_qpl_fi':				$this->setSourceQuestionPoolId($value);			break;
				case 'source_qpl_title':			$this->setSourceQuestionPoolTitle($value);		break;
				case 'answer_filter_enabled':		$this->setAnswerStatusFilterEnabled($value);	break;
				case 'tax_filter_enabled':			$this->setTaxonomyFilterEnabled($value);		break;
				case 'order_tax':					$this->setOrderingTaxonomyId($value);			break;
				case 'prev_quest_list_enabled':		$this->setPreviousQuestionsListEnabled($value);	break;
			}
		}
	}
	
	/**
	 * loads the question set config for current test from the database
	 * 
	 * @return boolean
	 */
	public function loadFromDb()
	{
		$res = $this->db->queryF(
				"SELECT * FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
				array('integer'), array($this->testOBJ->getTestId())
		);
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->initFromArray($row);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * saves the question set config for current test to the database
	 */
	public function saveToDb()
	{
		if( $this->dbRecordExists($this->testOBJ->getTestId()) )
		{
			$this->updateDbRecord($this->testOBJ->getTestId());
		}
		else
		{
			$this->insertDbRecord($this->testOBJ->getTestId());
		}
	}

	/**
	 * saves the question set config for test with given id to the database
	 *
	 * @param $testId
	 */
	public function cloneToDbForTestId($testId)
	{
		$this->insertDbRecord($testId);
	}

	/**
	 * deletes the question set config for current test from the database
	 *
	 * @return boolean
	 */
	public function deleteFromDb()
	{
		$aff = $this->db->manipulateF(
			"DELETE FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
			array('integer'), array($this->testOBJ->getTestId())
		);

		return (bool)$aff;
	}

	/**
	 * checks wether a question set config for current test exists in the database
	 *
	 * @param $testId
	 * @return boolean
	 */
	private function dbRecordExists($testId)
	{
		$res = $this->db->queryF(
			"SELECT COUNT(*) cnt FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
			array('integer'), array($testId)
		);
		
		$row = $this->db->fetchAssoc($res);
		
		return (bool)$row['cnt'];
	}

	/**
	 * updates the record in the database that corresponds
	 * to the question set config for the current test
	 *
	 * @param $testId
	 */
	private function updateDbRecord($testId)
	{
		$this->db->update('tst_dyn_quest_set_cfg',
			array(
				'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
				'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
				'answer_filter_enabled' => array('integer', $this->isAnswerStatusFilterEnabled()),
				'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
				'order_tax' => array('integer', $this->getOrderingTaxonomyId()),
				'prev_quest_list_enabled' => array('integer', $this->isPreviousQuestionsListEnabled())
			),
			array(
				'test_fi' => array('integer', $testId)
			)
		);
	}

	/**
	 * inserts a new record for the question set config
	 * for the current test into the database
	 *
	 * @param $testId
	 */
	private function insertDbRecord($testId)
	{
		$this->db->insert('tst_dyn_quest_set_cfg', array(
				'test_fi' => array('integer', $testId),
				'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
				'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
				'answer_filter_enabled' => array('integer', $this->isAnswerStatusFilterEnabled()),
				'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
				'order_tax' => array('integer', $this->getOrderingTaxonomyId()),
				'prev_quest_list_enabled' => array('integer', $this->isPreviousQuestionsListEnabled())
		));
	}
	
	/**
	 * returns the fact wether a useable question set config exists or not
	 * 
	 * @return boolean
	 */
	public function isQuestionSetConfigured()
	{
		return $this->getSourceQuestionPoolId() > 0;
	}
	
	/**
	 * returns the fact wether a useable question set config exists or not
	 * 
	 * @return boolean
	 */
	public function doesQuestionSetRelatedDataExist()
	{
		return $this->isQuestionSetConfigured();
	}
	
	/**
	 * removes all question set config related data
	 * (in this case it's only the config itself)
	 */
	public function removeQuestionSetRelatedData()
	{
		$this->deleteFromDb();
	}

	public function resetQuestionSetRelatedTestSettings()
	{
		// nothing to do
	}

	/**
	 * removes all question set config related data for cloned/copied test
	 *
	 * @param ilObjTest $cloneTestOBJ
	 */
	public function cloneQuestionSetRelatedData($cloneTestOBJ)
	{
		$this->loadFromDb();
		$this->cloneToDbForTestId($cloneTestOBJ->getTestId());
	}

	/**
	 * @param ilLanguage $lng
	 * @param ilTree $tree
	 * @return string
	 */
	public function getSourceQuestionPoolSummaryString(ilLanguage $lng)
	{
		$poolRefs = $this->getSourceQuestionPoolRefIds();
		
		if( !count($poolRefs) )
		{
			$sourceQuestionPoolSummaryString = sprintf(
					$lng->txt('tst_dyn_quest_set_src_qpl_summary_string_deleted'),
					$this->getSourceQuestionPoolTitle()
			);
			
			return $sourceQuestionPoolSummaryString;
		}
		
		foreach($poolRefs as $refId)
		{
			if( !$this->tree->isDeleted($refId) )
			{
				$sourceQuestionPoolSummaryString = sprintf(
					$lng->txt('tst_dynamic_question_set_source_questionpool_summary_string'),
					$this->getSourceQuestionPoolTitle(),
					$this->getQuestionPoolPathString($this->getSourceQuestionPoolId()),
					$this->getSourceQuestionPoolNumQuestions()
				);
				
				return $sourceQuestionPoolSummaryString;
			}
		}
		
		$sourceQuestionPoolSummaryString = sprintf(
				$lng->txt('tst_dyn_quest_set_src_qpl_summary_string_trashed'),
				$this->getSourceQuestionPoolTitle(),
				$this->getSourceQuestionPoolNumQuestions()
		);
		
		return $sourceQuestionPoolSummaryString;
	}
		
	/**
	 * @return integer
	 */
	private function getSourceQuestionPoolNumQuestions()
	{
		$query = "
			SELECT COUNT(*) num from qpl_questions
			WHERE obj_fi = %s AND original_id IS NULL
		";
		
		$res = $this->db->queryF(
				$query, array('integer'), array($this->getSourceQuestionPoolId())
		);
		
		$row = $this->db->fetchAssoc($res);
		
		return $row['num'];
	}
	
	public function areDepenciesInVulnerableState()
	{
		if( !$this->getSourceQuestionPoolId() )
		{
			return false;
		}
		
		$poolRefs = $this->getSourceQuestionPoolRefIds();
		
		foreach( $poolRefs as $refId )
		{
			if( !$this->tree->isDeleted($refId) )
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function getDepenciesInVulnerableStateMessage(ilLanguage $lng)
	{
		$msg = sprintf(
				$lng->txt('tst_dyn_quest_set_pool_trashed'), $this->getSourceQuestionPoolTitle()
		);
		
		return $msg;
	}
	
	public function areDepenciesBroken()
	{
		if( !$this->getSourceQuestionPoolId() )
		{
			return false;
		}
		
		$poolRefs = $this->getSourceQuestionPoolRefIds();

		if( count($poolRefs) )
		{
			return false;
		}
		
		return true;
	}
	
	public function getDepenciesBrokenMessage(ilLanguage $lng)
	{
		$msg = sprintf(
				$lng->txt('tst_dyn_quest_set_pool_deleted'), $this->getSourceQuestionPoolTitle()
		);
		
		return $msg;
	}
	
	private $sourceQuestionPoolRefIds = null;
	
	public function getSourceQuestionPoolRefIds()
	{
		if( $this->sourceQuestionPoolRefIds === null )
		{
			$this->sourceQuestionPoolRefIds = ilObject::_getAllReferences($this->getSourceQuestionPoolId());
		}
		
		return $this->sourceQuestionPoolRefIds;
	}
	
	/**
	 * @param integer $poolObjId
	 * @return \ilDynamicTestQuestionChangeListener
	 */
	public static function getPoolQuestionChangeListener(ilDB $db, $poolObjId)
	{
		$query = "
			SELECT obj_fi
			FROM tst_dyn_quest_set_cfg
			INNER JOIN tst_tests
			ON tst_tests.test_id = tst_dyn_quest_set_cfg.test_fi
			WHERE source_qpl_fi = %s
		";
		
		$res = $db->queryF($query, array('integer'), array($poolObjId));
		
		require_once 'Modules/Test/classes/class.ilDynamicTestQuestionChangeListener.php';
		$questionChangeListener = new ilDynamicTestQuestionChangeListener($db);
		
		while( $row = $db->fetchAssoc($res) )
		{
			$questionChangeListener->addTestObjId( $row['obj_fi'] );
		}
		
		return $questionChangeListener;
	}

	public function isResultTaxonomyFilterSupported()
	{
		return false;
	}
}
