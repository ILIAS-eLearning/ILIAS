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
 * Abstract Chart generator base class
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilChart
{
    public const TYPE_GRID = 1;
    public const TYPE_PIE = 2;
    public const TYPE_SPIDER = 3;

    protected ilGlobalTemplateInterface $tpl;
    protected string $id = "";
    protected string $width = "";
    protected string $height = "";
    protected array $data = [];
    protected ?ilChartLegend $legend = null;
    protected int $shadow = 0;
    protected array $colors = [];
    protected bool $auto_resize = false;
    protected bool $stacked = false;

    protected function __construct(string $a_id)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->id = $a_id;
        $this->data = array();
                
        $this->setShadow(2);
    }

    public static function getInstanceByType(
        int $a_type,
        string $a_id
    ) : ilChart {
        switch ($a_type) {
            case self::TYPE_GRID:
                return new ilChartGrid($a_id);
                
            case self::TYPE_PIE:
                return new ilChartPie($a_id);
                
            case self::TYPE_SPIDER:
                return new ilChartSpider($a_id);
        }
        throw new ilException("Unknown chart type.");
    }
    
    /**
     * Get data series instance
     */
    abstract public function getDataInstance(int $a_type = null) : ilChartData;

    /**
     * Validate data series
     */
    abstract protected function isValidDataType(ilChartData $a_series) : bool;

    /**
     * Basic validation
     */
    protected function isValid() : bool
    {
        if (sizeof($this->data)) {
            return true;
        }
        return false;
    }

    /**
     * Set chart size
     */
    public function setSize(string $a_x, string $a_y) : void
    {
        $this->width = $a_x;
        $this->height = $a_y;
    }

    /**
     * Add data series
     */
    public function addData(
        ilChartData $a_series,
        ?int $a_idx = null
    ) : ?int {
        if ($this->isValidDataType($a_series)) {
            if ($a_idx === null) {
                $a_idx = sizeof($this->data);
            }
            $this->data[$a_idx] = $a_series;
            return $a_idx;
        }
        return null;
    }

    public function setLegend(ilChartLegend $a_legend) : void
    {
        $this->legend = $a_legend;
    }
    
    public function setColors(array $a_values) : void
    {
        foreach ($a_values as $color) {
            if (self::isValidColor($color)) {
                $this->colors[] = $color;
            }
        }
    }

    public function getColors() : array
    {
        return $this->colors;
    }

    /**
     * Validate html color code
     */
    public static function isValidColor(string $a_value) : bool
    {
        if (preg_match("/^#[0-9a-f]{3}$/i", $a_value, $match)) {
            return true;
        } elseif (preg_match("/^#[0-9a-f]{6}$/i", $a_value, $match)) {
            return true;
        }
        return false;
    }

    /**
     * Render html color code
     */
    public static function renderColor(
        string $a_value,
        float $a_opacity = 1
    ) : string {
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
        return "";
    }

    public function setShadow(int $a_value) : void
    {
        $this->shadow = $a_value;
    }

    public function getShadow() : int
    {
        return $this->shadow;
    }
    
    /**
     * Toggle auto-resizing on window resize/redraw
     */
    public function setAutoResize(
        bool $a_value
    ) : void {
        $this->auto_resize = $a_value;
    }
    
    public function setStacked(bool $a_value) : void
    {
        $this->stacked = $a_value;
    }
    
    /**
     * Init JS script files
     */
    protected function initJS() : void
    {
        $tpl = $this->tpl;
        
        iljQueryUtil::initjQuery();
        
        $tpl->addJavaScript("Services/Chart/js/flot/excanvas.min.js");
        $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.min.js");
        
        if ($this->auto_resize) {
            // #13108
            $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.resize.min.js");
        }
        
        if ($this->stacked) {
            $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.stack.min.js");
        }
        
        $this->addCustomJS();
    }
    
    /**
     * Add type-specific JS script
     */
    protected function addCustomJS() : void
    {
    }
    
    /**
     * Convert (global) properties to flot config
     */
    public function parseGlobalOptions(stdClass $a_options) : void
    {
    }
    
    /**
     * Render
     */
    public function getHTML() : string
    {
        if (!$this->isValid()) {
            return "";
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
        $series_str = json_encode($json_series);
        
        
        // global options
        
        $json_options = new stdClass();
        $json_options->series = new stdClass();
        $json_options->series->shadowSize = $this->getShadow();
        $json_options->series->lines = new stdClass();
        $json_options->series->lines->show = false;
        $json_options->series->stack = $this->stacked;
        
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
    
        $options = json_encode($json_options);

        $this->tpl->addOnLoadCode('$.plot($("#ilChart' . $this->id . '"), ' . $series_str . ', ' . $options . ');');

        $ret = $chart->get();
        return $ret;
    }
}
