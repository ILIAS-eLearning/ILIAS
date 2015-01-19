<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";

/**
 * Chart data spider series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataSpider extends ilChartData
{
	protected function getTypeString()
	{
		return "spider";
	}
	
	public function parseData(array &$a_data)
	{
		parent::parseData($a_data);
		
		$fill = $this->getFill();
		if ($fill["color"] != "")
		{
			$a_data[count($a_data)-1]->color = ilChart::renderColor($fill["color"] , "0.5");
		}
	}
	
	public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart)
	{
		$spider = new stdClass();
		$spider->active = true;
		
		$spider->highlight = new stdClass();
		$spider->highlight->mode = "line";
		
		
		$spider->legs = new stdClass();
		$spider->legs->fillStyle = ilChart::renderColor("#000", "0.7");
		switch (count($a_chart->getLegLabels()))
		{
			case 4:
			case 6:
				$spider->legs->legStartAngle = 10; 				
				break;

			default:
				$spider->legs->legStartAngle = 0;
				break;
		}
		
		$spider->legs->data = array();
				
		$max_str_len = 0;
		foreach ($a_chart->getLegLabels() as $l)
		{
			$l = ilUtil::shortenText ($l, 80, true);
			
			$label =  new stdClass();
			$label->label = $l;
			$spider->legs->data[] = $label;

			$max_str_len = max($max_str_len, strlen($l));
		}
		
		// depending on caption length
		if ($max_str_len > 60)
		{
			$font_size = 10;
		}
		else if ($max_str_len > 30)
		{
			$font_size = 12;
		}
		else
		{
			$font_size = 15;
		}
		$spider->legs->font = $font_size."px Arial";
		
		$spider->spiderSize = 0.7;
		$spider->lineWidth = 1;
		$spider->pointSize = 0;
		
		$spider->connection = new StdClass();
		$spider->connection->width = 2;
		
		$spider->legMin = 0.0000001;
		$spider->legMax = $a_chart->getYAxisMax();	
		
		$a_options->series->spider = $spider;
	}
}

?>