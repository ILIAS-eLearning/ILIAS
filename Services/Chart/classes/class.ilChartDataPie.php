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
		$this->line_width = (int)$a_value;
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
	
	public function addPoint($a_value, $a_caption = null)
	{		
		$this->data[] = array($a_value, $a_caption);		
	}
		
	public function parseData(array &$a_data)
	{
		foreach($this->data as $slice)
		{		
			$series = new stdClass();
			$series->label = str_replace("\"", "\\\"", $slice[1]);
			$series->data = $slice[0];		

			$options = array("show"=>($this->isHidden() ? false : true));
			
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
		if($fill["fill"] || $width)
		{
			$a_options->series->pie->stroke = new stdClass;			
			if($width)
			{
				$a_options->series->pie->stroke->width = $width;
			}			
			if($fill["color"])
			{
				$a_options->series->pie->stroke->color = ilChart::renderColor($fill["color"], $fill["fill"]);
			}
		}

	}
}

?>