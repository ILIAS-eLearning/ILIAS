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
* QTI conditionvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIConditionvar
{	
	var not;
	var and;
	var or;
	var unanswered;
	var other;
	var varequal;
	var varlt;
	var varlte;
	var vargt;
	var vargte;
	var varsubset;
	var varinside;
	var varsubstring;
	var durequal;
	var durlt;
	var durlte;
	var durgt;
	var durgte;
	var varextension;
	
	function ilQTIConditionvar()
	{
		$this->not = array();
		$this->and = array();
		$this->or = array();
		$this->unanswered = array();
		$this->other = array();
		$this->varequal = array();
		$this->varlt = array();
		$this->varlte = array();
		$this->vargt = array();
		$this->vargte = array();
		$this->varsubset = array();
		$this->varinside = array();
		$this->varsubstring = array();
		$this->durequal = array();
		$this->durlt = array();
		$this->durlte = array();
		$this->durgt = array();
		$this->durgte = array();
		$this->varextension = array();
	}
	
	function addNot($a_not)
	{
		array_push($this->not, $a_not);
	}
	
	function addAnd($a_and)
	{
		array_push($this->and, $a_and);
	}

	function addOr($a_or)
	{
		array_push($this->or, $a_or);
	}
	
	function addUnanswered($a_unanswered)
	{
		array_push($this->unanswered, $a_unanswered);
	}
	
	function addOther($a_other)
	{
		array_push($this->other, $a_other);
	}
	
	function addVarequal($a_varequal)
	{
		array_push($this->varequal, $a_varequal);
	}
	
	function addVarlt($a_varlt)
	{
		array_push($this->varlt, $a_varlt);
	}
	
	function addVarlte($a_varlte)
	{
		array_push($this->varlte, $a_varlte);
	}
	
	function addVargt($a_vargt)
	{
		array_push($this->vargt, $a_vargt);
	}
	
	function addVargte($a_vargte)
	{
		array_push($this->vargte, $a_vargte);
	}
	
	function addVarsubset($a_varsubset)
	{
		array_push($this->varsubset, $a_varsubset);
	}
	
	function addVarinside($a_varinside)
	{
		array_push($this->varinside, $a_varinside);
	}
	
	function addVarsubstring($a_varsubstring)
	{
		array_push($this->varsubstring, $a_varsubstring);
	}
	
	function addDurequal($a_durequal)
	{
		array_push($this->durequal, $a_durequal);
	}
	
	function addDurlt($a_durlt)
	{
		array_push($this->durlt, $a_durlt);
	}
	
	function addDurlte($a_durlte)
	{
		array_push($this->durlte, $a_durlte);
	}
	
	function addDurgt($a_durgt)
	{
		array_push($this->durgt, $a_durgt);
	}
	
	function addDurgte($a_durgte)
	{
		array_push($this->durgte, $a_durgte);
	}
	
	function addVarextension($a_varextension)
	{
		array_push($this->varextension, $a_varextension);
	}
	
	function addResponseVar($a_responsevar)
	{
		switch ($a_responsevar->getVartype())
		{
			case RESPONSEVAR_EQUAL:
				$this->addVarequal($a_responsevar);
				break;
			case RESPONSEVAR_LT:
				$this->addVarlt($a_responsevar);
				break;			
			case RESPONSEVAR_LTE:
				$this->addVarlte($a_responsevar);
				break;
			case RESPONSEVAR_GT:
				$this->addVargt($a_responsevar);
				break;
			case RESPONSEVAR_GTE:
				$this->addVargte($a_responsevar);
				break;
			case RESPONSEVAR_SUBSET:
				$this->addVarsubset($a_responsevar);
				break;
			case RESPONSEVAR_INSIDE:
				$this->addVarinside($a_responsevar);
				break;
			case RESPONSEVAR_SUBSTRING:
				$this->addVarsubstring($a_responsevar);
				break;
		}
	}
}
?>
