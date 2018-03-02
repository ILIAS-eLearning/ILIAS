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
	
	public function getAccessFilteredList(callable $userAccessFilter)
	{
		$usrIds = call_user_func_array($userAccessFilter, [$this->getAllUserIds()]);
		
		$accessFilteredList = new self($this->getTestObj());
		
		foreach($usrIds as $usrId)
		{
			$participant = $this->getParticipantByUsrId($usrId);
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
			
			if( isset($rowData['client_ip']) )
			{
				$participant->setClientIp($rowData['client_ip']);
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
				'usr_id' => $data["usr_id"],
				'active_id' => $data['active_id'],
				'login' => $data["login"],
				'clientip' => $data["clientip"],
				'firstname' => $data["firstname"],
				'lastname' => $data["lastname"],
				'name' => $fullname,
				'started' => ($data["active_id"] > 0) ? 1 : 0,
				'unfinished' => $unfinished_pass_data,
				'finished' => ($data["test_finished"] == 1) ? 1 : 0,
				'access' => $access,
				'maxpass' => $maxpass,
				'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview'),
				'finish_link' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'finishTestPassForSingleUser')
			);
			
			$rows[] = $row;
		}
		
		return $rows;
	}
}