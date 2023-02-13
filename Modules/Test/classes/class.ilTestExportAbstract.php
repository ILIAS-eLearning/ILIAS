<?php

declare(strict_types=1);

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
 * @author Fabian Helfer <fhelfer@databay.de>
 */
abstract class ilTestExportAbstract
{
    protected ilTestEvaluationData $complete_data;
    protected array $aggregated_data;
    protected array $additionalFields;
    protected ilObjTest $test_obj;
    protected ilLanguage $lng;
    protected string $filter_key_participants;
    protected string $filtertext;
    protected bool $passedonly;

    public function __construct(
        ilObjTest $test_obj,
        string $filter_key_participants = ilTestEvaluationData::FILTER_BY_NONE,
        string $filtertext = '',
        bool $passedonly = false,
        ilLanguage $lng = null
    ) {
        global $DIC;
        $this->test_obj = $test_obj;
        $this->filter_key_participants = $filter_key_participants;
        $this->filtertext = $filtertext;
        $this->passedonly = $passedonly;
        $this->lng = $lng ?? $DIC->language();
        $this->complete_data = $this->test_obj->getCompleteEvaluationData(true, $this->filter_key_participants, $this->filtertext);
        $this->aggregated_data = $this->test_obj->getAggregatedResultsData();
        $this->additionalFields = $this->test_obj->getEvaluationAdditionalFields();
    }

    abstract public function deliver(string $title): void;
    abstract public function getContent(): ilAssExcelFormatHelper|string;

    public function getDatarows(ilObjTest $test_obj): array
    {
        $test_obj->setAccessFilteredParticipantList(
            $test_obj->buildStatisticsAccessFilteredParticipantList()
        );

        $headerrow = $this->getHeaderRow($this->lng, $test_obj);
        $counter = 1;
        $rows = [];
        foreach ($this->complete_data->getParticipants() as $active_id => $userdata) {
            $datarow = $headerrow;
            $remove = false;
            if ($this->passedonly && !$this->complete_data->getParticipant($active_id)->getPassed()) {
                continue;
            }
            $datarow2 = [];
            if ($test_obj->getAnonymity()) {
                $datarow2[] = $counter;
            } else {
                $datarow2[] = $this->complete_data->getParticipant($active_id)->getName();
                $datarow2[] = $this->complete_data->getParticipant($active_id)->getLogin();
            }
            if (count($this->additionalFields)) {
                $userfields = ilObjUser::_lookupFields($userdata->getUserID());
                foreach ($this->additionalFields as $fieldname) {
                    if (strcmp($fieldname, "gender") === 0) {
                        $datarow2[] = $userfields[$fieldname] !== '' ? $this->lng->txt(
                            'gender_' . $userfields[$fieldname]
                        ) : '';
                    } elseif (strcmp($fieldname, "exam_id") === 0) {
                        $datarow2[] = $userdata->getExamIdFromScoredPass();
                    } else {
                        $datarow2[] = $userfields[$fieldname];
                    }
                }
            }
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getReached();
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getMaxpoints();
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getMark();
            if ($test_obj->getECTSOutput()) {
                $datarow2[] = $this->complete_data->getParticipant($active_id)->getECTSMark();
            }
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getQuestionsWorkedThrough();
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getNumberOfQuestions();
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent() / 100.0;
            $time = $this->complete_data->getParticipant($active_id)->getTimeOfWork();
            $time_seconds = $time;
            $time_hours = floor($time_seconds / 3600);
            $time_seconds -= $time_hours * 3600;
            $time_minutes = floor($time_seconds / 60);
            $time_seconds -= $time_minutes * 60;
            $datarow2[] = sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds);
            $time = $this->complete_data->getParticipant($active_id)->getQuestionsWorkedThrough() ? $this->complete_data->getParticipant(
                $active_id
            )->getTimeOfWork() / $this->complete_data->getParticipant($active_id)->getQuestionsWorkedThrough() : 0;
            $time_seconds = $time;
            $time_hours = floor($time_seconds / 3600);
            $time_seconds -= $time_hours * 3600;
            $time_minutes = floor($time_seconds / 60);
            $time_seconds -= $time_minutes * 60;
            $datarow2[] = sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds);

            $fv = $this->complete_data->getParticipant($active_id)->getFirstVisit();
            $lv = $this->complete_data->getParticipant($active_id)->getLastVisit();
            foreach ([$fv, $lv] as $ts) {
                if ($ts) {
                    $visit = ilDatePresentation::formatDate(new ilDateTime($ts, IL_CAL_UNIX));
                    $datarow2[] = $visit;
                } else {
                    $datarow2[] = "";
                }
            }

