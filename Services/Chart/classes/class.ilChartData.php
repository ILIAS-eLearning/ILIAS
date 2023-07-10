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
 * Abstract chart data series base class
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilChartData
{
    protected string $type = "";
    protected string $label = "";
    protected array $data = [];
    protected float $fill = 0;
    protected string $fill_color = "";
    protected bool $hidden = false;

    abstract protected function getTypeString(): string;

    public function setHidden(bool $a_value): void
    {
        $this->hidden = $a_value;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setLabel(string $a_value): void
    {
        $this->label = $a_value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set data
     *
     * @param float $a_x
     * @param float $a_y
     */
    public function addPoint(
        float $a_x,
        ?float $a_y = null
    ): void {
        if ($a_y !== null) {
            $this->data[] = array($a_x, $a_y);
        } else {
            $this->data[] = $a_x;
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function setFill(
        float $a_value,
        string $a_color = ""
    ): void {
        $this->fill = $a_value;
        if (ilChart::isValidColor($a_color)) {
            $this->fill_color = $a_color;
        }
    }

    public function getFill(): array
    {
        return array("fill" => $this->fill, "color" => $this->fill_color);
    }

    /**
     * Convert data options to flot config
     */
    protected function parseDataOptions(array &$a_options): void
    {
    }

    /**
     * Convert data to flot config
     */
    public function parseData(array &$a_data): void
    {
        $series = new stdClass();
        $series->label = str_replace("\"", "\\\"", $this->getLabel());

        $series->data = array();
        foreach ($this->getData() as $point) {
            if (is_array($point)) {
                $series->data[] = array($point[0], $point[1]);
            } else {
                $series->data[] = $point;
            }
        }

        $options = array("show" => !$this->isHidden());

        $fill = $this->getFill();
        if ($fill["fill"]) {
            $options["fill"] = $fill["fill"];
            if ($fill["color"]) {
                $options["fillColor"] = ilChart::renderColor($fill["color"], $fill["fill"]);
            }
        }

        $this->parseDataOptions($options);

        $series->{$this->getTypeString()} = $options;

        $a_data[] = $series;
    }

    /**
     * Convert (global) properties to flot config
     */
    public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart): void
    {
    }
}
