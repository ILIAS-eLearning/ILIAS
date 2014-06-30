<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetQuestion
{
	/**
	 * @var integer
	 */
	private $questionId = null;

	/**
	 * @var integer
	 */
	private $sequencePosition = null;

	/**
	 * @var integer
	 */
	private $sourcePoolDefinitionId = null;

	/**
	 * @param int $questionId
	 */
	public function setQuestionId($questionId)
	{
		$this->questionId = $questionId;
	}

	/**
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->questionId;
	}

	/**
	 * @param int $sequencePosition
	 */
	public function setSequencePosition($sequencePosition)
	{
		$this->sequencePosition = $sequencePosition;
	}

	/**
	 * @return int
	 */
	public function getSequencePosition()
	{
		return $this->sequencePosition;
	}

	/**
	 * @param int $sourcePoolDefinitionId
	 */
	public function setSourcePoolDefinitionId($sourcePoolDefinitionId)
	{
		$this->sourcePoolDefinitionId = $sourcePoolDefinitionId;
	}

	/**
	 * @return int
	 */
	public function getSourcePoolDefinitionId()
	{
		return $this->sourcePoolDefinitionId;
	}
}