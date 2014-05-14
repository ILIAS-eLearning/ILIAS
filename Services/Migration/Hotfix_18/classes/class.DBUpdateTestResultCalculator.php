<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

define("SCORE_LAST_PASS", 0);
define("SCORE_BEST_PASS", 1);

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class DBUpdateTestResultCalculator
{
	/**
	 * type setting value for fixed question set
	 */
	const QUESTION_SET_TYPE_FIXED = 'FIXED_QUEST_SET';

	/**
	 * type setting value for random question set
	 */
	const QUESTION_SET_TYPE_RANDOM = 'RANDOM_QUEST_SET';

	/**
	 * type setting value for dynamic question set (continues testing mode)
	 */
	const QUESTION_SET_TYPE_DYNAMIC = 'DYNAMIC_QUEST_SET';

	public static function _updateTestPassResults($active_id, $pass, $obligationsEnabled, $questionSetType, $objId)
	{
		global $ilDB;

		$data = self::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass, $questionSetType);
		$time = self::_getWorkingTimeOfParticipantForPass($active_id, $pass);

		// update test pass results

		$result = $ilDB->queryF("
			SELECT		SUM(points) reachedpoints,
						SUM(hint_count) hint_count,
						SUM(hint_points) hint_points,
						COUNT(DISTINCT(question_fi)) answeredquestions
			FROM		tst_test_result
			WHERE		active_fi = %s
			AND			pass = %s
			",
			array('integer','integer'),
			array($active_id, $pass)
		);

		if ($result->numRows() > 0)
		{
			if( $obligationsEnabled )
			{
				$query = '
					SELECT		count(*) cnt,
								min( answered ) answ
					FROM		tst_test_question
					INNER JOIN	tst_active
					ON			active_id = %s
					AND			tst_test_question.test_fi = tst_active.test_fi
					LEFT JOIN	tst_test_result
					ON			tst_test_result.active_fi = %s
					AND			tst_test_result.pass = %s
					AND			tst_test_question.question_fi = tst_test_result.question_fi
					WHERE		obligatory = 1';

				$result_obligatory = $ilDB->queryF(
					$query, array('integer','integer','integer'), array($active_id, $active_id, $pass)
				);

				$row_obligatory = $ilDB->fetchAssoc($result_obligatory);

				if ($row_obligatory['cnt'] == 0)
				{
					$obligations_answered = 1;
				}
				else
				{
					$obligations_answered = (int) $row_obligatory['answ'];
				}
			}
			else
			{
				$obligations_answered = 1;
			}

			$row = $ilDB->fetchAssoc($result);

			if( $row['hint_count'] === null ) $row['hint_count'] = 0;
			if( $row['hint_points'] === null ) $row['hint_points'] = 0;

			$exam_identifier = self::getExamId( $active_id, $pass, $objId );

			/** @var $ilDB ilDB */
			$ilDB->replace('tst_pass_result',
				array(
					'active_fi' 			=> array('integer', $active_id),
					'pass' 					=> array('integer', strlen($pass) ? $pass : 0)),
				array(
					'points'				=> array('float', 	$row['reachedpoints'] ? $row['reachedpoints'] : 0),
					'maxpoints'				=> array('float', 	$data['points']),
					'questioncount'			=> array('integer', $data['count']),
					'answeredquestions'		=> array('integer', $row['answeredquestions']),
					'workingtime'			=> array('integer', $time),
					'tstamp'				=> array('integer', time()),
					'hint_count'			=> array('integer', $row['hint_count']),
					'hint_points'			=> array('float', 	$row['hint_points']),
					'obligations_answered'	=> array('integer', $obligations_answered),
					'exam_id'				=> array('text', 	$exam_identifier)
				)
			);
		}
	}
	
	private static function _getQuestionCountAndPointsForPassOfParticipant($active_id, $pass, $questionSetType)
	{
		global $ilDB;

		switch( $questionSetType )
		{
			case self::QUESTION_SET_TYPE_DYNAMIC:

				$res = $ilDB->queryF("
						SELECT		COUNT(qpl_questions.question_id) qcount,
									SUM(qpl_questions.points) qsum
						FROM		tst_active
						INNER JOIN	tst_tests
						ON			tst_tests.test_id = tst_active.test_fi
						INNER JOIN	tst_dyn_quest_set_cfg
						ON          tst_dyn_quest_set_cfg.test_fi = tst_tests.test_id
						INNER JOIN  qpl_questions
						ON          qpl_questions.obj_fi = tst_dyn_quest_set_cfg.source_qpl_fi
						AND         qpl_questions.original_id IS NULL
						AND         qpl_questions.complete = %s
						WHERE		tst_active.active_id = %s
					",
					array('integer', 'integer'),
					array(1, $active_id)
				);

				break;

			case self::QUESTION_SET_TYPE_RANDOM:

				$res = $ilDB->queryF("
						SELECT		tst_test_rnd_qst.pass,
									COUNT(tst_test_rnd_qst.question_fi) qcount,
									SUM(qpl_questions.points) qsum

						FROM		tst_test_rnd_qst,
									qpl_questions

						WHERE		tst_test_rnd_qst.question_fi = qpl_questions.question_id
						AND			tst_test_rnd_qst.active_fi = %s
						AND			pass = %s

						GROUP BY	tst_test_rnd_qst.active_fi,
									tst_test_rnd_qst.pass
					",
					array('integer', 'integer'),
					array($active_id, $pass)
				);

				break;

			case self::QUESTION_SET_TYPE_FIXED:

				$res = $ilDB->queryF("
						SELECT		COUNT(tst_test_question.question_fi) qcount,
									SUM(qpl_questions.points) qsum
						
						FROM		tst_test_question,
									qpl_questions,
									tst_active
						
						WHERE		tst_test_question.question_fi = qpl_questions.question_id
						AND			tst_test_question.test_fi = tst_active.test_fi
						AND			tst_active.active_id = %s
						
						GROUP BY	tst_test_question.test_fi
					",
					array('integer'),
					array($active_id)
				);

				break;

			default:

				throw new ilTestException("not supported question set type: $questionSetType");
		}

		$row = $ilDB->fetchAssoc($res);

		if( is_array($row) )
		{
			return array("count" => $row["qcount"], "points" => $row["qsum"]);
		}

		return array("count" => 0, "points" => 0);
	}

	private static function _getWorkingTimeOfParticipantForPass($active_id, $pass)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
			array('integer','integer'),
			array($active_id, $pass)
		);
		$time = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$time += ($epoch_2 - $epoch_1);
		}
		return $time;
	}
	
	private static function getExamId($active_id, $pass, $obj_id)
	{
		/** @TODO Move this to a proper place. */
		global $ilDB;

		$ilSetting = new ilSetting();

		$exam_id_query  = 'SELECT exam_id FROM tst_pass_result WHERE active_fi = %s AND pass = %s';
		$exam_id_result = $ilDB->queryF( $exam_id_query, array( 'integer', 'integer' ), array( $active_id, $pass ) );
		if ($ilDB->numRows( $exam_id_result ) == 1)
		{
			$exam_id_row = $ilDB->fetchAssoc( $exam_id_result );

			if ($exam_id_row['exam_id'] != null)
			{
				return $exam_id_row['exam_id'];
			}
		}

		$inst_id = $ilSetting->get( 'inst_id', null );
		return 'I' . $inst_id . '_T' . $obj_id . '_A' . $active_id . '_P' . $pass;
	}

	public static function _updateTestResultCache($active_id, $passScoring)
	{
		global $ilDB;

		$pass = self::_getResultPass($active_id, $passScoring);

		$query = "
			SELECT		tst_pass_result.*
			FROM		tst_pass_result
			WHERE		active_fi = %s
			AND			pass = %s
		";

		$result = $ilDB->queryF(
			$query, array('integer','integer'), array($active_id, $pass)
		);

		$row = $ilDB->fetchAssoc($result);

		$max = $row['maxpoints'];
		$reached = $row['points'];

		$obligationsAnswered = (int)$row['obligations_answered'];

		$percentage = (!$max) ? 0 : ($reached / $max) * 100.0;

		$mark = self::_getMatchingMarkFromActiveId($active_id, $percentage);

		$isPassed = (  $mark["passed"] ? 1 : 0 );
		$isFailed = ( !$mark["passed"] ? 1 : 0 );

		$query = "
			DELETE FROM		tst_result_cache
			WHERE			active_fi = %s
		";

		$affectedRows = $ilDB->manipulateF(
			$query, array('integer'), array($active_id)
		);

		$ilDB->insert('tst_result_cache', array(
			'active_fi'=> array('integer', $active_id),
			'pass'=> array('integer', strlen($pass) ? $pass : 0),
			'max_points'=> array('float', strlen($max) ? $max : 0),
			'reached_points'=> array('float', strlen($reached) ? $reached : 0),
			'mark_short'=> array('text', strlen($mark["short_name"]) ? $mark["short_name"] : " "),
			'mark_official'=> array('text', strlen($mark["official_name"]) ? $mark["official_name"] : " "),
			'passed'=> array('integer', $isPassed),
			'failed'=> array('integer', $isFailed),
			'tstamp'=> array('integer', time()),
			'hint_count'=> array('integer', $row['hint_count']),
			'hint_points'=> array('float', $row['hint_points']),
			'obligations_answered' => array('integer', $obligationsAnswered)
		));
	}

	private static function _getResultPass($active_id, $passScoring)
	{
		$counted_pass = NULL;
		if ($passScoring == SCORE_BEST_PASS)
		{
			$counted_pass = self::_getBestPass($active_id);
		}
		else
		{
			$counted_pass = self::_getMaxPass($active_id);
		}
		return $counted_pass;
	}
	
	private static function _getBestPass($active_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM tst_pass_result WHERE active_fi = %s",
			array('integer'),
			array($active_id)
		);
		if ($result->numRows())
		{
			$bestrow = null;
			$bestfactor = 0;
			while ($row = $ilDB->fetchAssoc($result))
			{
				if($row["maxpoints"] > 0)
				{
					$factor = $row["points"] / $row["maxpoints"];
				}
				else
				{
					$factor = 0;
				}

				if($factor > $bestfactor)
				{
					$bestrow = $row;
					$bestfactor = $factor;
				}
			}
			if (is_array($bestrow))
			{
				return $bestrow["pass"];
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}

	private static function _getMaxPass($active_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT MAX(pass) maxpass FROM tst_test_result WHERE active_fi = %s",
			array('integer'),
			array($active_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$max = $row["maxpass"];
		}
		else
		{
			$max = NULL;
		}
		return $max;
	}

	private static function _getMatchingMarkFromActiveId($active_id, $percentage)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$result = $ilDB->queryF("SELECT tst_mark.* FROM tst_active, tst_mark, tst_tests WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s ORDER BY minimum_level DESC",
			array('integer'),
			array($active_id)
		);

		/** @noinspection PhpAssignmentInConditionInspection */
		while ($row = $ilDB->fetchAssoc($result))
		{
			if ($percentage >= $row["minimum_level"])
			{
				return $row;
			}
		}
		return FALSE;
	}
} 