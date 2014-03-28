<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract chart data series base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
abstract class ilChartData
{
	protected $type; // [string]
	protected $label; // [string]
	protected $data; // [array]		
	protected $fill; // [float]
	protected $fill_color; // [color/gradient]
	protected $hidden; // [bool]

	/**
	 * Get series type
	 * 
	 * @return string
	 */
	abstract protected function getTypeString();	
	
	/**
	 * Set hidden
	 *
	 * @param bool $a_value
	 */
	public function setHidden($a_value)
	{
	   $this->hidden = (bool)$a_value;
	}

	/**
	 * Is hidden?
	 *
	 * @return bool
	 */
	public function isHidden()
	{
		return $this->hidden;
	}

	/**
	 * Set label
	 *
	 * @param string $a_value
	 */
	public function setLabel($a_value)
	{
		$this->label = (string)$a_value;
	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Set data
	 * 
	 * @param float $a_x
	 * @param float $a_y
	 */
	public function addPoint($a_x, $a_y = null)
	{
		if($a_y !== null)
		{
			$this->data[] = array($a_x, $a_y);
		}
		else
		{
			$this->data[] = $a_x;
		}
	}

	/**
	 * Get data
	 *
	 * @return array 
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set fill
	 *
	 * @param float $a_value
	 * @param string $a_color
	 */
	public function setFill($a_value, $a_color = null)
	{
		$this->fill = $a_value;
		if(ilChart::isValidColor($a_color))
		{
			$this->fill_color = $a_color;
		}
	}

	/**
	 * Get fill
	 *
	 * @return array (fill, color)
	 */
	public function getFill()
	{
		return array("fill"=>$this->fill, "color"=>$this->fill_color);
	}
	
	/**
	 * Convert data options to flot config
	 * 
	 * @param array $a_options
	 * @param ilChart $a_chart
	 */
	protected function parseDataOptions(array &$a_options)
	{
		
	}
	
	/**
	 * Convert data to flot config
	 * 
	 * @param array $a_data
	 * @return object
	 */
	public function parseData(array &$a_data)
	{
		$series = new stdClass();
		$series->label = str_replace("\"", "\\\"", $this->getLabel());
		
		$series->data = array();
		foreach($this->getData() as $point)
		{			
			$series->data[] = array($point[0], $point[1]);
		}
			
		$options = array("show"=>($this->isHidden() ? false : true));
				
		$fill = $this->getFill();
		if($fill["fill"])
		{
			$options["fill"] = $fill["fill"];
			if($fill["color"])
			{
				$options["fillColor"] = ilChart::renderColor($fill["color"], $fill["fill"]);
			}
		}
		
		$this->parseDataOptions($options);
		
		$series->{$this->getTypeString()} = $options;
		
		$a_data[] = $series;
	}
	
	/**
	 * Convert (global) properties to flot config
	 * 
	 * @param object $a_options	 
	 * @param ilChart $a_chart	 
	 */
	public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart)
	{
				
	}
}

?>