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
class ilExcelTestExport extends ilTestExportAbstract
{
    public function export(ilObjTest $test_obj): string
    {
        $worksheet = new ilAssExcelFormatHelper();
        $worksheet->addSheet($this->lng->txt('tst_results'));

        $row = 1;
        $header_row = $this->getHeaderRow($this->lng, $test_obj);
        foreach ($header_row as $col => $value) {
            $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . $row, $value);
        }
        $worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord(count($header_row) + 1) . $row);

        $datarows = $this->getDatarows($test_obj);
        foreach ($datarows as $row => $data) {
            if ($row % 2 === 0) {
                for ($col = 0, $colMax = count($header_row); $col < $colMax; $col++) {
                    $data[$col] = "";
                }
                foreach ($data as $col => $value) {
                    if ($value !== "") {
                        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . $row + 1, $value);
                    }
                }
            } else {
                foreach ($data as $col => $value) {
                    $worksheet->setCellByCoordinates($worksheet->getColumnCoord($col) . $row + 1, $value);
                }
            }
        }

        $data = $this->test_obj->getCompleteEvaluationData(true, $this->filterby, $this->filtertext);
        $additionalFields = $this->test_obj->getEvaluationAdditionalFields();

        if ($test_obj->getExportSettingsSingleChoiceShort() && !$test_obj->isRandomTest(
        ) && $test_obj->hasSingleChoiceQuestions()) {
            // special tab for single choice tests
            $titles = $test_obj->getQuestionTitlesAndIndexes();
            $positions = array();
            $pos = 0;
            $row = 1;
            foreach ($titles as $id => $title) {
                $positions[$id] = $pos;
                $pos++;
            }

            $usernames = array();
            $participantcount = count($data->getParticipants());
            $allusersheet = false;
            $pages = 0;

            $worksheet->addSheet($this->lng->txt('eval_all_users'));

            $col = 0;
            $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('name'));
            $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('login'));
            if (count($additionalFields)) {
                foreach ($additionalFields as $fieldname) {
                    if (strcmp($fieldname, "matriculation") === 0) {
                        $worksheet->setFormattedExcelTitle(
                            $worksheet->getColumnCoord($col++) . $row,
                            $this->lng->txt('matriculation')
                        );
                    }
                    if (strcmp($fieldname, "exam_id") === 0) {
                        $worksheet->setFormattedExcelTitle(
                            $worksheet->getColumnCoord($col++) . $row,
                            $this->lng->txt('exam_id_label')
                        );
                    }
                }
            }
            $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('test'));
            foreach ($titles as $title) {
                $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $title);
            }
            $worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

            $row++;
            foreach ($data->getParticipants() as $active_id => $userdata) {
                $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
                if (array_key_exists($username, $usernames)) {
                    $usernames[$username]++;
                    $username .= " ($usernames[$username])";
                } else {
                    $usernames[$username] = 1;
                }
                $col = 0;
                $worksheet->setCell($row, $col++, $username);
                $worksheet->setCell($row, $col++, $userdata->getLogin());
                if (count($additionalFields)) {
                    $userfields = ilObjUser::_lookupFields($userdata->getUserID());
                    foreach ($additionalFields as $fieldname) {
                        if (strcmp($fieldname, "matriculation") === 0) {
                            if ($userfields[$fieldname] !== '') {
                                $worksheet->setCell($row, $col++, $userfields[$fieldname]);
                            } else {
                                $col++;
                            }
                        }
                        if (strcmp($fieldname, "exam_id") === 0) {
                            if ($userfields[$fieldname] !== '') {
                                $worksheet->setCell($row, $col++, $userdata->getExamIdFromScoredPass());
                            } else {
                                $col++;
                            }
                        }
                    }
                }
                $worksheet->setCell($row, $col++, $test_obj->getTitle());
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
                            $worksheet->setCell($row, $col + $pos, $selectedanswer);
                        }
                    }
                }
                $row++;
            }

            if ($test_obj->isSingleChoiceTestWithoutShuffle()) {
                // special tab for single choice tests without shuffle option
                $pos = 0;
                $row = 1;
                $usernames = array();
                $allusersheet = false;
                $pages = 0;

                $worksheet->addSheet($this->lng->txt('eval_all_users') . ' (2)');

                $col = 0;
                $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('name'));
                $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('login'));
                if (count($additionalFields)) {
                    foreach ($additionalFields as $fieldname) {
                        if (strcmp($fieldname, "matriculation") === 0) {
                            $worksheet->setFormattedExcelTitle(
                                $worksheet->getColumnCoord($col++) . $row,
                                $this->lng->txt('matriculation')
                            );
                        }
                        if (strcmp($fieldname, "exam_id") === 0) {
                            $worksheet->setFormattedExcelTitle(
                                $worksheet->getColumnCoord($col++) . $row,
                                $this->lng->txt('exam_id_label')
                            );
                        }
                    }
                }
                $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('test'));
                foreach ($titles as $title) {
                    $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $title);
                }
                $worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

                $row++;
                foreach ($data->getParticipants() as $active_id => $userdata) {
                    $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
                    if (array_key_exists($username, $usernames)) {
                        $usernames[$username]++;
                        $username .= " ($usernames[$username])";
                    } else {
                        $usernames[$username] = 1;
                    }
                    $col = 0;
                    $worksheet->setCell($row, $col++, $username);
                    $worksheet->setCell($row, $col++, $userdata->getLogin());
                    if (count($additionalFields)) {
                        $userfields = ilObjUser::_lookupFields($userdata->getUserId());
                        foreach ($additionalFields as $fieldname) {
                            if (strcmp($fieldname, "matriculation") === 0) {
                                if ($userfields[$fieldname] !== '') {
                                    $worksheet->setCell($row, $col++, $userfields[$fieldname]);
                                } else {
                                    $col++;
                                }
                            }
                            if (strcmp($fieldname, "exam_id") === 0) {
                                if ($userfields[$fieldname] !== '') {
                                    $worksheet->setCell($row, $col++, $userdata->getExamIdFromScoredPass());
                                } else {
                                    $col++;
                                }
                            }
                        }
                    }
                    $worksheet->setCell($row, $col++, $test_obj->getTitle());
                    $pass = $userdata->getScoredPass();
                    if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
                        foreach ($userdata->getQuestions($pass) as $question) {
                            $objQuestion = ilObjTest::_instanciateQuestion($question["aid"]);
                            if (is_object($objQuestion) && strcmp(
                                $objQuestion->getQuestionType(),
                                'assSingleChoice'
                            ) === 0) {
                                $solution = $objQuestion->getSolutionValues($active_id, $pass);
                                $pos = $positions[$question["aid"]];
                                $selectedanswer = chr(65 + $solution[0]["value1"]);
                                $worksheet->setCell($row, $col + $pos, $selectedanswer);
                            }
                        }
                    }
                    $row++;
                }
            }
        } else {
            // test participant result export
            $usernames = array();
            $participantcount = count($data->getParticipants());
            $allusersheet = false;
            $pages = 0;
            $i = 0;
            foreach ($data->getParticipants() as $active_id => $userdata) {
                $i++;

                $username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
                if (array_key_exists($username, $usernames)) {
                    $usernames[$username]++;
                    $username .= " ($i)";
                } else {
                    $usernames[$username] = 1;
                }

                if ($participantcount > 250) {
                    if (!$allusersheet || ($pages - 1) < floor($row / 64000)) {
                        $worksheet->addSheet(
                            $this->lng->txt("eval_all_users") . (($pages > 0) ? " (" . ($pages + 1) . ")" : "")
                        );
                        $allusersheet = true;
                        $row = 1;
                        $pages++;
                    }
                } else {
                    $resultsheet = $worksheet->addSheet($username);
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
                        " (" . ($test_obj->getPassScoring() ? $this->lng->txt(
                            'tst_pass_scoring_best'
                        ) : $this->lng->txt('tst_pass_scoring_last')) . ")" : "");
                    $worksheet->setCell(
                        $row,
                        $col,
                        $title
                    );
                    $worksheet->setBold($worksheet->getColumnCoord($col) . $row);
                    $row += 2;
                    if (is_object($userdata) && is_array($userdata->getQuestions($passCount))) {
                        foreach ($userdata->getQuestions($passCount) as $question) {
                            require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
                            $question = assQuestion::instantiateQuestion((int) $question["id"]);
                            if (is_object($question)) {
                                $row = $question->setExportDetailsXLS($worksheet, $row, $col, $active_id, $passCount);
                            }
                        }
                    }
                    $col += 3;
                }
            }
        }

        if ($this->deliver) {
            $testname = $test_obj->getTitle();
            $testname .= '_results';
            $testname = ilFileUtils::getASCIIFilename(preg_replace("/\s/", "_", $testname)) . '.xlsx';
            $worksheet->sendToClient($testname);
        }
        $excelfile = ilFileUtils::ilTempnam();
        $worksheet->writeToFile($excelfile);
        return $excelfile . '.xlsx';
    }
}
