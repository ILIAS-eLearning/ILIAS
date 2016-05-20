<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSolutionComparisonExpressionImportList
{
	/**
	 * @var integer
	 */
	private $importQuestionId;
	
	/**
	 * @var integer
	 */
	private $importSkillBaseId;
	
	/**
	 * @var integer
	 */
	private $importSkillTrefId;
	
	/**
	 * @var array
	 */
	private $expressions;
	
	/**
	 * ilAssQuestionSolutionComparisonExpressionImportList constructor.
	 */
	public function __construct()
	{
		$this->importQuestionId = null;
		$this->importSkillBaseId = null;
		$this->importSkillTrefId = null;
		
		$this->expressions = array();
	}
	
	/**
	 * @return int
	 */
	public function getImportQuestionId()
	{
		return $this->importQuestionId;
	}
	
	/**
	 * @param int $importQuestionId
	 */
	public function setImportQuestionId($importQuestionId)
	{
		$this->importQuestionId = $importQuestionId;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillBaseId()
	{
		return $this->importSkillBaseId;
	}
	
	/**
	 * @param int $importSkillBaseId
	 */
	public function setImportSkillBaseId($importSkillBaseId)
	{
		$this->importSkillBaseId = $importSkillBaseId;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillTrefId()
	{
		return $this->importSkillTrefId;
	}
	
	/**
	 * @param int $importSkillTrefId
	 */
	public function setImportSkillTrefId($importSkillTrefId)
	{
		$this->importSkillTrefId = $importSkillTrefId;
	}
	
	/**
	 * @return array
	 */
	public function get()
	{
		return $this->expressions;
	}
	
	public function add(ilAssQuestionSolutionComparisonExpressionImport $expression)
	{
		$expression->setImportQuestionId($this->getImportQuestionId());
		$expression->setImportSkillBaseId($this->getImportSkillBaseId());
		$expression->setImportSkillTrefId($this->getImportSkillTrefId());
		
		$this->expressions[$expression->getOrderIndex()] = $expression;
	}
}