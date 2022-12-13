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
class ilCSVTestExport extends ilTestExportAbstract
{
    protected string $content;

    public function withAllResults(): self
    {
        $rows = $this->getDatarows($this->test_obj);
        $separator = ";";
        $csv = "";
        foreach ($rows as $evalrow) {
            $csvrow = $this->test_obj->processCSVRow($evalrow, true, $separator);
            $csv .= implode($separator, $csvrow) . "\n";
        }
        $this->content = $csv;
        return $this;
    }

    public function withAggregatedResults(): self
    {
        $data = $this->test_obj->getAggregatedResultsData();
        $rows = [];
        $rows[] = [
            $this->lng->txt("result"),
            $this->lng->txt("value")
        ];
        foreach ($data["overview"] as $key => $value) {
            $rows[] = [
                $key,
                $value
            ];
        }
        $rows[] = [
            $this->lng->txt("question_id"),
            $this->lng->txt("question_title"),
            $this->lng->txt("average_reached_points"),
            $this->lng->txt("points"),
            $this->lng->txt("percentage"),
            $this->lng->txt("number_of_answers")
        ];
        foreach ($data["questions"] as $key => $value) {
            $rows[] = [
                $key,
                $value[0],
                $value[4],
                $value[5],
                $value[6],
                $value[3]
            ];
        }
        $csv = "";
        $separator = ";";
        foreach ($rows as $evalrow) {
            $csvrow = &$this->test_obj->processCSVRow($evalrow, true, $separator);
            $csv .= implode($separator, $csvrow) . "\n";
        }
        $this->content = $csv;
        return $this;
    }


    public function deliver(string $title): void
    {
        ilUtil::deliverData($this->content, ilFileUtils::getASCIIFilename($title . ".csv"));
        exit;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
