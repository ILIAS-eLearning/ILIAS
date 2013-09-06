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
	private $poolId = null;
	
	public function setPoolId($poolId)
	{
		$this->poolId = $poolId;
	}
	
	public function getPoolId()
	{
		return $this->poolId;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
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
				case 'source_qpl_fi':			$this->setSourceQuestionPoolId($value);		break;
				case 'source_qpl_title':		$this->setSourceQuestionPoolTitle($value);	break;
				case 'tax_filter_enabled':		$this->setTaxonomyFilterEnabled($value);	break;
				case 'order_tax':				$this->setOrderingTaxonomyId($value);		break;
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
				"SELECT * FROM tst_dyn_quest_set_pools WHERE test_fi = %s",
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
	 * 
	 * @return boolean
	 */
	public function saveToDb()
	{
		if( $this->dbRecordExists($this->testOBJ->getTestId()) )
		{
			return $this->updateDbRecord($this->testOBJ->getTestId());
		}
		
		return $this->insertDbRecord($this->testOBJ->getTestId());
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
	 * @return boolean
	 */
	private function dbRecordExists()
	{
		$res = $this->db->queryF(
			"SELECT COUNT(*) cnt FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
			array('integer'), array($this->testOBJ->getTestId())
		);
		
		$row = $this->db->fetchAssoc($res);
		
		return (bool)$row['cnt'];
	}
	
	/**
	 * updates the record in the database that corresponds
	 * to the question set config for the current test
	 * 
	 * @return boolean
	 */
	private function updateDbRecord()
	{
		$aff = $this->db->update('tst_dyn_quest_set_cfg',
			array(
				'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
				'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
				'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
				'order_tax' => array('integer', $this->getOrderingTaxonomyId())
			),
			array(
				'test_fi' => array('integer', $this->testOBJ->getTestId())
			)
		);
		
		return (bool)$aff;
	}
	
	/**
	 * inserts a new record for the question set config
	 * for the current test into the database
	 * 
	 * @return boolean
	 */
	private function insertDbRecord()
	{
		$aff = $this->db->insert('tst_dyn_quest_set_cfg', array(
				'test_fi' => array('integer', $this->testOBJ->getTestId()),
				'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
				'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
				'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
				'order_tax' => array('integer', $this->getOrderingTaxonomyId())
		));
		
		return (bool)$aff;
	}

	// -----------------------------------------------------------------------------------------------------------------
}
