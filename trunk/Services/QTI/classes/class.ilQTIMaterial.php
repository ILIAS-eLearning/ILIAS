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
* QTI material class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMaterial
{
	var $label;
	var $flow;
	var $comment;
	var $mattext;
	var $matemtext;
	var $matimage;
	var $mataudio;
	var $matvideo;
	var $matapplet;
	var $matapplication;
	var $matref;
	var $matbreak;
	var $mat_extension;
	var $altmaterial;
	var $materials;
	
	function ilQTIMaterial()
	{
		$this->flow = 0;
		$this->altmaterial = array();
		$this->materials = array();
	}
	
	function addMattext($a_mattext)
	{
		array_push($this->materials, array("material" => $a_mattext, "type" => "mattext"));
	}

	function addMatimage($a_matimage)
	{
		array_push($this->materials, array("material" => $a_matimage, "type" => "matimage"));
	}

	function addMatemtext($a_matemtext)
	{
		array_push($this->materials, array("material" => $a_matemtext, "type" => "matemtext"));
	}

	function addMataudio($a_mataudio)
	{
		array_push($this->materials, array("material" => $a_mataudio, "type" => "mataudio"));
	}

	function addMatvideo($a_matvideo)
	{
		array_push($this->materials, array("material" => $a_matvideo, "type" => "matvideo"));
	}

	function addMatapplet($a_matapplet)
	{
		array_push($this->materials, array("material" => $a_matapplet, "type" => "matapplet"));
	}

	function addMatapplication($a_matapplication)
	{
		array_push($this->materials, array("material" => $a_matapplication, "type" => "matapplication"));
	}

	function addMatref($a_matref)
	{
		array_push($this->materials, array("material" => $a_matref, "type" => "matref"));
	}

	function addMatbreak($a_matbreak)
	{
		array_push($this->materials, array("material" => $a_matbreak, "type" => "matbreak"));
	}

	function addMat_extension($a_mat_extension)
	{
		array_push($this->materials, array("material" => $a_mat_extension, "type" => "mat_extension"));
	}

	function addAltmaterial($a_altmaterial)
	{
		array_push($this->materials, array("material" => $a_altmaterial, "type" => "altmaterial"));
	}
	
	function getMaterialCount()
	{
		return count($this->materials);
	}
	
	function getMaterial($a_index)
	{
		if (array_key_exists($a_index, $this->materials))
		{
			return $this->materials[$a_index];
		}
		else
		{
			return FALSE;
		}
	}
	
	function setFlow($a_flow)
	{
		$this->flow = $a_flow;
	}
	
	function getFlow()
	{
		return $this->flow;
	}
	
	function setLabel($a_label)
	{
		$this->label = $a_label;
	}
	
	function getLabel()
	{
		return $this->label;
	}
	
	function extractText()
	{
		$text = "";
		if ($this->getMaterialCount())
		{
			foreach ($this->materials as $mat)
			{
				if (strcmp($mat["type"], "mattext") == 0)
				{
					$text .= $mat["material"];
				}
			}
		}
		return $text;
	}
}
?>
