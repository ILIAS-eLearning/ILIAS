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
 * Chart data pie series
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartDataPie extends ilChartData
{
    protected int $line_width = 0;
    protected float $label_radius = 0;

    protected function getTypeString(): string
    {
        return "pie";
    }

    public function setLineWidth(int $a_value): void
    {
        $this->line_width = $a_value;
    }

    public function getLineWidth(): int
    {
        return $this->line_width;
    }

    /**
     * Sets the radius at which to place the labels. If value is between 0 and 1 (inclusive) then
     * it will use that as a percentage of the available space (size of the container), otherwise
     * it will use the value as a direct pixel length.
     */
    public function setLabelRadius(float $a_value): void
    {
        $this->label_radius = $a_value;
    }

    public function getLabelRadius(): float
    {
        return $this->label_radius;
    }

    public function addPiePoint(int $a_value, string $a_caption = null): void
    {
        $this->data[] = array($a_value, $a_caption);
    }

    public function parseData(array &$a_data): void
    {
        foreach ($this->data as $slice) {
            $series = new stdClass();
            $series->label = str_replace("\"", "\\\"", $slice[1]);

            // add percentage to legend
            if (!$this->getLabelRadius()) {
                $series->label .= " (" . $slice[0] . "%)";
            }

            $series->data = $slice[0];

            $options = array("show" => !$this->isHidden());

            $series->{$this->getTypeString()} = $options;

            $a_data[] = $series;
        }
    }

    public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart): void
    {
        $a_options->series->pie = new stdClass();
        $a_options->series->pie->show = true;

        // fill vs. stroke - trying to normalize data attributes

        $fill = $this->getFill();
        $width = $this->getLineWidth();
        if ($fill["fill"] || $width) {
            $a_options->series->pie->stroke = new stdClass();
            if ($width) {
                $a_options->series->pie->stroke->width = $width;
            }
            if ($fill["color"]) {
                $a_options->series->pie->stroke->color = ilChart::renderColor($fill["color"], $fill["fill"]);
            }
        }

        $radius = $this->getLabelRadius();
        if ($radius) {
            $a_options->series->pie->label = new stdClass();
            $a_options->series->pie->label->background = new stdClass();
            $a_options->series->pie->radius = 1;
            $a_options->series->pie->label->radius = $radius;
            $a_options->series->pie->label->show = true;
            $a_options->series->pie->label->background->color = "#444";
            $a_options->series->pie->label->background->opacity = 0.8;
        }
    }
}
