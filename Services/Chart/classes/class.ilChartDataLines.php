<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";

/**
 * Chart data lines series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataLines extends ilChartData
{
    protected $line_width; // [int]
    protected $steps; // [bool] lines
    
    protected function getTypeString()
    {
        return "lines";
    }
    
    /**
     * Set line width
     *
     * @param int $a_value
     */
    public function setLineWidth($a_value)
    {
        $this->line_width = (int) $a_value;
    }

    /**
     * Get line width
     *
     * @return int
     */
    public function getLineWidth()
    {
        return $this->line_width;
    }

    /**
     * Set line steps
     *
     * @param bool $a_value
     */
    public function setLineSteps($a_value)
    {
        $this->steps = (bool) $a_value;
    }

    /**
     * Get line steps
     *
     * @return bool
     */
    public function getLineSteps()
    {
        return $this->steps;
    }
    
    protected function parseDataOptions(array &$a_options)
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
