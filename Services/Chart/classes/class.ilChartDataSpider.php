<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Chart data spider series
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartDataSpider extends ilChartData
{
    protected function getTypeString(): string
    {
        return "spider";
    }

    public function parseData(array &$a_data): void
    {
        parent::parseData($a_data);

        $fill = $this->getFill();
        if ($fill["color"] != "") {
            $a_data[count($a_data) - 1]->color = ilChart::renderColor($fill["color"], 0.5);
        }
    }

    public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart): void
    {
        $spider = new stdClass();
        $spider->active = true;

        $spider->highlight = new stdClass();
        $spider->highlight->mode = "line";


        $spider->legs = new stdClass();
        $spider->legs->fillStyle = ilChart::renderColor("#000", 0.7);
        switch (count($a_chart->getLegLabels())) {
            case 4:
            case 6:
                $spider->legs->legStartAngle = 10;
                break;

            default:
                $spider->legs->legStartAngle = 0;
                break;
        }

        $spider->legs->data = array();

        $max_str_len = 0;
        foreach ($a_chart->getLegLabels() as $l) {
            $l = ilStr::shortenTextExtended($l, 80, true);

            $label = new stdClass();
            $label->label = $l;
            $spider->legs->data[] = $label;

            $max_str_len = max($max_str_len, strlen($l));
        }

        // depending on caption length
        if ($max_str_len > 60) {
            $font_size = 10;
        } elseif ($max_str_len > 30) {
            $font_size = 12;
        } else {
            $font_size = 15;
        }
        $spider->legs->font = $font_size . "px Arial";

        $spider->spiderSize = 0.7;
        $spider->lineWidth = 1;
        $spider->pointSize = 0;

        $spider->connection = new stdClass();
        $spider->connection->width = 2;

        $spider->legMin = 0.0000001;
        $spider->legMax = $a_chart->getYAxisMax();

        $a_options->series->spider = $spider;
    }
}
