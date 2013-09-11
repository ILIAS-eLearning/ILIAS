<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author bheyser
 */
class ilTestRandomQuestionSetSourcePool
{
	/**
	 * global $ilDB object instance
	 *
	 * @var ilDB
	 */
	protected $db = null;
	
	/**
	 * object instance of current test
	 *
	 * @var ilObjTest
	 */
	protected $testOBJ = null;
	
	private $poolId = null;
	
	private $poolTitle = null;
	
	private $poolPath = null;
	
	private $poolQuestionCount = null;
	
	private $filterTaxId = null;
	
	private $filterNodeId = null;
	
	private $questionAmount = null;
	
	private $sequencePosition = null;
	
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}
	
	public function setPoolId($poolId)
	{
		$this->poolId = $poolId;
	}
	
	public function getPoolId()
	{
		return $this->poolId;
	}
	
	public function setPoolTitle($poolTitle)
	{
		$this->poolTitle = $poolTitle;
	}
	
	public function getPoolTitle()
	{
		return $this->poolTitle;
	}
	
	public function setPoolPath($poolPath)
	{
		$this->poolPath = $poolPath;
	}
	
	public function getPoolPath()
	{
		return $this->poolPath;
	}
	
	public function setPoolQuestionCount($poolQuestionCount)
	{
		$this->poolQuestionCount = $poolQuestionCount;
	}
	
	public function getPoolQuestionCount()
	{
		return $this->poolQuestionCount;
	}
	
	public function setFilterTaxId($filterTaxId)
	{
		$this->filterTaxId = $filterTaxId;
	}
	
	public function getFilterTaxId()
	{
		return $this->filterTaxId;
	}
	
	public function setFilterNodeId($filterNodeId)
	{
		$this->filterNodeId = $filterNodeId;
	}
	
	public function getFilterNodeId()
	{
		return $this->filterNodeId;
	}
	
	public function setQuestionAmount($questionAmount)
	{
		$this->questionAmount = $questionAmount;
	}
	
	public function getQuestionAmount()
	{
		return $this->questionAmount;
	}
	
	public function setSequencePosition($sequencePosition)
	{
		$this->sequencePosition = $sequencePosition;
	}
	
	public function getSequencePosition()
	{
		return $this->sequencePosition;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	/**
	 * @param array $dataArray
	 */
	public function initFromArray($dataArray)
	{
		foreach($dataArray as $field => $value)
		{
			switch($field)
			{
				case 'pool_fi':				$this->setPoolId($value);				break;
				case 'pool_title':			$this->setPoolTitle($value);			break;
				case 'pool_path':			$this->setPoolPath($value);				break;
				case 'pool_quest_count':	$this->setPoolQuestionCount($value);	break;
				case 'filter_tax_fi':		$this->setFilterTaxId($value);			break;
				case 'filter_node_fi':		$this->setFilterNodeId($value);			break;
				case 'quest_amount':		$this->setQuestionAmount($value);		break;
				case 'sequence_pos':		$this->setSequencePosition($value);		break;
			}
		}
	}
	
	/**
	 * @param integer $poolId
	 * @return boolean
	 */
	public function loadFromDb($poolId)
	{
		$res = $this->db->queryF(
				"SELECT * FROM tst_rnd_quest_set_qpls WHERE test_fi = %s AND pool_fi = %s",
				array('integer', 'integer'), array($this->testOBJ->getTestId(), $poolId)
		);
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->initFromArray($row);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public function saveToDb()
	{
		if( $this->dbRecordExists() )
		{
			return $this->updateDbRecord();
		}
		
		return $this->insertDbRecord();
	}
	
	/**
	 * @return boolean
	 */
	public function deleteFromDb()
	{
		$aff = $this->db->manipulateF(
				"DELETE FROM tst_rnd_quest_set_qpls WHERE test_fi = %s AND pool_fi = %s",
				array('integer', 'integer'), array($this->testOBJ->getTestId(), $this->getPoolId())
		);
		
		return (bool)$aff;
	}
	
	/**
	 * @return boolean
	 */
	private function dbRecordExists()
	{
		$res = $this->db->queryF(
			"SELECT COUNT(*) cnt FROM tst_rnd_quest_set_qpls WHERE test_fi = %s AND pool_fi = %s",
			array('integer', 'integer'), array($this->testOBJ->getTestId(), $this->getPoolId())
		);
		
		$row = $this->db->fetchAssoc($res);
		
		return (bool)$row['cnt'];
	}
	
	/**
	 * @return boolean
	 */
	private function updateDbRecord()
	{
		$aff = $this->db->update('tst_rnd_quest_set_qpls',
			array(
				'pool_title' => array('text', $this->getPoolTitle()),
				'pool_path' => array('text', $this->getPoolPath()),
				'pool_quest_count' => array('integer', $this->getPoolQuestionCount()),
				'filter_tax_fi' => array('integer', $this->getFilterTaxId()),
				'filter_node_fi' => array('integer', $this->getFilterNodeId()),
				'quest_amount' => array('integer', $this->getQuestionAmount()),
				'sequence_pos' => array('integer', $this->getSequencePosition())
			),
			array(
				'test_fi' => array('integer', $this->testOBJ->getTestId()),
				'pool_fi' => array('integer', $this->getPoolId())
			)
		);
		
		return (bool)$aff;
	}
	
	/**
	 * @return boolean
	 */
	private function insertDbRecord()
	{
		$aff = $this->db->insert('tst_rnd_quest_set_qpls', array(
				'test_fi' => array('integer', $this->testOBJ->getTestId()),
				'pool_fi' => array('integer', $this->getPoolId()),
				'pool_title' => array('text', $this->getPoolTitle()),
				'pool_path' => array('text', $this->getPoolPath()),
				'pool_quest_count' => array('integer', $this->getPoolQuestionCount()),
				'filter_tax_fi' => array('integer', $this->getFilterTaxId()),
				'filter_node_fi' => array('integer', $this->getFilterNodeId()),
				'quest_amount' => array('integer', $this->getQuestionAmount()),
				'sequence_pos' => array('integer', $this->getSequencePosition())
		));
		
		return (bool)$aff;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	public function getPoolInfoLabel(ilLanguage $lng)
	{
		$poolInfoLabel = sprintf(
			$lng->txt('tst_dynamic_question_set_source_questionpool_summary_string'),
			$this->getPoolTitle(),
			$this->getPoolPath(),
			$this->getPoolQuestionCount()
		);
		
		return $poolInfoLabel;
	}

	// -----------------------------------------------------------------------------------------------------------------
}
