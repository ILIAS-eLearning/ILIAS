<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";

/**
 * Chart data pie series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataPie extends ilChartData
{
    protected $line_width; // [int]
    protected $label_radius; //mixed
    
    protected function getTypeString()
    {
        return "pie";
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
     *
     * Sets the radius at which to place the labels. If value is between 0 and 1 (inclusive) then
     * it will use that as a percentage of the available space (size of the container), otherwise
     * it will use the value as a direct pixel length.
     *
     * @param mixed $a_value
     */
    public function setLabelRadius($a_value)
    {
        $this->label_radius = $a_value;
    }

    /**
     * @return mixed
     */
    public function getLabelRadius()
    {
        return $this->label_radius;
    }
    
    public function addPoint($a_value, $a_caption = null)
    {
        $this->data[] = array($a_value, $a_caption);
    }
        
    public function parseData(array &$a_data)
    {
        foreach ($this->data as $slice) {
            $series = new stdClass();
            $series->label = str_replace("\"", "\\\"", $slice[1]);
            
            // add percentage to legend
            if (!$this->getLabelRadius()) {
                $series->label .= " (" . $slice[0] . "%)";
            }
            
            $series->data = $slice[0];

            $options = array("show" => ($this->isHidden() ? false : true));
            
            $series->{$this->getTypeString()} = $options;

            $a_data[] = $series;
        }
    }
    
    public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart)
    {
        $a_options->series->pie = new stdClass();
        $a_options->series->pie->show = true;
                
        // fill vs. stroke - trying to normalize data attributes
        
        $fill = $this->getFill();
        $width = $this->getLineWidth();
        if ($fill["fill"] || $width) {
            $a_options->series->pie->stroke = new stdClass;
            if ($width) {
                $a_options->series->pie->stroke->width = $width;
            }
            if ($fill["color"]) {
                $a_options->series->pie->stroke->color = ilChart::renderColor($fill["color"], $fill["fill"]);
            }
        }

        $radius = $this->getLabelRadius();
        if ($radius) {
            $a_options->series->pie->label = new stdClass;
            $a_options->series->pie->label->background = new stdClass;
            $a_options->series->pie->radius = 1;
            $a_options->series->pie->label->radius = $radius;
            $a_options->series->pie->label->show = true;
            $a_options->series->pie->label->background->color = "#444";
            $a_options->series->pie->label->background->opacity = 0.8;
        }
    }
}
