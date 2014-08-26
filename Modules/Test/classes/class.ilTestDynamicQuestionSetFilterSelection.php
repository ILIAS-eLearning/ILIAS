<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestDynamicQuestionSetFilterSelection
{
	/**
	 * @var integer
	 */
	private $answerStatusActiveId = null;
	
	/**
	 * @var string
	 */
	private $answerStatusSelection = null;

	/**
	 * @var array
	 */
	private $taxonomySelection = array();

	/**
	 * @var array
	 */
	private $forcedQuestionIds = array();

	/**
	 * @param int $answerStatusActiveId
	 */
	public function setAnswerStatusActiveId($answerStatusActiveId)
	{
		$this->answerStatusActiveId = $answerStatusActiveId;
	}

	/**
	 * @return int
	 */
	public function getAnswerStatusActiveId()
	{
		return $this->answerStatusActiveId;
	}

	/**
	 * @param null $answerStatusSelection
	 */
	public function setAnswerStatusSelection($answerStatusSelection)
	{
		$this->answerStatusSelection = $answerStatusSelection;
	}

	/**
	 * @return null
	 */
	public function getAnswerStatusSelection()
	{
		return $this->answerStatusSelection;
	}

	/**
	 * @param array $taxonomySelection
	 */
	public function setTaxonomySelection($taxonomySelection)
	{
		$this->taxonomySelection = $taxonomySelection;
	}

	/**
	 * @return array
	 */
	public function getTaxonomySelection()
	{
		return $this->taxonomySelection;
	}

	/**
	 * @param array $forcedQuestionIds
	 */
	public function setForcedQuestionIds($forcedQuestionIds)
	{
		$this->forcedQuestionIds = $forcedQuestionIds;
	}

	/**
	 * @return array
	 */
	public function getForcedQuestionIds()
	{
		return $this->forcedQuestionIds;
	}
}