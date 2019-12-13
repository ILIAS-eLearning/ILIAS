<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChart.php";

/**
 * Generator for grid-based charts
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartGrid extends ilChart
{
    protected $ticks; // [array]
    protected $integer_axis; // [array]
    
    const DATA_LINES = 1;
    const DATA_BARS = 2;
    const DATA_POINTS = 3;
    
    protected function __construct($a_id)
    {
        parent::__construct($a_id);
        
        $this->setXAxisToInteger(false);
        $this->setYAxisToInteger(false);
    }
    
    public function getDataInstance($a_type = null)
    {
        switch ($a_type) {
            case self::DATA_BARS:
                include_once "Services/Chart/classes/class.ilChartDataBars.php";
                return new ilChartDataBars();
                
            case self::DATA_POINTS:
                include_once "Services/Chart/classes/class.ilChartDataPoints.php";
                return new ilChartDataPoints();
            
            default:
            case self::DATA_LINES:
                include_once "Services/Chart/classes/class.ilChartDataLines.php";
                return new ilChartDataLines();
        }
    }
    
    protected function isValidDataType(ilChartData $a_series)
    {
        if ($a_series instanceof ilChartDataLines
            || $a_series instanceof ilChartDataBars
            || $a_series instanceof ilChartDataPoints) {
            return true;
        }
        return false;
    }
        
    /**
     * Set ticks
     *
     * @param int|array $a_x
     * @param int|array $a_y
     * @param bool $a_labeled
     */
    public function setTicks($a_x, $a_y, $a_labeled = false)
    {
        $this->ticks = array("x" => $a_x, "y" => $a_y, "labeled" => (bool) $a_labeled);
    }

    /**
     * Get ticks
     *
     * @return array (x, y)
     */
    public function getTicks()
    {
        return $this->ticks;
    }
    
    /**
     * Restrict y-axis to integer values
     *
     * @param bool $a_status
     */
    public function setYAxisToInteger($a_status)
    {
        $this->integer_axis["y"] = (bool) $a_status;
    }
    
    /**
     * Restrict x-axis to integer values
     *
     * @param bool $a_status
     */
    public function setXAxisToInteger($a_status)
    {
        $this->integer_axis["x"] = (bool) $a_status;
    }

    public function parseGlobalOptions(stdClass $a_options)
    {
        // axis/ticks
        $tmp = array();
        $ticks = $this->getTicks();
        if ($ticks) {
            $labeled = (bool) $ticks["labeled"];
            unset($ticks["labeled"]);
            foreach ($ticks as $axis => $def) {
                if (is_numeric($def) || is_array($def)) {
                    $a_options->{$axis . "axis"} = new stdClass();
                }
                if (is_numeric($def)) {
                    $a_options->{$axis . "axis"}->ticks = $def;
                } elseif (is_array($def)) {
                    $a_options->{$axis . "axis"}->ticks = array();
                    foreach ($def as $idx => $value) {
                        if ($labeled) {
                            $a_options->{$axis . "axis"}->ticks[] = array($idx, $value);
                        } else {
                            $a_options->{$axis . "axis"}->ticks[] = $value;
                        }
                    }
                }
            }
        }
        
        // optional: remove decimals
        if ($this->integer_axis["x"] && !isset($a_options->xaxis)) {
            $a_options->{"xaxis"} = new stdClass();
            $a_options->{"xaxis"}->tickDecimals = 0;
        }
        if ($this->integer_axis["y"] && !isset($a_options->yaxis)) {
            $a_options->{"yaxis"} = new stdClass();
            $a_options->{"yaxis"}->tickDecimals = 0;
        }
    }
}
