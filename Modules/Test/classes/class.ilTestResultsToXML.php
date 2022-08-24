<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilTestResultsToXML extends ilXmlWriter
{
    private int $test_id = 0;
    private bool $anonymized = false;
    private $active_ids;

    protected bool $includeRandomTestQuestionsEnabled = false;

    public function __construct($test_id, $anonymized = false)
    {
        parent::__construct();
        $this->test_id = $test_id;
        $this->anonymized = $anonymized;
    }

    public function isIncludeRandomTestQuestionsEnabled(): bool
    {
        return $this->includeRandomTestQuestionsEnabled;
    }

    public function setIncludeRandomTestQuestionsEnabled(bool $includeRandomTestQuestionsEnabled): void
    {
        $this->includeRandomTestQuestionsEnabled = $includeRandomTestQuestionsEnabled;
    }

    protected function exportActiveIDs(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
        $assessmentSetting = new ilSetting("assessment");
        $user_criteria = $assessmentSetting->get("user_criteria");
        if (strlen($user_criteria) == 0) {
            $user_criteria = 'usr_id';
        }

        if ($this->anonymized) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE test_fi = %s",
                array('integer'),
                array($this->test_id)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT tst_active.*, usr_data." . $user_criteria . " FROM tst_active, usr_data WHERE tst_active.test_fi = %s AND tst_active.user_fi = usr_data.usr_id",
                array('integer'),
                array($this->test_id)
            );
        }
        $this->xmlStartTag("tst_active", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'active_id' => $row['active_id'],
                'user_fi' => $row['user_fi'],
                'anonymous_id' => $row['anonymous_id'],
                'test_fi' => $row['test_fi'],
                'lastindex' => $row['lastindex'],
                'tries' => $row['tries'],
                'last_started_pass' => $row['last_started_pass'],
                'last_finished_pass' => $row['last_finished_pass'],
                'submitted' => $row['submitted'],
                'submittimestamp' => $row['submittimestamp'],
                'tstamp' => $row['tstamp']
            );
            $attrs['fullname'] = ilObjTestAccess::_getParticipantData($row['active_id']);
            if (!$this->anonymized) {
                $attrs['user_criteria'] = $user_criteria;
                $attrs[$user_criteria] = $row[$user_criteria];
            }
            array_push($this->active_ids, $row['active_id']);
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_active");
    }

    protected function exportPassResult(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_pass_result WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi, pass";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_pass_result", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'active_fi' => $row['active_fi'],
                'pass' => $row['pass'],
                'points' => $row['points'],
                'maxpoints' => $row['maxpoints'],
                'questioncount' => $row['questioncount'],
                'answeredquestions' => $row['answeredquestions'],
                'workingtime' => $row['workingtime'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_pass_result");
    }

    protected function exportResultCache(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_result_cache WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_result_cache", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'active_fi' => $row['active_fi'],
                'pass' => $row['pass'],
                'max_points' => $row['max_points'],
                'reached_points' => $row['reached_points'],
                'mark_short' => $row['mark_short'],
                'mark_official' => $row['mark_official'],
                'passed' => $row['passed'],
                'failed' => $row['failed'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_result_cache");
    }

    protected function exportTestSequence(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_sequence WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi, pass";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_sequence", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'active_fi' => $row['active_fi'],
                'pass' => $row['pass'],
                'sequence' => $row['sequence'],
                'postponed' => $row['postponed'],
                'hidden' => $row['hidden'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_sequence");
    }

    protected function exportTestSolutions(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_solutions WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY solution_id";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_solutions", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'solution_id' => $row['solution_id'],
                'active_fi' => $row['active_fi'],
                'question_fi' => $row['question_fi'],
                'points' => $row['points'],
                'pass' => $row['pass'],
                'value1' => $row['value1'],
                'value2' => $row['value2'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_solutions");
    }

    protected function exportRandomTestQuestions(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query("
			  SELECT * FROM tst_test_rnd_qst
			  WHERE {$ilDB->in('active_fi', $this->active_ids, false, 'integer')}
			  ORDER BY test_random_question_id
		");

        $this->xmlStartTag('tst_test_rnd_qst', null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array();

            foreach ($row as $field => $value) {
                $attrs[$field] = $value;
            }

            $this->xmlElement('row', $attrs);
        }
        $this->xmlEndTag('tst_test_rnd_qst');
    }


    protected function exportTestResults(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_test_result WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_test_result", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'test_result_id' => $row['test_result_id'],
                'active_fi' => $row['active_fi'],
                'question_fi' => $row['question_fi'],
                'points' => $row['points'],
                'pass' => $row['pass'],
                'manual' => $row['manual'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_test_result");
    }

    protected function exportTestTimes(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM tst_times WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
        $result = $ilDB->query($query);
        $this->xmlStartTag("tst_times", null);
        while ($row = $ilDB->fetchAssoc($result)) {
            $attrs = array(
                'times_id' => $row['times_id'],
                'active_fi' => $row['active_fi'],
                'started' => $row['started'],
                'finished' => $row['finished'],
                'pass' => $row['pass'],
                'tstamp' => $row['tstamp']
            );
            $this->xmlElement("row", $attrs);
        }
        $this->xmlEndTag("tst_times");
    }

    public function getXML(): void
    {
        $this->active_ids = array();
        $this->xmlHeader();
        $attrs = array("version" => "4.1.0");
        $this->xmlStartTag("results", $attrs);
        $this->exportActiveIDs();

        if ($this->isIncludeRandomTestQuestionsEnabled()) {
            $this->exportRandomTestQuestions();
        }

        $this->exportPassResult();
        $this->exportResultCache();
        $this->exportTestSequence();
        $this->exportTestSolutions();
        $this->exportTestResults();
        $this->exportTestTimes();
        $this->xmlEndTag("results");
    }

    public function xmlDumpMem(bool $format = true): string
    {
        $this->getXML();
        return parent::xmlDumpMem($format);
    }

    public function xmlDumpFile(string $file, bool $format = true): void
    {
        $this->getXML();
        parent::xmlDumpFile($file, $format);
    }
}
