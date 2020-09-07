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
    
    private $lastFinishedPass = null;
    
    private $passes = null;
    
    private $testPassedOnceCache = array();
    
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ)
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
        $lastFinishedPass = $lastFinishedPass === null ? -1 : $lastFinishedPass;
        $this->lastFinishedPass = $lastFinishedPass;
    }
    
    private function passesLoaded()
    {
        return is_array($this->passes);
    }
    
    private function ensureLoadedPasses()
    {
        if (!$this->passesLoaded()) {
            $this->loadPasses();
        }
    }
    private function loadPasses()
    {
        $query = "
			SELECT DISTINCT tst_pass_result.* FROM tst_pass_result
			LEFT JOIN tst_test_result
			ON tst_pass_result.pass = tst_test_result.pass
			AND tst_pass_result.active_fi = tst_test_result.active_fi
			WHERE tst_pass_result.active_fi = %s
			ORDER BY tst_pass_result.pass
		";
        
        $res = $this->db->queryF(
            $query,
            array('integer'),
            array($this->getActiveId())
        );
        
        $this->passes = array();
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->passes[$row['pass']] = $row;
        }
    }
    
    private function getLazyLoadedPasses()
    {
        $this->ensureLoadedPasses();
        return $this->passes;
    }

    public function loadLastFinishedPass()
    {
        $query = "
			SELECT last_finished_pass FROM tst_active WHERE active_id = %s
		";
        
        $res = $this->db->queryF(
            $query,
            array('integer'),
            array($this->getActiveId())
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->setLastFinishedPass($row['last_finished_pass']);
        }
    }

    public function getExistingPasses()
    {
        return array_keys($this->getLazyLoadedPasses());
    }
    
    public function hasExistingPasses()
    {
        return (bool) count($this->getExistingPasses());
    }

    public function getNumExistingPasses()
    {
        return count($this->getExistingPasses());
    }

    public function getClosedPasses()
    {
        $existingPasses = $this->getExistingPasses();
        $closedPasses = $this->fetchClosedPasses($existingPasses);

        return $closedPasses;
    }

    public function getReportablePasses()
    {
        $existingPasses = $this->getExistingPasses();
            
        $reportablePasses = $this->fetchReportablePasses($existingPasses);

        return $reportablePasses;
    }
    
    public function hasReportablePasses()
    {
        return (bool) count($this->getReportablePasses());
    }
    
    private function fetchReportablePasses($existingPasses)
    {
        $lastPass = $this->fetchLastPass($existingPasses);
        
        $reportablePasses = array();
        
        foreach ($existingPasses as $pass) {
            if ($this->isReportablePass($lastPass, $pass)) {
                $reportablePasses[] = $pass;
            }
        }
        
        return $reportablePasses;
    }
    
    private function fetchClosedPasses($existingPasses)
    {
        $closedPasses = array();
        
        foreach ($existingPasses as $pass) {
            if ($this->isClosedPass($pass)) {
                $closedPasses[] = $pass;
            }
        }
        
        return $closedPasses;
    }
    
    private function fetchLastPass($existingPasses)
    {
        $lastPass = null;
        
        foreach ($existingPasses as $pass) {
            if ($lastPass === null || $pass > $lastPass) {
                $lastPass = $pass;
            }
        }
        
        return $lastPass;
    }
    
    private function isReportablePass($lastPass, $pass)
    {
        switch ($this->testOBJ->getScoreReporting()) {
            case ilObjTest::SCORE_REPORTING_IMMIDIATLY:
                
                return true;
            
            case ilObjTest::SCORE_REPORTING_DATE:
                
                return $this->isReportingDateReached();
            
            case ilObjTest::SCORE_REPORTING_FINISHED:

                if ($pass < $lastPass) {
                    return true;
                }

                return $this->isClosedPass($pass);
                
            case ilObjTest::SCORE_REPORTING_AFTER_PASSED:
                
                if (!$this->hasTestPassedOnce($this->getActiveId())) {
                    return false;
                }
                
                return $this->isClosedPass($pass);
        }
        
        return false;
    }
    
    private function checkLastFinishedPassInitialised()
    {
        if ($this->getLastFinishedPass() === null) {
            throw new ilTestException('invalid object state: last finished pass was not set!');
        }
    }
    
    private function isClosedPass($pass)
    {
        $this->checkLastFinishedPassInitialised();
        
        if ($pass <= $this->getLastFinishedPass()) {
            return true;
        }
        
        if ($this->isProcessingTimeReached($pass)) {
            return true;
        }
        
        return false;
    }

    private function isReportingDateReached()
    {
        $reg = '/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/';
        $date = $this->testOBJ->getReportingDate();
        $matches = null;
        
        if (!preg_match($reg, $date, $matches)) {
            return false;
        }
        
        $repTS = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
        
        return time() >= $repTS;
    }
    
    private function isProcessingTimeReached($pass)
    {
        if (!$this->testOBJ->getEnableProcessingTime()) {
            return false;
        }
        
        $startingTime = $this->testOBJ->getStartingTimeOfUser($this->getActiveId(), $pass);
        
        if ($startingTime === false) {
            return false;
        }

        return $this->testOBJ->isMaxProcessingTimeReached($startingTime, $this->getActiveId());
    }
    
    /**
     * @return int timestamp
     */
    public function getLastFinishedPassTimestamp()
    {
        if ($this->getLastFinishedPass() === null) {
            return null;
        }
        
        $passes = $this->getLazyLoadedPasses();
        return $passes[$this->getLastFinishedPass()]['tstamp'];
    }
    
    public function hasTestPassedOnce($activeId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!isset($this->testPassedOnceCache[$activeId])) {
            $this->testPassedOnceCache[$activeId] = false;
            
            $res = $DIC->database()->queryF(
                "SELECT passed_once FROM tst_result_cache WHERE active_fi = %s",
                array('integer'),
                array($activeId)
            );
            
            while ($row = $DIC->database()->fetchAssoc($res)) {
                $this->testPassedOnceCache[$activeId] = (bool) $row['passed_once'];
            }
        }
        
        return $this->testPassedOnceCache[$activeId];
    }
}
