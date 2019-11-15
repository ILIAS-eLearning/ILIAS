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

define ("PROMPT_BOX", "1");
define ("PROMPT_DASHLINE", "2");
define ("PROMPT_ASTERISK", "3");
define ("PROMPT_UNDERLINE", "4");

define ("FIBTYPE_STRING", "1");
define ("FIBTYPE_INTEGER", "2");
define ("FIBTYPE_DECIMAL", "3");
define ("FIBTYPE_SCIENTIFIC", "4");

/**
* QTI render fib class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderFib
{
	var $minnumber;
	var $maxnumber;
	var $response_labels;
	var $material;
	var $prompt;
	var $encoding;
	var $fibtype;
	var $rows;
	var $maxchars;
	var $columns;
	var $charset;

	function __construct()
	{
		$this->response_labels = array();
		$this->material = array();
		$this->encoding = "UTF-8";
	}
	
	function setPrompt($a_prompt)
	{
		switch (strtolower($a_prompt))
		{
			case "1":
			case "box":
				$this->prompt = PROMPT_BOX;
				break;
			case "2":
			case "dashline":
				$this->prompt = PROMPT_DASHLINE;
				break;
			case "3":
			case "asterisk":
				$this->prompt = PROMPT_ASTERISK;
				break;
			case "4":
			case "underline":
				$this->prompt = PROMPT_UNDERLINE;
				break;
		}
	}
	
	function getPrompt()
	{
		return $this->prompt;
	}
	
	function setFibtype($a_fibtype)
	{
		switch (strtolower($a_fibtype))
		{
			case "1":
			case "string":
				$this->fibtype = FIBTYPE_STRING;
				break;
			case "2":
			case "integer":
				$this->fibtype = FIBTYPE_INTEGER;
				break;
			case "3":
			case "decimal":
				$this->fibtype = FIBTYPE_DECIMAL;
				break;
			case "4":
			case "scientific":
				$this->fibtype = FIBTYPE_SCIENTIFIC;
				break;
		}
	}
	
	function getFibtype()
	{
		return $this->fibtype;
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
	
	function setEncoding($a_encoding)
	{
		$this->encoding = $a_encoding;
	}
	
	function getEncoding()
	{
		return $this->encoding;
	}

	function setRows($a_rows)
	{
		$this->rows = $a_rows;
	}
	
	function getRows()
	{
		return $this->rows;
	}

	function setMaxchars($a_maxchars)
	{
		$this->maxchars = $a_maxchars;
	}
	
	function getMaxchars()
	{
		return $this->maxchars;
	}

	function setColumns($a_columns)
	{
		$this->columns = $a_columns;
	}
	
	function getColumns()
	{
		return $this->columns;
	}

	function setCharset($a_charset)
	{
		$this->charset = $a_charset;
	}
	
	function getCharset()
	{
		return $this->charset;
	}
}
?>
