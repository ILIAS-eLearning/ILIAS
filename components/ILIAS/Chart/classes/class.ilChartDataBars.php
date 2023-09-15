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
 * Chart data bars series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartDataBars extends ilChartData
{
    protected ?int $line_width = null;
    protected float $bar_width = 0;
    protected string $bar_align = "";
    protected bool $bar_horizontal = false;

    protected function getTypeString(): string
    {
        return "bars";
    }

    public function setLineWidth(int $a_value): void
    {
        $this->line_width = $a_value;
    }

    public function getLineWidth(): ?int
    {
        return $this->line_width;
    }

    public function setBarOptions(
        float $a_width,
        string $a_align = "center",
        bool $a_horizontal = false
    ): void {
        $this->bar_width = (float) str_replace(",", ".", (string) $a_width);
        if (in_array($a_align, array("center", "left"))) {
            $this->bar_align = $a_align;
        }
        $this->bar_horizontal = $a_horizontal;
    }

    protected function parseDataOptions(array &$a_options): void
    {
        $width = $this->getLineWidth();
        if ($width !== null) {
            $a_options["lineWidth"] = $width;
        }

        if ($this->bar_width) {
            $a_options["barWidth"] = $this->bar_width;
            $a_options["align"] = $this->bar_align;
            if ($this->bar_horizontal) {
                $a_options["horizontal"] = true;
            }
        }
    }
}
