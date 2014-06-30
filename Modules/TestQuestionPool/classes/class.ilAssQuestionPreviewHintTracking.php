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
		return false;
		
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
} 