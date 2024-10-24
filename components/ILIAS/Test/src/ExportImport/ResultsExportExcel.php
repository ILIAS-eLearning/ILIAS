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

namespace ILIAS\Test\ExportImport;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\DateFormat\DateFormat;

/**
 * @author Fabian Helfer <fhelfer@databay.de>
 */
class ResultsExportExcel implements Exporter
{
    public const EXCEL_BACKGROUND_COLOR = 'C0C0C0';

    private DateFormat $user_date_format;
    private array $aggregated_data;
    private ?\ilTestEvaluationData $complete_data = null;
    /**
     * @var array<string filter_field, mixed filter_value>
     */
    private array $filter = [];

    private \ilExcel $worksheet;

    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilObjUser $current_user,
        private readonly \ilObjTest $test_obj,
        private readonly string $filename = '',
        private readonly bool $scoredonly = true,
    ) {
        $this->user_date_format = $this->current_user->getDateTimeFormat();
        $this->aggregated_data = $test_obj->getAggregatedResultsData();
        $this->worksheet = new \ilExcel();
    }

    public function withFilterByActiveId(int $active_id): self
    {
        $clone = clone $this;
        $clone->filter[\ilTestEvaluationData::FILTER_BY_ACTIVE_ID] = $active_id;
        return $clone;
    }

    public function withAggregatedResultsPage(): self
    {
        $this->worksheet->addSheet($this->lng->txt('tst_results_aggregated'));

        $current_row = $this->addAggregatedOverviewHeader(1);
        $current_row = $this->addAggregatedOverviewContent($current_row);
        $current_row = $this->addAggregatedQuestionsHeader($current_row);
        $this->addAggregatedQuestionsContent($current_row);

        return $this;
    }

    public function withResultsPage(): self
    {
        $this->worksheet->addSheet($this->lng->txt('tst_results'));

        $cols_for_question_ids = $this->addResultsHeader();
        $this->addResultsContent($cols_for_question_ids);

        return $this;
    }

    public function withUserPages(): self
    {
        $usersheet_titles = [];
        foreach ($this->getCompleteData()->getParticipants() as $active_id => $user_data) {
            $active_id = 1;
            $usersheet_titles = $this->addUserSheet(
                $usersheet_titles,
                $user_data->getName(),
                $active_id
            );

            $passes = $this->getPassesDataFromUserData($user_data);
            $current_row = 1;
            foreach ($passes as $pass) {
                $pass_nr = $pass->getPass();
                $current_row = $this->addUserHeader(
                    $current_row,
                    $pass_nr,
                    $user_data->getName(),
                    $user_data->getScoredPass() === $pass_nr
                );

                $current_row = $this->addUserContent(
                    $current_row,
                    $user_data->getQuestions($pass_nr),
                    $pass,
                    $active_id
                );

                $current_row++;
            }
        }

        return $this;
    }

    public function write(): ?string
    {
        $path = \ilFileUtils::ilTempnam() . $this->filename;
        $this->worksheet->writeToFile($path);
        return $path;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function deliver(): void
    {
        $this->worksheet->sendToClient($this->filename);
    }

    public function getContent(): \ilExcel
    {
        return $this->worksheet;
    }

    private function getCompleteData(): \ilTestEvaluationData
    {
        if ($this->complete_data === null) {
            $filter_key = \ilTestEvaluationData::FILTER_BY_NONE;
            $filter_text = '';
            if ($this->filter !== []) {
                $filter_key = key($this->filter);
                $filter_text = current($this->filter);
            }
            $this->complete_data = $this->test_obj->getCompleteEvaluationData($filter_key, $filter_text);
        }
        return $this->complete_data;
    }

    private function addAggregatedOverviewHeader(int $current_row): int
    {
        $col = 0;
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('result'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('value'));

        $this->worksheet->setBold('A' . $current_row . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row);
        $this->worksheet->setColors('A' . $current_row . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row, self::EXCEL_BACKGROUND_COLOR);
        return ++$current_row;
    }

    private function addAggregatedOverviewContent(int $current_row): int
    {
        foreach ($this->aggregated_data['overview'] as $key => $value) {
            $col = 0;
            $this->worksheet->setCell($current_row, $col++, $this->lng->txt($key));
            $this->worksheet->setCell($current_row, $col++, $value);
            $current_row++;
        }
        return ++$current_row;
    }

    private function addAggregatedQuestionsHeader(int $current_row): int
    {
        $col = 0;
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('question_id'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('question_title'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('average_reached_points'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('points'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('percentage'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('number_of_answers'));

        $this->worksheet->setBold('A' . $current_row . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row);
        $this->worksheet->setColors('A' . $current_row . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row, self::EXCEL_BACKGROUND_COLOR);
        return ++$current_row;
    }

    private function addAggregatedQuestionsContent(int $current_row): int
    {
        foreach ($this->aggregated_data['questions'] as $key => $value) {
            $col = 0;
            $this->worksheet->setCell($current_row, $col++, $key);
            $this->worksheet->setCell($current_row, $col++, $value[0]);
            $this->worksheet->setCell($current_row, $col++, $value[4]);
            $this->worksheet->setCell($current_row, $col++, $value[5]);
            $this->worksheet->setCell($current_row, $col++, $value[6]);
            $this->worksheet->setCell($current_row, $col++, $value[3]);
            $current_row++;
        }
        return $current_row;
    }

    /**
     * @return array<int Question Id, int Column>
     */
    private function addResultsHeader(): array
    {
        $col = 0;

        if (!$this->scoredonly) {
            $this->worksheet->setCell(1, $col++, $this->lng->txt('scored_pass'));
        }

        $this->worksheet->setCell(
            1,
            $col++,
            $this->test_obj->getAnonymity() ? $this->lng->txt('counter') : $this->lng->txt('name')
        );

        if (!$this->test_obj->getAnonymity()) {
            $this->worksheet->setCell(1, $col++, $this->lng->txt('login'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('email'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('matriculation'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('gender'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('street'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('city'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('zipcode'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('country'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('institution'));
            $this->worksheet->setCell(1, $col++, $this->lng->txt('department'));
        }

        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_firstvisit'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_lastvisit'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_total_timeontask'));

        if ($this->test_obj->isShowExamIdInTestResultsEnabled()) {
            $this->worksheet->setCell(1, $col++, $this->lng->txt('exam_id_label'));
        }

        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_resultspoints'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('maximum_points'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_resultsmarks'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_qmax'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_qworkedthrough'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_pworkedthrough'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_timeontask'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_atimeofwork'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_stat_result_rank_participant'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_tbl_col_started_passes'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('tst_tbl_col_finished_passes'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('scored_pass'));
        $this->worksheet->setCell(1, $col++, $this->lng->txt('pass'));

        $question_cols = [];
        foreach ($this->test_obj->getQuestions() as $question_id) {
            $question_cols[$question_id] = $col;
            $this->worksheet->setCell(1, $col++, $this->getCompleteData()->getQuestionTitle($question_id));
        }

        $this->worksheet->setBold('A1:' . $this->worksheet->getColumnCoord($col - 1) . '1');
        $this->worksheet->setColors('A1:' . $this->worksheet->getColumnCoord($col - 1) . '1', self::EXCEL_BACKGROUND_COLOR);

        return $question_cols;
    }

    private function addResultsContent(array $cols_for_question_ids): void
    {
        $current_row = 2;
        foreach ($this->getCompleteData()->getParticipants() as $active_id => $user_data) {
            $userfields = $this->getUserFieldsForUserID($user_data->getUserID());
            for ($test_attempt = 0; $test_attempt <= $user_data->getLastPass(); $test_attempt++) {
                $finishdate = \ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $test_attempt);
                $is_scored_attempt = $test_attempt === $user_data->getScoredPass();
                if ($finishdate < 1
                    || $this->scoredonly && !$is_scored_attempt) {
                    continue;
                }

                /** @var \ilTestEvaluationPassData $test_attempt_data */
                $test_attempt_data = $user_data->getPass($test_attempt);
                $col = 0;

                if (!$this->scoredonly) {
                    $this->worksheet->setCell(
                        $current_row,
                        $col++,
                        $is_scored_attempt ? 'x' : ''
                    );
                }

                $this->worksheet->setCell(
                    $current_row,
                    $col++,
                    $this->test_obj->getAnonymity() ? $current_row - 1 : $user_data->getName()
                );
                if (!$this->test_obj->getAnonymity()) {
                    $this->worksheet->setCell($current_row, $col++, $user_data->getLogin());
                    $this->worksheet->setCell($current_row, $col++, $userfields['email'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['matriculation'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, isset($userfields['gender']) && $userfields['gender'] !== ''
                        ? $this->lng->txt('gender_' . $userfields['gender'])
                        : '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['street'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['city'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['zipcode'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['country'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['institution'] ?? '');
                    $this->worksheet->setCell($current_row, $col++, $userfields['departement'] ?? '');
                }

                $this->worksheet->setCell($current_row, $col++, $this->convertToUserDateFormat($user_data->getFirstVisit()));
                $this->worksheet->setCell($current_row, $col++, $this->convertToUserDateFormat($user_data->getLastVisit()));
                $this->worksheet->setCell($current_row, $col++, $this->secondsToHoursMinutesSecondsString(
                    $user_data->getQuestionsWorkedThrough() ? $user_data->getTimeOnTask() / $user_data->getQuestionsWorkedThrough() : 0
                ));

                if ($this->test_obj->isShowExamIdInTestResultsEnabled()) {
                    $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getExamId());
                }

                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getReachedPoints());
                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getMaxpoints());
                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getMark()->getShortName());
                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getQuestionCount());
                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getNrOfAnsweredQuestions());
                $this->worksheet->setCell($current_row, $col++, $test_attempt_data->getReachedPointsInPercent());
                $this->worksheet->setCell($current_row, $col++, $this->secondsToHoursMinutesSecondsString($test_attempt_data->getWorkingTime()));
                $this->worksheet->setCell($current_row, $col++, $this->secondsToHoursMinutesSecondsString(
                    $test_attempt_data->getAnsweredQuestionCount() ? $test_attempt_data->getWorkingTime() / $test_attempt_data->getAnsweredQuestionCount() : 0
                ));

                $this->worksheet->setCell(
                    $current_row,
                    $col++,
                    $this->getCompleteData()->getStatistics()->rank(
                        $test_attempt_data->getReachedPoints()
                    ) ?? ''
                );

                $this->worksheet->setCell($current_row, $col++, $user_data->getPassCount());
                $this->worksheet->setCell($current_row, $col++, $user_data->getFinishedPasses());
                if ($this->test_obj->getPassScoring() === \ilObjTest::SCORE_BEST_PASS) {
                    $this->worksheet->setCell($current_row, $col++, $user_data->getBestPass() + 1);
                } else {
                    $this->worksheet->setCell($current_row, $col++, $user_data->getLastPass() + 1);
                }
                $this->worksheet->setCell($current_row, $col++, $test_attempt + 1);

                foreach ($test_attempt_data->getAnsweredQuestions() as $question) {
                    $this->worksheet->setCell(
                        $current_row,
                        $cols_for_question_ids[$question['id']],
                        $question['reached']
                    );
                }

                $current_row++;
            }
        }
    }

    private function addUserSheet(
        array $usersheet_titles,
        string $user_name,
        int $active_id
    ): array {
        $username = mb_substr(
            $user_name !== '' ? $user_name : "ID {$active_id}",
            0,
            26
        );
        $username_to_lower = strtolower($username);
        if (array_key_exists($username_to_lower, $usersheet_titles)) {
            $username .= ' (' . ++$usersheet_titles[$username_to_lower] . ')';
        } else {
            $usersheet_titles[$username_to_lower] = 0;
        }

        $this->worksheet->addSheet($username);
        return $usersheet_titles;
    }

    private function addUserHeader(
        int $current_row,
        int $test_attempt,
        string $user_name,
        bool $is_scored_test_attempt
    ): int {
        $title = sprintf(
            $this->lng->txt('tst_result_user_name_pass'),
            $test_attempt + 1,
            $user_name
        );

        if (!$this->scoredonly && $is_scored_test_attempt) {
            $scoring_type = $this->test_obj->getPassScoring()
                ? $this->lng->txt('tst_pass_scoring_best')
                : $this->lng->txt('tst_pass_scoring_last');
            $title .= " - {$this->lng->txt('exp_scored_test_attempt')} ({$scoring_type})";
        }

        $this->worksheet->setCell($current_row, 0, $title);

        $current_row++;

        $col = 0;
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('title'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('question_type'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('answer'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('correct_answers'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('variables'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('tst_reached_points'));
        $this->worksheet->setCell($current_row, $col++, $this->lng->txt('tst_maximum_points'));

        $this->worksheet->mergeCells('A' . $current_row - 1 . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row - 1);
        $this->worksheet->setBold('A' . $current_row - 1 . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row);
        $this->worksheet->setColors('A' . $current_row - 1 . ':' . $this->worksheet->getColumnCoord($col - 1) . $current_row, self::EXCEL_BACKGROUND_COLOR);

        return ++$current_row;
    }

    private function addUserContent(
        int $current_row,
        ?array $questions,
        \ilTestEvaluationPassData $test_attempt,
        int $active_id
    ): int {
        if ($questions === null) {
            return $current_row;
        }

        usort(
            $questions,
            static fn(array $a, array $b): int => $a['sequence'] - $b['sequence']
        );

        foreach ($questions as $question) {
            $question_id = (int) $question['id'];

            $question_obj = \assQuestion::instantiateQuestion($question_id);

            $answers = '';
            $question_from_answered_questions = $test_attempt->getAnsweredQuestionByQuestionId($question_id);
            if ($question_from_answered_questions !== null
                && $question_from_answered_questions['isAnswered']) {
                $answers = $question_obj->getSolutionForTextOutput($active_id, $test_attempt->getPass());
            }

            if (is_array($answers)) {
                $answers = implode("\n", $answers);
            }

            $correct_answers = $question_obj->getCorrectSolutionForTextOutput($active_id, $test_attempt->getPass());
            if (is_array($correct_answers)) {
                $correct_answers = implode("\n", $correct_answers);
            }

            $col = 0;
            $this->worksheet->setCell($current_row, $col++, $question_obj->getTitle());
            $this->worksheet->setCell($current_row, $col++, $this->lng->txt($question_obj->getQuestionType()));
            $this->worksheet->setCell($current_row, $col++, $answers);
            $this->worksheet->setCell($current_row, $col++, $correct_answers);
            $this->worksheet->setCell($current_row, $col++, implode(', ', $question_obj->getVariablesAsTextArray($active_id, $test_attempt->getPass())));
            $this->worksheet->setCell($current_row, $col++, $test_attempt->getAnsweredQuestionByQuestionId($question_id)['reached'] ?? 0);
            $this->worksheet->setCell($current_row, $col++, $question['points']);

            $current_row++;
        }

        return $current_row;
    }

    /**
     * @return array<\ilTestEvaluationPassData>
     */
    private function getPassesDataFromUserData(\ilTestEvaluationUserData $user_data): array
    {
        if ($this->scoredonly) {
            return [$user_data->getScoredPassObject()];
        }
        return $user_data->getPasses();
    }

    private function getUserFieldsForUserID(?int $user_id): array
    {
        if ($user_id === null) {
            return [];
        }
        return $userfields = \ilObjUser::_lookupFields($user_id);
    }

    private function secondsToHoursMinutesSecondsString(int $seconds): string
    {
        $diff_hours = floor($seconds / 3600);
        $seconds -= $diff_hours * 3600;
        $diff_minutes = floor($seconds / 60);
        $seconds -= $diff_minutes * 60;
        return sprintf('%02d:%02d:%02d', $diff_hours, $diff_minutes, $seconds);
    }

    private function convertToUserDateFormat(?\DateTimeImmutable $date_time): string
    {
        if ($date_time === null) {
            return '';
        }

        return $date_time
            ->setTimezone(new \DateTimeZone($this->current_user->getTimeZone()))
            ->format($this->user_date_format->toString());
    }
}
