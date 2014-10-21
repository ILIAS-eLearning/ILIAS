<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssIncompleteQuestionPurger
{
	/**
	 * @var ilDB
	 */
	protected $db;
	
	protected $ownerId;
	
	private $ignoredContainerObjectTypes;
	
	public function __construct(ilDB $db)
	{
		$this->db = $db;
		
		$this->ignoredContainerObjectTypes = array('lm');
	}

	public function getOwnerId()
	{
		return $this->ownerId;
	}

	public function setOwnerId($ownerId)
	{
		$this->ownerId = $ownerId;
	}
	
	public function purge()
	{
		$questionIds = $this->getPurgableQuestionIds();
		$this->purgeQuestionIds($questionIds);
	}
	
	private function getPurgableQuestionIds()
	{
		$INtypes = $this->db->in('object_data.type', $this->getIgnoredContainerObjectTypes(), true, 'text');
		
		$query = "
			SELECT qpl_questions.question_id
			FROM qpl_questions
			INNER JOIN object_data
			ON object_data.obj_id = qpl_questions.obj_fi
			AND $INtypes
			WHERE qpl_questions.owner = %s
			AND qpl_questions.tstamp = %s
		";
		
		$res = $this->db->queryF($query, array('integer', 'integer'), array($this->getOwnerId(), 0));
		
		$questionIds = array();
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$questionIds[] = $row['question_id'];
		}
		
		return $questionIds;
	}
	
	private function purgeQuestionIds($questionIds)
	{
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
		foreach($questionIds as $questionId)
		{
			$question = assQuestion::_instantiateQuestion($questionId);
			$question->delete($questionId);
		}
	}
	
	protected function setIgnoredContainerObjectTypes($ignoredContainerObjectTypes)
	{
		$this->ignoredContainerObjectTypes = $ignoredContainerObjectTypes;
	}
	
	protected function getIgnoredContainerObjectTypes()
	{
		return $this->ignoredContainerObjectTypes;
	}
}
