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

/**
* QTI presentation class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIPresentation
{
	var $label;
	var $xmllang;
	var $x0;
	var $y0;
	var $width;
	var $height;
	
	var $material;
	var $response;
	var $order;
	
	function ilQTIPresentation()
	{
		$this->response = array();
		$this->material = array();
		$this->order = array();
	}
	
	function setLabel($a_label)
	{
		$this->label = $a_label;
	}
	
	function getLabel()
	{
		return $this->label;
	}
	
	function setXmllang($a_xmllang)
	{
		$this->xmllang = $a_xmllang;
	}
	
	function getXmllang()
	{
		return $this->xmllang;
	}
	
	function setX0($a_x0)
	{
		$this->x0 = $a_x0;
	}
	
	function getX0()
	{
		return $this->x0;
	}
	
	function setY0($a_y0)
	{
		$this->y0 = $a_y0;
	}
	
	function getY0()
	{
		return $this->y0;
	}
	
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function addMaterial($a_material)
	{
		$count = array_push($this->material, $a_material);
		array_push($this->order, array("type" =>"material", "index" => $count-1));
	}
	
	function addResponse($a_response)
	{
		$count = array_push($this->response, $a_response);
		array_push($this->order, array("type" =>"response", "index" => $count-1));
	}
}
?>
