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
	var $arr_not;
	var $arr_and;
	var $arr_or;
	var $unanswered;
	var $other;
	var $varequal;
	var $varlt;
	var $varlte;
	var $vargt;
	var $vargte;
	var $varsubset;
	var $varinside;
	var $varsubstring;
	var $durequal;
	var $durlt;
	var $durlte;
	var $durgt;
	var $durgte;
	var $varextension;
	var $order;
	
	function ilQTIConditionvar()
	{
		$this->arr_not = array();
		$this->arr_and = array();
		$this->arr_or = array();
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
		$this->order = array();
	}
	
	function addNot()
	{
		array_push($this->arr_not, 1);
		array_push($this->order, array("field" => "arr_not", "index" => count($this->arr_not) - 1));
	}
	
	function addAnd()
	{
		array_push($this->arr_and, 1);
		array_push($this->order, array("field" => "arr_and", "index" => count($this->arr_and) - 1));
	}

	function addOr()
	{
		array_push($this->arr_or, 1);
		array_push($this->order, array("field" => "arr_or", "index" => count($this->arr_or) - 1));
	}
	
	function addUnanswered($a_unanswered)
	{
		array_push($this->unanswered, $a_unanswered);
		array_push($this->order, array("field" => "unanswered", "index" => count($this->unanswered) - 1));
	}
	
	function addOther($a_other)
	{
		array_push($this->other, $a_other);
		array_push($this->order, array("field" => "other", "index" => count($this->other) - 1));
	}
	
	function addVarequal($a_varequal)
	{
		array_push($this->varequal, $a_varequal);
		array_push($this->order, array("field" => "varequal", "index" => count($this->varequal) - 1));
	}
	
	function addVarlt($a_varlt)
	{
		array_push($this->varlt, $a_varlt);
		array_push($this->order, array("field" => "varlt", "index" => count($this->varlt) - 1));
	}
	
	function addVarlte($a_varlte)
	{
		array_push($this->varlte, $a_varlte);
		array_push($this->order, array("field" => "varlte", "index" => count($this->varlte) - 1));
	}
	
	function addVargt($a_vargt)
	{
		array_push($this->vargt, $a_vargt);
		array_push($this->order, array("field" => "vargt", "index" => count($this->vargt) - 1));
	}
	
	function addVargte($a_vargte)
	{
		array_push($this->vargte, $a_vargte);
		array_push($this->order, array("field" => "vargte", "index" => count($this->vargte) - 1));
	}
	
	function addVarsubset($a_varsubset)
	{
		array_push($this->varsubset, $a_varsubset);
		array_push($this->order, array("field" => "varsubset", "index" => count($this->varsubset) - 1));
	}
	
	function addVarinside($a_varinside)
	{
		array_push($this->varinside, $a_varinside);
		array_push($this->order, array("field" => "varinside", "index" => count($this->varinside) - 1));
	}
	
	function addVarsubstring($a_varsubstring)
	{
		array_push($this->varsubstring, $a_varsubstring);
		array_push($this->order, array("field" => "varsubstring", "index" => count($this->varsubstring) - 1));
	}
	
	function addDurequal($a_durequal)
	{
		array_push($this->durequal, $a_durequal);
		array_push($this->order, array("field" => "durequal", "index" => count($this->durequal) - 1));
	}
	
	function addDurlt($a_durlt)
	{
		array_push($this->durlt, $a_durlt);
		array_push($this->order, array("field" => "durlt", "index" => count($this->durlt) - 1));
	}
	
	function addDurlte($a_durlte)
	{
		array_push($this->durlte, $a_durlte);
		array_push($this->order, array("field" => "durlte", "index" => count($this->durlte) - 1));
	}
	
	function addDurgt($a_durgt)
	{
		array_push($this->durgt, $a_durgt);
		array_push($this->order, array("field" => "durgt", "index" => count($this->durgt) - 1));
	}
	
	function addDurgte($a_durgte)
	{
		array_push($this->durgte, $a_durgte);
		array_push($this->order, array("field" => "durgte", "index" => count($this->durgte) - 1));
	}
	
	function addVarextension($a_varextension)
	{
		array_push($this->varextension, $a_varextension);
		array_push($this->order, array("field" => "varextension", "index" => count($this->varextension) - 1));
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
