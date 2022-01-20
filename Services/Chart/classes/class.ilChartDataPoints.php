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
 * Chart data points series
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartDataPoints extends ilChartData
{
    protected ?int $line_width = null;
    protected ?int $radius = null;
    
    protected function getTypeString() : string
    {
        return "points";
    }
    
    public function setLineWidth(?int $a_value) : void
    {
        $this->line_width = $a_value;
    }

    public function getLineWidth() : ?int
    {
        return $this->line_width;
    }
    
    public function setPointRadius(int $a_value) : void
    {
        $this->radius = $a_value;
    }

    public function getPointRadius() : ?int
    {
        return $this->radius;
    }
    
    protected function parseDataOptions(array &$a_options) : void
    {
        $width = $this->getLineWidth();
        if ($width !== null) {
            $a_options["lineWidth"] = $width;
        }
        
        $radius = $this->getPointRadius();
        if ($radius !== null) {
            $a_options["radius"] = $radius;
        }
    }
}
