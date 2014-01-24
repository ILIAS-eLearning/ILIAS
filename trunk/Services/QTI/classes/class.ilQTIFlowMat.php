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
* QTI flow_mat class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIFlowMat
{
	var $comment;
	var $flow_mat;
	var $material;
	var $material_ref;
	
	function ilQTIFlowMat()
	{
		$this->flow_mat = array();
		$this->material = array();
		$this->material_ref = array();
	}

	function setComment($a_comment)
	{
		$this->comment = $a_comment;
	}
	
	function getComment()
	{
		return $this->comment;
	}
	
	function addFlow_mat($a_flow_mat)
	{
		array_push($this->flow_mat, $a_flow_mat);
	}
	
	function addMaterial($a_material)
	{
		array_push($this->material, $a_material);
	}
	
	function addMaterial_ref($a_material_ref)
	{
		array_push($this->material_ref, $a_material_ref);
	}
}
?>
