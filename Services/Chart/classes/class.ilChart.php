<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartData.php";
include_once "Services/Chart/classes/class.ilChartLegend.php";

/**
 * Chart generator
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChart
{
	protected $id; // [string]
	protected $renderer; // [string]
	protected $width; // [string]
	protected $height; // [string]
	protected $data; // [array]
	protected $legend; // [ilChartLegend]
	protected $shadow; // [int]
	protected $colors; // [array]
	protected $ticks; // [array]
	protected $integer_axis; // [array]

	/**
	 * Constructor
	 *
	 * @param string $a_id
	 * @param int $a_width
	 * @param int $a_height
     * @param string $a_renderer
	 */
	public function __construct($a_id, $a_width = 500, $a_height = 500, $a_renderer = "flot")
	{
		$this->id = $a_id;
		$this->data = array();
		$this->setXAxisToInteger(false);
		$this->setYAxisToInteger(false);
		$this->setSize($a_width, $a_height);
		$this->setRenderer($a_renderer);
		$this->setShadow(2);
	}

	/**
	 * Set renderer
	 *
	 * @param string $a_value
	 */
	public function setRenderer($a_value)
	{
		if(in_array((string)$a_value, $this->getAllRenderers()))
		{
			$this->renderer = (string)$a_value;
		}
	}
	
	/**
	 * Get all available renderers
	 *
	 * @return array
	 */
	public function getAllRenderers()
	{
		return array("flot");
	}

	/**
	 * Set chart size
	 *
	 * @param int $a_x
	 * @param int $a_y
	 */
	public function setSize($a_x, $a_y)
	{
		$this->width = (int)$a_x;
		$this->height = (int)$a_y;
	}

	/**
	 * Add data series
	 *
	 * @param ilChartData $a_series
	 * @param mixed $a_id
	 * @return mixed index
	 */
	public function addData(ilChartData $a_series, $a_idx = null)
	{
		if($a_idx === null)
		{
			$a_idx = sizeof($this->data);
		}
		$this->data[$a_idx] = $a_series;
		return $a_idx;
	}

	/**
	 * Set chart legend
	 *
	 * @param ilChartLegend $a_legend
	 */
	public function setLegend(ilChartLegend $a_legend)
	{
		$this->legend = $a_legend;
	}

	/**
	 * Validate html color code
	 *
	 * @param string $a_value
	 * @return bool
	 */
	public static function isValidColor($a_value)
	{
	    if(preg_match("/^#[0-9a-f]{3}$/i", $a_value, $match))
		{
			return true;
		}
		else if(preg_match("/^#[0-9a-f]{6}$/i", $a_value, $match))
		{
			return true;
		}		
	}

	/**
	 * Render html color code
	 *
	 * @param string $a_value
	 * @param float $a_opacity
	 * @return string
	 */
	protected static function renderColor($a_value, $a_opacity = 1)
	{
		if(self::isValidColor($a_value))
		{
			if(strlen($a_value) == 4)
			{
				return "\"rgba(".hexdec($a_value[1].$a_value[1]).", ".
					hexdec($a_value[2].$a_value[2]).", ".
					hexdec($a_value[3].$a_value[3]).", ".$a_opacity.")\"";
			}
			else
			{
				return "\"rgba(".hexdec($a_value[1].$a_value[2]).", ".
					hexdec($a_value[3].$a_value[4]).", ".
					hexdec($a_value[5].$a_value[6]).", ".$a_opacity.")\"";
			}
		}
	}

	/**
	 * Set shadow
	 *
	 * @param int $a_value
	 */
	public function setShadow($a_value)
	{
		$this->shadow = (int)$a_value;
	}

	/**
	 * Get shadow
	 *
	 * @return int
	 */
	public function getShadow()
	{
		return $this->shadow;
	}

	/**
	 * Set colors
	 *
	 * @param array $a_values
	 */
	public function setColors($a_values)
	{
		foreach($a_values as $color)
		{
			if(self::isValidColor($color))
			{
				$this->colors[] = $color;
			}
		}
	}

	/**
	 * Get colors
	 *
	 * @return array
	 */
	public function getColors()
	{
		return $this->colors;
	}

	/**
	 * Set ticks
	 *
	 * @param int|array $a_x
	 * @param int|array $a_y
	 * @param bool $a_labeled
	 */
	public function setTicks($a_x, $a_y, $a_labeled = false)
	{
		$this->ticks = array("x" => $a_x, "y" => $a_y, "labeled" => (bool)$a_labeled);
	}

	/**
	 * Get ticks
	 *
	 * @return array (x, y)
	 */
	public function getTicks()
	{
		return $this->ticks;
	}

	/**
	 * Render (flot only currently)
	 */
	public function getHTML()
	{
		global $tpl;
		
		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		iljQueryUtil::initjQuery();
		
		$tpl->addJavascript("Services/Chart/js/flot/excanvas.min.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.min.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.pie.js");

		$chart = new ilTemplate("tpl.grid.html", true, true, "Services/Chart");
		$chart->setVariable("ID", $this->id);
		$chart->setVariable("WIDTH", $this->width);
		$chart->setVariable("HEIGHT", $this->height);
	
		$last = array_keys($this->data);
		$last = array_pop($last);
		$has_pie = false;
		foreach($this->data as $idx => $series)
		{
			$chart->setCurrentBlock("series");
			$chart->setVariable("SERIES_LABEL", str_replace("\"", "\\\"", $series->getLabel()));
			$chart->setVariable("SERIES_TYPE", $series->getType());

			$type = $series->getType();

			$points = array();
			if($type != "pie")
			{
				foreach($series->getData() as $point)
				{
					$points[] = "[".$point[0].",".$point[1]."]";
				}
				$chart->setVariable("SERIES_DATA", "[ ".implode(",", $points)." ]");
			}
			else
			{
				$has_pie = true;
				$chart->setVariable("SERIES_DATA", array_pop($series->getData()));
			}
			if($idx != $last)
			{
				$chart->setVariable("SERIES_END", ",");
			}

			$options = array("show: ".($series->isHidden() ? "false" : "true"));
			if($type != "points")
			{
				$width = $series->getLineWidth();
				if($width !== null)
				{
					$options[] = "lineWidth:".$width;
				}
				if($type == "bars")
				{
					$bar_options = $series->getBarOptions();
					if($bar_options["width"] !== null)
					{
						$options[] = "barWidth:".str_replace(",", ".", $bar_options["width"]);
						$options[] = "align: \"".$bar_options["align"]."\"";
						if($bar_options["horizontal"])
						{
							$options[] = "horizontal: true";
						}
					}
				}
				else if($type == "lines")
				{
					if($series->getLineSteps())
					{
						$options[] = "steps: true";
					}
				}
			}
			else
			{
				$radius = $series->getPointRadius();
				if($radius !== null)
				{
					$options[] = "radius:".$radius;
				}
			}
			$fill = $series->getFill();
			if($fill["fill"])
			{
				$options[] = "fill: ".$fill["fill"];
				if($fill["color"])
				{
					$options[] = "fillColor: ".self::renderColor($fill["color"], $fill["fill"]);
				}
			}
			$chart->setVariable("SERIES_OPTIONS", implode(", ", $options));

			$chart->parseCurrentBlock();
		}

		
		// global options

		$chart->setVariable("SHADOW", (int)$this->getShadow());
		$chart->setVariable("IS_PIE", ($has_pie ? "true" : "false"));
		
		$colors = $this->getColors();
		if($colors)
		{
			$tmp = array();
			foreach($colors as $color)
			{
				$tmp[] = self::renderColor($color);
			}
		}
		if(sizeof($tmp))
		{
			$chart->setVariable("COLORS", implode(",", $tmp));
		}

		// legend
		if(!$this->legend)
		{
			$chart->setVariable("LEGEND", "show: false");
		}
		else
		{
			$margin = $this->legend->getMargin();
			$legend = array();
			$legend[] = "show: true";
			$legend[] = "noColumns: ".$this->legend->getColumns();
			$legend[] = "position: \"".$this->legend->getPosition()."\"";
			$legend[] = "margin: [".$margin["x"].", ".$margin["y"]."]";
			$legend[] = "backgroundColor: ".self::renderColor($this->legend->getBackground());
			$legend[] = "backgroundOpacity: ".str_replace(",",".",$this->legend->getOpacity());
			$legend[] = "labelBoxBorderColor: ".self::renderColor($this->legend->getLabelBorder());

			$chart->setVariable("LEGEND", implode(", ", $legend));
		}

		// axis/ticks
		$tmp = array();
		$ticks = $this->getTicks();
		if($ticks)
		{			
			foreach($ticks as $axis => $def)
			{
				if(is_numeric($def))
				{
					$tmp[$axis] = $axis."axis: { ticks: ".$def." }";
				}
				else if(is_array($def))
				{
					$ttmp = array();
					foreach($def as $idx => $value)
					{
						if($ticks["labeled"])
						{
							$ttmp[] = "[".$idx.", \"".$value."\"]";
						}
						else
						{
							$ttmp[] = $value;
						}
					}
					$tmp[$axis] = $axis."axis: { ticks: [".implode(", ", $ttmp)."] }";
				}
			}
		}
		
		// optional: remove decimals
	    if(!isset($tmp["x"]) && $this->integer_axis["x"])
		{
			$tmp["x"] = "xaxis: { tickDecimals: 0 }";
		}
		if(!isset($tmp["y"]) && $this->integer_axis["y"])
		{
			$tmp["y"] = "yaxis: { tickDecimals: 0 }";
		}		
		
		if(sizeof($tmp))
		{
			$chart->setVariable("AXIS", ",".implode(", ", $tmp));
		}
		
		return $chart->get();
	}
	
	function setYAxisToInteger($a_status)
	{
		$this->integer_axis["y"] = (bool)$a_status;
	}
	
	function setXAxisToInteger($a_status)
	{
		$this->integer_axis["x"] = (bool)$a_status;
	}
	
	/*
	ilChart
	->setColors
	->setHover() [tooltip?]
	[->setClick]
	->setZooming
	->setPanning
	->setTooltip
	->addData[Series]
		- labels
		- type
			- pie: nur 1x
			- bar, lines, points, steps, stacked?
		- highlight?!
		=> min/max, transmission type (google api)


	grid-based
	->setGrid
	->setTicks(color, size, decimals, length)
	->addAxisX (multiple, Zero) [id, mode, position, color, label]
	->addAxisY (multiple?) [id, mode, position, color, label]
	->setBackgroundFill(opacity, color/gradient)
	->setThreshold(int/float)
	->setToggles(bool)

	pie
	->setCollapse(int/float, color, label)
	->setRotation(int angle?)
	->setRadius(inner, outer)
	*/
}

?>