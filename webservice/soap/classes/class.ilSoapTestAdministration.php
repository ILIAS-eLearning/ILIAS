<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
 |                                                                             |
 | This program is free software; you can redistribute it and/or               |
 | modify it under the terms of the GNU General Public License                 |
 | as published by the Free Software Foundation; either version 2              |
 | of the License, or (at your option) any later version.                      |
 |                                                                             |
 | This program is distributed in the hope that it will be useful,             |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
 | GNU General Public License for more details.                                |
 |                                                                             |
 | You should have received a copy of the GNU General Public License           |
 | along with this program; if not, write to the Free Software                 |
 | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
 +-----------------------------------------------------------------------------+
*/

/**
 * Test & Assessment Soap functions
 * @author  Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 * @package ilias
 */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapTestAdministration extends ilSoapAdministration
{
    private function hasWritePermissionForTest(int $active_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        $permission_ok = false;
        $result = $ilDB->queryF(
            "SELECT tst_tests.obj_fi FROM tst_active, tst_tests WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
            array('integer'),
            array($active_id)
        );
        $row = $ilDB->fetchAssoc($result);
        if ($row['obj_fi']) {
            $obj_id = $row['obj_fi'];
            foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
                if ($ilAccess->checkAccess("write", "", $ref_id)) {
                    $permission_ok = true;
                    break;
                }
            }
        }
        return $permission_ok;
    }

    public function isAllowedCall(string $sid, int $active_id, bool $saveaction = true) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if ($this->hasWritePermissionForTest($active_id)) {
            return true;
        }

        if ($saveaction) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_times WHERE active_fi = %s ORDER BY started DESC",
                array('integer'),
                array($active_id)
            );
            if ($result->numRows()) {
                $row = $ilDB->fetchAssoc($result);
                if (preg_match("/(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})/", $row["started"], $matches)) {
                    $time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
                    $now = time();
                    $diff = $now - $time;
                    $client = explode("::", $sid);
                    global $DIC;

                    $ilClientIniFile = $DIC['ilClientIniFile'];
                    $expires = $ilClientIniFile->readVariable('session', 'expire');
                    return $diff <= $expires;
                }

                return false;
            }

            return false;
        }

        $result = $ilDB->queryF(
            "SELECT user_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        $row = $ilDB->fetchAssoc($result);

        return (int) $row['user_fi'] === $ilUser->getId();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function saveQuestion(string $sid, int $active_id, int $question_id, int $pass, array $solution)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFactory.php';
        $processLockerFactory = new ilAssQuestionProcessLockerFactory(new ilSetting('assessment'), $ilDB);
        $processLockerFactory->setQuestionId($question_id);
        $processLockerFactory->setUserId($ilUser->getId());
        include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
        $processLockerFactory->setAssessmentLogEnabled(ilObjAssessmentFolder::_enabledAssessmentLogging());
        $processLocker = $processLockerFactory->getLocker();

        $totalrows = 0;

        $processLocker->executePersistWorkingStateLockOperation(function () use (
            &$totalrows,
            $processLocker,
            $active_id,
            $question_id,
            $pass,
            $solution
        ) {
            $processLocker->executeUserSolutionUpdateLockOperation(function () use (
                &$totalrows,
                $active_id,
                $question_id,
                $pass,
                $solution
            ) {
                $ilDB = $GLOBALS['DIC']['ilDB'];
                if (($active_id > 0) && ($question_id > 0) && ($pass > 0)) {
                    $affectedRows = $ilDB->manipulateF(
                        "DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                        array('integer', 'integer', 'integer'),
                        array($active_id, $question_id, $pass)
                    );
                }
                for ($i = 0, $iMax = count($solution); $i < $iMax; $i += 3) {
                    $next_id = $ilDB->nextId('tst_solutions');
                    $affectedRows = $ilDB->insert("tst_solutions", array(
                        "solution_id" => array("integer", $next_id),
                        "active_fi" => array("integer", $active_id),
                        "question_fi" => array("integer", $question_id),
                        "value1" => array("clob", $solution[$i]),
                        "value2" => array("clob", $solution[$i + 1]),
                        "points" => array("float", $solution[$i + 2]),
                        "pass" => array("integer", $pass),
                        "tstamp" => array("integer", time())
                    ));
                    $totalrows += $affectedRows;
                }
            });

            if ($totalrows !== 0) {
                include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
                $question = assQuestion::instantiateQuestion($question_id);
                $question->setProcessLocker($processLocker);
                $question->calculateResultsFromSolution($active_id, $pass);
            }
        });

        if ($totalrows === 0) {
            return $this->raiseError(
                "Wrong solution data. ILIAS did not execute any database queries: Solution data: " . print_r(
                    $solution,
                    true
                ),
                'No result'
            );
        }
        return true;
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function saveQuestionSolution(string $sid, int $active_id, int $question_id, int $pass, int $solution)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        $solutions = [];
        if (preg_match("/<values>(.*?)<\/values>/is", $solution, $matches)) {
            if (preg_match_all(
                "/<value>(.*?)<\/value><value>(.*?)<\/value><points>(.*?)<\/points>/is",
                $solution,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    if (count($match) === 4) {
                        for ($i = 1, $iMax = count($match); $i < $iMax; $i++) {
                            $solutions[] = trim($match[$i]);
                        }
                    }
                }
            }
        }

        if (count($solutions) === 0) {
            return $this->raiseError(
                "Wrong solution data. ILIAS did not find one or more solution triplets: $solution",
                ""
            );
        }

        $ilDB = $GLOBALS['DIC']['ilDB'];
        if (($active_id > 0) && ($question_id > 0) && ($pass > 0)) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                array('integer', 'integer', 'integer'),
                array($active_id, $question_id, $pass)
            );
        }
        $totalrows = 0;
        for ($i = 0, $iMax = count($solutions); $i < $iMax; $i += 3) {
            $next_id = $ilDB->nextId('tst_solutions');
            $affectedRows = $ilDB->insert("tst_solutions", array(
                "solution_id" => array("integer", $next_id),
                "active_fi" => array("integer", $active_id),
                "question_fi" => array("integer", $question_id),
                "value1" => array("clob", $solutions[$i]),
                "value2" => array("clob", $solutions[$i + 1]),
                "points" => array("float", $solutions[$i + 2]),
                "pass" => array("integer", $pass),
                "tstamp" => array("integer", time())
            ));
            $totalrows += $affectedRows;
        }
        if ($totalrows === 0) {
            return $this->raiseError("Wrong solution data. ILIAS did not execute any database queries", '');
        }

        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $question = assQuestion::instantiateQuestion($question_id);
        $question->calculateResultsFromSolution($active_id, $pass);
        return "TRUE";
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public function getQuestionSolution(string $sid, int $active_id, int $question_id, int $pass)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id, false)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }
        $solution = array();

        global $DIC;

        $ilDB = $DIC['ilDB'];

        $use_previous_answers = 1;

        $result = $ilDB->queryF(
            "SELECT tst_tests.use_previous_answers FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $use_previous_answers = $row["use_previous_answers"];
        }
        $lastpass = 0;
        if ($use_previous_answers) {
            $result = $ilDB->queryF(
                "SELECT MAX(pass) maxpass FROM tst_test_result WHERE active_fi = %s AND question_fi = %s",
                array('integer', 'integer'),
                array($active_id, $question_id)
            );
            if ($result->numRows() === 1) {
                $row = $ilDB->fetchAssoc($result);
                $lastpass = (int) $row["maxpass"];
            }
        } else {
            $lastpass = $pass;
        }

        if (($active_id > 0) && ($question_id > 0) && ($lastpass > 0)) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                array('integer', 'integer', 'integer'),
                array($active_id, $question_id, $lastpass)
            );
            if ($result->numRows()) {
                while ($row = $ilDB->fetchAssoc($result)) {
                    $solution[] = $row["value1"];
                    $solution[] = $row["value2"];
                    $solution[] = $row["points"];
                }
            }
        }
        return $solution;
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public function getTestUserData(string $sid, int $active_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id, false)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT user_fi, test_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        $row = $ilDB->fetchAssoc($result);
        $user_id = $row["user_fi"];
        $test_id = $row["test_fi"];

        $result = $ilDB->queryF(
            "SELECT anonymity FROM tst_tests WHERE test_id = %s",
            array('integer'),
            array($test_id)
        );
        $row = $ilDB->fetchAssoc($result);
        $anonymity = $row["anonymity"];

        $result = $ilDB->queryF(
            "SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = %s",
            array('integer'),
            array($user_id)
        );

        $userdata = array();
        if ($result->numRows() === 0) {
            $userdata["fullname"] = $lng->txt("deleted_user");
            $userdata["title"] = "";
            $userdata["firstname"] = "";
            $userdata["lastname"] = $lng->txt("anonymous");
            $userdata["login"] = "";
        } else {
            $data = $ilDB->fetchAssoc($result);
            if ((int) $user_id === ANONYMOUS_USER_ID || $anonymity) {
                $userdata["fullname"] = $lng->txt("anonymous");
                $userdata["title"] = "";
                $userdata["firstname"] = "";
                $userdata["lastname"] = $lng->txt("anonymous");
                $userdata["login"] = "";
            } else {
                $userdata["fullname"] = trim($data["title"] . " " . $data["firstname"] . " " . $data["lastname"]);
                $userdata["title"] = $data["title"];
                $userdata["firstname"] = $data["firstname"];
                $userdata["lastname"] = $data["lastname"];
                $userdata["login"] = $data["login"];
            }
        }
        return array_values($userdata);
    }

    /**
     * @return false|int|soap_fault|SoapFault|string|null
     */
    public function getPositionOfQuestion(string $sid, int $active_id, int $question_id, int $pass)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id, false)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows() !== 1) {
            return -1;
        }
        $row = $ilDB->fetchAssoc($result);
        $is_random = $row["random_test"];

        include_once "./Modules/Test/classes/class.ilTestSequence.php";
        $sequence = new ilTestSequence($active_id, $pass, $is_random);
        return $sequence->getSequenceForQuestion($question_id);
    }

    /**
     * @return array|int|soap_fault|SoapFault|null
     */
    public function getPreviousReachedPoints(string $sid, int $active_id, int $question_id, int $pass)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id, false)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows() !== 1) {
            return -1;
        }
        $row = $ilDB->fetchAssoc($result);
        $is_random = $row["random_test"];

        include_once "./Modules/Test/classes/class.ilTestSequence.php";
        $sequence = new ilTestSequence($active_id, $pass, $is_random);
        $result = $ilDB->queryF(
            "SELECT question_fi, points FROM tst_test_result WHERE active_fi = %s AND pass = %s",
            array('integer', 'integer'),
            array($active_id, $pass)
        );
        $reachedpoints = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $reachedpoints[$row["question_fi"]] = $row["points"];
        }
        $atposition = false;
        $pointsforposition = array();
        foreach ($sequence->getUserSequence() as $seq) {
            if (!$atposition) {
                $qid = $sequence->getQuestionForSequence($seq);
                if ($qid == $question_id) {
                    $atposition = true;
                } else {
                    $pointsforposition[] = $reachedpoints[$qid];
                }
            }
        }
        return $pointsforposition;
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public function getNrOfQuestionsInPass(string $sid, int $active_id, int $pass)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$this->isAllowedCall($sid, $active_id, false)) {
            return $this->raiseError("The required user information is only available for active users.", "");
        }

        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows() !== 1) {
            return 0;
        }
        $row = $ilDB->fetchAssoc($result);
        $is_random = $row["random_test"];

        include_once "./Modules/Test/classes/class.ilTestSequence.php";
        $sequence = new ilTestSequence($active_id, $pass, $is_random);
        return $sequence->getUserQuestionCount();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function removeTestResults(string $sid, int $test_ref_id, array $a_user_ids)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!($test_ref_id > 0)) {
            return $this->raiseError(
                'No test id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (!$this->checkManageParticipantsAccess($test_ref_id)) {
            return $this->raiseError('no permission. Aborting!', 'Client');
        }

        if (ilObject::_isInTrash($test_ref_id)) {
            return $this->raiseError(
                'Test is trashed. Aborting!',
                'Client'
            );
        }

        if (!$tst = ilObjectFactory::getInstanceByRefId($test_ref_id, false)) {
            return $this->raiseError('No test found for id: ' . $test_ref_id, 'Client');
        }
        if ($tst->getType() !== 'tst') {
            return $this->raiseError(
                'Object with ref_id ' . $test_ref_id . ' is not of type test. Aborting',
                'Client'
            );
        }

        // Dirty hack
        if (isset($a_user_ids['item'])) {
            $a_user_ids = $a_user_ids['item'];
        }

        include_once './Modules/Test/classes/class.ilObjTest.php';
        include_once './Modules/Test/classes/class.ilTestParticipantData.php';
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $part = new ilTestParticipantData($GLOBALS['DIC']['ilDB'], $GLOBALS['DIC']['lng']);
        $part->setParticipantAccessFilter(
            ilTestParticipantAccessFilter::getManageParticipantsUserFilter($test_ref_id)
        );
        $part->setUserIdsFilter((array) $a_user_ids);
        $part->load($tst->getTestId());
        $tst->removeTestResults($part);

        return true;
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getTestResults(string $sid, int $test_ref_id, bool $sum_only)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!($test_ref_id > 0)) {
            return $this->raiseError(
                'No test id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (ilObject::_isInTrash($test_ref_id)) {
            return $this->raiseError(
                'Test is trashed. Aborting!',
                'Client'
            );
        }

        if (!$obj_id = ilObject::_lookupObjectId($test_ref_id)) {
            return $this->raiseError(
                'No test found for id: ' . $test_ref_id,
                'Client'
            );
        }

        $permission_ok = false;
        foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($rbacsystem->checkAccess('write', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }
        if (!$permission_ok && $this->checkParticipantsResultsAccess($test_ref_id)) {
            $permission_ok = $this->checkParticipantsResultsAccess($test_ref_id);
        }

        if (!$permission_ok) {
            return $this->raiseError(
                'No permission to edit the object with id: ' . $test_ref_id,
                'Server'
            );
        }

        include_once './webservice/soap/classes/class.ilXMLResultSet.php';
        include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("user_id");
        $xmlResultSet->addColumn("login");
        $xmlResultSet->addColumn("firstname");
        $xmlResultSet->addColumn("lastname");
        $xmlResultSet->addColumn("matriculation");

        include_once './Modules/Test/classes/class.ilObjTest.php';
        $test_obj = new ilObjTest($obj_id, false);
        $participants = $test_obj->getTestParticipants();

        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $accessFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter($test_ref_id);
        $participantList = new ilTestParticipantList($test_obj);
        $participantList->initializeFromDbRows($participants);
        $participantList = $participantList->getAccessFilteredList($accessFilter);
        $participantList = $participantList->getScoredParticipantList();
        foreach ($participants as $activeId => $part) {
            if ($participantList->isActiveIdInList($activeId)) {
                $participants[$activeId]['passed'] = $participantList->getParticipantByActiveId($activeId)->getScoring()->isPassed();
                continue;
            }

            unset($participants[$activeId]);
        }

        if ($sum_only) {
            $data = $test_obj->getAllTestResults($participants, false);

            $xmlResultSet->addColumn("maximum_points");
            $xmlResultSet->addColumn("received_points");
            $xmlResultSet->addColumn("passed");
            // skip titles
            $titles = array_shift($data);
            foreach ($data as $row) {
                $xmlRow = new ilXMLResultSetRow();
                $xmlRow->setValue(0, $row["user_id"]);
                $xmlRow->setValue(1, $row["login"]);
                $xmlRow->setValue(2, $row["firstname"]);
                $xmlRow->setValue(3, $row["lastname"]);
                $xmlRow->setValue(4, $row["matriculation"]);
                $xmlRow->setValue(5, $row["max_points"]);
                $xmlRow->setValue(6, $row["reached_points"]);
                $xmlRow->setValue(7, $row["passed"]);
                $xmlResultSet->addRow($xmlRow);
            }
        } else {
            $data = $test_obj->getDetailedTestResults($participants);

            $xmlResultSet->addColumn("question_id");
            $xmlResultSet->addColumn("question_title");
            $xmlResultSet->addColumn("maximum_points");
            $xmlResultSet->addColumn("received_points");
            $xmlResultSet->addColumn("passed");
            foreach ($data as $row) {
                $xmlRow = new ilXMLResultSetRow();
                $xmlRow->setValue(0, $row["user_id"]);
                $xmlRow->setValue(1, $row["login"]);
                $xmlRow->setValue(2, $row["firstname"]);
                $xmlRow->setValue(3, $row["lastname"]);
                $xmlRow->setValue(4, $row["matriculation"]);
                $xmlRow->setValue(5, $row["question_id"]);
                $xmlRow->setValue(6, $row["question_title"]);
                $xmlRow->setValue(7, $row["max_points"]);
                $xmlRow->setValue(8, $row["reached_points"]);
                $xmlRow->setValue(9, $row["passed"]);
                $xmlResultSet->addRow($xmlRow);
            }
        }

        $xmlWriter = new ilXMLResultSetWriter($xmlResultSet);
        $xmlWriter->start();
        return $xmlWriter->getXML();
    }

    protected function checkManageParticipantsAccess(int $refId) : bool
    {
        return $this->getTestAccess($refId)->checkManageParticipantsAccess();
    }

    protected function checkParticipantsResultsAccess(int $refId) : bool
    {
        return $this->getTestAccess($refId)->checkParticipantsResultsAccess();
    }

    protected function getTestAccess(int $refId) : ilTestAccess
    {
        require_once 'Modules/Test/classes/class.ilTestAccess.php';

        $testId = ilObjTestAccess::_getTestIDFromObjectID(ilObject::_lookupObjectId($refId));
        return new ilTestAccess($refId, $testId);
    }
}
