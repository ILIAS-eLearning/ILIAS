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
* Class for matching question terms
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingTerm
{
	protected $arrData;

	/**
	* assAnswerMatchingTerm constructor
	*
	* @param string $text Definition text
	* @param string $picture Definition picture
	* @param integer $identifier Random number identifier
	*/
	function __construct($text = "", $picture = "", $identifier = "")
	{
		if (strlen($identifier) == 0)
		{
			mt_srand((double)microtime()*1000000);
			$identifier = mt_rand(1, 100000);
		}
		$this->arrData = array(
			'text' => $text,
			'picture' => $picture,
			'identifier' => $identifier
		);
	}

	/**
	* Object getter
	*/
	protected function __get($value)
	{
		switch ($value)
		{
			case "text":
			case "picture":
				if (strlen($this->arrData[$value]))
				{
					return $this->arrData[$value];
				}
				else
				{
					return null;
				}
				break;
			case "identifier":
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
	protected function __set($key, $value)
	{
		switch ($key)
		{
			case "text":
			case "picture":
			case "identifier":
				$this->arrData[$key] = $value;
				break;
			default:
				break;
		}
	}
}

?>
