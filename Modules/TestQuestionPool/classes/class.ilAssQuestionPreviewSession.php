<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionPreviewSession
{
	const SESSION_BASEINDEX = 'ilAssQuestionPreviewSessions';
	
	const SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE = 'instantResponseActive';
	const SESSION_SUBINDEX_PARTICIPANT_SOLUTION = 'participantSolution';

	private $userId;
	private $questionId;
	
	public function __construct($userId, $questionId)
	{
		$this->userId = $userId;
		$this->questionId = $questionId;
	}
	
	public function init()
	{
		if( !isset($_SESSION[self::SESSION_BASEINDEX]) || !is_array($_SESSION[self::SESSION_BASEINDEX]) )
		{
			$_SESSION[self::SESSION_BASEINDEX] = array();
		}
		
		$baseSession = &$_SESSION[self::SESSION_BASEINDEX];
		
		if( !isset($baseSession[$this->getSessionContextIndex()]) )
		{
			$baseSession[$this->getSessionContextIndex()] = array();
		}

		$contextSession = &$baseSession[$this->getSessionContextIndex()];

		if( !isset($contextSession[self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE]) )
		{
			$contextSession[self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE] = 0;
		}
		
		if( !isset($contextSession[self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION]) )
		{
			$contextSession[self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION] = null;
		}
	}
	
	public function getUserId()
	{
		return $this->userId;
	}
	
	public function getQuestionId()
	{
		return $this->questionId;
	}
	
	private function getSessionContextIndex()
	{
		return "u{$this->userId}::q{$this->questionId}";
	}
	
	private function saveSessionValue($subIndex, $value)
	{
		$_SESSION[self::SESSION_BASEINDEX][$this->getSessionContextIndex()][$subIndex] = $value;
	}
	
	private function readSessionValue($subIndex)
	{
		return $_SESSION[self::SESSION_BASEINDEX][$this->getSessionContextIndex()][$subIndex];
	}

	public function setInstantResponseActive($instantResponseActive)
	{
		$this->saveSessionValue(self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE, $instantResponseActive);
	}
	
	public function isInstantResponseActive()
	{
		return $this->readSessionValue(self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE);
	}
	
	public function setParticipantsSolution($participantSolution)
	{
		$this->saveSessionValue(self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION, $participantSolution);
	}

	public function getParticipantsSolution()
	{
		return $this->readSessionValue(self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION);
	}
	
	public function getNumRequestedHints()
	{
		return 0;
	}
}