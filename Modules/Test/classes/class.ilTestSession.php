<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public const ACCESS_CODE_SESSION_INDEX = "tst_access_code";
    public const ACCESS_CODE_CHAR_DOMAIN = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    public const ACCESS_CODE_LENGTH = 5;

    protected ilDBInterface $db;
    protected ilObjUser $user;

    private int $ref_id;
    private int $pass;
    public int $active_id;
    public int $user_id;
    /** @var int|string|null */
    public $anonymous_id = null;
    public int $test_id;
    public int $lastsequence;
    protected ?string $lastPresentationMode;
    public bool $submitted;
    public int $tstamp;
    public ?string $submittedTimestamp;
    private int $objectiveOrientedContainerId;

    private $lastFinishedPass;
    private $lastStartedPass;


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
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->user = $DIC['ilUser'];

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

    public function setRefId(int $a_val): void
    {
        $this->ref_id = $a_val;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    protected function activeIDExists($user_id, $test_id): bool
    {
        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            $result = $this->db->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
                ['integer','integer'],
                [$user_id, $test_id]
            );
            if ($result->numRows()) {
                $row = $this->db->fetchAssoc($result);
                $this->active_id = (int) $row["active_id"];
                $this->user_id = (int) $row["user_fi"];
                $this->anonymous_id = $row["anonymous_id"];
                $this->test_id = (int) $row["test_fi"];
                $this->lastsequence = (int) $row["lastindex"];
                $this->pass = (int) $row["tries"];
                $this->submitted = ($row["submitted"]) ? true : false;
                $this->submittedTimestamp = (string) $row["submittimestamp"];
                $this->tstamp = (int) $row["tstamp"];

                $this->setLastStartedPass($row['last_started_pass']);
                $this->setLastFinishedPass($row['last_finished_pass']);
                $this->setObjectiveOrientedContainerId((int) $row['objective_container']);

                return true;
            }
        }
        return false;
    }

    public function increaseTestPass(): void
    {
        if (!$this->active_id) {
            throw new ilTestException('missing active id on test pass increase!');
        }

        $this->increasePass();
        $this->setLastSequence(0);
        $submitted = ($this->isSubmitted()) ? 1 : 0;
        $active = ilSession::get($this->active_id);
        if (!isset($active['tst_last_increase_pass']) || $active['tst_last_increase_pass'] !== null) {
            $active['tst_last_increase_pass'] = 0;
            //ilSession::set($this->active_id, $active);
            //$_SESSION[$this->active_id]['tst_last_increase_pass'] = 0;
        }

        // there has to be at least 10 seconds between new test passes (to ensure that noone double clicks the finish button and increases the test pass by more than 1)
        if (time() - $active['tst_last_increase_pass'] > 10) {
            $active['tst_last_increase_pass'] = time();
            //ilSession::set($this->active_id, $active);
            $this->tstamp = time();
            $submittedtimestamp = $this->getSubmittedTimestamp() !== null && $this->getSubmittedTimestamp() !== '' ? $this->getSubmittedTimestamp() : null;
            $this->db->update(
                'tst_active',
                [
                    'lastindex' => ['integer', $this->getLastSequence()],
                    'tries' => ['integer', $this->getPass()],
                    'submitted' => ['integer', $submitted],
                    'submittimestamp' => ['timestamp', $submittedtimestamp],
                    'tstamp' => ['integer', time()],
                    'last_finished_pass' => ['integer', $this->getLastFinishedPass()],
                    'last_started_pass' => ['integer', $this->getLastStartedPass()],
                    'objective_container' => ['integer', $this->getObjectiveOrientedContainerId()]
                ],
                [
                    'active_id' => ['integer', $this->getActiveId()]
                ]
            );
        }
    }

    public function saveToDb(): void
    {
        $submitted = ($this->isSubmitted()) ? 1 : 0;
        $submittedtimestamp = $this->getSubmittedTimestamp() !== null && $this->getSubmittedTimestamp() !== '' ? $this->getSubmittedTimestamp() : null;
        if ($this->active_id > 0) {
            $this->db->update(
                'tst_active',
                [
                    'lastindex' => ['integer', $this->getLastSequence()],
                    'tries' => ['integer', $this->getPass()],
                    'submitted' => ['integer', $submitted],
                    'submittimestamp' => ['timestamp', $submittedtimestamp],
                    'tstamp' => ['integer', time() - 10],
                    'last_finished_pass' => ['integer', $this->getLastFinishedPass()],
                    'last_started_pass' => ['integer', $this->getPass()],
                    'objective_container' => ['integer', $this->getObjectiveOrientedContainerId()]
                ],
                [
                    'active_id' => ['integer', $this->getActiveId()]
                ]
            );
        } else {
            if (!$this->activeIDExists($this->getUserId(), $this->getTestId())) {
                $anonymous_id = $this->getAnonymousId() ?: null;

                $next_id = $this->db->nextId('tst_active');
                $this->db->insert(
                    'tst_active',
                    [
                        'active_id' => ['integer', $next_id],
                        'user_fi' => ['integer', $this->getUserId()],
                        'anonymous_id' => ['text', $anonymous_id],
                        'test_fi' => ['integer', $this->getTestId()],
                        'lastindex' => ['integer', $this->getLastSequence()],
                        'tries' => ['integer', $this->getPass()],
                        'submitted' => ['integer', $submitted],
                        'submittimestamp' => ['timestamp', (strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : null],
                        'tstamp' => ['integer', time() - 10],
                        'last_finished_pass' => ['integer', $this->getLastFinishedPass()],
                        'last_started_pass' => ['integer', $this->getPass()],
                        'objective_container' => ['integer', $this->getObjectiveOrientedContainerId()]
                    ]
                );
                $this->active_id = $next_id;
            }
        }
    }

    public function loadTestSession($test_id, $user_id = "", $anonymous_id = ""): void
    {
        if (!$user_id) {
            $user_id = $this->user->getId();
        }
        if (($this->user->getId() == ANONYMOUS_USER_ID) && $this->doesAccessCodeInSessionExists()) {
            $result = $this->db->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                ['integer','integer','text'],
                [$user_id, $test_id, $this->getAccessCodeFromSession()]
            );
        } elseif ($anonymous_id && $anonymous_id !== '') {
            $result = $this->db->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                ['integer','integer','text'],
                [$user_id, $test_id, $anonymous_id]
            );
        } else {
            if ($this->user->getId() == ANONYMOUS_USER_ID) {
                return;
            }
            $result = $this->db->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
                ['integer','integer'],
                [$user_id, $test_id]
            );
        }

        // TODO bheyser: Refactor
        $this->user_id = $user_id;

        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
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
        $result = $this->db->queryF(
            "SELECT * FROM tst_active WHERE active_id = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
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

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setTestId($test_id): void
    {
        $this->test_id = $test_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    /**
     * @param int|null|string $anonymous_id
     * @return void
     */
    public function setAnonymousId($anonymous_id): void
    {
        $this->anonymous_id = $anonymous_id;
    }

    /**
     * @return int|string|null
     */
    public function getAnonymousId()
    {
        return $this->anonymous_id;
    }

    public function setLastSequence($lastsequence)
    {
        $this->lastsequence = $lastsequence;
    }

    public function getLastSequence(): int
    {
        return $this->lastsequence;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function increasePass()
    {
        $this->pass += 1;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function setSubmitted()
    {
        $this->submitted = true;
    }

    public function getSubmittedTimestamp(): ?string
    {
        return $this->submittedTimestamp;
    }

    public function setSubmittedTimestamp()
    {
        $this->submittedTimestamp = date('Y-m-d H:i:s');
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

    public function getObjectiveOrientedContainerId(): int
    {
        return $this->objectiveOrientedContainerId;
    }

    /**
     * @return int
     */
    public function getLastStartedPass(): ?int
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

    public function isObjectiveOriented(): bool
    {
        return (bool) $this->getObjectiveOrientedContainerId();
    }

    public function persistTestStartLock($testStartLock)
    {
        $this->db->update(
            'tst_active',
            ['start_lock' => ['text', $testStartLock]],
            ['active_id' => ['integer', $this->getActiveId()]]
        );
    }

    public function lookupTestStartLock()
    {
        $res = $this->db->queryF(
            "SELECT start_lock FROM tst_active WHERE active_id = %s",
            ['integer'],
            [$this->getActiveId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            return $row['start_lock'];
        }

        return null;
    }

    public function setAccessCodeToSession($access_code)
    {
        if (!is_array(ilSession::get(self::ACCESS_CODE_SESSION_INDEX))) {
            ilSession::set(self::ACCESS_CODE_SESSION_INDEX, []);
        }
        $session_code = ilSession::get(self::ACCESS_CODE_SESSION_INDEX);
        $session_code[$this->getTestId()] = $access_code;
        ilSession::set(self::ACCESS_CODE_SESSION_INDEX, $session_code);
    }

    public function unsetAccessCodeInSession()
    {
        $session_code = ilSession::get(self::ACCESS_CODE_SESSION_INDEX);
        unset($session_code[$this->getTestId()]);
        ilSession::set(self::ACCESS_CODE_SESSION_INDEX, $session_code);
    }

    public function getAccessCodeFromSession()
    {
        if (!is_array(ilSession::get(self::ACCESS_CODE_SESSION_INDEX))) {
            return null;
        }
        $session_code = ilSession::get(self::ACCESS_CODE_SESSION_INDEX);
        if (!isset($session_code[$this->getTestId()])) {
            return null;
        }

        return $session_code[$this->getTestId()];
    }

    public function doesAccessCodeInSessionExists(): bool
    {
        if (!is_array(ilSession::get(self::ACCESS_CODE_SESSION_INDEX))) {
            return false;
        }
        $session_code = ilSession::get(self::ACCESS_CODE_SESSION_INDEX);
        if (!isset($session_code[$this->getTestId()])) {
            return false;
        }

        return isset($session_code[$this->getTestId()]);
    }

    public function createNewAccessCode(): string
    {
        do {
            $code = $this->buildAccessCode();
        } while ($this->isAccessCodeUsed($code));

        return $code;
    }

    public function isAccessCodeUsed($code): bool
    {
        $query = "SELECT anonymous_id FROM tst_active WHERE test_fi = %s AND anonymous_id = %s";

        $result = $this->db->queryF(
            $query,
            ['integer', 'text'],
            [$this->getTestId(), $code]
        );

        return ($result->numRows() > 0);
    }

    private function buildAccessCode(): string
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

    public function isAnonymousUser(): bool
    {
        return $this->getUserId() == ANONYMOUS_USER_ID;
    }

    public function isPasswordChecked(): bool
    {
        if (ilSession::get('pw_checked_' . $this->active_id) === null) {
            return false;
        }
        return ilSession::get('pw_checked_' . $this->active_id);
    }

    public function setPasswordChecked(bool $value): void
    {
        ilSession::set('pw_checked_' . $this->active_id, $value);
    }

    /**
     * @var null|bool
     */
    private $reportableResultsAvailable = null;

    /**
     * @param ilObjTest $testOBJ
     * @return bool
     */
    public function reportableResultsAvailable(ilObjTest $testOBJ): ?bool
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
    public function hasSinglePassReportable(ilObjTest $testObj): bool
    {
        $testPassesSelector = new ilTestPassesSelector($this->db, $testObj);
        $testPassesSelector->setActiveId($this->getActiveId());
        $testPassesSelector->setLastFinishedPass($this->getLastFinishedPass());

        if (count($testPassesSelector->getReportablePasses()) == 1) {
            return true;
        }

        return false;
    }
}
