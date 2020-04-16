<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Test session handler
*
* This class manages the test session for a participant
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestSession
{
    const ACCESS_CODE_SESSION_INDEX = "tst_access_code";
    
    const ACCESS_CODE_CHAR_DOMAIN = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    
    const ACCESS_CODE_LENGTH = 5;
    
    /**
    * The unique identifier of the test session
    *
    * @var integer
    */
    public $active_id;

    /**
    * The user id of the participant
    *
    * @var integer
    */
    public $user_id;

    /**
    * The anonymous id of the participant
    *
    * @var integer
    */
    public $anonymous_id;

    /**
    * The database id of the test
    *
    * @var integer
    */
    public $test_id;

    /**
    * The last sequence of the participant
    *
    * @var integer
    */
    public $lastsequence;

    /**
     * @var string
     */
    protected $lastPresentationMode;

    /**
    * Indicates if the test was submitted already
    *
    * @var boolean
    */
    public $submitted;

    /**
    * The timestamp of the last session
    *
    * @var boolean
    */
    public $tstamp;

    /**
    * The timestamp of the test submission
    *
    * @var string
    */
    public $submittedTimestamp;

    private $lastFinishedPass;

    private $lastStartedPass;
    
    private $objectiveOrientedContainerId;

    /**
    * ilTestSession constructor
    *
    * The constructor takes possible arguments an creates an instance of
    * the ilTestSession object.
    *
    * @access public
    */
    public function __construct()
    {
        $this->active_id = 0;
        $this->user_id = 0;
        $this->anonymous_id = 0;
        $this->test_id = 0;
        $this->lastsequence = 0;
        $this->lastPresentationMode = null;
        $this->submitted = false;
        $this->submittedTimestamp = "";
        $this->pass = 0;
        $this->ref_id = 0;
        $this->tstamp = 0;

        $this->lastStartedPass = null;
        $this->lastFinishedPass = null;
        $this->objectiveOrientedContainerId = 0;
    }

    /**
     * Set Ref id
     *
     * @param	integer	Ref id
     */
    public function setRefId($a_val)
    {
        $this->ref_id = $a_val;
    }

    /**
     * Get Ref id
     *
     * @return	integer	Ref id
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    protected function activeIDExists($user_id, $test_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($GLOBALS['DIC']['ilUser']->getId() != ANONYMOUS_USER_ID) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
                array('integer','integer'),
                array($user_id, $test_id)
            );
            if ($result->numRows()) {
                $row = $ilDB->fetchAssoc($result);
                $this->active_id = $row["active_id"];
                $this->user_id = $row["user_fi"];
                $this->anonymous_id = $row["anonymous_id"];
                $this->test_id = $row["test_fi"];
                $this->lastsequence = $row["lastindex"];
                $this->pass = $row["tries"];
                $this->submitted = ($row["submitted"]) ? true : false;
                $this->submittedTimestamp = $row["submittimestamp"];
                $this->tstamp = $row["tstamp"];

                $this->setLastStartedPass($row['last_started_pass']);
                $this->setLastFinishedPass($row['last_finished_pass']);
                $this->setObjectiveOrientedContainerId((int) $row['objective_container']);

                return true;
            }
        }
        return false;
    }
    
    public function increaseTestPass()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        if (!$this->active_id) {
            require_once 'Modules/Test/exceptions/class.ilTestException.php';
            throw new ilTestException('missing active id on test pass increase!');
        }

        $this->increasePass();
        $this->setLastSequence(0);
        $submitted = ($this->isSubmitted()) ? 1 : 0;
        
        if (!isset($_SESSION[$this->active_id]['tst_last_increase_pass'])) {
            $_SESSION[$this->active_id]['tst_last_increase_pass'] = 0;
        }
        
        // there has to be at least 10 seconds between new test passes (to ensure that noone double clicks the finish button and increases the test pass by more than 1)
        if (time() - $_SESSION[$this->active_id]['tst_last_increase_pass'] > 10) {
            $_SESSION[$this->active_id]['tst_last_increase_pass'] = time();
            $this->tstamp = time();
            $ilDB->update(
                'tst_active',
                array(
                        'lastindex' => array('integer', $this->getLastSequence()),
                        'tries' => array('integer', $this->getPass()),
                        'submitted' => array('integer', $submitted),
                        'submittimestamp' => array('timestamp', strlen($this->getSubmittedTimestamp()) ? $this->getSubmittedTimestamp() : null),
                        'tstamp' => array('integer', time()),
                        'last_finished_pass' => array('integer', $this->getLastFinishedPass()),
                        'last_started_pass' => array('integer', $this->getLastStartedPass()),
                        'objective_container' => array('integer', (int) $this->getObjectiveOrientedContainerId())
                    ),
                array(
                        'active_id' => array('integer', $this->getActiveId())
                    )
            );
        }
    }
    
    public function saveToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        $submitted = ($this->isSubmitted()) ? 1 : 0;
        if ($this->active_id > 0) {
            $ilDB->update(
                'tst_active',
                array(
                    'lastindex' => array('integer', $this->getLastSequence()),
                    'tries' => array('integer', $this->getPass()),
                    'submitted' => array('integer', $submitted),
                    'submittimestamp' => array('timestamp', (strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : null),
                    'tstamp' => array('integer', time() - 10),
                    'last_finished_pass' => array('integer', $this->getLastFinishedPass()),
                    'last_started_pass' => array('integer', $this->getPass()),
                    'objective_container' => array('integer', (int) $this->getObjectiveOrientedContainerId())
                ),
                array(
                    'active_id' => array('integer', $this->getActiveId())
                )
            );
        } else {
            if (!$this->activeIDExists($this->getUserId(), $this->getTestId())) {
                $anonymous_id = ($this->getAnonymousId()) ? $this->getAnonymousId() : null;

                $next_id = $ilDB->nextId('tst_active');
                $ilDB->insert(
                    'tst_active',
                    array(
                        'active_id' => array('integer', $next_id),
                        'user_fi' => array('integer', $this->getUserId()),
                        'anonymous_id' => array('text', $anonymous_id),
                        'test_fi' => array('integer', $this->getTestId()),
                        'lastindex' => array('integer', $this->getLastSequence()),
                        'tries' => array('integer', $this->getPass()),
                        'submitted' => array('integer', $submitted),
                        'submittimestamp' => array('timestamp', (strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : null),
                        'tstamp' => array('integer', time() - 10),
                        'last_finished_pass' => array('integer', $this->getLastFinishedPass()),
                        'last_started_pass' => array('integer', $this->getPass()),
                        'objective_container' => array('integer', (int) $this->getObjectiveOrientedContainerId())
                    )
                );
                $this->active_id = $next_id;
            }
        }
    }
    
    public function loadTestSession($test_id, $user_id = "", $anonymous_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (!$user_id) {
            $user_id = $ilUser->getId();
        }
        if (($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) && $this->doesAccessCodeInSessionExists()) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                array('integer','integer','text'),
                array($user_id, $test_id, $this->getAccessCodeFromSession())
            );
        } elseif (strlen($anonymous_id)) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                array('integer','integer','text'),
                array($user_id, $test_id, $anonymous_id)
            );
        } else {
            if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) {
                return null;
            }
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
                array('integer','integer'),
                array($user_id, $test_id)
            );
        }

        // TODO bheyser: Refactor
        $this->user_id = $user_id;

        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $this->active_id = $row["active_id"];
            $this->user_id = $row["user_fi"];
            $this->anonymous_id = $row["anonymous_id"];
            $this->test_id = $row["test_fi"];
            $this->lastsequence = $row["lastindex"];
            $this->pass = $row["tries"];
            $this->submitted = ($row["submitted"]) ? true : false;
            $this->submittedTimestamp = $row["submittimestamp"];
            $this->tstamp = $row["tstamp"];

            $this->setLastStartedPass($row['last_started_pass']);
            $this->setLastFinishedPass($row['last_finished_pass']);
            $this->setObjectiveOrientedContainerId((int) $row['objective_container']);
        } elseif ($this->doesAccessCodeInSessionExists()) {
            $this->unsetAccessCodeInSession();
        }
    }
    
    /**
    * Loads the session data for a given active id
    *
    * @param integer $active_id The database id of the test session
    */
    public function loadFromDb($active_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $this->active_id = $row["active_id"];
            $this->user_id = $row["user_fi"];
            $this->anonymous_id = $row["anonymous_id"];
            $this->test_id = $row["test_fi"];
            $this->lastsequence = $row["lastindex"];
            $this->pass = $row["tries"];
            $this->submitted = ($row["submitted"]) ? true : false;
            $this->submittedTimestamp = $row["submittimestamp"];
            $this->tstamp = $row["tstamp"];

            $this->setLastStartedPass($row['last_started_pass']);
            $this->setLastFinishedPass($row['last_finished_pass']);
            $this->setObjectiveOrientedContainerId((int) $row['objective_container']);
        }
    }
    
    public function getActiveId()
    {
        return $this->active_id;
    }
    
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    public function setTestId($test_id)
    {
        $this->test_id = $test_id;
    }
    
    public function getTestId()
    {
        return $this->test_id;
    }
    
    public function setAnonymousId($anonymous_id)
    {
        $this->anonymous_id = $anonymous_id;
    }
    
    public function getAnonymousId()
    {
        return $this->anonymous_id;
    }

    public function setLastSequence($lastsequence)
    {
        $this->lastsequence = $lastsequence;
    }
    
    public function getLastSequence()
    {
        return $this->lastsequence;
    }
    
    public function setPass($pass)
    {
        $this->pass = $pass;
    }
    
    public function getPass()
    {
        return $this->pass;
    }

    public function increasePass()
    {
        $this->pass += 1;
    }

    public function isSubmitted()
    {
        return $this->submitted;
    }
    
    public function setSubmitted()
    {
        $this->submitted = true;
    }
    
    public function getSubmittedTimestamp()
    {
        return $this->submittedTimestamp;
    }
    
    public function setSubmittedTimestamp()
    {
        $this->submittedTimestamp = strftime("%Y-%m-%d %H:%M:%S");
    }

    public function setLastFinishedPass($lastFinishedPass)
    {
        $this->lastFinishedPass = $lastFinishedPass;
    }

    public function getLastFinishedPass()
    {
        return $this->lastFinishedPass;
    }

    public function setObjectiveOrientedContainerId($objectiveOriented)
    {
        $this->objectiveOrientedContainerId = $objectiveOriented;
    }

    public function getObjectiveOrientedContainerId()
    {
        return $this->objectiveOrientedContainerId;
    }

    /**
     * @return int
     */
    public function getLastStartedPass()
    {
        return $this->lastStartedPass;
    }

    /**
     * @param int $lastStartedPass
     */
    public function setLastStartedPass($lastStartedPass)
    {
        $this->lastStartedPass = $lastStartedPass;
    }

    public function isObjectiveOriented()
    {
        return (bool) $this->getObjectiveOrientedContainerId();
    }
    
    public function persistTestStartLock($testStartLock)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            'tst_active',
            array('start_lock' => array('text', $testStartLock)),
            array('active_id' => array('integer', $this->getActiveId()))
        );
    }

    public function lookupTestStartLock()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->queryF(
            "SELECT start_lock FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($this->getActiveId())
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            return $row['start_lock'];
        }
        
        return null;
    }

    public function setAccessCodeToSession($access_code)
    {
        if (!is_array($_SESSION[self::ACCESS_CODE_SESSION_INDEX])) {
            $_SESSION[self::ACCESS_CODE_SESSION_INDEX] = array();
        }
        
        $_SESSION[self::ACCESS_CODE_SESSION_INDEX][$this->getTestId()] = $access_code;
    }

    public function unsetAccessCodeInSession()
    {
        unset($_SESSION[self::ACCESS_CODE_SESSION_INDEX][$this->getTestId()]);
    }

    public function getAccessCodeFromSession()
    {
        if (!is_array($_SESSION[self::ACCESS_CODE_SESSION_INDEX])) {
            return null;
        }

        if (!isset($_SESSION[self::ACCESS_CODE_SESSION_INDEX][$this->getTestId()])) {
            return null;
        }

        return $_SESSION[self::ACCESS_CODE_SESSION_INDEX][$this->getTestId()];
    }
    
    public function doesAccessCodeInSessionExists()
    {
        if (!is_array($_SESSION[self::ACCESS_CODE_SESSION_INDEX])) {
            return false;
        }

        return isset($_SESSION[self::ACCESS_CODE_SESSION_INDEX][$this->getTestId()]);
    }

    public function createNewAccessCode()
    {
        do {
            $code = $this->buildAccessCode();
        } while ($this->isAccessCodeUsed($code));

        return $code;
    }

    public function isAccessCodeUsed($code)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT anonymous_id FROM tst_active WHERE test_fi = %s AND anonymous_id = %s";

        $result = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($this->getTestId(), $code)
        );
        
        return ($result->numRows() > 0);
    }

    private function buildAccessCode()
    {
        // create a 5 character code
        $codestring = self::ACCESS_CODE_CHAR_DOMAIN;

        mt_srand();

        $code = "";

        for ($i = 1; $i <= self::ACCESS_CODE_LENGTH; $i++) {
            $index = mt_rand(0, strlen($codestring) - 1);
            $code .= substr($codestring, $index, 1);
        }

        return $code;
    }
    
    public function isAnonymousUser()
    {
        return $this->getUserId() == ANONYMOUS_USER_ID;
    }
    
    /**
     * @var null|bool
     */
    private $reportableResultsAvailable = null;
    
    /**
     * @param ilObjTest $testOBJ
     * @return bool
     */
    public function reportableResultsAvailable(ilObjTest $testOBJ)
    {
        if ($this->reportableResultsAvailable === null) {
            $this->reportableResultsAvailable = true;
            
            if (!$this->getActiveId()) {
                $this->reportableResultsAvailable = false;
            }
            
            if (!$testOBJ->canShowTestResults($this)) {
                $this->reportableResultsAvailable = false;
            }
        }
        
        return $this->reportableResultsAvailable;
    }
    
    /**
     * @return bool
     */
    public function hasSinglePassReportable(ilObjTest $testObj)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $testPassesSelector = new ilTestPassesSelector($DIC->database(), $testObj);
        $testPassesSelector->setActiveId($this->getActiveId());
        $testPassesSelector->setLastFinishedPass($this->getLastFinishedPass());
        
        if (count($testPassesSelector->getReportablePasses()) == 1) {
            return true;
        }
        
        return false;
    }
}
