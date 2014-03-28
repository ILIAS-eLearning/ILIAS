<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";

/**
 * Chart data points series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataPoints extends ilChartData
{
	protected $line_width; // [int]	
	protected $radius; // [int] points
	
	protected function getTypeString()
	{
		return "points";
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
	
	/**
	 * Set radius
	 *
	 * @param int $a_value
	 */
	public function setPointRadius($a_value)
	{
		$this->radius = (int)$a_value;
	}

	/**
	 * Get radius
	 *
	 * @return int
	 */
	public function getPointRadius()
	{
		return $this->radius;
	}	
	
	protected function parseDataOptions(array &$a_options)
	{		
		$width = $this->getLineWidth();
		if($width !== null)
		{
			$a_options["lineWidth"] = $width;
		}
		
		$radius = $this->getPointRadius();
		if($radius !== null)
		{
			$a_options["radius"] = $radius;
		}				
	}
}

?>