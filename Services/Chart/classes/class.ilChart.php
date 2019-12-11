<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartLegend.php";

/**
 * Abstract Chart generator base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
abstract class ilChart
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $id; // [string]
    protected $width; // [string]
    protected $height; // [string]
    protected $data; // [array]
    protected $legend; // [ilChartLegend]
    protected $shadow; // [int]
    protected $colors; // [array]
    protected $auto_resize; // [bool]
    protected $stacked; // [bool]

    const TYPE_GRID = 1;
    const TYPE_PIE = 2;
    const TYPE_SPIDER = 3;

    /**
     * Constructor
     *
     * @param string $a_id
     */
    protected function __construct($a_id)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->id = $a_id;
        $this->data = array();
                
        $this->setShadow(2);
    }

    /**
     * Get type instance
     *
     * @param int $a_type
     * @param string $a_id
     * @return ilChart
     */
    public static function getInstanceByType($a_type, $a_id)
    {
        switch ($a_type) {
            case self::TYPE_GRID:
                include_once "Services/Chart/classes/class.ilChartGrid.php";
                return new ilChartGrid($a_id);
                
            case self::TYPE_PIE:
                include_once "Services/Chart/classes/class.ilChartPie.php";
                return new ilChartPie($a_id);
                
            case self::TYPE_SPIDER:
                include_once "Services/Chart/classes/class.ilChartSpider.php";
                return new ilChartSpider($a_id);
        }
    }
    
    /**
     * Get data series instance
     *
     * @return ilChartData
     */
    abstract public function getDataInstance($a_type = null);
    
    /**
     * Validate data series
     *
     * @return bool
     */
    abstract protected function isValidDataType(ilChartData $a_series);
    
    /**
     * Basic validation
     *
     * @return bool
     */
    protected function isValid()
    {
        if (sizeof($this->data)) {
            return true;
        }
        return false;
    }

    /**
     * Set chart size
     *
     * @param int $a_x
     * @param int $a_y
     */
    public function setSize($a_x, $a_y)
    {
        $this->width = $a_x;
        $this->height = $a_y;
    }

    /**
     * Add data series
     *
     * @param ilChartData $a_series
     * @param mixed $a_id
     * @return mixed index
     */
    public function addData(ilChartData $a_series, $a_idx = null)
    {
        if ($this->isValidDataType($a_series)) {
            if ($a_idx === null) {
                $a_idx = sizeof($this->data);
            }
            $this->data[$a_idx] = $a_series;
            return $a_idx;
        }
    }

    /**
     * Set chart legend
     *
     * @param ilChartLegend $a_legend
     */
    public function setLegend(ilChartLegend $a_legend)
    {
        $this->legend = $a_legend;
    }
    
    /**
     * Set colors
     *
     * @param array $a_values
     */
    public function setColors($a_values)
    {
        foreach ($a_values as $color) {
            if (self::isValidColor($color)) {
                $this->colors[] = $color;
            }
        }
    }

    /**
     * Get colors
     *
     * @return array
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Validate html color code
     *
     * @param string $a_value
     * @return bool
     */
    public static function isValidColor($a_value)
    {
        if (preg_match("/^#[0-9a-f]{3}$/i", $a_value, $match)) {
            return true;
        } elseif (preg_match("/^#[0-9a-f]{6}$/i", $a_value, $match)) {
            return true;
        }
    }

    /**
     * Render html color code
     *
     * @param string $a_value
     * @param float $a_opacity
     * @return string
     */
    public static function renderColor($a_value, $a_opacity = 1)
    {
        if (self::isValidColor($a_value)) {
            if (strlen($a_value) == 4) {
                return "rgba(" . hexdec($a_value[1] . $a_value[1]) . ", " .
                    hexdec($a_value[2] . $a_value[2]) . ", " .
                    hexdec($a_value[3] . $a_value[3]) . ", " . $a_opacity . ")";
            } else {
                return "rgba(" . hexdec($a_value[1] . $a_value[2]) . ", " .
                    hexdec($a_value[3] . $a_value[4]) . ", " .
                    hexdec($a_value[5] . $a_value[6]) . ", " . $a_opacity . ")";
            }
        }
    }

    /**
     * Set shadow
     *
     * @param int $a_value
     */
    public function setShadow($a_value)
    {
        $this->shadow = (int) $a_value;
    }

    /**
     * Get shadow
     *
     * @return int
     */
    public function getShadow()
    {
        return $this->shadow;
    }
    
    /**
     * Toggle auto-resizing on window resize/redraw
     *
     * @param bool $a_value
     */
    public function setAutoResize($a_value)
    {
        $this->auto_resize = (bool) $a_value;
    }
    
    /**
     * Toggle stacking
     *
     * @param bool $a_value
     */
    public function setStacked($a_value)
    {
        $this->stacked = (bool) $a_value;
    }
    
    /**
     * Init JS script files
     */
    protected function initJS()
    {
        $tpl = $this->tpl;
        
        include_once "Services/jQuery/classes/class.iljQueryUtil.php";
        iljQueryUtil::initjQuery();
        
        $tpl->addJavascript("Services/Chart/js/flot/excanvas.min.js");
        $tpl->addJavascript("Services/Chart/js/flot/jquery.flot.min.js");
        
        if ((bool) $this->auto_resize) {
            // #13108
            $tpl->addJavascript("Services/Chart/js/flot/jquery.flot.resize.min.js");
        }
        
        if ((bool) $this->stacked) {
            $tpl->addJavascript("Services/Chart/js/flot/jquery.flot.stack.min.js");
        }
        
        $this->addCustomJS();
    }
    
    /**
     * Add type-specific JS script
     */
    protected function addCustomJS()
    {
    }
    
    /**
     * Convert (global) properties to flot config
     *
     * @param object $a_options
     */
    public function parseGlobalOptions(stdClass $a_options)
    {
    }
    
    /**
     * Render
     */
    public function getHTML()
    {
        if (!$this->isValid()) {
            return;
        }
        
        $this->initJS();
    
        $chart = new ilTemplate("tpl.grid.html", true, true, "Services/Chart");
        $chart->setVariable("ID", $this->id);
        
        if ($this->width) {
            if (is_numeric($this->width)) {
                $chart->setVariable("WIDTH", "width:" . $this->width . "px;");
            } else {
                $chart->setVariable("WIDTH", "width:" . $this->width . ";");
            }
        }
        if ($this->height) {
            if (is_numeric($this->height)) {
                $chart->setVariable("HEIGHT", "height:" . $this->height . "px;");
            } else {
                $chart->setVariable("HEIGHT", "height:" . $this->height . ";");
            }
        }
        
        
        // (series) data
        
        $json_series = array();
        foreach ($this->data as $series) {
            $series->parseData($json_series);
        }
        $chart->setVariable("SERIES", json_encode($json_series));
        
        
        // global options
        
        $json_options = new stdClass();
        $json_options->series = new stdClass();
        $json_options->series->shadowSize = (int) $this->getShadow();
        $json_options->series->lines = new stdClass();
        $json_options->series->lines->show = false;
        $json_options->series->stack = (bool) $this->stacked;
        
        foreach ($this->data as $series) {
            $series->parseGlobalOptions($json_options, $this);
        }
        
        $this->parseGlobalOptions($json_options);
        
        $colors = $this->getColors();
        if ($colors) {
            $json_options->colors = array();
            foreach ($colors as $color) {
                $json_options->colors[] = self::renderColor($color);
            }
        }

        // legend
        $json_options->legend = new stdClass();
        if (!$this->legend) {
            $json_options->legend->show = false;
        } else {
            $this->legend->parseOptions($json_options->legend);
        }
    
        $chart->setVariable("OPTIONS", json_encode($json_options));
        
        $ret = $chart->get();
        return $ret;
    }
    
    /*
    ilChart
    ->setColors
    ->setHover() [tooltip?]
    [->setClick]
    ->setZooming
    ->setPanning
    ->setTooltip
    ->addData[Series]
        - labels
        - type
            - pie: nur 1x
            - bar, lines, points, steps, stacked?
        - highlight?!
        => min/max, transmission type (google api)


    grid-based
    ->setGrid
    ->setTicks(color, size, decimals, length)
    ->addAxisX (multiple, Zero) [id, mode, position, color, label]
    ->addAxisY (multiple?) [id, mode, position, color, label]
    ->setBackgroundFill(opacity, color/gradient)
    ->setThreshold(int/float)
    ->setToggles(bool)

    pie
    ->setCollapse(int/float, color, label)
    ->setRotation(int angle?)
    ->setRadius(inner, outer)
    */
}
