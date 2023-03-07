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
class ilExcelTestExport extends ilTestExportAbstract
{
    private bool $bestonly;
    protected ilAssExcelFormatHelper $worksheet;

    public function __construct(
        ilObjTest $test_obj,
        string $filter_key_participants = ilTestEvaluationData::FILTER_BY_NONE,
        string $filtertext = '',
        bool $passedonly = false,
        bool $bestonly = true,
        ilLanguage $lng = null
    ) {
        $this->bestonly = $bestonly;
        $this->worksheet = new ilAssExcelFormatHelper();
        parent::__construct($test_obj, $filter_key_participants, $filtertext, $passedonly, $lng);
    }

    public function withResultsPage(): self
    {
        $this->worksheet->addSheet($this->lng->txt('tst_results'));

        $row = 1;
        $header_row = $this->getHeaderRow($this->lng, $this->test_obj);
        foreach ($header_row as $col => $value) {
            $this->worksheet->setFormattedExcelTitle($this->worksheet->getColumnCoord($col) . $row, $value);
        }
        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord(count($header_row) + 1) . $row);

        $datarows = $this->getDatarows($this->test_obj);
        foreach ($datarows as $row => $data) {
            if ($this->bestonly && $row % 2 === 0) {
                for ($col = 0, $colMax = count($header_row); $col < $colMax; $col++) {
                    $data[$col] = "";
                }
                foreach ($data as $col => $value) {
                    if ($value !== "") {
                        $this->worksheet->setFormattedExcelTitle(
                            $this->worksheet->getColumnCoord($col) . $row + 1,
                            $value
                        );
                    }
                }
            } else {
                foreach ($data as $col => $value) {
                    $this->worksheet->setCellByCoordinates($this->worksheet->getColumnCoord($col) . $row + 1, $value);
                }
            }
        }

