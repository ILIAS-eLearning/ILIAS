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
 * Page question processor
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageQuestionProcessor
{
    public function __construct()
    {
    }


    public static function saveQuestionAnswer(
        string $a_type,
        int $a_id,
        string $a_answer
    ) : void {
        global $DIC;

        $ilUser = $DIC->user();
        $ilLog = $DIC["ilLog"];
        $ilDB = $DIC->database();
        $ilLog->write($a_type);
        $ilLog->write($a_id);
        $ilLog->write($a_answer);
        $answer = json_decode($a_answer, false, 512, JSON_THROW_ON_ERROR);
        $passed = $answer->passed;
        $choice = $answer->choice;
        $points = self::calculatePoints($a_type, $a_id, $choice);
        $ilLog->write("Points: " . $points);

        $set = $ilDB->query(
            "SELECT * FROM page_qst_answer WHERE " .
            " qst_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            " user_id = " . $ilDB->quote($ilUser->getId(), "integer")
        );

        // #15146
        if (!$ilDB->fetchAssoc($set)) {
            $ilDB->replace(
                "page_qst_answer",
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
        } else {
            $ilDB->manipulate(
                "UPDATE page_qst_answer SET " .
                " try = try + 1," .
                " passed = " . $ilDB->quote($passed, "integer") . "," .
                " points = " . $ilDB->quote($points, "float") .
                " WHERE qst_id = " . $ilDB->quote($a_id, "integer") .
                " AND user_id = " . $ilDB->quote($ilUser->getId(), "integer")
            );
        }
    }

    public static function getQuestionStatistics(
        int $a_q_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE " .
            " qst_id = " . $ilDB->quote($a_q_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $all = $rec["usr_cnt"];

        $first = false;
        $second = false;
        $third_or_more = false;

        if ($all > 0) {
            $set = $ilDB->query(
                "SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE " .
                " qst_id = " . $ilDB->quote($a_q_id, "integer") . " AND " .
                " passed = " . $ilDB->quote(1, "integer") . " AND " .
                " try = " . $ilDB->quote(1, "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            $first = $rec["usr_cnt"];
            
            $set = $ilDB->query(
                "SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE " .
                " qst_id = " . $ilDB->quote($a_q_id, "integer") . " AND " .
                " passed = " . $ilDB->quote(1, "integer") . " AND " .
                " try = " . $ilDB->quote(2, "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            $second = $rec["usr_cnt"];

            $set = $ilDB->query(
                $q = "SELECT count(user_id) usr_cnt FROM page_qst_answer WHERE " .
                " qst_id = " . $ilDB->quote($a_q_id, "integer") . " AND " .
                " passed = " . $ilDB->quote(1, "integer") . " AND " .
                " try >= " . $ilDB->quote(3, "integer")
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
     */
    public static function calculatePoints(
        string $a_type,
        int $a_id,
        array $a_choice
    ) : int {
        $points = 0;

        switch ($a_type) {
            case "assSingleChoice":
                $q = new assSingleChoice();
                $q->loadFromDb($a_id);
                $points = 0;
                foreach ($q->getAnswers() as $key => $answer) {
                    if (isset($a_choice[0]) && $key == $a_choice[0]) {
                        $points += $answer->getPoints();
                    }
                }
                break;

            case "assMultipleChoice":
                $q = new assMultipleChoice();
                $q->loadFromDb($a_id);
                $points = 0;
                foreach ($q->getAnswers() as $key => $answer) {
                    if (is_array($a_choice) && in_array($key, $a_choice)) {
                        $points += $answer->getPoints();
                    } else {
                        $points += $answer->getPointsUnchecked();
                    }
                }
                break;

            case "assClozeTest":
                $q = new assClozeTest();
                $q->loadFromDb($a_id);
                $points = 0;
                foreach ($q->getGaps() as $id => $gap) {
                    $choice = $a_choice[$id];
                    switch ($gap->getType()) {
                        case CLOZE_TEXT:
                            $gappoints = 0;
                            for ($order = 0; $order < $gap->getItemCount(); $order++) {
                                $answer = $gap->getItem($order);
                                $gotpoints = $q->getTextgapPoints(
                                    $answer->getAnswertext(),
                                    $choice,
                                    $answer->getPoints()
                                );
                                if ($gotpoints > $gappoints) {
                                    $gappoints = $gotpoints;
                                }
                            }
                            $points += $gappoints;
//$ilLog->write("ct: ".$gappoints);
                            break;

                        case CLOZE_NUMERIC:
                            $gappoints = 0;
                            for ($order = 0; $order < $gap->getItemCount(); $order++) {
                                $answer = $gap->getItem($order);
                                $gotpoints = $q->getNumericgapPoints(
                                    $answer->getAnswertext(),
                                    $choice,
                                    $answer->getPoints(),
                                    $answer->getLowerBound(),
                                    $answer->getUpperBound()
                                );
                                if ($gotpoints > $gappoints) {
                                    $gappoints = $gotpoints;
                                }
                            }
                            $points += $gappoints;
//$ilLog->write("cn: ".$gappoints);
                            break;

                        case CLOZE_SELECT:
                            for ($order = 0; $order < $gap->getItemCount(); $order++) {
                                $answer = $gap->getItem($order);
                                if ($choice == $answer->getOrder()) {
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
                $q = new assMatchingQuestion();
                $q->loadFromDb($a_id);
                $points = 0;
                for ($i = 0; $i < $q->getMatchingPairCount(); $i++) {
                    $pair = $q->getMatchingPair($i);
                    if (is_array($a_choice) && in_array($pair->definition->identifier . "-" . $pair->term->identifier, $a_choice)) {
                        $points += $pair->points;
                    }
                }
                break;

            case "assOrderingQuestion":
                
                // TODO-LSD: change calculation strategy according to lsd cleanup changes
                
                $q = new assOrderingQuestion();
                $q->loadFromDb($a_id);
                $points = 0;
                $cnt = 1;
                $right = true;
                foreach ($q->getOrderElements() as $answer) {
                    if ($a_choice[$cnt - 1] != $cnt) {
                        $right = false;
                    }
                    $cnt++;
                }
                if ($right) {
                    $points = $q->getPoints();
                }
                break;

            case "assImagemapQuestion":
                $q = new assImagemapQuestion();
                $q->loadFromDb($a_id);
                $points = 0;

                foreach ($q->getAnswers() as $key => $answer) {
                    if (is_array($a_choice) && in_array($key, $a_choice)) {
                        $points += $answer->getPoints();
                    }
                }
                break;

        }

        if ($points < 0) {
            $points = 0;
        }

        return (int) $points;
    }

    /**
     * @param int|array $a_q_id
     */
    public static function getAnswerStatus(
        $a_q_id,
        int $a_user_id = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $qst = (is_array($a_q_id))
            ? $ilDB->in("qst_id", $a_q_id, false, "integer")
            : " qst_id = " . $ilDB->quote($a_q_id, "integer");

        $and = ($a_user_id > 0)
            ? " AND user_id = " . $ilDB->quote($a_user_id, "integer")
            : "";

        $set = $ilDB->query(
            "SELECT * FROM page_qst_answer WHERE " .
            $qst .
            $and
        );

        if (is_array($a_q_id) || $a_user_id == 0) {
            $recs = array();
            while ($rec = $ilDB->fetchAssoc($set)) {
                $key = ($a_user_id == 0)
                    ? $rec["qst_id"] . ":" . $rec["user_id"]
                    : $rec["qst_id"];
                $recs[$key] = $rec;
            }
            return $recs;
        } else {
            return $ilDB->fetchAssoc($set);
        }
    }

    /**
     * Reset tries for user and question
     */
    public static function resetTries(
        int $a_q_id,
        int $a_user_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            $q = "UPDATE page_qst_answer SET " .
                " try = " . $ilDB->quote(0, "integer") . "," .
                " passed = " . $ilDB->quote(0, "integer") . "," .
                " points = " . $ilDB->quote(0, "integer") . "," .
                " unlocked = " . $ilDB->quote(0, "integer") .
                " WHERE qst_id = " . $ilDB->quote($a_q_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    /**
     * Unlock question for user
     */
    public static function unlock(
        int $a_q_id,
        int $a_user_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            $q = "UPDATE page_qst_answer SET " .
                " unlocked = " . $ilDB->quote(1, "integer") .
                " WHERE qst_id = " . $ilDB->quote($a_q_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }
}