            $median = $this->complete_data->getStatistics()->getStatistics()->median();
            $pct = $this->complete_data->getParticipant($active_id)->getMaxpoints() ? $median / $this->complete_data->getParticipant(
                $active_id
            )->getMaxpoints() * 100.0 : 0;
            $mark = $test_obj->mark_schema->getMatchingMark($pct);
            $mark_short_name = "";
            if (is_object($mark)) {
                $mark_short_name = $mark->getShortName();
            }
            $datarow2[] = $mark_short_name;
            $datarow2[] = $this->complete_data->getStatistics()->getStatistics()->rank(
                $this->complete_data->getParticipant($active_id)->getReached()
            );
            $datarow2[] = $this->complete_data->getStatistics()->getStatistics()->rank_median();
            $datarow2[] = $this->complete_data->getStatistics()->getStatistics()->count();
            $datarow2[] = $median;

            $datarow2[] = $this->complete_data->getParticipant($active_id)->getPassCount();
            $datarow2[] = $this->complete_data->getParticipant($active_id)->getFinishedPasses();
            if ($test_obj->getPassScoring() === SCORE_BEST_PASS) {
                $datarow2[] = $this->complete_data->getParticipant($active_id)->getBestPass() + 1;
            } else {
                $datarow2[] = $this->complete_data->getParticipant($active_id)->getLastPass() + 1;
            }
            for ($pass = 0; $pass <= $this->complete_data->getParticipant($active_id)->getLastPass(); $pass++) {
                $finishdate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);
                if ($finishdate > 0) {
                    if ($pass > 0) {
                        for ($i = 1, $iMax = count($headerrow); $i < $iMax; $i++) {
                            $datarow2[] = "";
                            $datarow[] = "";
                        }
                        $datarow[] = "";
                    }
                    $datarow2[] = $pass + 1;
                    if (is_object($this->complete_data->getParticipant($active_id)) && is_array(
                        $evaluated_questions = $this->complete_data->getParticipant($active_id)->getQuestions($pass)
                    )) {
                        $questions = $this->orderQuestions($evaluated_questions);
                        foreach ($questions as $question) {
                            $question_data = $this->complete_data->getParticipant($active_id)->getPass(
                                $pass
                            )->getAnsweredQuestionByQuestionId($question["id"]);
                            if (is_null($question_data)) {
                                $question_data = ['reached' => 0];
                            }
                            $datarow2[] = $question_data["reached"];
                            $datarow[] = preg_replace("/<.*?>/", "", $this->complete_data->getQuestionTitle($question["id"]));
                        }
                    }
                    if (($counter === 1 && $pass === 0) || $test_obj->isRandomTest()) {
                        $rows[] = $datarow;
                    }
                    $datarow = array();
                    $rows[] = $datarow2;
                    $datarow2 = array();
                }
            }
            $counter++;
        }

        return $rows;
    }

    public function getHeaderRow(ilLanguage $lng, ilObjTest $test_obj): array
    {
        if ($test_obj->getAnonymity()) {
            $datarow[] = $lng->txt("counter");
        } else {
            $datarow[] = $lng->txt("name");
            $datarow[] = $lng->txt("login");
        }
        if (count($this->additionalFields)) {
            foreach ($this->additionalFields as $fieldname) {
                if (strcmp($fieldname, "exam_id") === 0) {
                    $datarow[] = $lng->txt('exam_id_label');
                    continue;
                }
                $datarow[] = $lng->txt($fieldname);
            }
        }
        $datarow[] = $this->lng->txt("tst_stat_result_resultspoints");
        $datarow[] = $lng->txt("maximum_points");
        $datarow[] = $lng->txt("tst_stat_result_resultsmarks");
        if ($test_obj->getECTSOutput()) {
            $datarow[] = $lng->txt("ects_grade");
        }
        $datarow[] = $lng->txt("tst_stat_result_qworkedthrough");
        $datarow[] = $lng->txt("tst_stat_result_qmax");
        $datarow[] = $lng->txt("tst_stat_result_pworkedthrough");
        $datarow[] = $lng->txt("tst_stat_result_timeofwork");
        $datarow[] = $lng->txt("tst_stat_result_atimeofwork");
        $datarow[] = $lng->txt("tst_stat_result_firstvisit");
        $datarow[] = $lng->txt("tst_stat_result_lastvisit");
        $datarow[] = $lng->txt("tst_stat_result_mark_median");
        $datarow[] = $lng->txt("tst_stat_result_rank_participant");
        $datarow[] = $lng->txt("tst_stat_result_rank_median");
        $datarow[] = $lng->txt("tst_stat_result_total_participants");
        $datarow[] = $lng->txt("tst_stat_result_median");
        $datarow[] = $lng->txt("tst_tbl_col_started_passes");
        $datarow[] = $lng->txt("tst_tbl_col_finished_passes");
        $datarow[] = $lng->txt("scored_pass");
        $datarow[] = $lng->txt("pass");

        return $datarow;
    }

    protected function orderQuestions(array $questions): array
    {
        $key = $this->test_obj->isRandomTest() ? 'qid' : 'sequence';
        usort(
            $questions,
            static function ($a, $b) use ($key) {
                if (isset($a[$key], $b[$key]) && $a[$key] > $b[$key]) {
                    return 1;
                }
                return -1;
            }
        );
        return $questions;
    }
}
