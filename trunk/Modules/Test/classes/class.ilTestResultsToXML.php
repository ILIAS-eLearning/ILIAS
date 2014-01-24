<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Test results to XML class
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup ModulesTest
*/
include_once './Services/Xml/classes/class.ilXmlWriter.php';

class ilTestResultsToXML extends ilXmlWriter
{
	private $test_id = 0;
	private $anonymized = false;
	private $active_ids;

	function __construct($test_id, $anonymized = false)
	{
		parent::__construct();
		$this->test_id = $test_id;
		$this->anonymized = $anonymized;
	}
	
	protected function exportActiveIDs()
	{
		global $ilDB, $ilSetting;

		include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
		$assessmentSetting = new ilSetting("assessment");
		$user_criteria = $assessmentSetting->get("user_criteria");
		if (strlen($user_criteria) == 0) $user_criteria = 'usr_id';
		
		if ($this->anonymized)
		{
			$result = $ilDB->queryF("SELECT * FROM tst_active WHERE test_fi = %s",
				array('integer'),
				array($this->test_id)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT tst_active.*, usr_data." . $user_criteria . " FROM tst_active, usr_data WHERE tst_active.test_fi = %s AND tst_active.user_fi = usr_data.usr_id",
				array('integer'),
				array($this->test_id)
			);
		}
		$this->xmlStartTag("tst_active", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$attrs = array(
				'active_id' => $row['active_id'],
				'user_fi' => $row['user_fi'],
				'anonymous_id' => $row['anonymous_id'],
				'test_fi' => $row['test_fi'],
				'lastindex' => $row['lastindex'],
				'tries' => $row['tries'],
				'submitted' => $row['submitted'],
				'submittimestamp' => $row['submittimestamp'],
				'tstamp' => $row['tstamp']
			);
			$attrs['fullname'] = ilObjTestAccess::_getParticipantData($row['active_id']);
			if (!$this->anonymized)
			{
				$attrs['user_criteria'] = $user_criteria;
				$attrs[$user_criteria] = $row[$user_criteria];
			}
			array_push($this->active_ids, $row['active_id']);
			$this->xmlElement("row", $attrs);
		}
		$this->xmlEndTag("tst_active");
	}

	protected function exportPassResult()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_pass_result WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi, pass";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_pass_result", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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

	protected function exportResultCache()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_result_cache WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_result_cache", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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

	protected function exportTestSequence()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_sequence WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi, pass";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_sequence", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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

	protected function exportTestSolutions()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_solutions WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY solution_id";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_solutions", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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

	protected function exportTestQuestions()
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT * FROM tst_test_question WHERE test_fi = %s",
			array('integer'),
			array($this->test_id)
		);
		$this->xmlStartTag("tst_test_question", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$attrs = array(
				'test_question_id' => $row['test_question_id'],
				'test_fi' => $row['test_fi'],
				'question_fi' => $row['question_fi'],
				'sequence' => $row['sequence'],
				'tstamp' => $row['tstamp']
			);
			$this->xmlElement("row", $attrs);
		}
		$this->xmlEndTag("tst_test_question");
	}
	

	protected function exportTestResults()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_test_result WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_test_result", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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
	
	protected function exportTestTimes()
	{
		global $ilDB;
		
		$query = "SELECT * FROM tst_times WHERE " . $ilDB->in('active_fi', $this->active_ids, false, 'integer') . " ORDER BY active_fi";
		$result = $ilDB->query($query);
		$this->xmlStartTag("tst_times", NULL);
		while ($row = $ilDB->fetchAssoc($result))
		{
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
	
	function getXML()
	{
		$this->active_ids = array();
		$this->xmlHeader();
		$attrs = array("version" => "4.1.0");
		$this->xmlStartTag("results", $attrs);
		$this->exportActiveIDs();
		$this->exportTestQuestions();
		$this->exportPassResult();
		$this->exportResultCache();
		$this->exportTestSequence();
		$this->exportTestSolutions();
		$this->exportTestResults();
		$this->exportTestTimes();
		$this->xmlEndTag("results");
	}

	function xmlDumpMem($format = TRUE)
	{
		$this->getXML();
		return parent::xmlDumpMem($format);
	}

	function xmlDumpFile($file, $format = TRUE)
	{
		$this->getXML();
		return parent::xmlDumpFile($file, $format);
	}


}
?>
