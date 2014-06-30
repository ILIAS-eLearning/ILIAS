<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionPreviewHintTracking
{
	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var ilAssQuestionPreviewSession
	 */
	private $previewSession;
	
	public function __construct(ilDB $db, ilAssQuestionPreviewSession $previewSession)
	{
		$this->db = $db;
		$this->previewSession = $previewSession;
	}
	
	public function requestsExist()
	{
		return (
			$this->previewSession->getNumRequestedHints() > 0
		);
	}
	
	public function requestsPossible()
	{
		$query = "
			SELECT		COUNT(qht_hint_id) cnt_available
			FROM		qpl_hints
			WHERE		qht_question_fi = %s
		";

		$res = $this->db->queryF(
			$query, array('integer'), array($this->previewSession->getQuestionId())
		);

		$row = $this->db->fetchAssoc($res);

		if( $row['cnt_available'] > $this->previewSession->getNumRequestedHints() )
		{
			return true;
		}

		return false;
	}
	
	public function getNextRequestableHint()
	{
		$query = "
			SELECT		qht_hint_id
			
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
			
			ORDER BY	qht_hint_index ASC
		";

		$res = $this->db->queryF(
			$query, array('integer'), array($this->previewSession->getQuestionId())
		);

		while( $row = $this->db->fetchAssoc($res) )
		{
			if( !$this->isRequested($row['qht_hint_id']) )
			{
				return ilAssQuestionHint::getInstanceById($row['qht_hint_id']);
			}
		}

		throw new ilTestException(
			"no next hint found for questionId={$this->previewSession->getQuestionId()}, userId={$this->previewSession->getUserId()}"
		);
	}

	public function storeRequest(ilAssQuestionHint $questionHint)
	{
		$this->previewSession->addRequestedHint($questionHint->getId());
	}
	
	public function isRequested($hintId)
	{
		return $this->previewSession->isHintRequested($hintId);
	}

	public function getNumExistingRequests()
	{
		return $this->previewSession->getNumRequestedHints();
	}

	public function getRequestedHintsList()
	{
		$hintIds = $this->previewSession->getRequestedHints();

		$requestedHintsList = ilAssQuestionHintList::getListByHintIds($hintIds);

		return $requestedHintsList;
	}
} 