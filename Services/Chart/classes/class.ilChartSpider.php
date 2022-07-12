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
 * Generator for spider charts
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartSpider extends ilChart
{
    protected array $leg_labels = array();
    protected float $y_max = 0;
    
    public function getDataInstance(int $a_type = null) : ilChartData
    {
        return new ilChartDataSpider();
    }
    
    protected function isValidDataType(ilChartData $a_series) : bool
    {
        return ($a_series instanceof ilChartDataSpider);
    }
    
    /**
     * Set leg labels
     * @param array $a_val leg labels (array of strings)
     */
    public function setLegLabels(array $a_val) : void
    {
        $this->leg_labels = $a_val;
    }
    
    /**
     * Get leg labels
     * @return array leg labels (array of strings)
     */
    public function getLegLabels() : array
    {
        return $this->leg_labels;
    }
    
    
    /**
     * Set y axis max value
     * @param float $a_val y axis max value
     */
    public function setYAxisMax(float $a_val) : void
    {
        $this->y_max = $a_val;
    }
    
    /**
     * Get y axis max value
     * @return float y axis max value
     */
    public function getYAxisMax() : float
    {
        return $this->y_max;
    }
    
    protected function addCustomJS() : void
    {
        $tpl = $this->tpl;
        
        $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.highlighter.js");
        $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.spider.js");
    }
    
    public function parseGlobalOptions(stdClass $a_options) : void
    {
        $a_options->grid = new stdClass();
        $a_options->grid->hoverable = false;
        $a_options->grid->clickable = false;
        $a_options->grid->ticks = $this->getYAxisMax();
        $a_options->grid->tickColor = ilChart::renderColor("#000", "0.1");
        $a_options->grid->mode = "spider";
    }
}
