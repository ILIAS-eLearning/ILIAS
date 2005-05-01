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

	define ("RT_RESPONSE_LID", "1");
	define ("RT_RESPONSE_XY", "2");
	define ("RT_RESPONSE_STR", "3");
	define ("RT_RESPONSE_NUM", "4");
	define ("RT_RESPONSE_GRP", "5");
	define ("RT_RESPONSE_EXTENSION", "6");
	
	define ("R_CARDINALITY_SINGLE", "1");
	define ("R_CARDINALITY_MULTIPLE", "2");
	define ("R_CARDINALITY_ORDERED", "3");

/**
* QTI response class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponse
{
	var $response_type;
	var $ident;
	var $rcardinality;
	var $render_choices;
	
	function ilQTIResponse($a_response_type = 0)
	{
		$this->render_choices = array();
		$this->response_type = $a_response_type;
	}
	
	function setResponsetype($a_responsetype)
	{
		$this->response_type = $a_responsetype;
	}
	
	function getResponsetype()
	{
		return $this->response_type;
	}
	
	function setIdent($a_ident)
	{
		$this->ident = $a_ident;
	}
	
	function getIdent()
	{
		return $this->ident;
	}
	
	function setRCardinality($a_rcardinality)
	{
		switch (strtolower($a_rcardinality))
		{
			case "single":
			case "1":
				$this->rcardinality = R_CARDINALITY_SINGLE;
				break;
			case "multiple":
			case "2":
				$this->rcardinality = R_CARDINALITY_MULTIPLE;
				break;
			case "ordered":
			case "3":
				$this->rcardinality = R_CARDINALITY_ORDERED;
				break;
		}
	}
	
	function getRCardinality()
	{
		return $this->rcardinality;
	}
	
	function addRenderChoice($a_render_choice)
	{
		array_push($this->render_choices, $a_render_choice);
	}
}
?>
