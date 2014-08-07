<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Chart legend
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartLegend
{
	protected $position; // [string]  
	protected $columns; // [int]
	protected $margin_x; // [int]
	protected $margin_y; // [int]
	protected $background; // [color]
	protected $opacity; // [float] 0-1
	protected $border; // [color]
	protected $container; // [string]

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->setPosition("ne");
		$this->setColumns(1);
		$this->setMargin(5, 5);
		$this->setBackground("#888");
		$this->setOpacity(0.1);
		$this->setLabelBorder("#bbb");
	}

	/**
	 * Set Position
	 *
	 * @param string $position
	 */
	public function setPosition($a_position)
	{
		$all = array("ne", "nw", "se", "sw");
		if(in_array((string)$a_position, $all))
		{
			$this->position = (string)$a_position;
		}
	}

	/**
	 * Get Position
	 *
	 * @return string
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Set number of columns
	 *
	 * @param int $a_value
	 */
	public function setColumns($a_value)
	{
		$this->columns = (int)$a_value;
	}

	/**
	 * Get number of columns
	 *
	 * @return int
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Set margins
	 *
	 * @param int $a_x
	 * @param int $a_y
	 */
	public function setMargin($a_x, $a_y)
	{
		$this->margin_x = (int)$a_x;
		$this->margin_y = (int)$a_y;
	}

	/**
	 * Get margins
	 *
	 * @return array (x, y)
	 */
	public function getMargin()
	{
		return array("x"=>$this->margin_x, "y"=>$this->margin_y);
	}

	/**
	 * Set background color
	 *
	 * @param string $a_color
	 */
	public function setBackground($a_color)
	{
		if(ilChart::isValidColor($a_color))
		{
			$this->background = $a_color;
		}
	}

	/**
	 * Get background color
	 *
	 * @return string
	 */
	public function getBackground()
	{
		return $this->background;
	}

	/**
	 * Set Opacity
	 *
	 * @param float $a_value
	 */
	public function setOpacity($a_value)
	{
		$a_value = (float)$a_value;
		if($a_value >= 0 && $a_value <= 1)
		{
			$this->opacity = $a_value;
		}
	}

	/**
	 * Get opacity
	 *
	 * @return float
	 */
	public function getOpacity()
	{
		return $this->opacity;
	}

	/**
	 * Set label border
	 *
	 * @param string $a_color
	 */
	public function setLabelBorder($a_color)
	{
		if(ilChart::isValidColor($a_color))
		{
			$this->border = $a_color;
		}
	}

	/**
	 * Get label border
	 *
	 * @return string
	 */
	public function getLabelBorder()
	{
		return $this->border;
	}
	
	/**
	 * Set container id
	 *
	 * @param string
	 */
	public function setContainer($a_value)
	{
		$this->container = trim($a_value);
	}
	
	/**
	 * Get container id
	 *
	 * @return string
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	/**
	 * Convert (global) properties to flot config
	 * 
	 * @param object $a_options	 
	 */
	public function parseOptions(stdClass $a_options)
	{
		$a_options->show = true;
		
		$a_options->noColumns = $this->getColumns();
		$a_options->position = $this->getPosition();
		
		$margin = $this->getMargin();
		$a_options->margin = array($margin["x"], $margin["y"]);
		
		$a_options->backgroundColor = ilChart::renderColor($this->getBackground());
		$a_options->backgroundOpacity = str_replace(",",".",$this->getOpacity());
		$a_options->labelBoxBorderColor = ilChart::renderColor($this->getLabelBorder());
		
		$container = $this->getContainer();
		if($container)
		{
			$a_options->container = '#'.$container;
		}
	}			
}

?>