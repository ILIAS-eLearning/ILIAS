<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "assessment/classes/class.assImagemapQuestion.php";

/**
* Class assImageMapArea
*
* Map Area of an Image Map
*
* @author
* @version $Id$
*
* @package content
*/
class ASS_ImagemapArea
{
	var $shape;
	var $coords;
	var $title;

	/**
	* map area
	*
	*/
	function ASS_ImagemapArea()
	{
	}

	/**
	* set shape (RECTANGLE, CIRCLE, POLYGON)
	*
	* @param	string		$a_shape	shape of map area
	*/
	function setShape($a_shape = RECTANGLE)
	{
		$this->shape = $a_shape;
	}

	/**
	* get shape
	*
	* @return	string		(RECTANGLE, CIRCLE, POLYGON)
	*/
	function getShape()
	{
		return $this->shape;
	}

	/**
	* set coords of area
	*
	* @param	string		$a_coords		coords (comma separated integers)
	*/
	function setCoords($a_coords)
	{
		$this->coords = $a_coords;
	}

	/**
	* get coords
	*
	* @return	string		coords (comma separated integers)
	*/
	function getCoords()
	{
		return $this->coords;
	}

	/**
	* set (tooltip)title of area
	*
	* @param	string		$a_title		title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* get (tooltip) title
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* draw image to
	*
	* @param	boolean		$a_close_poly		close polygon
	*/
	function draw(&$a_image, $a_col1, $a_col2, $a_close_poly = true)
	{
		switch ($this->getShape())
		{
			case "RECT" :
				$this->drawRect($a_image, $this->getCoords(), $a_col1, $a_col2);
				break;

			case "CIRCLE" :
				$this->drawCircle($a_image, $this->getCoords(), $a_col1, $a_col2);
				break;

			case "POLY" :
				$this->drawPoly($a_image, $this->getCoords(), $a_col1, $a_col2, $a_close_poly);
				break;
		}
	}

	/**
	* draws an outlined two color line in an image
	*
	* @param 	int		$im		image identifier as returned by ImageCreateFromGIF() etc.
	* @param	int		$x1		x-coordinate of starting point
	* @param	int		$y1		y-coordinate of starting point
	* @param	int		$x2		x-coordinate of ending point
	* @param	int		$y2		y-coordinate of ending point
	* @param	int		$c1		color identifier 1
	* @param	int		$c2		color identifier 2
	*/
	function drawLine(&$im, $x1, $y1, $x2, $y2, $c1, $c2)
	{
		imageline($im, $x1+1, $y1, $x2+1, $y2, $c1);
		imageline($im, $x1-1, $y1, $x2-1, $y2, $c1);
		imageline($im, $x1, $y1+1, $x2, $y2+1, $c1);
		imageline($im, $x1, $y1-1, $x2, $y2-1, $c1);
		imageline($im, $x1, $y1, $x2, $y2, $c2);
	}

	/**
	* draws an outlined two color rectangle
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF() etc.
	* @param	string		$coords     coordinate string, format : "x1,y1,x2,y2" with (x1,y1) is top left
	*									and (x2,y2) is bottom right point of the rectangle
	* @param	int			$c1			color identifier 1
	* @param	int			$c2			color identifier 2
	*/
	function drawRect(&$im,$coords,$c1,$c2)
	{
		$coord=explode(",", $coords);
		$this->drawLine($im, $coord[0], $coord[1], $coord[0], $coord[3], $c1, $c2);
		$this->drawLine($im, $coord[0], $coord[3], $coord[2], $coord[3], $c1, $c2);
		$this->drawLine($im, $coord[2], $coord[3], $coord[2], $coord[1], $c1, $c2);
		$this->drawLine($im, $coord[2], $coord[1], $coord[0], $coord[1], $c1, $c2);
	}


	/**
	* draws an outlined two color polygon
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF() etc.
	* @param	string		$coords     coordinate string, format : "x1,y1,x2,y2,..." with every (x,y) pair is
	*									an ending point of a line of the polygon
	* @param	int			$c1			color identifier 1
	* @param	int			$c3			color identifier 2
	* @param	boolean		$closed		true: the first and the last point will be connected with a line
	*/
	function drawPoly(&$im, $coords, $c1, $c2, $closed)
	{
		if ($closed)
		{
			$p = 0;
		}
		else
		{
			$p = 1;
		}

		$anz = assImageMapArea::countCoords($coords);

		if ($anz < (3 - $p))
		{
			return;
		}

		$c = explode(",", $coords);

		for($i=0; $i<$anz-$p; $i++)
		{
			$this->drawLine($im, $c[$i*2], $c[$i*2+1], $c[($i*2+2)%(2*$anz)],
				$c[($i*2+3)%(2*$anz)], $c1, $c2);
		}
	}


	/**
	* draws an outlined two colored circle
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF()
	* @param	string		$coords     coordinate string, format : "x,y,r" with (x,y) as center point
	*									and r as radius
	* @param	int			$c1			color identifier 1
	* @param	int			$c3			color identifier 2
	*/
	function drawCircle(&$im, $coords, $c1, $c2)
	{
		$c = explode(",", $coords);
		imagearc($im, $c[0], $c[1], ($c[2]+1)*2, ($c[2]+1)*2, 1, 360, $c1);
		imagearc($im, $c[0], $c[1], ($c[2]-1)*2, ($c[2]-1)*2, 1, 360, $c1);
		imagearc($im, $c[0], $c[1], $c[2]*2, $c[2]*2, 1, 360, $c2);
	}

	/**
	* count the number of coordinates (x,y) in a coordinate string (format: "x1,y1,x2,y2,x3,y3,...")
	*
	* @param	string		$c		coordinate string
	*/
	function countCoords($c)
	{
		if ($c == "")
		{
			return 0;
		}
		else
		{
			$coord_array = explode(",", $c);
			return (count($coord_array) / 2);
		}
	}

}
?>
