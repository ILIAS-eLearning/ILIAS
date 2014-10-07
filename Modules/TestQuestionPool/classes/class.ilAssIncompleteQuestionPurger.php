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
	
	public function __construct(ilDB $db)
	{
		$this->db = $db;
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
		$query = "SELECT question_id FROM qpl_questions WHERE owner = %s AND tstamp = %s";
		
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
}
