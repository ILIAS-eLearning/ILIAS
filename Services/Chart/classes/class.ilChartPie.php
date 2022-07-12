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
 * Generator for pie charts
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartPie extends ilChart
{
    public function getDataInstance(int $a_type = null) : ilChartData
    {
        return new ilChartDataPie();
    }
    
    protected function isValidDataType(ilChartData $a_series) : bool
    {
        return ($a_series instanceof ilChartDataPie);
    }
    
    protected function addCustomJS() : void
    {
        $tpl = $this->tpl;
        
        $tpl->addJavaScript("Services/Chart/js/flot/jquery.flot.pie.js");
    }
    
    public function parseGlobalOptions(stdClass $a_options) : void
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
