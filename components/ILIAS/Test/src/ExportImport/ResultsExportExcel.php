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

/**
 * @author Fabian Helfer <fhelfer@databay.de>
 */
class ResultsExportExcel extends ResultsExportAbstract
{
    protected \ilAssExcelFormatHelper $worksheet;

    public function __construct(
        \ilLanguage $lng,
        \ilObjTest $test_obj,
        string $filename = '',
        bool $scoredonly = true,
    ) {
        $this->worksheet = new \ilAssExcelFormatHelper();
        parent::__construct($lng, $test_obj, $filename, $scoredonly);
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

        foreach ($this->getDatarows($this->test_obj) as $row => $data) {
            if ($this->scoredonly && $row % 2 === 0) {
                for ($col = 0, $col_max = count($header_row); $col < $col_max; $col++) {
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

    public function getContent(): \ilAssExcelFormatHelper
    {
        return $this->worksheet;
    }

    public function withUserPages(): self
    {
        $row = 1;
        $usernames = [];
        $allusersheet = false;
        $pages = 0;
        // test participant result export
        $participantcount = count($this->getCompleteData()->getParticipants());
        foreach ($this->getCompleteData()->getParticipants() as $active_id => $userdata) {
            $username = mb_substr(
                (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID {$active_id}",
                0,
                26
            );
            $username_to_lower = strtolower($username);
            if (array_key_exists($username_to_lower, $usernames)) {
                $usernames[$username_to_lower]++;
                $username .= ' (' . $usernames[$username_to_lower] . ')';
            } else {
                $usernames[$username_to_lower] = 0;
            }

            if ($participantcount > 250) {
                if (!$allusersheet || ($pages - 1) < floor($row / 64000)) {
                    $this->worksheet->addSheet(
                        $this->lng->txt('eval_all_users') . (($pages > 0) ? ' (' . ($pages + 1) . ')' : '')
                    );
                    $allusersheet = true;
                    $row = 1;
                    $pages++;
                }
            } else {
                $this->worksheet->addSheet($username);
            }
            if ($this->scoredonly) {
                $passes = [$userdata->getScoredPassObject()];
            } else {
                $passes = $userdata->getPasses();
            }
            $col = 0;
            foreach ($passes as $pass) {
                $pass_count = $pass->getPass();
                $row = ($allusersheet) ? $row : 1;
                $title = sprintf(
                    $this->lng->txt("tst_result_user_name_pass"),
                    $pass_count + 1,
                    $userdata->getName()
                ) .
                    (!$this->scoredonly && $userdata->getScoredPass() === $pass_count ? " " .
                    $this->lng->txt("exp_scored_test_run") .
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
                if (is_object($userdata) && is_array($userdata->getQuestions($pass_count))) {
                    $questions = $userdata->getQuestions($pass_count);
                    usort($questions, static function ($a, $b) {
                        return $a['sequence'] - $b['sequence'];
                    });
                    foreach ($questions as $question) {
                        $question = \assQuestion::instantiateQuestion((int) $question["id"]);
                        if (is_object($question)) {
                            $row = $question->setExportDetailsXLSX($this->worksheet, $row, $col, $active_id, $pass_count);
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
        $col_results = 0;
        $this->worksheet->setCell($row, $col_results++, $this->lng->txt('result'));
        $this->worksheet->setCell($row, $col_results++, $this->lng->txt('value'));

        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col_results - 1) . $row);

        $row++;
        foreach ($this->aggregated_data['overview'] as $key => $value) {
            $col_aggregated = 0;
            $this->worksheet->setCell($row, $col_aggregated++, $key);
            $this->worksheet->setCell($row, $col_aggregated++, $value);
            $row++;
        }

        $row++;
        $col_overview = 0;

        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('question_id'));
        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('question_title'));
        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('average_reached_points'));
        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('points'));
        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('percentage'));
        $this->worksheet->setCell($row, $col_overview++, $this->lng->txt('number_of_answers'));

        $this->worksheet->setBold('A' . $row . ':' . $this->worksheet->getColumnCoord($col_overview - 1) . $row);

        $row++;
        foreach ($this->aggregated_data['questions'] as $key => $value) {
            $col = 0;
            $this->worksheet->setCell($row, $col_overview++, $key);
            $this->worksheet->setCell($row, $col_overview++, $value[0]);
            $this->worksheet->setCell($row, $col_overview++, $value[4]);
            $this->worksheet->setCell($row, $col_overview++, $value[5]);
            $this->worksheet->setCell($row, $col_overview++, $value[6]);
            $this->worksheet->setCell($row, $col_overview++, $value[3]);
            $row++;
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
}
