<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Chart data series
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartData
{
	protected $type; // [string]
	protected $label; // [string]
	protected $data; // [array]
	protected $line_width; // [int]
	protected $bar_width; // [float] bar
	protected $bar_align; // [string] bar
	protected $bar_horizontal; // [bool] bar
	protected $radius; // [int] points
	protected $steps; // [bool] lines	
	protected $fill; // [float]
	protected $fill_color; // [color/gradient]
	protected $hidden; // [bool]

	/**
	 * Constructor
	 *
	 * @param string $a_type
	 */
	public function __construct($a_type)
	{
		$this->setType($a_type);
	}

	/**
	 * Set type
	 *
	 * @param string $a_value
	 */
	public function setType($a_value)
	{
		if($this->isValidType($a_value))
		{
			$this->type = (string)$a_value;
		}
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

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
	 * Is given type valid?
	 *
	 * @param string $a_value
	 * @return bool
	 */
	public function isValidType($a_value)
	{
		$all = array("lines", "bars", "points", "pie");
		if(in_array((string)$a_value, $all))
		{
			return true;
		}
		return false;
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
	 * Set line steps
	 *
	 * @param bool $a_value
	 */
	public function setLineSteps($a_value)
	{
		$this->steps = (bool)$a_value;
	}

	/**
	 * Get line steps
	 *
	 * @return bool
	 */
	public function getLineSteps()
	{
		return $this->steps;
	}

	/**
	 * Set bar options
	 *
	 * @param float $a_width
	 * @param string $a_align
	 * @param bool $a_horizontal
	 */
	public function setBarOptions($a_width, $a_align = "center", $a_horizontal = false)
	{
		$this->bar_width = (float)$a_width;
		if(in_array((string)$a_align, array("center", "left")))
		{
			$this->bar_align = (string)$a_align;
		}
		$this->bar_horizontal = (bool)$a_horizontal;
	}

	/**
	 * Get bar options
	 *
	 * @return array (width, align, horizontal)
	 */
	public function getBarOptions()
	{
		return array("width" => $this->bar_width,
			"align" => $this->bar_align,
			"horizontal" => $this->bar_horizontal);
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
}

?>