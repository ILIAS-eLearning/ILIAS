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
			$a_data[0]->color = ilChart::renderColor($fill["color"] , "0.5");
		}
	}		
	
	public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart)
	{
		$a_options->spider = new stdClass();
		$a_options->spider->active = true;
		
		$a_options->spider->highlight = new stdClass();
		$a_options->spider->highlight->mode = "line";
		
		
		$a_options->spider->legs = new stdClass();		
		$a_options->spider->legs->fillStyle = ilChart::renderColor("#000", 0.7);
		
		switch (count($a_chart->getLegLabels()))
		{
			case 4:
			case 6:
				$a_options->spider->legs->legStartAngle = 10; 				
				break;

			default:
				$a_options->spider->legs->legStartAngle = 0;
				break;
		}
		
		$a_options->spider->legs->data = array();
				
		$max_str_len = 0;
		foreach ($a_chart->getLegLabels() as $l)
		{
			$l = ilUtil::shortenText ($l, 80, true);
			
			$label =  new stdClass();
			$label->label = $l;
			$a_options->spider->legs->data[] = $label;

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
		$a_options->spider->legs->font = $font_size."px Arial";
		
		$a_options->spider->spiderSize = 0.7;
		$a_options->spider->lineWidth = 1;
		$a_options->spider->pointSize = 0;
		
		$a_options->spider->connection = new StdClass();
		$a_options->spider->connection->width = 2;
		
		$a_options->spider->legMin = 0.0000001;
		$a_options->spider->legMax = $a_chart->getYAxisMax();	
	}
}

?>