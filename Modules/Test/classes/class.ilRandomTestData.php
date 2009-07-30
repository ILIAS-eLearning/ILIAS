<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a random test input property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ModulesTest
*/
class ilRandomTestData
{
	protected $data = array();
	
	/**
	* Constructor
	*
	* @param	string	$a_count	Question count
	* @param	string	$a_qpl	Questionpool id
	*/
	function __construct($a_count = "", $a_qpl = "")
	{
		$this->data = array('count' => $a_count, 'qpl' => $a_qpl);
	}

	public function __get($property)
	{
		switch ($property)
		{
			case 'count':
				if ((strlen($this->data[$property]) == 0) || (!is_numeric($this->data[$property]))) return 0;
				return $this->data[$property];
				break;
			case 'qpl':
				return $this->data[$property];
				break;
			default:
				return null;
				break;
		}
	}
	
	public function __set($property, $value)
	{
		switch ($property)
		{
			case 'count':
			case 'qpl':
				$this->data[$property] = $value;
				break;
			default:
				break;
		}
	}
}