        return $this;
    }

    public function withAllUsersPages(): self
    {
        if ($this->test_obj->getExportSettingsSingleChoiceShort() && !$this->test_obj->isRandomTest(
        ) && $this->test_obj->hasSingleChoiceQuestions()) {
            $this->withAllUsersPageSingleChoice();
            if ($this->test_obj->isSingleChoiceTestWithoutShuffle()) {
                $this->withAllUsersPageSingleChoiceWithoutShuffle();
            }
        }
        return $this;
    }

    private function withAllUsersPageSingleChoice(): void
    {
        $titles = $this->test_obj->getQuestionTitlesAndIndexes();
        $positions = [];
        $pos = 0;
        $row = 1;
        foreach ($titles as $id => $title) {
            $positions[$id] = $pos;
            $pos++;
        }

        $usernames = [];
        $participantcount = count($this->complete_data->getParticipants());
        $allusersheet = false;
        $pages = 0;

        $this->worksheet->addSheet($this->lng->txt('eval_all_users'));

        $col = 0;
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('name')
        );
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('login')
        );
        if (count($this->additionalFields)) {
            foreach ($this->additionalFields as $fieldname) {
                if (strcmp($fieldname, "matriculation") === 0) {
                    $this->worksheet->setFormattedExcelTitle(
                        $this->worksheet->getColumnCoord($col++) . $row,
                        $this->lng->txt('matriculation')
                    );
                }
                if (strcmp($fieldname, "exam_id") === 0) {
                    $this->worksheet->setFormattedExcelTitle(
                        $this->worksheet->getColumnCoord($col++) . $row,
                        $this->lng->txt('exam_id_label')
                    );
                }
            }
        }
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('test')
        );
        foreach ($titles as $title) {
            $this->worksheet->setFormattedExcelTitle($this->worksheet->getColumnCoord($col++) . $row, $title);
        }
        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col - 1) . $row);

        $row++;
        foreach ($this->complete_data->getParticipants() as $active_id => $userdata) {
            $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
            if (array_key_exists($username, $usernames)) {
                $usernames[$username]++;
                $username .= " ($usernames[$username])";
            } else {
                $usernames[$username] = 1;
            }
            $col = 0;
            $this->worksheet->setCell($row, $col++, $username);
            $this->worksheet->setCell($row, $col++, $userdata->getLogin());
            if (count($this->additionalFields)) {
                $userfields = ilObjUser::_lookupFields($userdata->getUserID());
                foreach ($this->additionalFields as $fieldname) {
                    if (strcmp($fieldname, "matriculation") === 0) {
                        if ($userfields[$fieldname] !== '') {
                            $this->worksheet->setCell($row, $col++, $userfields[$fieldname]);
                        } else {
                            $col++;
                        }
                    }
                    if (strcmp($fieldname, "exam_id") === 0) {
                        if ($userfields[$fieldname] !== '') {
                            $this->worksheet->setCell($row, $col++, $userdata->getExamIdFromScoredPass());
                        } else {
                            $col++;
                        }
                    }
                }
            }
            $this->worksheet->setCell($row, $col++, $this->test_obj->getTitle());
            $pass = $userdata->getScoredPass();
            if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
                foreach ($userdata->getQuestions($pass) as $question) {
                    $objQuestion = assQuestion::_instantiateQuestion($question["id"]);
                    if (is_object($objQuestion) && strcmp(
                        $objQuestion->getQuestionType(),
                        'assSingleChoice'
                    ) === 0) {
                        $solution = $objQuestion->getSolutionValues($active_id, $pass);
                        $pos = $positions[$question["id"]];
                        $selectedanswer = "x";
                        foreach ($objQuestion->getAnswers() as $id => $answer) {
                            if ($solution[0]["value1"] !== '' && $id === $solution[0]["value1"]) {
                                $selectedanswer = $answer->getAnswertext();
                            }
                        }
                        $this->worksheet->setCell($row, $col + $pos, $selectedanswer);
                    }
                }
            }
            $row++;
        }
    }


    public function getContent(): ilAssExcelFormatHelper
    {
        return $this->worksheet;
    }

    private function withAllUsersPageSingleChoiceWithoutShuffle(): void
    {
        $positions = [];
        $pos = 0;
        $titles = $this->test_obj->getQuestionTitlesAndIndexes();
        foreach ($titles as $id => $title) {
            $positions[$id] = $pos;
            $pos++;
        }
        $row = 1;
        $pos = 0;
        $usernames = [];



        $this->worksheet->addSheet($this->lng->txt('eval_all_users') . ' (2)');

        $col = 0;
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('name')
        );
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('login')
        );
        if (count($this->additionalFields)) {
            foreach ($this->additionalFields as $fieldname) {
                if (strcmp($fieldname, "matriculation") === 0) {
                    $this->worksheet->setFormattedExcelTitle(
                        $this->worksheet->getColumnCoord($col++) . $row,
                        $this->lng->txt('matriculation')
                    );
                }
                if (strcmp($fieldname, "exam_id") === 0) {
                    $this->worksheet->setFormattedExcelTitle(
                        $this->worksheet->getColumnCoord($col++) . $row,
                        $this->lng->txt('exam_id_label')
                    );
                }
            }
        }
        $this->worksheet->setFormattedExcelTitle(
            $this->worksheet->getColumnCoord($col++) . $row,
            $this->lng->txt('test')
        );
        foreach ($titles as $title) {
            $this->worksheet->setFormattedExcelTitle($this->worksheet->getColumnCoord($col++) . $row, $title);
        }
        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col - 1) . $row);

        $row++;
        foreach ($this->complete_data->getParticipants() as $active_id => $userdata) {
            $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
            if (array_key_exists($username, $usernames)) {
                $usernames[$username]++;
                $username .= " ($usernames[$username])";
            } else {
                $usernames[$username] = 1;
            }
            $col = 0;
            $this->worksheet->setCell($row, $col++, $username);
            $this->worksheet->setCell($row, $col++, $userdata->getLogin());
            if (count($this->additionalFields)) {
                $userfields = ilObjUser::_lookupFields($userdata->getUserId());
                foreach ($this->additionalFields as $fieldname) {
                    if (strcmp($fieldname, "matriculation") === 0) {
                        if ($userfields[$fieldname] !== '') {
                            $this->worksheet->setCell($row, $col++, $userfields[$fieldname]);
                        } else {
                            $col++;
                        }
                    }
                    if (strcmp($fieldname, "exam_id") === 0) {
                        if ($userfields[$fieldname] !== '') {
                            $this->worksheet->setCell($row, $col++, $userdata->getExamIdFromScoredPass());
                        } else {
                            $col++;
                        }
                    }
                }
            }
            $this->worksheet->setCell($row, $col++, $this->test_obj->getTitle());
            $pass = $userdata->getScoredPass();
            if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
                foreach ($userdata->getQuestions($pass) as $question) {
                    $objQuestion = ilObjTest::_instanciateQuestion($question["aid"]);
                    if ($objQuestion && is_object($objQuestion) && strcmp(
                        $objQuestion->getQuestionType(),
                        'assSingleChoice'
                    ) === 0) {
                        $solution = $objQuestion->getSolutionValues($active_id, $pass);
                        $pos = $positions[$question["aid"]];
                        $selectedanswer = chr(65 + $solution[0]["value1"]);
                        $this->worksheet->setCell($row, $col + $pos, $selectedanswer);
                    }
                }
            }
            $row++;
        }
    }

    public function withUserPages(): self
    {
        $row = 1;
        $usernames = [];
        $allusersheet = false;
        $pages = 0;
        // test participant result export
        $participantcount = count($this->complete_data->getParticipants());
        foreach ($this->complete_data->getParticipants() as $active_id => $userdata) {
            $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
            $username = mb_substr($username, 0, 26);
            $username_to_lower = strtolower($username);
            if (array_key_exists($username_to_lower, $usernames)) {
                $usernames[$username_to_lower]++;
                $username .= " (" . $usernames[$username_to_lower] . ")";
            } else {
                $usernames[$username_to_lower] = 0;
            }

            if ($participantcount > 250) {
                if (!$allusersheet || ($pages - 1) < floor($row / 64000)) {
                    $this->worksheet->addSheet(
                        $this->lng->txt("eval_all_users") . (($pages > 0) ? " (" . ($pages + 1) . ")" : "")
                    );
                    $allusersheet = true;
                    $row = 1;
                    $pages++;
                }
            } else {
                $resultsheet = $this->worksheet->addSheet($username);
            }
            if ($this->bestonly) {
                $passes = [$userdata->getScoredPassObject()];
            } else {
                $passes = $userdata->getPasses();
            }
            $col = 0;
            foreach ($passes as $pass) {
                $passCount = $pass->getPass();
                $row = ($allusersheet) ? $row : 1;
                $title = sprintf(
                    $this->lng->txt("tst_result_user_name_pass"),
                    $passCount + 1,
                    $userdata->getName()
                ) .
                    (!$this->bestonly && $userdata->getScoredPass() === $passCount ? " " .
                    $this->lng->txt("exp_best_pass") .
                    " (" . ($this->test_obj->getPassScoring() ? $this->lng->txt(
                        'tst_pass_scoring_best'
                    ) : $this->lng->txt('tst_pass_scoring_last')) . ")" : "");
                $this->worksheet->setCell(
                    $row,
                    $col,
                    $title
                );
                $this->worksheet->setBold($this->worksheet->getColumnCoord($col) . $row);
                $row += 2;
                if (is_object($userdata) && is_array($userdata->getQuestions($passCount))) {
                    $questions = $userdata->getQuestions($passCount);
                    usort($questions, static function ($a, $b) {
                        return $a['sequence'] - $b['sequence'];
                    });
                    foreach ($questions as $question) {
                        $question = assQuestion::instantiateQuestion((int) $question["id"]);
                        if (is_object($question)) {
                            $row = $question->setExportDetailsXLS($this->worksheet, $row, $col, $active_id, $passCount);
                        }
                    }
                }
                $col += 3;
            }
        }

        return $this;
    }

    public function withAggregatedResultsPage(): self
    {
        $this->worksheet->addSheet($this->lng->txt('tst_results_aggregated'));

        $row = 1;
        $col = 0;
        $this->worksheet->setCell($row, $col++, $this->lng->txt('result'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('value'));

        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col - 1) . $row);

        $row++;
        foreach ($this->aggregated_data['overview'] as $key => $value) {
            $col = 0;
            $this->worksheet->setCell($row, $col++, $key);
            $this->worksheet->setCell($row, $col++, $value);
            $row++;
        }

        $row++;
        $col = 0;

        $this->worksheet->setCell($row, $col++, $this->lng->txt('question_id'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('question_title'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('average_reached_points'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('points'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('percentage'));
        $this->worksheet->setCell($row, $col++, $this->lng->txt('number_of_answers'));

        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col - 1) . $row);

        $row++;
        foreach ($this->aggregated_data['questions'] as $key => $value) {
            $col = 0;
            $this->worksheet->setCell($row, $col++, $key);
            $this->worksheet->setCell($row, $col++, $value[0]);
            $this->worksheet->setCell($row, $col++, $value[4]);
            $this->worksheet->setCell($row, $col++, $value[5]);
            $this->worksheet->setCell($row, $col++, $value[6]);
            $this->worksheet->setCell($row, $col++, $value[3]);
            $row++;
        }

        return $this;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function deliver(string $title): void
    {
        $testname = ilFileUtils::getASCIIFilename(preg_replace("/\s/", "_", $title)) . '.xlsx';
        $this->worksheet->sendToClient($testname);
    }
}
