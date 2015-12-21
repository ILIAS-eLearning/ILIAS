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
	
	private $adminModeEnabled;
	
	private $activeId;
	
	private $lastFinishedPass;
	
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;

		$this->adminModeEnabled = false;
	}

	public function isAdminModeEnabled()
	{
		return $this->adminModeEnabled;
	}

	public function setAdminModeEnabled($adminModeEnabled)
	{
		$this->adminModeEnabled = $adminModeEnabled;
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

	public function getClosedPasses()
	{
		$existingPasses = $this->loadExistingPasses();
		$closedPasses = $this->fetchClosedPasses($existingPasses);

		return $closedPasses;
	}

	public function getReportablePasses()
	{
		$existingPasses = $this->loadExistingPasses();
		
		if( $this->isAdminModeEnabled() )
		{
			return $existingPasses;
		}
			
		$reportablePasses = $this->fetchReportablePasses($existingPasses);

		return $reportablePasses;
	}
	
	private function loadExistingPasses()
	{
		$query = "
			SELECT DISTINCT tst_pass_result.pass FROM tst_pass_result
			LEFT JOIN tst_test_result
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
	
	private function fetchClosedPasses($existingPasses)
	{
		$closedPasses = array();
		
		foreach($existingPasses as $pass)
		{
			if( $this->isClosedPass($pass) )
			{
				$closedPasses[] = $pass;
			}
		}
		
		return $closedPasses;
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
		switch( $this->testOBJ->getScoreReporting() )
		{
			case ilObjTest::SCORE_REPORTING_IMMIDIATLY:
				
				return true;
			
			case ilObjTest::SCORE_REPORTING_DATE:
				
				return $this->isReportingDateReached();
			
			case ilObjTest::SCORE_REPORTING_FINISHED:

				if($pass < $lastPass)
				{
					return true;
				}

				return $this->isClosedPass($pass);
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

	private function isReportingDateReached()
	{
		$reg = '/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/';
		$date = $this->testOBJ->getReportingDate();
		$matches = null;
		
		if( !preg_match($reg, $date, $matches) )
		{
			return false;
		}
		
		$repTS = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
		
		return time() >= $repTS;
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