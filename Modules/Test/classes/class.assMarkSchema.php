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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * A class defining mark schemas for assessment test objects
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
class ASS_MarkSchema
{
    public array $mark_steps;

    public function __construct()
    {
        $this->mark_steps = array();
    }

    /**
     * Creates a simple mark schema for two mark steps:
     * failed and passed.
     *
     * @see    $mark_steps
     *
     * @param string    $txt_failed_short    The short text of the failed mark.
     * @param string    $txt_failed_official The official text of the failed mark.
     * @param float|int $percentage_failed   The minimum percentage level reaching the failed mark.
     * @param integer   $failed_passed       Indicates the passed status of the failed mark (0 = failed, 1 = passed).
     * @param string    $txt_passed_short    The short text of the passed mark.
     * @param string    $txt_passed_official The official text of the passed mark.
     * @param float|int $percentage_passed   The minimum percentage level reaching the passed mark.
     * @param integer   $passed_passed       Indicates the passed status of the passed mark (0 = failed, 1 = passed).
     */
    public function createSimpleSchema(
        string $txt_failed_short = "failed",
        string $txt_failed_official = "failed",
        float $percentage_failed = 0,
        int $failed_passed = 0,
        string $txt_passed_short = "passed",
        string $txt_passed_official = "passed",
        float $percentage_passed = 50,
        int $passed_passed = 1
    ) {
        $this->flush();
        $this->addMarkStep($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed);
        $this->addMarkStep($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed);
    }

    /**
     * Adds a mark step to the mark schema. A new ASS_Mark object will be created and stored
     * in the $mark_steps array.
     *
     * @see $mark_steps
     *
     * @param string  $txt_short    The short text of the mark.
     * @param string  $txt_official The official text of the mark.
     * @param float   $percentage   The minimum percentage level reaching the mark.
     * @param integer $passed       The passed status of the mark (0 = failed, 1 = passed).
     */
    public function addMarkStep(string $txt_short = "", string $txt_official = "", $percentage = 0, $passed = 0) : void
    {
        require_once './Modules/Test/classes/class.assMark.php';
        $mark = new ASS_Mark($txt_short, $txt_official, $percentage, $passed);
        array_push($this->mark_steps, $mark);
    }

