<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChart.php";

/**
 * Generator for spider charts
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartSpider extends ilChart
{
	protected $leg_labels = array(); // [array]
	protected $y_max = 0; // [float]
	
	public function getDataInstance($a_type = null)
	{		
		include_once "Services/Chart/classes/class.ilChartDataSpider.php";
		return new ilChartDataSpider();					
	}
	
	protected function isValidDataType(ilChartData $a_series)
	{
		return ($a_series instanceof ilChartDataSpider);
	}
	
	/**
	 * Set leg labels
	 *
	 * @param array $a_val leg labels (array of strings)	
	 */
	public function setLegLabels($a_val)
	{
		$this->leg_labels = $a_val;
	}
	
	/**
	 * Get leg labels
	 *
	 * @return array leg labels (array of strings)
	 */
	public function getLegLabels()
	{
		return $this->leg_labels;
	}
	
	
	/**
	 * Set y axis max value
	 *
	 * @param float $a_val y axis max value	
	 */
	public function setYAxisMax($a_val)
	{
		$this->y_max = $a_val;
	}
	
	/**
	 * Get y axis max value
	 *
	 * @return float y axis max value
	 */
	public function getYAxisMax()
	{
		return $this->y_max;
	}
	
	protected function addCustomJS()
	{
		global $tpl;
		
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.highlighter.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.spider.js");
	}
	
	public function parseGlobalOptions(stdClass $a_options)
	{		
		$a_options->grid = new stdClass();
		$a_options->grid->hoverable = false;
		$a_options->grid->clickable = false;
		$a_options->grid->ticks = $this->getYAxisMax();
		$a_options->grid->tickColor = ilChart::renderColor("#000", "0.1");
		$a_options->grid->mode = "spider";
	}

	/* Optes:

        series: {
			shadowSize: {SHADOW},
			lines: { show: false },
			pie: { show: {IS_PIE} }
			<!-- BEGIN spider -->
			,spider:{
				active: true
				,highlight: {mode: "line"}
				,legs: {
					data: [{LEG_LABELS}]
					,font: "{FONT_SIZE}px Arial"
					,fillStyle: "rgba(0,0,0,0.7)"
					,legStartAngle: {LEG_START_ANGLE}
				}
				,spiderSize: 0.7
				,lineWidth: 1
				,pointSize: 0
				,connection: { width: 2 }
				,legMin: 0.0000001
				,legMax: {LEG_MAX}
			}
			<!-- END spider -->


				$chart->setCurrentBlock("spider");
			$lab_strings = array();
			$max_str_len = 0;
			foreach ($this->getLegLabels() as $l)
			{
				$l = ilUtil::shortenText ($l, 80, true);
				$lab_strings[] = "{label: \"".$l."\"}";
				$max_str_len = max($max_str_len, strlen($l));
			}
			$chart->setVariable("LEG_LABELS", implode($lab_strings, ","));
			$chart->setVariable("LEG_MAX", $this->getYAxisMax());
			switch (count($this->getLegLabels()))
			{
				case 4:
				case 6:
					$chart->setVariable("LEG_START_ANGLE", "10");
					break;

				default:
					$chart->setVariable("LEG_START_ANGLE", "0");
					break;
			}
			if ($max_str_len > 60)
			{
				$chart->setVariable("FONT_SIZE", "10");
			}
			else if ($max_str_len > 30)
			{
				$chart->setVariable("FONT_SIZE", "12");
			}
			else
			{
				$chart->setVariable("FONT_SIZE", "15");
			}
			$chart->parseCurrentBlock();

			$chart->setCurrentBlock("spider_grid_options");
			$chart->setVariable("NR_TICKS", $this->getYAxisMax());
			$chart->parseCurrentBlock();


	 */

}

?>