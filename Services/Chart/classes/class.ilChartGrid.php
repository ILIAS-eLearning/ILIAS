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
 * Generator for grid-based charts
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartGrid extends ilChart
{
    public const DATA_LINES = 1;
    public const DATA_BARS = 2;
    public const DATA_POINTS = 3;

    protected array $ticks;
    protected array $integer_axis;

    protected function __construct(string $a_id)
    {
        parent::__construct($a_id);
        
        $this->setXAxisToInteger(false);
        $this->setYAxisToInteger(false);
    }
    
    public function getDataInstance(int $a_type = null) : ilChartData
    {
        switch ($a_type) {
            case self::DATA_BARS:
                return new ilChartDataBars();
                
            case self::DATA_POINTS:
                return new ilChartDataPoints();
            
            default:
            case self::DATA_LINES:
                return new ilChartDataLines();
        }
    }
    
    protected function isValidDataType(ilChartData $a_series) : bool
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
     * @param int|array $a_x
     * @param int|array $a_y
     * @param bool $a_labeled
     */
    public function setTicks($a_x, $a_y, bool $a_labeled = false) : void
    {
        $this->ticks = array("x" => $a_x, "y" => $a_y, "labeled" => $a_labeled);
    }

    /**
     * Get ticks
     * @return array (x, y)
     */
    public function getTicks() : array
    {
        return $this->ticks;
    }
    
    /**
     * Restrict y-axis to integer values
     */
    public function setYAxisToInteger(bool $a_status) : void
    {
        $this->integer_axis["y"] = $a_status;
    }
    
    /**
     * Restrict x-axis to integer values
     */
    public function setXAxisToInteger(bool $a_status) : void
    {
        $this->integer_axis["x"] = $a_status;
    }

    public function parseGlobalOptions(stdClass $a_options) : void
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
