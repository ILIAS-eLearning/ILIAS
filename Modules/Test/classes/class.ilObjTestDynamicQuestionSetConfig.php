<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilObjTestDynamicQuestionSetConfig
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
	
	/**
	 * id of question pool to be used as source
	 *
	 * @var integer
	 */
	private $sourceQuestionPoolId = null;
	
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
	 * Constructor
	 */
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}
	
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
	
	/**
	 * returns the fact wether a question set config exists or not
	 * 
	 * @return boolean
	 */
	public function isQuestionSetConfigured()
	{
		return $this->getSourceQuestionPoolId() > 0;
	}
	
	/**
	 * removes all question set config related data
	 * (in this case it's only the config itself)
	 */
	public function removeQuestionSetRelatedData()
	{
		$this->deleteFromDb();
	}
}
