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

define ("SHOWDRAW_NO", "1");
define ("SHOWDRAW_YES", "2");

/**
* QTI render hotspot class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderHotspot
{
	var $showdraw;
	var $minnumber;
	var $maxnumber;
	var $response_labels;
	var $material;

	function __construct()
	{
		$this->showdraw = SHOWDRAW_NO;
		$this->response_labels = array();
		$this->material = array();
	}
	
	function setShowdraw($a_showdraw)
	{
		switch (strtolower($a_showdraw))
		{
			case "1":
			case "no":
				$this->showdraw = SHOWDRAW_NO;
				break;
			case "2":
			case "yes":
				$this->showdraw = SHOWDRAW_YES;
				break;
		}
	}
	
	function getShowdraw()
	{
		return $this->showdraw;
	}
	
	function setMinnumber($a_minnumber)
	{
		$this->minnumber = $a_minnumber;
	}
	
	function getMinnumber()
	{
		return $this->minnumber;
	}
	
	function setMaxnumber($a_maxnumber)
	{
		$this->maxnumber = $a_maxnumber;
	}
	
	function getMaxnumber()
	{
		return $this->maxnumber;
	}
	
	function addResponseLabel($a_response_label)
	{
		array_push($this->response_labels, $a_response_label);
	}

	function addMaterial($a_material)
	{
		array_push($this->material, $a_material);
	}
}
?>
