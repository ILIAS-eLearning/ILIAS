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

declare(strict_types=1);

use ILIAS\Modules\Test\AccessFileUploadAnswer;
use ILIAS\Modules\Test\AccessFileUploadPreview;
use ILIAS\Modules\Test\AccessQuestionImage;
use ILIAS\Modules\Test\SimpleAccess;
use ILIAS\Modules\Test\Readable;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;

/**
* Class ilObjTestAccess
*
* This class contains methods that check object specific conditions
* for accessing test objects.
*
* @author	Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 	Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilObjTestAccess extends ilObjectAccess implements ilConditionHandling
{
    private ilDBInterface $db;
    private ilObjUser $user;
    private ilLanguage $lng;
    private ilRbacSystem $rbac_system;
    private ilAccessHandler $access;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->access = $DIC['ilAccess'];
    }

    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        $readable = new Readable($this->access);

        $can_it = $this->findMatch($ilWACPath->getPath(), [
            new AccessFileUploadAnswer($this->user, $this->db, $readable),
            new AccessQuestionImage($readable),
            new AccessFileUploadPreview($this->db, $this->access),
        ]);


        return !$can_it->isOk() || $can_it->value();
    }

    private function findMatch(string $path, array $array): Result
    {
        return array_reduce($array, fn(Result $result, SimpleAccess $access) => $result->except(
            fn() => $access->isPermitted($path)
        ), new Error('Not a known path.'));
    }

    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here.
    */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, int $user_id = null): bool
    {
        if (is_null($user_id)) {
            $user_id = $this->user->getId();
        }

        $is_admin = $this->rbac_system->checkAccessOfUser($user_id, 'write', $ref_id);


        switch ($permission) {
            case "visible":
            case "read":
                if (!ilObjTestAccess::_lookupCreationComplete($obj_id) &&
                    !$is_admin) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt("tst_warning_test_not_complete"));
                    return false;
                }
                break;
        }

        switch ($cmd) {
            case "eval_a":
            case "eval_stat":
                if (!ilObjTestAccess::_lookupCreationComplete($obj_id)) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt("tst_warning_test_not_complete"));
                    return false;
                }
                break;
        }

        return true;
    }

    /**
    * Returns TRUE if the user with the user id $user_id passed the test with the object id $a_obj_id
    *
    * @param int $user_id The user id
    * @param int $a_obj_id The object id
    * @return boolean TRUE if the user passed the test, FALSE otherwise
    */
    public static function _isPassed($user_id, $a_obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $test = new ilObjTest($a_obj_id, false);

        $result = $ilDB->queryF(
            "SELECT tst_result_cache.* FROM tst_result_cache, tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s AND tst_result_cache.active_fi = tst_active.active_id",
            ['integer','integer'],
            [$user_id, $a_obj_id]
        );
        if (!$result->numRows()) {
            $result = $ilDB->queryF(
                "SELECT tst_active.active_id FROM tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s",
                ['integer','integer'],
                [$user_id, $a_obj_id]
            );
            $row = $ilDB->fetchAssoc($result);
            if ($row !== null && $row['active_id'] > 0) {
                $test->updateTestResultCache($row['active_id']);
            } else {
                return false;
            }
        }
        $result = $ilDB->queryF(
            "SELECT tst_result_cache.* FROM tst_result_cache, tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s AND tst_result_cache.active_fi = tst_active.active_id",
            ['integer','integer'],
            [$user_id, $a_obj_id]
        );
        if (!$result->numRows()) {
            $result = $ilDB->queryF(
                "SELECT tst_pass_result.*, tst_tests.pass_scoring, tst_tests.test_id FROM tst_pass_result, tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s AND tst_pass_result.active_fi = tst_active.active_id ORDER BY tst_pass_result.pass",
                ['integer','integer'],
                [$user_id, $a_obj_id]
            );

            if (!$result->numRows()) {
                return false;
            }

            $points = [];
            while ($row = $ilDB->fetchAssoc($result)) {
                array_push($points, $row);
            }
            $reached = 0;
            $max = 0;
            if ($points[0]["pass_scoring"] == 0) {
                $reached = $points[count($points) - 1]["points"];
                $max = $points[count($points) - 1]["maxpoints"];
                if (!$max) {
                    $active_id = $points[count($points) - 1]["active_fi"];
                    $pass = $points[count($points) - 1]["pass"];
                    if (strlen($active_id) && strlen($pass)) {
                        $res = $test->updateTestPassResults($active_id, $pass, false, null, $a_obj_id);
                        $max = $res['maxpoints'];
                        $reached = $res['points'];
                    }
                }
            } else {
                foreach ($points as $row) {
                    if ($row["points"] > $reached) {
                        $reached = $row["points"];
                        $max = $row["maxpoints"];
                        if (!$max) {
                            $active_id = $row["active_fi"];
                            $pass = $row["pass"];
                            if (strlen($active_id) && strlen($pass)) {
                                $res = $test->updateTestPassResults($active_id, $pass, false, null, $a_obj_id);
                                $max = $res['maxpoints'];
                                $reached = $res['points'];
                            }
                        }
                    }
                }
            }
            $percentage = (!$max) ? 0 : ($reached / $max) * 100.0;
            return $test->getMarkSchema()->getMatchingMark($percentage)->getPassed() === 1;
        } else {
            $row = $ilDB->fetchAssoc($result);
            return ($row['passed']) ? true : false;
        }
    }

    /**
     * Returns TRUE if the user with the user id $user_id failed the test with the object id $a_obj_id
     *
     * @param int $user_id The user id
     * @param int $a_obj_id The object id
     * @return boolean TRUE if the user failed the test, FALSE otherwise
     */
    public static function isFailed($user_id, $a_obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ret = self::updateTestResultCache($user_id, $a_obj_id);

        if (!$ret) {
            return false;
        }

        $test = new ilObjTest($a_obj_id, false);

        $result = $ilDB->queryF(
            "SELECT tst_result_cache.* FROM tst_result_cache, tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s AND tst_result_cache.active_fi = tst_active.active_id",
            ['integer','integer'],
            [$user_id, $a_obj_id]
        );

        if (!$result->numRows()) {
            $result = $ilDB->queryF(
                "SELECT tst_pass_result.*, tst_tests.pass_scoring FROM tst_pass_result, tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s AND tst_pass_result.active_fi = tst_active.active_id ORDER BY tst_pass_result.pass",
                ['integer','integer'],
                [$user_id, $a_obj_id]
            );

            while ($row = $ilDB->fetchAssoc($result)) {
                array_push($points, $row);
            }
            $reached = 0;
            $max = 0;
            if ($points[0]["pass_scoring"] == 0) {
                $reached = $points[count($points) - 1]["points"];
                $max = $points[count($points) - 1]["maxpoints"];
                if (!$max) {
                    $active_id = $points[count($points) - 1]["active_fi"];
                    $pass = $points[count($points) - 1]["pass"];
                    if (strlen($active_id) && strlen($pass)) {
                        $res = $test->updateTestPassResults($active_id, $pass, false, null, $a_obj_id);
                        $max = $res['maxpoints'];
                        $reached = $res['points'];
                    }
                }
            } else {
                foreach ($points as $row) {
                    if ($row["points"] > $reached) {
                        $reached = $row["points"];
                        $max = $row["maxpoints"];
                        if (!$max) {
                            $active_id = $row["active_fi"];
                            $pass = $row["pass"];
                            if (strlen($active_id) && strlen($pass)) {
                                $res = $test->updateTestPassResults($active_id, $pass, false, null, $a_obj_id);
                                $max = $res['maxpoints'];
                                $reached = $res['points'];
                            }
                        }
                    }
                }
            }
            $percentage = (!$max) ? 0 : ($reached / $max) * 100.0;
            return $test->getMarkSchema()->getMatchingMark($percentage)->getPassed() === 0;
        } else {
            $row = $ilDB->fetchAssoc($result);
            return ($row['failed']) ? true : false;
        }
    }

    protected static function updateTestResultCache($a_user_id, $a_obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT tst_result_cache.* FROM tst_result_cache, tst_active, tst_tests " .
                "WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s " .
                "AND tst_tests.obj_fi = %s AND tst_result_cache.active_fi = tst_active.active_id",
            ['integer','integer'],
            [$a_user_id, $a_obj_id]
        );
        if (!$result->numRows()) {
            $result = $ilDB->queryF(
                "SELECT tst_active.active_id FROM tst_active, tst_tests WHERE tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND tst_tests.obj_fi = %s",
                ['integer','integer'],
                [$a_user_id, $a_obj_id]
            );
            $row = $ilDB->fetchAssoc($result);
            if ($row !== null && $row['active_id'] > 0) {
                $test = new ilObjTest($a_obj_id, false);
                $test->updateTestResultCache($row['active_id']);
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }


    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators(): array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED,
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_NOT_FINISHED
        ];
    }


    /**
    * check condition
    *
    * this method is called by ilConditionHandler
    */
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilObjTestAccess::_isPassed($a_usr_id, $a_trigger_obj_id);
                break;

            case ilConditionHandler::OPERATOR_FAILED:
                return ilObjTestAccess::isFailed($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_FINISHED:
                return ilObjTestAccess::hasFinished($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_NOT_FINISHED:
                return !ilObjTestAccess::hasFinished($a_usr_id, $a_trigger_obj_id);

            default:
                return true;
        }
        return true;
    }

    public static function _getCommands(): array
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('assessment');

        $commands = [
            ["permission" => "write", "cmd" => "questionsTabGateway", "lang_var" => "tst_edit_questions"],
            ["permission" => "write", "cmd" => "ilObjTestSettingsMainGUI::showForm", "lang_var" => "settings"],
            ["permission" => "read", "cmd" => "testScreen", "lang_var" => "tst_run", "default" => true],
            ["permission" => "tst_statistics", "cmd" => "outEvaluation", "lang_var" => "tst_statistical_evaluation"],
            ["permission" => "read", "cmd" => "userResultsGateway", "lang_var" => "tst_user_results"],
            ["permission" => "write", "cmd" => "testResultsGateway", "lang_var" => "results"],
            ["permission" => "eval_a", "cmd" => "testResultsGateway", "lang_var" => "results"]
        ];

        return $commands;
    }

    //
    // object specific access related methods
    //

    /**
    * checks wether all necessary parts of the test are given
    */
    public static function _lookupCreationComplete($a_obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT complete FROM tst_tests WHERE obj_fi=%s",
            ['integer'],
            [$a_obj_id]
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
        }

        return isset($row['complete']) && $row['complete'];
    }

    /**
     * Request Cache for hasFinished Information
     *
     * @var array
     */
    private static $hasFinishedCache = [];

    /**
     * Returns (request cached) information if a specific user has finished at least one test pass
     *
     * @param integer $a_user_id obj_id of the user
     * @param integer $a_obj_id obj_id of the test
     * @return bool
     */
    public static function hasFinished($a_user_id, $a_obj_id): bool
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        if (!isset(self::$hasFinishedCache["{$a_user_id}:{$a_obj_id}"])) {
            $testOBJ = ilObjectFactory::getInstanceByObjId($a_obj_id);

            $partData = new ilTestParticipantData($ilDB, $lng);
            $partData->setUserIdsFilter([$a_user_id]);
            $partData->load($testOBJ->getTestId());

            $activeId = $partData->getActiveIdByUserId($a_user_id);

            /** @noinspection PhpParamsInspection */
            $testSessionFactory = new ilTestSessionFactory($testOBJ, $ilDB, $ilUser);
            $testSession = $testSessionFactory->getSession($activeId);
            /** @noinspection PhpParamsInspection */
            $testPassesSelector = new ilTestPassesSelector($ilDB, $testOBJ);
            $testPassesSelector->setActiveId($activeId);
            $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());

            self::$hasFinishedCache["{$a_user_id}:{$a_obj_id}"] = count($testPassesSelector->getClosedPasses());
        }

        return (bool) self::$hasFinishedCache["{$a_user_id}:{$a_obj_id}"];
    }

    /**
    * Returns the ILIAS test id for a given object id
    *
    * @param integer $object_id The object id
    * @return mixed The ILIAS test id or FALSE if the query was not successful
    * @access public
    */
    public static function _getTestIDFromObjectID($object_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $test_id = false;
        $result = $ilDB->queryF(
            "SELECT test_id FROM tst_tests WHERE obj_fi = %s",
            ['integer'],
            [$object_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $test_id = $row["test_id"];
        }
        return $test_id;
    }

    /**
     * Lookup object id for test id
     *
     * @param		int		test id
     * @return		int		object id
     */
    public static function _lookupObjIdForTestId($a_test_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT obj_fi FROM tst_tests WHERE test_id = %s",
            ['integer'],
            [$a_test_id]
        );

        $row = $ilDB->fetchAssoc($result);
        return $row["obj_fi"];
    }

    /**
    * Get all tests using a question pool for random selection
    *
    * @param    int     question pool id
    * @return 	array 	list if test obj ids
    * @access	public
    */
    public static function _getRandomTestsForQuestionPool($qpl_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT DISTINCT t.obj_fi
			FROM tst_tests t
			INNER JOIN tst_rnd_quest_set_qpls r
			ON t.test_id = r.test_fi
			WHERE r.pool_fi = %s
		";

        $result = $ilDB->queryF($query, ['integer'], [$qpl_id]);

        $tests = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $tests[] = $row['obj_fi'];
        }

        return $tests;
    }
    // fim.

    /**
    * Checks if a user is allowd to run an online exam
    *
    * @return mixed true if the user is allowed to run the online exam or if the test isn't an online exam, an alert message if the test is an online exam and the user is not allowed to run it
    * @access public
    */
    public static function _lookupOnlineTestAccess($a_test_id, $a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $result = $ilDB->queryF(
            "SELECT tst_tests.* FROM tst_tests WHERE tst_tests.obj_fi = %s",
            ['integer'],
            [$a_test_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            if ($row["fixed_participants"]) {
                $result = $ilDB->queryF(
                    "SELECT * FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
                    ['integer','integer'],
                    [$row["test_id"], $a_user_id]
                );
                if ($result->numRows()) {
                    $row = $ilDB->fetchAssoc($result);
                    if ($row['clientip'] !== null && trim($row['clientip']) != "") {
                        $row['clientip'] = preg_replace("/[^0-9.?*,:]+/", "", $row['clientip']);
                        $row['clientip'] = str_replace(".", "\\.", $row['clientip']);
                        $row['clientip'] = str_replace(["?","*",","], ["[0-9]","[0-9]*","|"], $row['clientip']);
                        if (!preg_match("/^" . $row['clientip'] . "$/", $_SERVER["REMOTE_ADDR"])) {
                            $lng->loadLanguageModule('assessment');
                            return $lng->txt("user_wrong_clientip");
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return $lng->txt("tst_user_not_invited");
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
    * Retrieves a participant name from active id
    *
    * @param integer $active_id Active ID of the participant
    * @return string The output name of the user
    * @access public
    */
    public static function _getParticipantData($active_id): string
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result_active = $ilDB->queryF(
            'SELECT * FROM tst_active WHERE active_id = %s',
            ['integer'],
            [$active_id]
        );
        $row_active = $ilDB->fetchAssoc($result_active);
        $importname = $row_active['importname'];

        if ($importname !== null
            && $importname !== '') {
            return $importname . ' (' . $lng->txt('imported') . ')';
        }

        if ($row_active['user_fi'] === ANONYMOUS_USER_ID) {
            return '';
        }

        $uname = ilObjUser::_lookupName($row_active['user_fi']);

        $result_test = $ilDB->queryF(
            "SELECT obj_fi FROM tst_tests WHERE test_id = %s",
            ["integer"],
            [$row_active['test_fi']]
        );
        $row_test = $ilDB->fetchAssoc($result_test);
        $obj_id = $row_test["obj_fi"];

        if (ilObjTest::_lookupAnonymity($obj_id)) {
            return $lng->txt("anonymous");
        }

        if ($uname['firstname'] . $uname['lastname'] === '') {
            return $lng->txt('deleted_user');
        }

        return trim($uname['lastname'] . ', ' . $uname['firstname']);
    }

    /**
     * Get user id for active id
     *
     * @param	int		active ID of the participant
     * @return	int		user id
     */
    public static function _getParticipantId($active_id): int
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT user_fi FROM tst_active WHERE active_id = %s",
            ["integer"],
            [$active_id]
        );
        $row = $ilDB->fetchAssoc($result);
        return $row["user_fi"];
    }


    /**
    * Returns an array containing the users who passed the test
    *
    * @return array An array containing the users who passed the test.
    *         Format of the values of the resulting array:
    *           [
    *             "user_id"        => user ID,
    *             "max_points"     => maximum available points in the test
    *             "reached_points" => maximum reached points of the user
    *             "mark_short"     => short text of the passed mark
    *             "mark_official"  => official text of the passed mark
    *           ]
    * @access public
    */
    public static function _getPassedUsers($a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $passed_users = [];
        // Maybe SELECT DISTINCT(tst_active.user_fi)... ?
        $userresult = $ilDB->queryF(
            "
			SELECT tst_active.active_id, COUNT(tst_sequence.active_fi) sequences, tst_active.last_finished_pass,
				CASE WHEN
					(tst_tests.nr_of_tries - 1) = tst_active.last_finished_pass
				THEN '1'
				ELSE '0'
				END is_last_pass
			FROM tst_tests
			INNER JOIN tst_active
			ON tst_active.test_fi = tst_tests.test_id
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE tst_tests.obj_fi = %s
			GROUP BY tst_active.active_id
			",
            ['integer'],
            [$a_obj_id]
        );
        $all_participants = [];
        $notAttempted = [];
        $lastPassUsers = [];
        while ($row = $ilDB->fetchAssoc($userresult)) {
            if ($row['sequences'] == 0) {
                $notAttempted[$row['active_id']] = $row['active_id'];
            }
            if ($row['is_last_pass']) {
                $lastPassUsers[$row['active_id']] = $row['active_id'];
            }

            $all_participants[$row['active_id']] = $row['active_id'];
        }

        $result = $ilDB->query("SELECT tst_result_cache.*, tst_active.user_fi FROM tst_result_cache, tst_active WHERE tst_active.active_id = tst_result_cache.active_fi AND " . $ilDB->in('active_fi', $all_participants, false, 'integer'));
        $found_all = ($result->numRows() == count($all_participants)) ? true : false;
        if (!$found_all) {
            $test = new ilObjTest($a_obj_id, false);
            // if the result cache entries do not exist, create them
            $found_participants = [];
            while ($data = $ilDB->fetchAssoc($result)) {
                array_push($found_participants, $data['active_fi']);
            }
            foreach ($all_participants as $active_id) {
                if (!in_array($active_id, $found_participants)) {
                    $test->updateTestResultCache($active_id);
                }
            }
            $result = $ilDB->query("SELECT tst_result_cache.*, tst_active.user_fi FROM tst_result_cache, tst_active WHERE tst_active.active_id = tst_result_cache.active_fi AND " . $ilDB->in('active_fi', $all_participants, false, 'integer'));
        }
        while ($data = $ilDB->fetchAssoc($result)) {
            if (isset($notAttempted[$data['active_fi']])) {
                $data['failed'] = 0;
                $data['passed'] = 0;
                $data['not_attempted'] = 1;
            }

            $data['user_id'] = $data['user_fi'];
            array_push($passed_users, $data);
        }
        return $passed_users;
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode("_", $target);

        if ($t_arr[0] != "tst" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * returns the objects's OFFline status
     *
     * Used in ListGUI and Learning Progress
     */
    public static function _isOffline(int $obj_id): bool
    {
        return ilObject::lookupOfflineStatus($obj_id);
    }


    public static function visibleUserResultExists($test_obj_id, $user_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $test_obj = ilObjectFactory::getInstanceByObjId($test_obj_id, false);

        if (!($test_obj instanceof ilObjTest)) {
            return false;
        }

        $test_session_factory = new ilTestSessionFactory($test_obj, $ilDB, $ilUser);
        $test_session = $test_session_factory->getSessionByUserId($user_id);

        return $test_obj->canShowTestResults($test_session);
    }
}