    public function saveToDb(int $test_id) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $oldmarks = array();
        include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
                array('integer'),
                array($test_id)
            );
            if ($result->numRows()) {
                /** @noinspection PhpAssignmentInConditionInspection */
                while ($row = $ilDB->fetchAssoc($result)) {
                    $oldmarks[$row["minimum_level"]] = $row;
                }
            }
        }
        
        if (!$test_id) {
            return;
        }
        // Delete all entries
        $ilDB->manipulateF(
            "DELETE FROM tst_mark WHERE test_fi = %s",
            array('integer'),
            array($test_id)
        );
        if (count($this->mark_steps) == 0) {
            return;
        }
    
        // Write new datasets
        foreach ($this->mark_steps as $key => $value) {
            $next_id = $ilDB->nextId('tst_mark');
            $ilDB->manipulateF(
                "INSERT INTO tst_mark (mark_id, test_fi, short_name, official_name, minimum_level, passed, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','text','text','float','text','integer'),
                array(
                    $next_id,
                    $test_id,
                    $value->getShortName(),
                    $value->getOfficialName(),
                    $value->getMinimumLevel(),
                    $value->getPassed(),
                    time()
                )
            );
        }
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
                array('integer'),
                array($test_id)
            );
            $newmarks = array();
            if ($result->numRows()) {
                /** @noinspection PhpAssignmentInConditionInspection */
                while ($row = $ilDB->fetchAssoc($result)) {
                    $newmarks[$row["minimum_level"]] = $row;
                }
            }
            foreach ($oldmarks as $level => $row) {
                if (array_key_exists($level, $newmarks)) {
                    $difffields = array();
                    foreach ($row as $key => $value) {
                        if (strcmp($value, $newmarks[$level][$key]) != 0) {
                            switch ($key) {
                                case "mark_id":
                                case "tstamp":
                                    break;
                                default:
                                    array_push($difffields, "$key: $value => " . $newmarks[$level][$key]);
                                    break;
                            }
                        }
                    }
                    if (count($difffields)) {
                        $this->logAction($test_id, $lng->txtlng("assessment", "log_mark_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . join(", ", $difffields));
                    }
                } else {
                    $this->logAction($test_id, $lng->txtlng("assessment", "log_mark_removed", ilObjAssessmentFolder::_getLogLanguage()) . ": " .
                        $lng->txtlng("assessment", "tst_mark_minimum_level", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["minimum_level"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_short_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["short_name"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_official_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["official_name"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_passed", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["passed"]);
                }
            }
            foreach ($newmarks as $level => $row) {
                if (!array_key_exists($level, $oldmarks)) {
                    $this->logAction($test_id, $lng->txtlng("assessment", "log_mark_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " .
                        $lng->txtlng("assessment", "tst_mark_minimum_level", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["minimum_level"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_short_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["short_name"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_official_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["official_name"] . ", " .
                        $lng->txtlng("assessment", "tst_mark_passed", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["passed"]);
                }
            }
        }
    }

    public function loadFromDb(int $test_id) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (!$test_id) {
            return;
        }
        $result = $ilDB->queryF(
            "SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
            array('integer'),
            array($test_id)
        );
        if ($result->numRows() > 0) {
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($data = $ilDB->fetchAssoc($result)) {
                $this->addMarkStep($data["short_name"], $data["official_name"], (float) $data["minimum_level"], (int) $data["passed"]);
            }
        }
    }
  
    public function flush() : void
    {
        $this->mark_steps = array();
    }
  
    /**
     * Sorts the mark schema using the minimum level values.
     *
     * @see $mark_steps
     */
    public function sort() : void
    {
        function level_sort($a, $b) : int
        {
            if ($a->getMinimumLevel() == $b->getMinimumLevel()) {
                $res = strcmp($a->getShortName(), $b->getShortName());
                if ($res == 0) {
                    return strcmp($a->getOfficialName(), $b->getOfficialName());
                } else {
                    return $res;
                }
            }
            return ($a->getMinimumLevel() < $b->getMinimumLevel()) ? -1 : 1;
        }
        usort($this->mark_steps, 'level_sort');
    }
  
    /**
     * Deletes the mark step with a given index.
     *
     * @see $mark_steps
     *
     * @param integer $index The index of the mark step to delete.
     */
    public function deleteMarkStep($index = 0)
    {
        if ($index < 0) {
            return;
        }
        if (count($this->mark_steps) < 1) {
            return;
        }
        if ($index >= count($this->mark_steps)) {
            return;
        }
        unset($this->mark_steps[$index]);
        $this->mark_steps = array_values($this->mark_steps);
    }

    /**
     * Deletes multiple mark steps using their index positions.
     * @param array $indexes An array with all the index positions to delete.
     */
    public function deleteMarkSteps(array $indexes) : void
    {
        foreach ($indexes as $key => $index) {
            if (!(($index < 0) or (count($this->mark_steps) < 1))) {
                unset($this->mark_steps[$index]);
            }
        }
        $this->mark_steps = array_values($this->mark_steps);
    }

    /**
     * Returns the matching mark for a given percentage.
     *
     * @see $mark_steps
     *
     * @param double $percentage A percentage value between 0 and 100.
     *
     * @return ASS_Mark|bool The mark object, if a matching mark was found, false otherwise.
     */
    public function getMatchingMark($percentage)
    {
        for ($i = count($this->mark_steps) - 1; $i >= 0; $i--) {
            $curMinLevel = $this->mark_steps[$i]->getMinimumLevel();
            $reached = round($percentage, 2);
            $level = round($curMinLevel, 2);
            if ($reached >= $level) {
                return $this->mark_steps[$i];
            }
        }
        return false;
    }
  
    /**
     * Returns the matching mark for a given percentage.
     *
     * @see $mark_steps
     *
     * @param integer 	$test_id 	The database id of the test.
     * @param double 	$percentage	A percentage value between 0 and 100.
     *
     * @return false|ASS_Mark The mark object, if a matching mark was found, false otherwise.
     */
    public static function _getMatchingMark($test_id, $percentage)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level DESC",
            array('integer'),
            array($test_id)
        );

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($percentage >= $row["minimum_level"]) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Returns the matching mark for a given percentage.
     *
     * @see $mark_steps
     *
     * @param integer	$a_obj_id 	The database id of the test.
     *
     * @return false|ASS_Mark The mark object, if a matching mark was found, false otherwise.
     */
    public static function _getMatchingMarkFromObjId($a_obj_id, float $percentage)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tst_mark.* FROM tst_mark, tst_tests WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.obj_fi = %s ORDER BY minimum_level DESC",
            array('integer'),
            array($a_obj_id)
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($percentage >= $row["minimum_level"]) {
                return $row;
            }
        }
        return false;
    }
    
    /**
     * Returns the matching mark for a given percentage
     *
     * @see $mark_steps
     *
     * @param int 		$active_id 	The database id of the test
     * @param double 	$percentage A percentage value between 0 and 100
     *
     * @return ASS_Mark|bool The mark object, if a matching mark was found, false otherwise
    */
    public static function _getMatchingMarkFromActiveId($active_id, $percentage)
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tst_mark.* FROM tst_active, tst_mark, tst_tests WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s ORDER BY minimum_level DESC",
            array('integer'),
            array($active_id)
        );
        
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($percentage >= $row["minimum_level"]) {
                return $row;
            }
        }
        return false;
    }
    
    /**
     * Check the marks for consistency.
     *
     * @see $mark_steps
     *
     * @return bool|string true if the check succeeds, als a text string containing a language string for an error message
     */
    public function checkMarks()
    {
        $minimum_percentage = 100;
        $passed = 0;
        for ($i = 0; $i < count($this->mark_steps); $i++) {
            if ($this->mark_steps[$i]->getMinimumLevel() < $minimum_percentage) {
                $minimum_percentage = $this->mark_steps[$i]->getMinimumLevel();
            }
            if ($this->mark_steps[$i]->getPassed()) {
                $passed++;
            }
        }
        
        if ($minimum_percentage != 0) {
            return "min_percentage_ne_0";
        }
        
        if ($passed == 0) {
            return "no_passed_mark";
        }
        return true;
    }

    /**
     * @return ASS_Mark[]
     */
    public function getMarkSteps() : array
    {
        return $this->mark_steps;
    }

    /**
     * @param ASS_Mark[] $mark_steps
     */
    public function setMarkSteps(array $mark_steps) : void
    {
        $this->mark_steps = $mark_steps;
    }

    /**
     * Logs an action into the Test&Assessment log.
     */
    public function logAction($test_id, string $logtext = "") : void
    {
        /** @var $ilUser ilObjUser */
        global $DIC;
        $ilUser = $DIC['ilUser'];
        include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
        ilObjAssessmentFolder::_addLog($ilUser->getId(), ilObjTest::_getObjectIDFromTestID($test_id), $logtext, "", "", true);
    }
}
