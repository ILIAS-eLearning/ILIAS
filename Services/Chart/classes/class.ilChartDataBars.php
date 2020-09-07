<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";

/**
 * Chart data bars series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataBars extends ilChartData
{
    protected $line_width; // [int]
    protected $bar_width; // [float] bar
    protected $bar_align; // [string] bar
    protected $bar_horizontal; // [bool] bar
    
    protected function getTypeString()
    {
        return "bars";
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
     * Set bar options
     *
     * @param float $a_width
     * @param string $a_align
     * @param bool $a_horizontal
     */
    public function setBarOptions($a_width, $a_align = "center", $a_horizontal = false)
    {
        $this->bar_width = (float) str_replace(",", ".", $a_width);
        if (in_array((string) $a_align, array("center", "left"))) {
            $this->bar_align = (string) $a_align;
        }
        $this->bar_horizontal = (bool) $a_horizontal;
    }

    protected function parseDataOptions(array &$a_options)
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
