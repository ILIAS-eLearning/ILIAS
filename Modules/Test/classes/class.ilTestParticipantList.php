<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestParticipant.php';

/**
 * Class ilTestParticipantList
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestParticipantList implements Iterator
{
	/**
	 * @var ilTestParticipant[]
	 */
	protected $participants = array();
	
	/**
	 * @var ilObjTest
	 */
	protected $testObj;
	
	/**
	 * @param ilObjTest $testObj
	 */
	public function __construct(ilObjTest $testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @return ilObjTest
	 */
	public function getTestObj()
	{
		return $this->testObj;
	}
	
	/**
	 * @param ilObjTest $testObj
	 */
	public function setTestObj($testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @param ilTestParticipant $participant
	 */
	public function addParticipant(ilTestParticipant $participant)
	{
		$this->participants[] = $participant;
	}
	
	public function getParticipantByUsrId($usrId)
	{
		foreach($this as $participant)
		{
			if( $participant->getUsrId() != $usrId )
			{
				continue;
			}
			
			return $participant;
		}
	}
	
	/**
	 * @return bool
	 */
	public function hasUnfinishedPasses()
	{
		foreach($this as $participant)
		{
			if( $participant->hasUnfinishedPasses() )
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @return bool
	 */
	public function hasTestResults()
	{
		foreach($this as $participant)
		{
			if( $participant->getActiveId() )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function getAllUserIds()
	{
		$usrIds = array();
		
		foreach($this as $participant)
		{
			$usrIds[] = $participant->getUsrId();
		}
		
		return $usrIds;
	}
	
	public function isActiveIdInList($activeId)
	{
		foreach($this as $participant)
		{
			if( $participant->getActiveId() == $activeId )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function getAccessFilteredList(callable $userAccessFilter)
	{
		$usrIds = call_user_func_array($userAccessFilter, [$this->getAllUserIds()]);
		
		$accessFilteredList = new self($this->getTestObj());
		
		foreach($usrIds as $usrId)
		{
			$participant = $this->getParticipantByUsrId($usrId);
			$participant = clone $participant;
			$accessFilteredList->addParticipant($participant);
		}
		
		return $accessFilteredList;
	}

	public function current() { return current($this->participants); }
	public function next() { return next($this->participants); }
	public function key() { return key($this->participants); }
	public function valid() { return key($this->participants) !== null; }
	public function rewind() { return reset($this->participants); }
	
	/**
	 * @param array[] $dbRows
	 */
	public function initializeFromDbRows($dbRows)
	{
		foreach($dbRows as $rowKey => $rowData)
		{
			$participant = new ilTestParticipant();
			
			if( (int)$rowData['active_id'] )
			{
				$participant->setActiveId((int)$rowData['active_id']);
			}
			
			$participant->setUsrId((int)$rowData['usr_id']);
			
			$participant->setLogin($rowData['login']);
			$participant->setLastname($rowData['lastname']);
			$participant->setFirstname($rowData['firstname']);
			$participant->setMatriculation($rowData['matriculation']);
			
			$participant->setActiveStatus((bool)$rowData['active']);
			
			if( isset($rowData['clientip']) )
			{
				$participant->setClientIp($rowData['clientip']);
			}
			
			$participant->setFinishedTries((int)$rowData['tries']);
			$participant->setTestFinished((bool)$rowData['test_finished']);
			$participant->setUnfinishedPasses((bool)$rowData['unfinished_passes']);
			
			$this->addParticipant($participant);
		}
	}
	
	public function getTableRows()
	{
		$rows = array();
		
		foreach($this as $participant)
		{
			$row = array(
				'usr_id' => $participant->getUsrId(),
				'active_id' => $participant->getActiveId(),
				'login' => $participant->getLogin(),
				'clientip' => $participant->getClientIp(),
				'firstname' => $participant->getFirstname(),
				'lastname' => $participant->getLastname(),
				'name' => $this->buildFullname($participant),
				'started' => ($participant->getActiveId() > 0) ? 1 : 0,
				'unfinished' => $participant->hasUnfinishedPasses() ? 1 : 0,
				'finished' => $participant->isTestFinished() ? 1 : 0,
				'access' => $this->lookupLastAccess($participant->getActiveId()),
				'tries' => $this->lookupNrOfTries($participant->getActiveId())
			);
			
			$rows[] = $row;
		}
		
		return $rows;
	}
	
	/**
	 * @param integer $activeId
	 * @return int|null
	 */
	public function lookupNrOfTries($activeId)
	{
		$maxPassIndex = ilObjTest::_getMaxPass($activeId);
		
		if( $maxPassIndex !== null )
		{
			$nrOfTries = $maxPassIndex + 1;
			return $nrOfTries;
		}
		
		return null;
	}
	
	/**
	 * @param integer $activeId
	 * @return string
	 */
	protected function lookupLastAccess($activeId)
	{
		if( !$activeId )
		{
			return '';
		}
		
		return $this->getTestObj()->_getLastAccess($activeId);
	}
	
	/**
	 * @param ilTestParticipant $participant
	 * @return string
	 */
	protected function buildFullname(ilTestParticipant $participant)
	{
		if( $this->getTestObj()->getFixedParticipants() && !$participant->getActiveId() )
		{
			return $this->buildInviteeFullname($participant);
		}
		
		return $this->buildParticipantsFullname($participant);
	}
	
	/**
	 * @param ilTestParticipant $participant
	 * @return string
	 */
	protected function buildInviteeFullname(ilTestParticipant $participant)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( strlen($participant->getFirstname().$participant->getLastname()) == 0 )
		{
			return $DIC->language()->txt("deleted_user");
		}
		
		if( $this->getTestObj()->getAnonymity() )
		{
			return $DIC->language()->txt('anonymous');
		}

		return trim($participant->getLastname() . ", " . $participant->getFirstname() );
	}
	
	/**
	 * @param ilTestParticipant $participant
	 * @return string
	 */
	protected function buildParticipantsFullname(ilTestParticipant $participant)
	{
		require_once 'Modules/Test/classes/class.ilObjTestAccess.php';
		return ilObjTestAccess::_getParticipantData($participant->getActiveId());
	}
}