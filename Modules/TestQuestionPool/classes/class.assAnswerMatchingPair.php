<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. | 
   +----------------------------------------------------------------------------+
*/

/**
* Class for matching question pairs
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPair
{
	protected $arrData;

	/**
	* assAnswerMatchingPair constructor
	*
	* @param string $text Definition text
	* @param string $picture Definition picture
	* @param integer $identifier Random number identifier
	*/
	function __construct($term = null, $definition = null, $points = 0.0)
	{
		$this->arrData = array(
			'term' => $term,
			'definition' => $definition,
			'points' => $points
		);
	}

	/**
	* Object getter
	*/
	public function __get($value)
	{
		switch ($value)
		{
			case "term":
			case "definition":
			case "points":
				return $this->arrData[$value];
				break;
			default:
				return null;
				break;
		}
	}

	/**
	* Object setter
	*/
	public function __set($key, $value)
	{
		switch ($key)
		{
			case "term":
			case "definition":
			case "points":
				$this->arrData[$key] = $value;
				break;
			default:
				break;
		}
	}
}

?>
