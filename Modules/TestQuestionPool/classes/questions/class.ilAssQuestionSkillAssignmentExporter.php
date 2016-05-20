<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentExporter
{
	/**
	 * @var ilXmlWriter
	 */
	protected $xmlWriter;

	/**
	 * @var integer
	 */
	protected $parentObjId;

	/**
	 * @var array
	 */
	protected $questionIds;

	/**
	 * ilAssQuestionSkillAssignmentExporter constructor.
	 */
	public function __construct()
	{
		$this->xmlWriter = null;
		$this->questionIds = array();
	}

	/**
	 * @return ilXmlWriter
	 */
	public function getXmlWriter()
	{
		return $this->xmlWriter;
	}

	/**
	 * @param ilXmlWriter $xmlWriter
	 */
	public function setXmlWriter(ilXmlWriter $xmlWriter)
	{
		$this->xmlWriter = $xmlWriter;
	}

	/**
	 * @return int
	 */
	public function getParentObjId()
	{
		return $this->parentObjId;
	}

	/**
	 * @param int $parentObjId
	 */
	public function setParentObjId($parentObjId)
	{
		$this->parentObjId = $parentObjId;
	}

	/**
	 * @return array
	 */
	public function getQuestionIds()
	{
		return $this->questionIds;
	}

	/**
	 * @param array $questionIds
	 */
	public function setQuestionIds($questionIds)
	{
		$this->questionIds = $questionIds;
	}

	public function exportSkillAssignments()
	{
		global $ilDB;

		$this->getXmlWriter()->xmlStartTag('QuestionSkillAssignments');

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);

		$assignmentList->setParentObjId($this->getParentObjId());
		$assignmentList->loadFromDb();
		$assignmentList->loadAdditionalSkillData();

		foreach($this->getQuestionIds() as $questionId)
		{
			$this->getXmlWriter()->xmlStartTag('TriggerQuestion', array('Id' => $questionId));

			foreach($assignmentList->getAssignmentsByQuestionId($questionId) as $questionSkillAssignment)
			{
				/* @var ilAssQuestionSkillAssignment $questionSkillAssignment */

				$this->getXmlWriter()->xmlStartTag('TriggeredSkill', array(
					'SkillBaseId' => $questionSkillAssignment->getSkillBaseId(),
					'SkillTrefId' => $questionSkillAssignment->getSkillTrefId()
				));
				
				$this->getXmlWriter()->xmlElement(
					'OriginalSkillTitle', null, $questionSkillAssignment->getSkillTitle()
				);
				
				$this->getXmlWriter()->xmlElement(
					'OriginalSkillPath', null, $questionSkillAssignment->getSkillPath()
				);

				switch( $questionSkillAssignment->getEvalMode() )
				{
					case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT:

						$this->getXmlWriter()->xmlElement('EvalByQuestionResult', array(
							'SkillPoints' => $questionSkillAssignment->getSkillPoints()
						));
						break;

					case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION:

						$this->getXmlWriter()->xmlStartTag('EvalByQuestionSolution');

						$questionSkillAssignment->initSolutionComparisonExpressionList();
						$expressionList = $questionSkillAssignment->getSolutionComparisonExpressionList();

						foreach($expressionList->get() as $expression)
						{
							/* @var ilAssQuestionSolutionComparisonExpression $expression */

							$this->getXmlWriter()->xmlStartTag('SolutionComparisonExpression', array(
								'SkillPoints' => $expression->getPoints(),
								'OrderIndex' => $expression->getOrderIndex()
							));

							$this->getXmlWriter()->xmlData($expression->getExpression(), false, true);

							$this->getXmlWriter()->xmlEndTag('SolutionComparisonExpression');
						}

						$this->getXmlWriter()->xmlEndTag('EvalByQuestionSolution');
						break;
				}

				$this->getXmlWriter()->xmlEndTag('TriggeredSkill');
			}

			$this->getXmlWriter()->xmlEndTag('TriggerQuestion');
		}

		$this->getXmlWriter()->xmlEndTag('QuestionSkillAssignments');
	}
}