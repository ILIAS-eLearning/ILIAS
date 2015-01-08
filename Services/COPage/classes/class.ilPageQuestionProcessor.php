<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page question processor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageQuestionProcessor
{
	/**
	 * constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{

	}


	/**
	 * Save question answer
	 *
	 * @param
	 * @return
	 */
	static function saveQuestionAnswer($a_type, $a_id, $a_answer)
	{
		global $ilUser, $ilLog, $ilDB;
//$a_type = "assOrderingQuestion";
//$a_id = 74;
//$a_answer = '{"tries":1,"wrong":2,"passed":false,"answer":[true,true,false,true,false],"interactionId":null,"choice":["1","2","5","4","3"]}';
		$ilLog->write($a_type);
		$ilLog->write($a_id);
		$ilLog->write($a_answer);
		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$answer = ilJsonUtil::decode($a_answer);
		$tries = $answer->tries;
		$passed = $answer->passed;
		$choice = $answer->choice;
		$points = self::calculatePoints($a_type, $a_id, $choice);
		$ilLog->write("Points: ".$points);

		$set = $ilDB->query("SELECT * FROM page_qst_answer WHERE ".
			" qst_id = ".$ilDB->quote($a_id, "integer")." AND ".
			" user_id = ".$ilDB->quote($ilUser->getId(), "integer")
			);
		
		/*
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE page_qst_answer SET ".
				" try = try + 1,".
				" passed = ".$ilDB->quote($passed, "integer").",".
				" points = ".$ilDB->quote($points, "float").
				" WHERE qst_id = ".$ilDB->quote($a_id, "integer").
				" AND user_id = ".$ilDB->quote($ilUser->getId(), "integer")
				);
		}
		else
		{
			$ilDB->manipulate("INSERT INTO page_qst_answer ".
				"(qst_id, user_id, try, passed, points) VALUES (".
				$ilDB->quote($a_id, "integer").",".
				$ilDB->quote($ilUser->getId(), "integer").",".
				$ilDB->quote(1, "integer").",".
				$ilDB->quote($passed, "integer").",".
				$ilDB->quote($points, "float").
				")");
		}
		*/
		
		// #15146
		if (!$ilDB->fetchAssoc($set))
		{
			$ilDB->replace("page_qst_answer",
				array(
					"qst_id" => array("integer", $a_id),
					"user_id" => array("integer", $ilUser->getId())
				),
				array(
					"try" => array("integer", 1),
					"passed" => array("integer", $passed),
					"points" => array("float", $points)
				)
			);
		}
		else
		{
			$ilDB->manipulate("UPDATE page_qst_answer SET ".
				" try = try + 1,".
				" passed = ".$ilDB->quote($passed, "integer").",".
				" points = ".$ilDB->quote($points, "float").
				" WHERE qst_id = ".$ilDB->quote($a_id, "integer").
				" AND user_id = ".$ilDB->quote($ilUser->getId(), "integer")
			);
		}
	}

	/**
	 * Get statistics for question
	 *
	 * @param	int		question id
	 * @return	array
	 */
	static function getQuestionStatistics($a_q_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE ".
			" qst_id = ".$ilDB->quote($a_q_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		$all = $rec["usr_cnt"];

		$first = false;
		$second = false;
		$third_or_more = false;

		if ($all > 0)
		{
			$set = $ilDB->query("SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE ".
				" qst_id = ".$ilDB->quote($a_q_id, "integer")." AND ".
				" passed = ".$ilDB->quote(1, "integer")." AND ".
				" try = ".$ilDB->quote(1, "integer")
				);
			$rec = $ilDB->fetchAssoc($set);
			$first = $rec["usr_cnt"];
			
			$set = $ilDB->query("SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE ".
				" qst_id = ".$ilDB->quote($a_q_id, "integer")." AND ".
				" passed = ".$ilDB->quote(1, "integer")." AND ".
				" try = ".$ilDB->quote(2, "integer")
				);
			$rec = $ilDB->fetchAssoc($set);
			$second = $rec["usr_cnt"];

			$set = $ilDB->query($q = "SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE ".
				" qst_id = ".$ilDB->quote($a_q_id, "integer")." AND ".
				" passed = ".$ilDB->quote(1, "integer")." AND ".
				" try >= ".$ilDB->quote(3, "integer")
				);
			$rec = $ilDB->fetchAssoc($set);
			$third_or_more = $rec["usr_cnt"];
		}

		return array("all" => $all, "first" => $first, "second" => $second, "third_or_more" => $third_or_more);
	}

	/**
	 * Calculate points
	 *
	 * This function calculates the points for a given answer.
	 * Better would be to re-use from T&A here in the future.
	 * When this code has been written this has not been possible yet.
	 *
	 * @param
	 * @return
	 */
	static function calculatePoints($a_type, $a_id, $a_choice)
	{
		global $ilLog;

		switch ($a_type)
		{
			case "assSingleChoice":
				include_once("./Modules/TestQuestionPool/classes/class.assSingleChoice.php");
				$q = new assSingleChoice();
				$q->loadFromDb($a_id);
				$points = 0;
				foreach ($q->getAnswers() as $key => $answer)
				{
					if (isset($a_choice[0]) && $key == $a_choice[0])
					{
						$points += $answer->getPoints();
					}
				}
				break;

			case "assMultipleChoice":
				include_once("./Modules/TestQuestionPool/classes/class.assMultipleChoice.php");
				$q = new assMultipleChoice();
				$q->loadFromDb($a_id);
				$points = 0;
				foreach ($q->getAnswers() as $key => $answer)
				{
					if (is_array($a_choice) && in_array($key, $a_choice))
					{
						$points += $answer->getPoints();
					}
					else
					{
						$points += $answer->getPointsUnchecked();
					}
				}
				break;

			case "assClozeTest":
				include_once("./Modules/TestQuestionPool/classes/class.assClozeTest.php");
				$q = new assClozeTest();
				$q->loadFromDb($a_id);
				$points = 0;
				foreach ($q->getGaps() as $id => $gap)
				{
					$choice = $a_choice[$id];
					switch ($gap->getType())
					{
						case CLOZE_TEXT:
							$gappoints = 0;
							for ($order = 0; $order < $gap->getItemCount(); $order++)
							{
								$answer = $gap->getItem($order);
								$gotpoints = $q->getTextgapPoints($answer->getAnswertext(),
									$choice, $answer->getPoints());
								if ($gotpoints > $gappoints) $gappoints = $gotpoints;
							}
							$points += $gappoints;
//$ilLog->write("ct: ".$gappoints);
							break;

						case CLOZE_NUMERIC:
							$gappoints = 0;
							for ($order = 0; $order < $gap->getItemCount(); $order++)
							{
								$answer = $gap->getItem($order);
								$gotpoints = $q->getNumericgapPoints($answer->getAnswertext(),
									$choice, $answer->getPoints(),
									$answer->getLowerBound(), $answer->getUpperBound());
								if ($gotpoints > $gappoints) $gappoints = $gotpoints;
							}
							$points += $gappoints;
//$ilLog->write("cn: ".$gappoints);
							break;

						case CLOZE_SELECT:
							for ($order = 0; $order < $gap->getItemCount(); $order++)
							{
								$answer = $gap->getItem($order);
								if ($choice == $answer->getOrder())
								{
									$answerpoints = $answer->getPoints();
									$points += $answerpoints;
//$ilLog->write("cs: ".$answerpoints);
								}
							}
							break;
					}
				}
				break;

			case "assMatchingQuestion":
				include_once("./Modules/TestQuestionPool/classes/class.assMatchingQuestion.php");
				$q = new assMatchingQuestion();
				$q->loadFromDb($a_id);
				$points = 0;
				for ($i = 0; $i < $q->getMatchingPairCount(); $i++)
				{
					$pair = $q->getMatchingPair($i);
					if (is_array($a_choice) && in_array($pair->definition->identifier."-".$pair->term->identifier, $a_choice))
					{
						$points += $pair->points;
					}
				}
				break;

			case "assOrderingQuestion":
				include_once("./Modules/TestQuestionPool/classes/class.assOrderingQuestion.php");
				$q = new assOrderingQuestion();
				$q->loadFromDb($a_id);
				$points = 0;
				$cnt = 1;
				$right = true;
				foreach ($q->getAnswers() as $answer)
				{
					if ($a_choice[$cnt - 1] != $cnt)
					{
						$right = false;
					}
					$cnt++;
				}
				if ($right)
				{
					$points = $q->getPoints();
				}
				break;

			case "assImagemapQuestion":
				include_once("./Modules/TestQuestionPool/classes/class.assImagemapQuestion.php");
				$q = new assImagemapQuestion();
				$q->loadFromDb($a_id);
				$points = 0;

				foreach ($q->getAnswers() as $key => $answer)
				{
					if (is_array($a_choice) && in_array($key, $a_choice))
					{
						$points += $answer->getPoints();
					}
				}
				break;

		}

		if ($points < 0)
		{
			$points = 0;
		}

		return (int) $points;
	}

	/**
	 * Get statistics for question
	 *
	 * @param	int		question id
	 * @return	array
	 */
	static function getAnswerStatus($a_q_id, $a_user_id = 0)
	{
		global $ilDB;

		$qst = (is_array($a_q_id))
			? $ilDB->in("qst_id", $a_q_id, false, "integer")
			: " qst_id = ".$ilDB->quote($a_q_id, "integer");

		$and = ($a_user_id > 0)
			? " AND user_id = ".$ilDB->quote($a_user_id, "integer")
			: "";

		$set = $ilDB->query("SELECT * FROM page_qst_answer WHERE ".
			$qst.
			$and
		);

		if (is_array($a_q_id))
		{
			$recs = array();
			while ($rec = $ilDB->fetchAssoc($set))
			{
				$recs[$rec["qst_id"]] = $rec;
			}
			return $recs;
		}
		else
		{
			return $ilDB->fetchAssoc($set);
		}
	}

	/**
	 * Reset tries
	 *
	 * @param int $a_q_id question id
	 * @param int $a_user_id user id
	 */
	static function resetTries($a_q_id, $a_user_id)
	{
		global $ilDB;

		$ilDB->manipulate($q = "UPDATE page_qst_answer SET ".
				" try = ".$ilDB->quote(0, "integer").",".
				" passed = ".$ilDB->quote(0, "integer").",".
				" points = ".$ilDB->quote(0, "integer").",".
				" unlocked = ".$ilDB->quote(0, "integer").
				" WHERE qst_id = ".$ilDB->quote($a_q_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer")
		);
	}

	/**
	 * Reset tries
	 *
	 * @param int $a_q_id question id
	 * @param int $a_user_id user id
	 */
	static function unlock($a_q_id, $a_user_id)
	{
		global $ilDB;

		$ilDB->manipulate($q = "UPDATE page_qst_answer SET ".
				" unlocked = ".$ilDB->quote(1, "integer").
				" WHERE qst_id = ".$ilDB->quote($a_q_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer")
		);
	}


}
?>
