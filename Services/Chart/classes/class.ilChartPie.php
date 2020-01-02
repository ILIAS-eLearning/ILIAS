<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChart.php";

/**
 * Generator for pie charts
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartPie extends ilChart
{
    public function getDataInstance($a_type = null)
    {
        include_once "Services/Chart/classes/class.ilChartDataPie.php";
        return new ilChartDataPie();
    }
    
    protected function isValidDataType(ilChartData $a_series)
    {
        return ($a_series instanceof ilChartDataPie);
    }
    
    protected function addCustomJS()
    {
        $tpl = $this->tpl;
        
        $tpl->addJavascript("Services/Chart/js/flot/jquery.flot.pie.js");
    }
    
    public function parseGlobalOptions(stdClass $a_options)
    {
        // if no inner labels set, use legend
        if (!isset($a_options->series->pie->label) &&
            !$this->legend) {
            $legend = new ilChartLegend();
            $legend->setPosition("nw");
            $this->setLegend($legend);
        }
    }
}
