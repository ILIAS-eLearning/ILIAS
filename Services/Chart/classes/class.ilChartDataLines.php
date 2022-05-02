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
 * Chart data lines series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartDataLines extends ilChartData
{
    protected ?int $line_width = null;
    protected bool $steps = false;
    
    protected function getTypeString() : string
    {
        return "lines";
    }
    
    public function setLineWidth(int $a_value) : void
    {
        $this->line_width = $a_value;
    }

    public function getLineWidth() : ?int
    {
        return $this->line_width;
    }

    public function setLineSteps(bool $a_value) : void
    {
        $this->steps = $a_value;
    }

    public function getLineSteps() : bool
    {
        return $this->steps;
    }
    
    protected function parseDataOptions(array &$a_options) : void
    {
        $width = $this->getLineWidth();
        if ($width !== null) {
            $a_options["lineWidth"] = $width;
        }
        
        if ($this->getLineSteps()) {
            $a_options["steps"] = true;
        }
    }
}
