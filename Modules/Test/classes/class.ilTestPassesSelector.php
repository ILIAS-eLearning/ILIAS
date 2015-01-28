<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPassesSelector
{
	protected $db;
	
	protected $testOBJ;
	
	private $activeId;
	
	private $lastFinishedPass;
	
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}

	public function getActiveId()
	{
		return $this->activeId;
	}

	public function setActiveId($activeId)
	{
		$this->activeId = $activeId;
	}

	public function getLastFinishedPass()
	{
		return $this->lastFinishedPass;
	}

	public function setLastFinishedPass($lastFinishedPass)
	{
		$this->lastFinishedPass = $lastFinishedPass;
	}

	public function getExistingPasses()
	{
		return $this->loadExistingPasses();
	}

	public function getNumExistingPasses()
	{
		return count($this->loadExistingPasses());
	}

	public function getReportablePasses()
	{
		$existingPasses = $this->loadExistingPasses();
		$reportablePasses = $this->fetchReportablePasses($existingPasses);

		return $reportablePasses;
	}
	
	private function loadExistingPasses()
	{
		$query = "
			SELECT DISTINCT tst_pass_result.pass FROM tst_pass_result
			INNER JOIN tst_test_result
			ON tst_pass_result.pass = tst_test_result.pass
			AND tst_pass_result.active_fi = tst_test_result.active_fi
			WHERE tst_pass_result.active_fi = %s
		";
		
		$res = $this->db->queryF(
			$query, array('integer'), array($this->getActiveId())
		);

		$existingPasses = array();
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$existingPasses[] = $row['pass'];
		}
		
		return $existingPasses;
	}
	
	private function fetchReportablePasses($existingPasses)
	{
		$lastPass = $this->fetchLastPass($existingPasses);
		
		$reportablePasses = array();
		
		foreach($existingPasses as $pass)
		{
			if( $this->isReportablePass($lastPass, $pass) )
			{
				$reportablePasses[] = $pass;
			}
		}
		
		return $reportablePasses;
	}
	
	private function fetchLastPass($existingPasses)
	{
		$lastPass = null;
		
		foreach($existingPasses as $pass)
		{
			if( $lastPass === null || $pass > $lastPass )
			{
				$lastPass = $pass;
			}
		}
		
		return $lastPass;
	}
	
	private function isReportablePass($lastPass, $pass)
	{
		if($pass < $lastPass)
		{
			return true;
		}
		
		if( $this->isClosedPass($pass) )
		{
			return true;
		}
		
		return false;
	}
	
	private function isClosedPass($pass)
	{
		if( $pass <= $this->getLastFinishedPass() )
		{
			return true;
		}
		
		if( $this->isProcessingTimeReached($pass) )
		{
			return true;
		}
		
		return false;
	}
	
	private function isProcessingTimeReached($pass)
	{
		if( !$this->testOBJ->getEnableProcessingTime() )
		{
			return false;
		}
		
		$startingTime = $this->testOBJ->getStartingTimeOfUser($this->getActiveId(), $pass);
		
		if($startingTime === FALSE)
		{
			return false;
		}

		return $this->testOBJ->isMaxProcessingTimeReached($startingTime, $this->getActiveId());
	}
}