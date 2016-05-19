<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpressionListImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImport
{
	/**
	 * @var integer
	 */
	private $targetParentObjId;
	
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
	 * @var integer
	 */
	private $importSkillPoints;
	
	/**
	 * @var string
	 */
	private $importEvalMode;
	
	/**
	 * @var ilAssQuestionSolutionComparisonExpressionList
	 */
	private $importSolutionComparisonExpressionList;
	
	/**
	 * ilAssQuestionSkillAssignmentImport constructor.
	 */
	public function __construct()
	{
		$this->importSolutionComparisonExpressionListImport = new ilAssQuestionSolutionComparisonExpressionListImport();
	}
	
	/**
	 * @param int $targetParentObjId
	 */
	public function setTargetParentObjId($targetParentObjId)
	{
		$this->targetParentObjId = $targetParentObjId;
	}
	
	/**
	 * @return int
	 */
	public function getTargetParentObjId()
	{
		return $this->targetParentObjId;
	}
	
	/**
	 * @param int $skillPoints
	 */
	public function setImportSkillPoints($importSkillPoints)
	{
		$this->importSkillPoints = $importSkillPoints;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillPoints()
	{
		return $this->importSkillPoints;
	}
	
	/**
	 * @param int $questionId
	 */
	public function setImportQuestionId($importQuestionId)
	{
		$this->importQuestionId = $importQuestionId;
	}
	
	/**
	 * @return int
	 */
	public function getImportQuestionId()
	{
		return $this->importQuestionId;
	}
	
	/**
	 * @param int $skillBaseId
	 */
	public function setImportSkillBaseId($importSkillBaseId)
	{
		$this->importSkillBaseId = $importSkillBaseId;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillBaseId()
	{
		return $this->importSkillBaseId;
	}
	
	/**
	 * @param int $skillTrefId
	 */
	public function setImportSkillTrefId($importSkillTrefId)
	{
		$this->importSkillTrefId = $importSkillTrefId;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillTrefId()
	{
		return $this->importSkillTrefId;
	}
	
	public function getImportEvalMode()
	{
		return $this->importEvalMode;
	}
	
	public function setImportEvalMode($importEvalMode)
	{
		$this->importEvalMode = $importEvalMode;
	}
	
	public function hasImportEvalModeBySolution()
	{
		return $this->getImportEvalMode() == ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION;
	}
	
	public function initImportSolutionComparisonExpressionList()
	{
		$this->importSolutionComparisonExpressionList->setImportQuestionId($this->getImportQuestionId());
		$this->importSolutionComparisonExpressionList->setImportSkillBaseId($this->getImportSkillBaseId());
		$this->importSolutionComparisonExpressionList->setImportSkillTrefId($this->getImportSkillTrefId());
	}
	
	public function getImportSolutionComparisonExpressionList()
	{
		return $this->importSolutionComparisonExpressionList;
	}
}