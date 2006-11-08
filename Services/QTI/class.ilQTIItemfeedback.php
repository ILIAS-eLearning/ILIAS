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

define ("VIEW_ALL", "1");
define ("VIEW_ADMINISTRATOR", "2");
define ("VIEW_ADMINAUTHORITY", "3");
define ("VIEW_ASSESSOR", "4");
define ("VIEW_AUTHOR", "5");
define ("VIEW_CANDIDATE", "6");
define ("VIEW_INVIGILATORPROCTOR", "7");
define ("VIEW_PSYCHOMETRICIAN", "8");
define ("VIEW_SCORER", "9");
define ("VIEW_TUTOR", "10");

/**
* QTI itemfeedback class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIItemfeedback
{
	var $view;
	var $ident;
	var $title;
	var $flow_mat;
	var $material;
	var $solution;
	var $hint;
	
	function ilQTIItemfeedback()
	{
		$this->flow_mat = array();
		$this->material = array();
		$this->solution = array();
		$this->hint = array();
	}
	
	function setView($a_view)
	{
		switch (strtolower($a_view))
		{
			case "1":
			case "all":
				$this->view = VIEW_ALL;
				break;
			case "2":
			case "administrator":
				$this->view = VIEW_ADMINISTRATOR;
				break;
			case "3":
			case "adminauthority":
				$this->view = VIEW_ADMINAUTHORITY;
				break;
			case "4":
			case "assessor":
				$this->view = VIEW_ASSESSOR;
				break;
			case "5":
			case "author":
				$this->view = VIEW_AUTHOR;
				break;
			case "6":
			case "candidate":
				$this->view = VIEW_CANDIDATE;
				break;
			case "7":
			case "invigilatorproctor":
				$this->view = VIEW_INVIGILATORPROCTOR;
				break;
			case "8":
			case "psychometrician":
				$this->view = VIEW_PSYCHOMETRICIAN;
				break;
			case "9":
			case "scorer":
				$this->view = VIEW_SCORER;
				break;
			case "10":
			case "tutor":
				$this->view = VIEW_TUTOR;
				break;
		}
	}
	
	function getView()
	{
		return $this->view;
	}
	
	function setIdent($a_ident)
	{
		$this->ident = $a_ident;
	}
	
	function getIdent()
	{
		return $this->ident;
	}
	
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	function getTitle()
	{
		return $this->title;
	}
	
	function addFlow_mat($a_flow_mat)
	{
		array_push($this->flow_mat, $a_flow_mat);
	}
	
	function addMaterial($a_material)
	{
		array_push($this->material, $a_material);
	}
	
	function addSolution($a_solution)
	{
		array_push($this->solution, $a_solution);
	}
	
	function addHint($a_hint)
	{
		array_push($this->hint, $a_hint);
	}
}
?>
